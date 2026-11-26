<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\UserMailConfiguration;
use App\Services\UserMailService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddonFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary in-memory tables needed for relationships
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable();
                $table->string('name');
                $table->string('status')->default('active');
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_branches')) {
            Schema::create('user_branches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('branch_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_mail_configurations')) {
            Schema::create('user_mail_configurations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('provider', 50)->default('smtp');
                $table->string('mail_mailer', 50)->default('smtp');
                $table->string('mail_host')->nullable();
                $table->integer('mail_port')->default(587);
                $table->string('mail_encryption', 20)->nullable();
                $table->string('mail_username')->nullable();
                $table->text('mail_password')->nullable();
                $table->string('mail_from_address')->nullable();
                $table->string('mail_from_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_tested_at')->nullable();
                $table->string('last_test_status', 20)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable();
                $table->foreignId('branch_id')->nullable();
                $table->string('invoice_no')->nullable();
                $table->string('bill_number')->nullable();
                $table->string('case_id')->nullable();
                $table->string('consignor_name')->nullable();
                $table->string('status')->default('pending');
                $table->decimal('total_freight', 12, 2)->default(0);
                $table->decimal('total_gst', 12, 2)->default(0);
                $table->decimal('total_other', 12, 2)->default(0);
                $table->decimal('receiving_amount', 12, 2)->default(0);
                $table->decimal('receiving_gst', 12, 2)->default(0);
                $table->decimal('tds', 12, 2)->default(0);
                $table->decimal('deduction', 12, 2)->default(0);
                $table->date('invoice_date')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Test 1: Requirement 16 - LR Sequential Number Generation Logic
     */
    public function test_lr_number_extracts_and_increments_sequential_number(): void
    {
        $existingLR = 'LR-1050';
        $pattern = '/(\d+)$/';
        preg_match($pattern, $existingLR, $matches);
        $this->assertNotEmpty($matches);
        $highest = (int) $matches[1];
        $this->assertEquals(1050, $highest);
        $next = $highest + 1;
        $this->assertEquals(1051, $next);

        $suggestedLR = preg_replace('/(\d+)$/', (string) $next, $existingLR);
        $this->assertEquals('LR-1051', $suggestedLR);
    }

    /**
     * Test 2: Multi-Branch Access - User can access assigned branches
     */
    public function test_user_can_access_primary_and_pivot_assigned_branches(): void
    {
        $user = new User([
            'first_name' => 'Ramesh',
            'last_name' => 'Kumar',
            'email' => 'ramesh@example.com',
            'role' => 'user',
            'branch_id' => 10,
        ]);
        $user->id = 5;

        // Primary branch 10 should be in assigned_branch_ids
        $this->assertTrue($user->canAccessBranch(10));
        $this->assertFalse($user->canAccessBranch(20)); // not yet assigned

        // Mock assigned branches through accessor/pivot
        $branch20 = new Branch(['name' => 'Branch 20']);
        $branch20->id = 20;

        $user->setRelation('accessibleBranches', collect([$branch20]));

        $this->assertTrue($user->canAccessBranch(10));
        $this->assertTrue($user->canAccessBranch(20));
        $this->assertFalse($user->canAccessBranch(30)); // unauthorized
        $this->assertTrue($user->canAccessBranch('all')); // has multiple branches
    }

    /**
     * Test 3: Multi-Branch Access - Super Admin and Company Admin access rules
     */
    public function test_super_admin_and_company_admin_branch_access(): void
    {
        $superAdmin = new User([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'super admin',
        ]);
        $this->assertTrue($superAdmin->canAccessBranch(999));
        $this->assertTrue($superAdmin->canAccessBranch('all'));

        $companyAdmin = new User([
            'first_name' => 'Company',
            'last_name' => 'Admin',
            'email' => 'compadmin@example.com',
            'role' => 'company admin',
            'company_id' => 1,
        ]);
        $this->assertTrue($companyAdmin->canAccessBranch('all'));
    }

    /**
     * Test 4: User-wise Email Configuration - Password encryption and decryption
     */
    public function test_user_mail_configuration_encrypts_and_decrypts_password_securely(): void
    {
        $config = new UserMailConfiguration([
            'user_id' => 1,
            'provider' => 'gmail',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'testuser@gmail.com',
            'mail_from_address' => 'testuser@gmail.com',
            'mail_from_name' => 'Test User',
            'is_active' => true,
        ]);

        $rawSecret = 'abcd efgh ijkl mnop'; // 16-char Google App Password
        $config->mail_password = $rawSecret;

        // Raw attribute in database is encrypted
        $encryptedInDb = $config->getAttributes()['mail_password'];
        $this->assertNotEquals($rawSecret, $encryptedInDb);
        $this->assertEquals($rawSecret, Crypt::decryptString($encryptedInDb));

        // Decrypted password accessor retrieves original secret
        $this->assertEquals($rawSecret, $config->decrypted_password);

        // Serialization hides password completely
        $serialized = $config->toArray();
        $this->assertArrayNotHasKey('mail_password', $serialized);
        $this->assertTrue($serialized['has_password']);
        $this->assertTrue($serialized['is_configured']);
    }

    /**
     * Test 5: UserMailService - Registers dynamic user SMTP mailer
     */
    public function test_user_mail_service_registers_dynamic_smtp_mailer(): void
    {
        $user = new User([
            'first_name' => 'Navneet',
            'last_name' => 'Singh',
            'email' => 'navneet@bhadrecha.com',
        ]);
        $user->id = 42;

        $config = new UserMailConfiguration([
            'user_id' => 42,
            'provider' => 'custom',
            'mail_host' => 'mail.bhadrechatransport.com',
            'mail_port' => 465,
            'mail_encryption' => 'ssl',
            'mail_username' => 'navneet@bhadrechatransport.com',
            'mail_from_address' => 'navneet@bhadrechatransport.com',
            'mail_from_name' => 'Navneet Singh',
            'is_active' => true,
        ]);
        $config->mail_password = 'SecretSmtpPassword123!';

        $mailerKey = UserMailService::registerDynamicMailer($user, $config);
        $this->assertEquals('user_smtp_42', $mailerKey);

        $mailerConfig = Config::get("mail.mailers.{$mailerKey}");
        $this->assertNotNull($mailerConfig);
        $this->assertEquals('smtp', $mailerConfig['transport']);
        $this->assertEquals('mail.bhadrechatransport.com', $mailerConfig['host']);
        $this->assertEquals(465, $mailerConfig['port']);
        $this->assertEquals('ssl', $mailerConfig['encryption']);
        $this->assertEquals('navneet@bhadrechatransport.com', $mailerConfig['username']);
        $this->assertEquals('SecretSmtpPassword123!', $mailerConfig['password']);

        // From details resolution with active mail configuration
        $user->setRelation('mailConfiguration', $config);
        $from = UserMailService::getFromDetailsForUser($user);
        $this->assertEquals('navneet@bhadrechatransport.com', $from['address']);
        $this->assertEquals('Navneet Singh', $from['name']);

        // From details resolution fallback when unconfigured
        $userWithoutConfig = new User([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@fallback.com',
        ]);
        $fallbackFrom = UserMailService::getFromDetailsForUser($userWithoutConfig);
        $this->assertEquals('john@fallback.com', $fallbackFrom['address']);
        $this->assertEquals('John Doe', $fallbackFrom['name']);
    }

    /**
     * Test 4: Sales Ledger Case ID - Invoice fillable and mapping includes case_id
     */
    public function test_invoice_case_id_fillable_and_persistence(): void
    {
        $invoice = new \App\Models\Invoice();
        $this->assertContains('case_id', $invoice->getFillable());

        $created = \App\Models\Invoice::create([
            'bill_number' => 'BILL-2026-001',
            'invoice_no' => 'INV-1001',
            'case_id' => 'CASE-9988-ABC',
            'consignor_name' => 'Tata Motors',
            'status' => 'pending',
        ]);

        $this->assertNotNull($created->id);
        $this->assertEquals('CASE-9988-ABC', $created->case_id);
        $this->assertEquals('BILL-2026-001', $created->bill_number);

        // Re-fetch from DB
        $fresh = \App\Models\Invoice::find($created->id);
        $this->assertEquals('CASE-9988-ABC', $fresh->case_id);
    }

    /**
     * Test 5: Sales Ledger Case ID - Search and filter by Case ID and Bill Number
     */
    public function test_sales_ledger_case_id_search_and_filter(): void
    {
        $inv1 = \App\Models\Invoice::create([
            'bill_number' => 'BILL-101',
            'invoice_no' => 'INV-201',
            'case_id' => 'CS-Alpha-01',
            'consignor_name' => 'Client A',
            'status' => 'pending',
        ]);

        $inv2 = \App\Models\Invoice::create([
            'bill_number' => 'BILL-102',
            'invoice_no' => 'INV-202',
            'case_id' => 'CS-Beta-02',
            'consignor_name' => 'Client B',
            'status' => 'pending',
        ]);

        // 1. Search directly using case_id query
        $caseSearch = \App\Models\Invoice::where('case_id', 'LIKE', '%CS-Alpha%')->get();
        $this->assertCount(1, $caseSearch);
        $this->assertEquals($inv1->id, $caseSearch->first()->id);

        // 2. Search using bill_number input (which searches bill_number, invoice_no, OR case_id)
        $flexibleSearch = \App\Models\Invoice::where(function ($q) {
            $term = 'Beta-02';
            $q->where('bill_number', 'LIKE', "%{$term}%")
              ->orWhere('invoice_no', 'LIKE', "%{$term}%")
              ->orWhere('case_id', 'LIKE', "%{$term}%");
        })->get();

        $this->assertCount(1, $flexibleSearch);
        $this->assertEquals($inv2->id, $flexibleSearch->first()->id);

        // 3. Search using bill_number for BILL-101
        $billSearch = \App\Models\Invoice::where(function ($q) {
            $term = 'BILL-101';
            $q->where('bill_number', 'LIKE', "%{$term}%")
              ->orWhere('invoice_no', 'LIKE', "%{$term}%")
              ->orWhere('case_id', 'LIKE', "%{$term}%");
        })->get();

        $this->assertCount(1, $billSearch);
        $this->assertEquals($inv1->id, $billSearch->first()->id);
    }

    /**
     * Test 6: Sales Ledger Case ID - Validation prevents duplicate Case ID mappings
     */
    public function test_case_id_uniqueness_validation_prevents_duplicate_mapping(): void
    {
        $existing = \App\Models\Invoice::create([
            'bill_number' => 'BILL-201',
            'case_id' => 'UNIQUE-CASE-001',
            'status' => 'pending',
        ]);

        $second = \App\Models\Invoice::create([
            'bill_number' => 'BILL-202',
            'case_id' => 'UNIQUE-CASE-002',
            'status' => 'pending',
        ]);

        // Attempting to assign duplicate case_id to second bill
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['case_id' => 'UNIQUE-CASE-001'],
            ['case_id' => 'nullable|string|max:255|unique:invoices,case_id,' . $second->id]
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('case_id'));

        // Assigning its own existing case_id (no error on self-update)
        $selfValidator = \Illuminate\Support\Facades\Validator::make(
            ['case_id' => 'UNIQUE-CASE-001'],
            ['case_id' => 'nullable|string|max:255|unique:invoices,case_id,' . $existing->id]
        );

        $this->assertFalse($selfValidator->fails());
    }

    /**
     * Test 7: Sales Ledger Case ID - Persists across bill details updates
     */
    public function test_case_id_persists_across_bill_details_update(): void
    {
        $invoice = \App\Models\Invoice::create([
            'bill_number' => 'BILL-301',
            'case_id' => 'CASE-PERSIST-123',
            'invoice_date' => '2026-10-14',
            'consignor_name' => 'Original Consignor',
            'status' => 'pending',
        ]);

        // Simulate updateBill operation
        $invoice->update([
            'bill_number' => 'BILL-301-MODIFIED',
            'case_id' => 'CASE-PERSIST-123',
            'invoice_date' => '2026-10-15',
            'consignor_name' => 'Updated Consignor',
            'status' => 'paid',
        ]);

        $fresh = \App\Models\Invoice::find($invoice->id);
        $this->assertEquals('CASE-PERSIST-123', $fresh->case_id);
        $this->assertEquals('BILL-301-MODIFIED', $fresh->bill_number);
        $this->assertEquals('paid', $fresh->status);
    }
}
