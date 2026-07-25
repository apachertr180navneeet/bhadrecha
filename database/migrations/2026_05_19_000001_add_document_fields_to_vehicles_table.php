<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('registration_cert')->nullable()->after('status');
            $table->string('insurance_doc')->nullable()->after('registration_cert');
            $table->string('fitness_doc')->nullable()->after('insurance_doc');
            $table->string('permit_doc')->nullable()->after('fitness_doc');
            $table->string('pollution_cert')->nullable()->after('permit_doc');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['registration_cert', 'insurance_doc', 'fitness_doc', 'permit_doc', 'pollution_cert']);
        });
    }
};
