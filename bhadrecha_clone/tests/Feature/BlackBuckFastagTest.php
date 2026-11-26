<?php

namespace Tests\Feature;

use App\Models\FastagSyncLog;
use App\Models\FastagTransaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BlackBuckFastagService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BlackBuckFastagTest extends TestCase
{
    protected BlackBuckFastagService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
            'blackbuck.base_url' => 'https://api-fms.blackbuck.test',
            'blackbuck.transaction_endpoint' => '/fmstoll/api/lfo-transaction-details/transactions',
            'blackbuck.token' => 'VALID_TEST_TOKEN_SECRET',
            'blackbuck.phone_number' => '9829894925',
            'blackbuck.timeout' => 5,
            'blackbuck.retry_count' => 1,
            'blackbuck.retry_delay_ms' => 10,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->setUpDatabase();

        $this->service = new BlackBuckFastagService();
    }

    protected function setUpDatabase(): void
    {
        Schema::dropIfExists('fastag_transactions');
        Schema::dropIfExists('fastag_sync_logs');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_roles');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('slug')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('admin');
            $table->string('status')->default('active');
            $table->string('avatar')->nullable();
            $table->string('bio')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number')->unique();
            $table->string('vehicle_type')->nullable();
            $table->string('make_model')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fastag_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('vehicle_number', 50);
            $table->string('transaction_id', 150)->unique();
            $table->string('tag_id', 100)->nullable();
            $table->string('toll_plaza_name', 255)->nullable();
            $table->string('transaction_type', 50)->default('DEBIT');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->dateTime('transaction_date');
            $table->string('transaction_status', 50)->default('SUCCESS');
            $table->string('lane_code', 50)->nullable();
            $table->string('plaza_code', 50)->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });

        Schema::create('fastag_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number', 50)->nullable();
            $table->dateTime('sync_from')->nullable();
            $table->dateTime('sync_to')->nullable();
            $table->string('sync_status', 50)->default('PENDING');
            $table->unsignedInteger('total_records_received')->default(0);
            $table->unsignedInteger('total_records_saved')->default(0);
            $table->text('error_message')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Test 1: Valid API token & Token Authorization header.
     */
    public function test_valid_api_token_header(): void
    {
        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response(['data' => []], 200),
        ]);

        $this->service->fetchTransactions(['RJ19GJ3506'], now()->subDays(1), now());

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Token VALID_TEST_TOKEN_SECRET')
                && $request->header('Content-Type')[0] === 'application/json'
                && $request->method() === 'POST';
        });
    }

    /**
     * Test 2: Invalid API token handling.
     */
    public function test_invalid_api_token(): void
    {
        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response([
                'status' => 400,
                'message' => '{"status":false,"error":{"code":"BAD_REQUEST","text":"Invalid authentication token"}}',
            ], 400),
        ]);

        $res = $this->service->syncVehicles(['RJ19GJ3506']);

        $this->assertFalse($res['success']);
        $this->assertEquals('FAILED', $res['status']);
        $this->assertStringContainsString('Invalid authentication token', $res['error']);

        $this->assertDatabaseHas('fastag_sync_logs', [
            'vehicle_number' => 'RJ19GJ3506',
            'sync_status' => 'FAILED',
        ]);
    }

    /**
     * Test 3: Single truck request format & payload verification.
     */
    public function test_single_truck_request(): void
    {
        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response(['data' => []], 200),
        ]);

        $this->service->fetchTransactions(['RJ19GJ3506']);

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            return $data['truck_filter'] === 'SELECTIVE'
                && $data['truck_numbers'] === ['RJ19GJ3506']
                && $data['phone_number'] === 9829894925;
        });
    }

    /**
     * Test 4: Multiple truck request format & payload verification.
     */
    public function test_multiple_truck_request(): void
    {
        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response(['data' => []], 200),
        ]);

        $trucks = ['RJ19GJ3506', 'RJ09GA1234', 'RJ27GB9999'];
        $this->service->fetchTransactions($trucks);

        Http::assertSent(function (Request $request) use ($trucks) {
            $data = $request->data();
            return $data['truck_filter'] === 'SELECTIVE'
                && $data['truck_numbers'] === $trucks;
        });
    }

    /**
     * Test 5: Custom date range support.
     */
    public function test_custom_date_range(): void
    {
        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response(['data' => []], 200),
        ]);

        $from = '2026-11-01 10:00:00';
        $to = '2026-11-05 18:30:00';

        $this->service->fetchTransactions(['RJ19GJ3506'], $from, $to);

        $expectedFromMs = Carbon::parse($from)->timestamp * 1000;
        $expectedToMs = Carbon::parse($to)->timestamp * 1000;

        Http::assertSent(function (Request $request) use ($expectedFromMs, $expectedToMs) {
            $data = $request->data();
            return $data['from_time'] === $expectedFromMs
                && $data['to_time'] === $expectedToMs;
        });
    }

    /**
     * Test 6: Unix timestamp conversion in milliseconds (13 digits, never seconds).
     */
    public function test_unix_timestamp_conversion_in_milliseconds(): void
    {
        $dateStr = '2026-11-14 12:00:00';
        $ms = $this->service->toMilliseconds($dateStr);

        // 13 digits check
        $this->assertIsInt($ms);
        $this->assertGreaterThan(1000000000000, $ms);
        $this->assertEquals(strlen((string) $ms), 13);

        // Carbon instance test
        $carbon = Carbon::parse('2026-11-14 12:00:00');
        $msFromCarbon = $this->service->toMilliseconds($carbon);
        $this->assertEquals($ms, $msFromCarbon);

        // Raw milliseconds passthrough check
        $rawMs = 1794444318000;
        $this->assertEquals(1794444318000, $this->service->toMilliseconds($rawMs));
    }

    /**
     * Test 7: Successful transaction response & database insertion.
     */
    public function test_successful_transaction_response(): void
    {
        Vehicle::create([
            'vehicle_number' => 'RJ19GJ3506',
            'make_model' => 'Tata LPT',
        ]);

        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response([
                'data' => [
                    [
                        'transaction_id' => 'TXN_BB_1001',
                        'truck_number' => 'RJ19GJ3506',
                        'toll_plaza_name' => 'Kherwara Toll Plaza',
                        'amount' => 310.00,
                        'transaction_time' => 1794444318000,
                        'tag_id' => 'TAG123456',
                        'status' => 'SUCCESS',
                        'type' => 'DEBIT',
                    ],
                ],
            ], 200),
        ]);

        $res = $this->service->syncVehicles(['RJ19GJ3506']);

        $this->assertTrue($res['success']);
        $this->assertEquals(1, $res['records_received']);
        $this->assertEquals(1, $res['records_saved']);

        $this->assertDatabaseHas('fastag_transactions', [
            'transaction_id' => 'TXN_BB_1001',
            'vehicle_number' => 'RJ19GJ3506',
            'toll_plaza_name' => 'Kherwara Toll Plaza',
            'amount' => 310.00,
            'transaction_status' => 'SUCCESS',
            'transaction_type' => 'DEBIT',
        ]);
    }

    /**
     * Test 8: Empty transaction response handling.
     */
    public function test_empty_transaction_response(): void
    {
        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $res = $this->service->syncVehicles(['RJ19GJ3506']);

        $this->assertTrue($res['success']);
        $this->assertEquals(0, $res['records_received']);
        $this->assertEquals(0, $res['records_saved']);
        $this->assertEquals('SUCCESS', $res['status']);
    }

    /**
     * Test 9: Duplicate transaction handling via updateOrCreate.
     */
    public function test_duplicate_transaction_handling(): void
    {
        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response([
                'data' => [
                    [
                        'transaction_id' => 'TXN_DUP_SAFE_1',
                        'truck_number' => 'RJ19GJ3506',
                        'amount' => 150.00,
                        'toll_plaza_name' => 'Pindwara Plaza',
                        'transaction_time' => 1794444318000,
                    ],
                ],
            ], 200),
        ]);

        // Sync first time
        $this->service->syncVehicles(['RJ19GJ3506']);
        $this->assertEquals(1, FastagTransaction::count());

        // Sync second time with same transaction
        $this->service->syncVehicles(['RJ19GJ3506']);

        // Must remain exactly 1, no duplicate keys
        $this->assertEquals(1, FastagTransaction::count());
        $this->assertEquals(1, FastagTransaction::where('transaction_id', 'TXN_DUP_SAFE_1')->count());
    }

    /**
     * Test 10: API timeout handling.
     */
    public function test_api_timeout(): void
    {
        Http::fake([
            'https://api-fms.blackbuck.test/*' => function () {
                throw new ConnectionException('cURL error 28: Connection timed out after 60000 milliseconds');
            },
        ]);

        $res = $this->service->syncVehicles(['RJ19GJ3506']);

        $this->assertFalse($res['success']);
        $this->assertEquals('FAILED', $res['status']);
        $this->assertStringContainsString('timed out', $res['error']);

        $this->assertDatabaseHas('fastag_sync_logs', [
            'vehicle_number' => 'RJ19GJ3506',
            'sync_status' => 'FAILED',
        ]);
    }

    /**
     * Test 11: Database storage & raw response JSON preservation.
     */
    public function test_database_storage_and_raw_json(): void
    {
        $rawPayload = [
            'transaction_id' => 'TXN_STORE_999',
            'truck_number' => 'RJ19GJ3506',
            'amount' => 450.75,
            'toll_plaza_name' => 'Ratanpur Border Plaza',
            'transaction_time' => 1794444318000,
            'lane_code' => 'L03',
            'plaza_code' => 'PLZ_99',
            'status' => 'SUCCESS',
            'custom_blackbuck_field' => 'debug_data_123',
        ];

        Http::fake([
            'https://api-fms.blackbuck.test/*' => Http::response([
                'data' => [$rawPayload],
            ], 200),
        ]);

        $this->service->syncVehicles(['RJ19GJ3506']);

        $txn = FastagTransaction::where('transaction_id', 'TXN_STORE_999')->first();
        $this->assertNotNull($txn);
        $this->assertEquals('RJ19GJ3506', $txn->vehicle_number);
        $this->assertEquals(450.75, $txn->amount);
        $this->assertEquals('L03', $txn->lane_code);
        $this->assertEquals('PLZ_99', $txn->plaza_code);
        $this->assertIsArray($txn->raw_response);
        $this->assertEquals('debug_data_123', $txn->raw_response['custom_blackbuck_field']);
    }

    /**
     * Test 12: Transaction listing and filters.
     */
    public function test_transaction_listing_and_filters(): void
    {
        $user = User::create(['email' => 'admin_test@test.com', 'role' => 'admin']);
        $this->actingAs($user);

        FastagTransaction::create([
            'transaction_id' => 'TXN_FILTER_1',
            'vehicle_number' => 'RJ19GJ3506',
            'amount' => 200.00,
            'toll_plaza_name' => 'Udaipur Plaza',
            'transaction_type' => 'DEBIT',
            'transaction_status' => 'SUCCESS',
            'transaction_date' => '2026-11-14 10:00:00',
        ]);

        FastagTransaction::create([
            'transaction_id' => 'TXN_FILTER_2',
            'vehicle_number' => 'RJ09GA1234',
            'amount' => 120.00,
            'toll_plaza_name' => 'Jaipur Plaza',
            'transaction_type' => 'DEBIT',
            'transaction_status' => 'SUCCESS',
            'transaction_date' => '2026-11-14 11:00:00',
        ]);

        // Filter by vehicle number
        $response = $this->getJson(route('admin.fastag.index', ['vehicle_number' => 'RJ19GJ3506']));
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('TXN_FILTER_1', $data[0]['transaction_id']);

        // Filter by plaza name
        $responsePlaza = $this->getJson(route('admin.fastag.index', ['plaza' => 'Jaipur']));
        $responsePlaza->assertStatus(200);
        $dataPlaza = $responsePlaza->json('data');
        $this->assertCount(1, $dataPlaza);
        $this->assertEquals('TXN_FILTER_2', $dataPlaza[0]['transaction_id']);
    }
}
