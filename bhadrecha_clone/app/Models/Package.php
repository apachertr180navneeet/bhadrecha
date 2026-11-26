<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'customer_id',
        'services',
        'duration_id',
        'qty',
        'amount',
        'advance',
        'remaining',
        'payment_history',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'services' => 'array',
        'payment_history' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function duration()
    {
        return $this->belongsTo(Duration::class);
    }

    public function getServicesWithQtyAttribute()
    {
        $raw = $this->services;
        if (!is_array($raw)) {
            return [];
        }

        $items = [];
        foreach ($raw as $key => $val) {
            if (is_array($val)) {
                $serviceId = $val['service_id'] ?? $val['id'] ?? $key;
                $qty = (int)($val['qty'] ?? 1);
                $price = isset($val['price']) ? (float)$val['price'] : null;
            } else {
                $serviceId = $val;
                $qty = 1;
                $price = null;
            }
            $items[] = [
                'service_id' => (int)$serviceId,
                'qty' => max(1, $qty),
                'price' => $price
            ];
        }
        return $items;
    }

    public function getUsageDetails($excludeAppointmentId = null)
    {
        $items = $this->services_with_qty;
        $serviceIds = collect($items)->pluck('service_id')->filter()->toArray();
        $servicesMap = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

        $startDate = $this->start_date ? \Carbon\Carbon::parse($this->start_date)->startOfDay() : null;
        $endDate = $this->end_date ? \Carbon\Carbon::parse($this->end_date)->endOfDay() : null;

        $appointmentsQuery = Appointment::with(['service', 'staff', 'appointmentServices.service', 'appointmentServices.staff'])
            ->where('customer_id', $this->customer_id)
            ->whereNotIn('status', ['cancelled']);

        if ($excludeAppointmentId) {
            $appointmentsQuery->where('id', '!=', $excludeAppointmentId);
        }

        if ($startDate) {
            $appointmentsQuery->whereDate('appointment_date', '>=', $startDate->format('Y-m-d'));
        }
        if ($endDate) {
            $appointmentsQuery->whereDate('appointment_date', '<=', $endDate->format('Y-m-d'));
        }

        $appointments = $appointmentsQuery->orderBy('appointment_date', 'desc')->get();

        $servicesBreakdown = [];
        $totalAllocatedQty = 0;
        $totalUsedQty = 0;

        foreach ($items as $item) {
            $sId = $item['service_id'];
            $allocatedQty = (int)$item['qty'];
            $sObj = $servicesMap->get($sId);

            $usedQty = 0;
            foreach ($appointments as $apt) {
                if ($apt->appointmentServices && $apt->appointmentServices->count() > 0) {
                    $usedQty += $apt->appointmentServices->where('service_id', $sId)->count();
                } elseif ($apt->service_id == $sId) {
                    $usedQty += 1;
                }
            }

            $remainingQty = max(0, $allocatedQty - $usedQty);

            $totalAllocatedQty += $allocatedQty;
            $totalUsedQty += min($allocatedQty, $usedQty);

            $servicesBreakdown[] = [
                'service_id' => $sId,
                'service_name' => $sObj ? $sObj->service_name : ('Service #' . $sId),
                'allocated_qty' => $allocatedQty,
                'used_qty' => min($allocatedQty, $usedQty),
                'remaining_qty' => $remainingQty,
            ];
        }

        $totalRemainingQty = max(0, $totalAllocatedQty - $totalUsedQty);

        $today = \Carbon\Carbon::today();
        $status = 'Active';
        if ($totalAllocatedQty > 0 && $totalRemainingQty <= 0) {
            $status = 'Fully Utilized';
        } elseif ($endDate && $today->greaterThan($endDate->endOfDay())) {
            $status = 'Expired';
        }

        return [
            'services_breakdown' => $servicesBreakdown,
            'total_allocated_qty' => $totalAllocatedQty,
            'total_used_qty' => $totalUsedQty,
            'total_remaining_qty' => $totalRemainingQty,
            'status' => $status,
            'appointments' => $appointments
        ];
    }

    public static function getActivePackagesForCustomer($customerId, $excludeAppointmentId = null)
    {
        $today = \Carbon\Carbon::today();
        $packages = static::where('customer_id', $customerId)
            ->where(function($q) use ($today) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $today->format('Y-m-d'));
            })
            ->get();

        $activePackages = [];
        $coveredServices = [];

        foreach ($packages as $pkg) {
            $usage = $pkg->getUsageDetails($excludeAppointmentId);
            if ($usage['status'] === 'Active' && $usage['total_remaining_qty'] > 0) {
                $activePackages[] = [
                    'package' => $pkg,
                    'usage' => $usage,
                ];

                foreach ($usage['services_breakdown'] as $sb) {
                    if ($sb['remaining_qty'] > 0) {
                        $sId = $sb['service_id'];
                        if (!isset($coveredServices[$sId])) {
                            $coveredServices[$sId] = [
                                'package_id' => $pkg->id,
                                'remaining_qty' => $sb['remaining_qty'],
                                'service_name' => $sb['service_name'],
                            ];
                        } else {
                            $coveredServices[$sId]['remaining_qty'] += $sb['remaining_qty'];
                        }
                    }
                }
            }
        }

        return [
            'active_packages' => $activePackages,
            'covered_services' => $coveredServices,
        ];
    }
}

