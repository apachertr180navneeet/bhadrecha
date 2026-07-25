<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            if (!Schema::hasColumn('bulties', 'mode')) {
                $table->string('mode')->nullable()->after('payment_type');
            }
            if (!Schema::hasColumn('bulties', 'e_lr_no')) {
                $table->string('e_lr_no')->nullable()->after('lr_no');
            }
            if (!Schema::hasColumn('bulties', 'damage_amount')) {
                $table->decimal('damage_amount', 10, 2)->default(0)->after('other_charges');
            }
            if (!Schema::hasColumn('bulties', 'shortage_amount')) {
                $table->decimal('shortage_amount', 10, 2)->default(0)->after('damage_amount');
            }
        });

        Schema::table('bulty_details', function (Blueprint $table) {
            if (!Schema::hasColumn('bulty_details', 'ul_date')) {
                $table->date('ul_date')->nullable()->after('gate_out_date');
            }
            if (!Schema::hasColumn('bulty_details', 'bag_ld')) {
                $table->integer('bag_ld')->default(0)->after('recd_qty');
            }
            if (!Schema::hasColumn('bulty_details', 'bag_ul')) {
                $table->integer('bag_ul')->default(0)->after('bag_ld');
            }
            if (!Schema::hasColumn('bulty_details', 'bag_short')) {
                $table->integer('bag_short')->default(0)->after('bag_ul');
            }
            if (!Schema::hasColumn('bulty_details', 'rate_mt')) {
                $table->decimal('rate_mt', 10, 2)->default(0)->after('bag_short');
            }
            if (!Schema::hasColumn('bulty_details', 'qty_mt')) {
                $table->decimal('qty_mt', 10, 2)->default(0)->after('rate_mt');
            }
            if (!Schema::hasColumn('bulty_details', 'description_services')) {
                $table->text('description_services')->nullable()->after('qty_mt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropColumn(['mode', 'e_lr_no', 'damage_amount', 'shortage_amount']);
        });

        Schema::table('bulty_details', function (Blueprint $table) {
            $table->dropColumn(['ul_date', 'bag_ld', 'bag_ul', 'bag_short', 'rate_mt', 'qty_mt', 'description_services']);
        });
    }
};
