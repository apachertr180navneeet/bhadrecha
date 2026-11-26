<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'mobile', 'email', 'gender', 'dob',
        'address', 'notes', 'loyalty_points', 'has_received_link'
    ];

    protected $casts = [
        'dob' => 'date',
        'has_received_link' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function getNetBalanceAttribute()
    {
        $appointments = $this->appointments()->where('status', '!=', 'cancelled')->get();
        $net = 0.0;
        foreach ($appointments as $appt) {
            $final = (float)($appt->final_amount ?? 0);
            $paid = (float)($appt->paid_amount ?? 0);
            if ($paid == 0 && $appt->payment_status === 'paid') {
                $paid = $final;
            }
            $net += ($paid - $final);
        }
        return $net;
    }

    public function getCreditBalanceAttribute()
    {
        return max(0.0, $this->net_balance);
    }

    public function getDebitBalanceAttribute()
    {
        return max(0.0, -$this->net_balance);
    }

    public function getBalanceDetails()
    {
        $credit = $this->credit_balance;
        $debit = $this->debit_balance;

        if ($credit > 0) {
            return [
                'type' => 'credit',
                'credit_balance' => $credit,
                'debit_balance' => 0.0,
                'net_balance' => $this->net_balance,
                'text' => 'Credit Balance: ₹' . number_format($credit, 2),
                'badge_class' => 'bg-label-success',
            ];
        } elseif ($debit > 0) {
            return [
                'type' => 'debit',
                'credit_balance' => 0.0,
                'debit_balance' => $debit,
                'net_balance' => $this->net_balance,
                'text' => 'Outstanding Due: ₹' . number_format($debit, 2),
                'badge_class' => 'bg-label-danger',
            ];
        } else {
            return [
                'type' => 'zero',
                'credit_balance' => 0.0,
                'debit_balance' => 0.0,
                'net_balance' => 0.0,
                'text' => 'Balance: ₹0.00 (Clear)',
                'badge_class' => 'bg-label-secondary',
            ];
        }
    }
}
