<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            if (!Schema::hasColumn('bulties', 'driver_id')) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('vehicle_id');
            }
        });

        DB::statement('UPDATE bulties b LEFT JOIN consignors c ON b.consignor_id = c.id SET b.consignor_id = NULL WHERE b.consignor_id IS NOT NULL AND c.id IS NULL');
        DB::statement('UPDATE bulties b LEFT JOIN consignees c ON b.consignee_id = c.id SET b.consignee_id = NULL WHERE b.consignee_id IS NOT NULL AND c.id IS NULL');
        DB::statement('UPDATE bulties b LEFT JOIN vehicles v ON b.vehicle_id = v.id SET b.vehicle_id = NULL WHERE b.vehicle_id IS NOT NULL AND v.id IS NULL');
        DB::statement('UPDATE bulties b LEFT JOIN drivers d ON b.driver_id = d.id SET b.driver_id = NULL WHERE b.driver_id IS NOT NULL AND d.id IS NULL');

        if (Schema::hasColumn('bulties', 'consignor_name')) {
            DB::statement('UPDATE bulties b JOIN consignors c ON b.consignor_id IS NULL AND b.consignor_name = c.name AND (b.consignor_phone IS NULL OR b.consignor_phone = c.phone) SET b.consignor_id = c.id');
        }

        if (Schema::hasColumn('bulties', 'consignee_name')) {
            DB::statement('UPDATE bulties b JOIN consignees c ON b.consignee_id IS NULL AND b.consignee_name = c.name AND (b.consignee_phone IS NULL OR b.consignee_phone = c.phone) SET b.consignee_id = c.id');
        }

        if (Schema::hasColumn('bulties', 'vehicle_number')) {
            DB::statement('UPDATE bulties b JOIN vehicles v ON b.vehicle_id IS NULL AND b.vehicle_number = v.vehicle_number SET b.vehicle_id = v.id');
        }

        if (Schema::hasColumn('bulties', 'driver_name')) {
            DB::statement('UPDATE bulties b JOIN drivers d ON b.driver_id IS NULL AND b.driver_name = d.name AND (b.driver_mobile IS NULL OR b.driver_mobile = d.phone) SET b.driver_id = d.id');
        }

        if (!Schema::hasTable('bulty_items')) {
            Schema::create('bulty_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bulty_id')->constrained('bulties')->onDelete('cascade');
                $table->foreignId('item_id')->nullable()->constrained('items')->onDelete('set null');
                $table->string('item_name')->nullable();
                $table->string('packaging_type')->nullable();
                $table->unsignedInteger('articles')->default(0);
                $table->decimal('weight', 10, 2)->default(0);
                $table->string('unit')->nullable();
                $table->decimal('freight_per_mt', 10, 2)->default(0);
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('bulties', 'items')) {
            DB::table('bulties')
                ->select('id', 'items')
                ->whereNotNull('items')
                ->orderBy('id')
                ->chunkById(100, function ($bulties) {
                    foreach ($bulties as $bulty) {
                        $items = json_decode($bulty->items, true);

                        if (!is_array($items)) {
                            continue;
                        }

                        foreach ($items as $item) {
                            if (empty($item['item_name'])) {
                                continue;
                            }

                            $masterItemId = DB::table('items')
                                ->where('name', $item['item_name'])
                                ->value('id');

                            DB::table('bulty_items')->insert([
                                'bulty_id' => $bulty->id,
                                'item_id' => $masterItemId,
                                'item_name' => $item['item_name'] ?? null,
                                'packaging_type' => $item['packaging_type'] ?? null,
                                'articles' => (int) ($item['articles'] ?? 0),
                                'weight' => (float) ($item['weight'] ?? 0),
                                'unit' => $item['unit'] ?? null,
                                'freight_per_mt' => (float) ($item['freight_per_mt'] ?? 0),
                                'amount' => (float) ($item['amount'] ?? 0),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });
        }

        Schema::table('bulties', function (Blueprint $table) {
            $columns = [
                'consignor_name',
                'consignor_phone',
                'consignor_address',
                'consignor_gstin',
                'consignee_name',
                'consignee_phone',
                'consignee_address',
                'consignee_gstin',
                'pickup_location',
                'delivery_location',
                'goods_description',
                'quantity',
                'weight',
                'vehicle_number',
                'vehicle_type',
                'make_model',
                'capacity_tons',
                'owner_name',
                'owner_phone',
                'driver_name',
                'driver_mobile',
                'driver_license_no',
                'driver_license_expiry',
                'driver_address',
                'insurance_expiry',
                'fitness_expiry',
                'permit_expiry',
                'items',
            ];

            $existingColumns = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('bulties', $column)));

            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            if (!Schema::hasColumn('bulties', 'consignor_name')) $table->string('consignor_name')->nullable();
            if (!Schema::hasColumn('bulties', 'consignor_phone')) $table->string('consignor_phone')->nullable();
            if (!Schema::hasColumn('bulties', 'consignor_address')) $table->string('consignor_address')->nullable();
            if (!Schema::hasColumn('bulties', 'consignor_gstin')) $table->string('consignor_gstin')->nullable();
            if (!Schema::hasColumn('bulties', 'consignee_name')) $table->string('consignee_name')->nullable();
            if (!Schema::hasColumn('bulties', 'consignee_phone')) $table->string('consignee_phone')->nullable();
            if (!Schema::hasColumn('bulties', 'consignee_address')) $table->string('consignee_address')->nullable();
            if (!Schema::hasColumn('bulties', 'consignee_gstin')) $table->string('consignee_gstin')->nullable();
            if (!Schema::hasColumn('bulties', 'pickup_location')) $table->string('pickup_location')->nullable();
            if (!Schema::hasColumn('bulties', 'delivery_location')) $table->string('delivery_location')->nullable();
            if (!Schema::hasColumn('bulties', 'goods_description')) $table->string('goods_description')->nullable();
            if (!Schema::hasColumn('bulties', 'quantity')) $table->integer('quantity')->default(0);
            if (!Schema::hasColumn('bulties', 'weight')) $table->decimal('weight', 10, 2)->default(0);
            if (!Schema::hasColumn('bulties', 'vehicle_number')) $table->string('vehicle_number')->nullable();
            if (!Schema::hasColumn('bulties', 'vehicle_type')) $table->string('vehicle_type')->nullable();
            if (!Schema::hasColumn('bulties', 'make_model')) $table->string('make_model')->nullable();
            if (!Schema::hasColumn('bulties', 'capacity_tons')) $table->decimal('capacity_tons', 10, 2)->nullable();
            if (!Schema::hasColumn('bulties', 'owner_name')) $table->string('owner_name')->nullable();
            if (!Schema::hasColumn('bulties', 'owner_phone')) $table->string('owner_phone')->nullable();
            if (!Schema::hasColumn('bulties', 'driver_name')) $table->string('driver_name')->nullable();
            if (!Schema::hasColumn('bulties', 'driver_mobile')) $table->string('driver_mobile')->nullable();
            if (!Schema::hasColumn('bulties', 'driver_license_no')) $table->string('driver_license_no')->nullable();
            if (!Schema::hasColumn('bulties', 'driver_license_expiry')) $table->date('driver_license_expiry')->nullable();
            if (!Schema::hasColumn('bulties', 'driver_address')) $table->string('driver_address')->nullable();
            if (!Schema::hasColumn('bulties', 'insurance_expiry')) $table->date('insurance_expiry')->nullable();
            if (!Schema::hasColumn('bulties', 'fitness_expiry')) $table->date('fitness_expiry')->nullable();
            if (!Schema::hasColumn('bulties', 'permit_expiry')) $table->date('permit_expiry')->nullable();
            if (!Schema::hasColumn('bulties', 'items')) $table->json('items')->nullable();
        });

        Schema::dropIfExists('bulty_items');

        Schema::table('bulties', function (Blueprint $table) {
            if (Schema::hasColumn('bulties', 'driver_id')) {
                $table->dropColumn('driver_id');
            }
        });
    }
};
