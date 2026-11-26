<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('bank_holder_name')->nullable()->after('logo');
            $table->string('bank_name')->nullable()->after('bank_holder_name');
            $table->string('bank_account_no')->nullable()->after('bank_name');
            $table->string('bank_ifsc')->nullable()->after('bank_account_no');
            $table->string('bank_branch')->nullable()->after('bank_ifsc');
            $table->string('digital_signature')->nullable()->after('bank_branch');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'bank_holder_name',
                'bank_name',
                'bank_account_no',
                'bank_ifsc',
                'bank_branch',
                'digital_signature',
            ]);
        });
    }
};
