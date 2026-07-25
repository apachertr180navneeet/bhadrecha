<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropForeign(['route_id']);
            $table->dropColumn(['route_id', 'trip_id', 'dispatched_at', 'delivered_at', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->unsignedBigInteger('route_id')->nullable();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }
};
