<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            if (!Schema::hasColumn('cities', 'status')) {
                $table->string('status')->default('active')->after('state');
            }
            $table->softDeletes();
        });

        Schema::table('fuel_pumps', function (Blueprint $table) {
            if (!Schema::hasColumn('fuel_pumps', 'status')) {
                $table->string('status')->default('active')->after('fuel_company_id');
            }
            $table->softDeletes();
        });

        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'status')) {
                $table->string('status')->default('active')->after('description');
            }
            $table->softDeletes();
        });

        Schema::table('packagings', function (Blueprint $table) {
            if (!Schema::hasColumn('packagings', 'status')) {
                $table->string('status')->default('active')->after('description');
            }
            $table->softDeletes();
        });

        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'status')) {
                $table->string('status')->default('active')->after('description');
            }
            $table->softDeletes();
        });

        Schema::table('fuel_companies', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropSoftDeletes();
            if (Schema::hasColumn('cities', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('fuel_pumps', function (Blueprint $table) {
            $table->dropSoftDeletes();
            if (Schema::hasColumn('fuel_pumps', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropSoftDeletes();
            if (Schema::hasColumn('items', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('packagings', function (Blueprint $table) {
            $table->dropSoftDeletes();
            if (Schema::hasColumn('packagings', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropSoftDeletes();
            if (Schema::hasColumn('units', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('fuel_companies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
