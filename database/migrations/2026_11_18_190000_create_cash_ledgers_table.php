<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cash_ledgers')) {
            Schema::create('cash_ledgers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('account_name')->default('Cash in Hand');
                $table->string('account_number')->nullable();
                $table->enum('transaction_type', ['debit', 'credit'])->comment('debit=expense/outgoing, credit=deposit/incoming');
                $table->decimal('amount', 15, 2)->default(0);
                $table->date('date');
                $table->string('voucher_no')->nullable();
                $table->string('category')->nullable();
                $table->text('description')->nullable();
                $table->string('payment_mode')->default('Cash');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'branch_id']);
                $table->index(['date', 'transaction_type']);
                $table->index('account_name');
            });
        }

        // Create Cash Ledger Permissions
        $permissions = [
            'view cash ledger',
            'add cash entry',
            'edit cash entry',
            'delete cash entry',
            'create cash entry',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Assign to Super Admin & Company Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            foreach ($permissions as $permName) {
                if (!$superAdmin->hasPermissionTo($permName)) {
                    $superAdmin->givePermissionTo($permName);
                }
            }
        }

        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            foreach ($permissions as $permName) {
                if (!$admin->hasPermissionTo($permName)) {
                    $admin->givePermissionTo($permName);
                }
            }
        }

        $companyAdmin = Role::where('name', 'Company Admin')->first();
        if ($companyAdmin) {
            foreach ($permissions as $permName) {
                if (!$companyAdmin->hasPermissionTo($permName)) {
                    $companyAdmin->givePermissionTo($permName);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_ledgers');

        $permissions = [
            'view cash ledger',
            'add cash entry',
            'edit cash entry',
            'delete cash entry',
            'create cash entry',
        ];

        foreach ($permissions as $permName) {
            $p = Permission::where('name', $permName)->first();
            if ($p) {
                $p->delete();
            }
        }
    }
};
