<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('route_id')->nullable();
            
            $table->string('lr_no')->unique();
            $table->string('lr_date')->nullable();
            
            // Consignor
            $table->string('consignor_name');
            $table->string('consignor_phone');
            $table->string('consignor_address')->nullable();
            $table->string('consignor_gstin')->nullable();
            
            // Consignee
            $table->string('consignee_name');
            $table->string('consignee_phone');
            $table->string('consignee_address')->nullable();
            $table->string('consignee_gstin')->nullable();
            
            // From/To
            $table->string('pickup_location');
            $table->string('delivery_location');
            
            // Goods Details
            $table->string('goods_description')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('declared_value', 12, 2)->default(0);
            
            // Billing
            $table->decimal('freight_charges', 10, 2)->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('payment_type', ['paid', 'topay', 'tobill'])->default('topay');
            $table->enum('gst_type', ['none', 'intrastate', 'interstate'])->default('none');
            
            // Tracking
            $table->enum('status', ['pending', 'planned', 'dispatched', 'in_transit', 'delivered', 'partially_delivered', 'rejected'])->default('pending');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'branch_id', 'status']);
            $table->index('lr_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulties');
    }
};
