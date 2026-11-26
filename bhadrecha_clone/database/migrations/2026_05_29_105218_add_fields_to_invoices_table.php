<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('invoices', 'consignor_name')) {
                $table->string('consignor_name')->nullable()->after('consignor_id');
            }
            if (!Schema::hasColumn('invoices', 'from_city_name')) {
                $table->string('from_city_name')->nullable()->after('consignor_name');
            }
            if (!Schema::hasColumn('invoices', 'to_city_name')) {
                $table->string('to_city_name')->nullable()->after('from_city_name');
            }
            if (!Schema::hasColumn('invoices', 'total_freight')) {
                $table->decimal('total_freight', 12, 2)->default(0)->after('to_city_name');
            }
            if (!Schema::hasColumn('invoices', 'total_gst')) {
                $table->decimal('total_gst', 12, 2)->default(0)->after('total_freight');
            }
            if (!Schema::hasColumn('invoices', 'total_other')) {
                $table->decimal('total_other', 12, 2)->default(0)->after('total_gst');
            }
            if (!Schema::hasColumn('invoices', 'amount_in_words')) {
                $table->text('amount_in_words')->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('invoices', 'visible_fields')) {
                $table->json('visible_fields')->nullable()->after('amount_in_words');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $columns = ['user_id', 'consignor_name', 'from_city_name', 'to_city_name',
                'total_freight', 'total_gst', 'total_other', 'amount_in_words', 'visible_fields'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
