<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_mail_configurations')) {
            Schema::create('user_mail_configurations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
                $table->string('provider', 50)->default('smtp'); // smtp, gmail, outlook
                $table->string('mail_mailer', 50)->default('smtp');
                $table->string('mail_host')->nullable();
                $table->integer('mail_port')->default(587);
                $table->string('mail_encryption', 20)->nullable()->default('tls'); // tls, ssl, or null
                $table->string('mail_username')->nullable();
                $table->text('mail_password')->nullable(); // encrypted
                $table->string('mail_from_address')->nullable();
                $table->string('mail_from_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_tested_at')->nullable();
                $table->string('last_test_status', 20)->nullable();
                $table->text('last_test_message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mail_configurations');
    }
};
