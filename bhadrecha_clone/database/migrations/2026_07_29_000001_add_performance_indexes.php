<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indexes for trip_fuel_details
        Schema::table('trip_fuel_details', function (Blueprint $table) {
            $table->index('date', 'trip_fuel_details_date_index');
            $table->index('fuel_company_id', 'trip_fuel_details_fuel_company_id_index');
            $table->index('payment_type', 'trip_fuel_details_payment_type_index');
        });

        // Indexes for trip_fast_tag_details
        Schema::table('trip_fast_tag_details', function (Blueprint $table) {
            $table->index('transaction_time', 'trip_fast_tag_details_transaction_time_index');
            $table->index('description', 'trip_fast_tag_details_description_index');
        });

        // Indexes for trip_adblue_details
        Schema::table('trip_adblue_details', function (Blueprint $table) {
            $table->index('date', 'trip_adblue_details_date_index');
            $table->index('adblue_company_id', 'trip_adblue_details_adblue_company_id_index');
            $table->index('payment_type', 'trip_adblue_details_payment_type_index');
        });

        // Indexes for trip_other_amount_details
        Schema::table('trip_other_amount_details', function (Blueprint $table) {
            $table->index('date', 'trip_other_amount_details_date_index');
        });

        // Indexes for trip_advance_details
        Schema::table('trip_advance_details', function (Blueprint $table) {
            $table->index('date', 'trip_advance_details_date_index');
            $table->index('fuel_company_id', 'trip_advance_details_fuel_company_id_index');
            $table->index('fuel_pump_id', 'trip_advance_details_fuel_pump_id_index');
            $table->index('payment_type', 'trip_advance_details_payment_type_index');
        });

        // Indexes for bulties (most queried table)
        Schema::table('bulties', function (Blueprint $table) {
            $table->index('vehicle_id', 'bulties_vehicle_id_index');
            $table->index('driver_id', 'bulties_driver_id_index');
            $table->index('from_city', 'bulties_from_city_index');
            $table->index('to_city', 'bulties_to_city_index');
            $table->index('consignor_id', 'bulties_consignor_id_index');
            $table->index('consignee_id', 'bulties_consignee_id_index');
            $table->index('gst_master_id', 'bulties_gst_master_id_index');
            $table->index('bill_status', 'bulties_bill_status_index');
        });

        // Indexes for invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('company_id', 'invoices_company_id_index');
            $table->index('branch_id', 'invoices_branch_id_index');
            $table->index('consignor_id', 'invoices_consignor_id_index');
            $table->index('invoice_type', 'invoices_invoice_type_index');
            $table->index('status', 'invoices_status_index');
            $table->index('invoice_date', 'invoices_invoice_date_index');
            $table->index('bill_number', 'invoices_bill_number_index');
        });

        // Indexes for fuel_pump_payments
        Schema::table('fuel_pump_payments', function (Blueprint $table) {
            $table->index('date', 'fuel_pump_payments_date_index');
            $table->index('fuel_company_id', 'fuel_pump_payments_fuel_company_id_index');
            $table->index('fuel_pump_id', 'fuel_pump_payments_fuel_pump_id_index');
        });

        // Indexes for adblue_company_payments
        Schema::table('adblue_company_payments', function (Blueprint $table) {
            $table->index('date', 'adblue_company_payments_date_index');
            $table->index('adblue_company_id', 'adblue_company_payments_adblue_company_id_index');
        });

        // Indexes for bill_receivings
        Schema::table('bill_receivings', function (Blueprint $table) {
            $table->index('company_id', 'bill_receivings_company_id_index');
            $table->index('branch_id', 'bill_receivings_branch_id_index');
            $table->index('date', 'bill_receivings_date_index');
            $table->index('invoice_id', 'bill_receivings_invoice_id_index');
        });

        // Indexes for bulty_items
        Schema::table('bulty_items', function (Blueprint $table) {
            $table->index('item_id', 'bulty_items_item_id_index');
        });

        // Indexes for bulty_details
        Schema::table('bulty_details', function (Blueprint $table) {
            $table->index('bulty_id', 'bulty_details_bulty_id_index');
        });

        // Indexes for toll_invoice_details
        Schema::table('toll_invoice_details', function (Blueprint $table) {
            $table->index('toll_invoice_id', 'toll_invoice_details_toll_invoice_id_index');
            $table->index('builty_id', 'toll_invoice_details_builty_id_index');
        });

        // Indexes for consignors (used in LIKE searches)
        Schema::table('consignors', function (Blueprint $table) {
            $table->index('name', 'consignors_name_index');
            $table->index('phone', 'consignors_phone_index');
        });

        // Indexes for consignees
        Schema::table('consignees', function (Blueprint $table) {
            $table->index('name', 'consignees_name_index');
            $table->index('phone', 'consignees_phone_index');
        });

        // Indexes for drivers
        Schema::table('drivers', function (Blueprint $table) {
            $table->index('name', 'drivers_name_index');
            $table->index('phone', 'drivers_phone_index');
        });

        // Index for lr_date on bulties (date range queries)
        Schema::table('bulties', function (Blueprint $table) {
            $table->index('lr_date', 'bulties_lr_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('trip_fuel_details', function (Blueprint $table) {
            $table->dropIndex('trip_fuel_details_date_index');
            $table->dropIndex('trip_fuel_details_fuel_company_id_index');
            $table->dropIndex('trip_fuel_details_payment_type_index');
        });

        Schema::table('trip_fast_tag_details', function (Blueprint $table) {
            $table->dropIndex('trip_fast_tag_details_transaction_time_index');
            $table->dropIndex('trip_fast_tag_details_description_index');
        });

        Schema::table('trip_adblue_details', function (Blueprint $table) {
            $table->dropIndex('trip_adblue_details_date_index');
            $table->dropIndex('trip_adblue_details_adblue_company_id_index');
            $table->dropIndex('trip_adblue_details_payment_type_index');
        });

        Schema::table('trip_other_amount_details', function (Blueprint $table) {
            $table->dropIndex('trip_other_amount_details_date_index');
        });

        Schema::table('trip_advance_details', function (Blueprint $table) {
            $table->dropIndex('trip_advance_details_date_index');
            $table->dropIndex('trip_advance_details_fuel_company_id_index');
            $table->dropIndex('trip_advance_details_fuel_pump_id_index');
            $table->dropIndex('trip_advance_details_payment_type_index');
        });

        Schema::table('bulties', function (Blueprint $table) {
            $table->dropIndex('bulties_vehicle_id_index');
            $table->dropIndex('bulties_driver_id_index');
            $table->dropIndex('bulties_from_city_index');
            $table->dropIndex('bulties_to_city_index');
            $table->dropIndex('bulties_consignor_id_index');
            $table->dropIndex('bulties_consignee_id_index');
            $table->dropIndex('bulties_gst_master_id_index');
            $table->dropIndex('bulties_bill_status_index');
            $table->dropIndex('bulties_lr_date_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_company_id_index');
            $table->dropIndex('invoices_branch_id_index');
            $table->dropIndex('invoices_consignor_id_index');
            $table->dropIndex('invoices_invoice_type_index');
            $table->dropIndex('invoices_status_index');
            $table->dropIndex('invoices_invoice_date_index');
            $table->dropIndex('invoices_bill_number_index');
        });

        Schema::table('fuel_pump_payments', function (Blueprint $table) {
            $table->dropIndex('fuel_pump_payments_date_index');
            $table->dropIndex('fuel_pump_payments_fuel_company_id_index');
            $table->dropIndex('fuel_pump_payments_fuel_pump_id_index');
        });

        Schema::table('adblue_company_payments', function (Blueprint $table) {
            $table->dropIndex('adblue_company_payments_date_index');
            $table->dropIndex('adblue_company_payments_adblue_company_id_index');
        });

        Schema::table('bill_receivings', function (Blueprint $table) {
            $table->dropIndex('bill_receivings_company_id_index');
            $table->dropIndex('bill_receivings_branch_id_index');
            $table->dropIndex('bill_receivings_date_index');
            $table->dropIndex('bill_receivings_invoice_id_index');
        });

        Schema::table('bulty_items', function (Blueprint $table) {
            $table->dropIndex('bulty_items_item_id_index');
        });

        Schema::table('bulty_details', function (Blueprint $table) {
            $table->dropIndex('bulty_details_bulty_id_index');
        });

        Schema::table('toll_invoice_details', function (Blueprint $table) {
            $table->dropIndex('toll_invoice_details_toll_invoice_id_index');
            $table->dropIndex('toll_invoice_details_builty_id_index');
        });

        Schema::table('consignors', function (Blueprint $table) {
            $table->dropIndex('consignors_name_index');
            $table->dropIndex('consignors_phone_index');
        });

        Schema::table('consignees', function (Blueprint $table) {
            $table->dropIndex('consignees_name_index');
            $table->dropIndex('consignees_phone_index');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropIndex('drivers_name_index');
            $table->dropIndex('drivers_phone_index');
        });
    }
};
