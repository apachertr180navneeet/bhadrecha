<?php

namespace Tests\Unit;

use Tests\TestCase;

class ModelsTest extends TestCase
{
    public function test_company_model_has_expected_fillable_attributes(): void
    {
        $fillable = (new \App\Models\Company)->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_branch_model_has_expected_fillable_attributes(): void
    {
        $fillable = (new \App\Models\Branch)->getFillable();
        $this->assertContains('company_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_trip_model_has_expected_fillable_attributes(): void
    {
        $fillable = (new \App\Models\Trip)->getFillable();
        $this->assertContains('trip_no', $fillable);
        $this->assertContains('vehicle_id', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_user_model_implements_jwt_subject(): void
    {
        $user = new \App\Models\User;
        $this->assertInstanceOf(\Tymon\JWTAuth\Contracts\JWTSubject::class, $user);
    }

    public function test_user_model_uses_has_roles_trait(): void
    {
        $traits = class_uses(\App\Models\User::class);
        $this->assertContains(\Spatie\Permission\Traits\HasRoles::class, $traits);
    }

    public function test_consignor_model_has_company_relationship(): void
    {
        $consignor = new \App\Models\Consignor;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $consignor->company());
    }

    public function test_consignee_model_has_company_relationship(): void
    {
        $consignee = new \App\Models\Consignee;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $consignee->company());
    }



    public function test_notification_model_uses_uuid_primary_key(): void
    {
        $notification = new \App\Models\Notification;
        $this->assertFalse($notification->incrementing);
        $this->assertEquals('string', $notification->getKeyType());
    }

    public function test_activity_log_model_has_log_method(): void
    {
        $this->assertTrue(method_exists(\App\Models\ActivityLog::class, 'log'));
    }

    public function test_trip_number_format_is_correct(): void
    {
        $number = \App\Models\Trip::generateTripNumber();
        $this->assertStringStartsWith('TRIP-' . date('Y') . '-', $number);
    }

    public function test_lr_number_format_is_correct(): void
    {
        $number = \App\Models\Bulty::generateLRNumber();
        $this->assertStringStartsWith('LR-' . date('Y') . '-', $number);
    }

    public function test_dispatch_number_format_is_correct(): void
    {
        $number = \App\Models\Dispatch::generateDispatchNumber();
        $this->assertStringStartsWith('DISP-' . date('Y') . '-', $number);
    }
}
