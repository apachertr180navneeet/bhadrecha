<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;
    protected $table = 'staffs';
    protected $fillable = [
        'store_id', 'branch_id', 'user_id', 'first_name', 'last_name', 'email', 'phone',
        'address', 'position', 'salary', 'shift_time', 'working_hours',
        'id_proof', 'qualification_doc', 'joining_date', 'status'
    ];

    protected $appends = ['full_name', 'attendance_percentage'];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getAttendancePercentageAttribute()
    {
        if (!$this->relationLoaded('attendances') || $this->attendances->isEmpty()) {
            return 0;
        }

        $totalDays = $this->attendances->count();
        $presentDays = $this->attendances->where('status', 'present')->count();

        return ($totalDays > 0) ? round(($presentDays / $totalDays) * 100, 1) : 0;
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }


    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
}
