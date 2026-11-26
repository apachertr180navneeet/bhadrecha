<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'appointment_number', 'store_id', 'branch_id', 'customer_id', 'staff_id', 'service_id',
        'appointment_date', 'end_time', 'check_in_time', 'completed_time', 'status', 'notes',
        'total_amount', 'discount', 'final_amount', 'paid_amount', 'payment_status', 'payment_type', 'cash_amount', 'upi_amount', 'card_amount', 'upi_reference'
    ];

    public function getCreditAmountAttribute()
    {
        if ($this->status === 'cancelled') {
            return 0.0;
        }
        return max(0.0, (float)$this->paid_amount - (float)$this->final_amount);
    }

    public function getDebitAmountAttribute()
    {
        if ($this->status === 'cancelled') {
            return 0.0;
        }
        return max(0.0, (float)$this->final_amount - (float)$this->paid_amount);
    }

    public static function calculateAdjustedBalancesForAppointments($appointmentsCollection)
    {
        $customerIds = $appointmentsCollection->pluck('customer_id')->filter()->unique();
        $adjustedData = [];

        foreach ($customerIds as $customerId) {
            $customerAppts = static::where('customer_id', $customerId)
                ->where('status', '!=', 'cancelled')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $credits = [];
            $dues = [];

            foreach ($customerAppts as $appt) {
                $final = (float)($appt->final_amount ?? 0);
                $paid = (float)($appt->paid_amount ?? 0);
                $diff = $paid - $final;

                if ($diff > 0) {
                    $credits[] = [
                        'appt_id' => $appt->id,
                        'amount' => $diff,
                        'remaining' => $diff,
                        'used' => 0.0,
                    ];
                } elseif ($diff < 0) {
                    $dues[] = [
                        'appt_id' => $appt->id,
                        'amount' => abs($diff),
                        'remaining' => abs($diff),
                        'covered' => 0.0,
                    ];
                }
            }

            foreach ($credits as &$c) {
                foreach ($dues as &$d) {
                    if ($c['remaining'] <= 0) break;
                    if ($d['remaining'] <= 0) continue;

                    $match = min($c['remaining'], $d['remaining']);
                    $c['remaining'] -= $match;
                    $c['used'] += $match;
                    $d['remaining'] -= $match;
                    $d['covered'] += $match;
                }
            }
            unset($c, $d);

            foreach ($customerAppts as $appt) {
                $final = (float)($appt->final_amount ?? 0);
                $paid = (float)($appt->paid_amount ?? 0);
                $diff = $paid - $final;

                $rawCredit = max(0.0, $diff);
                $rawDebit = max(0.0, -$diff);

                $creditInfo = collect($credits)->firstWhere('appt_id', $appt->id);
                $dueInfo = collect($dues)->firstWhere('appt_id', $appt->id);

                $remCredit = $creditInfo ? $creditInfo['remaining'] : 0.0;
                $usedCredit = $creditInfo ? $creditInfo['used'] : 0.0;

                $remDebit = $dueInfo ? $dueInfo['remaining'] : 0.0;
                $coveredDebit = $dueInfo ? $dueInfo['covered'] : 0.0;

                $adjustedData[$appt->id] = [
                    'raw_credit' => $rawCredit,
                    'raw_debit' => $rawDebit,
                    'adjusted_credit' => $remCredit,
                    'adjusted_debit' => $remDebit,
                    'credit_used' => $usedCredit,
                    'credit_covered' => $coveredDebit,
                ];
            }
        }

        return $adjustedData;
    }

    protected $casts = [
        'appointment_date' => 'datetime',
        'end_time' => 'datetime',
        'check_in_time' => 'datetime',
        'completed_time' => 'datetime',
    ];

    public static function generateAppointmentNumber($dateInput)
    {
        $dateStr = \Carbon\Carbon::parse($dateInput)->format('Y-m-d');

        $lastAppointment = static::whereDate('appointment_date', $dateStr)
            ->whereNotNull('appointment_number')
            ->where('appointment_number', 'like', $dateStr . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(appointment_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $nextNum = 1;
        if ($lastAppointment && $lastAppointment->appointment_number) {
            $parts = explode('-', $lastAppointment->appointment_number);
            $lastSeq = (int) end($parts);
            $nextNum = $lastSeq + 1;
        }

        return sprintf('%s-%02d', $dateStr, $nextNum);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->appointment_number)) {
                $appointment->appointment_number = static::generateAppointmentNumber($appointment->appointment_date);
            }
        });

        static::updating(function ($appointment) {
            if ($appointment->isDirty('appointment_date')) {
                $oldDate = $appointment->getOriginal('appointment_date')
                    ? \Carbon\Carbon::parse($appointment->getOriginal('appointment_date'))->format('Y-m-d')
                    : null;
                $newDate = \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d');

                if ($oldDate !== $newDate || empty($appointment->appointment_number)) {
                    $appointment->appointment_number = static::generateAppointmentNumber($appointment->appointment_date);
                }
            }
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }
}
