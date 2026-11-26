<?php

namespace App\Services;

use App\Models\Bulty;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Document;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ExpiryNotificationService
{
    /**
     * Scan and generate notifications for all expiring documents and E-Way bills.
     * 
     * @param int $daysAhead Default is 1 day ahead (as requested).
     * @return array Summary of generated notifications
     */
    public static function checkAndGenerateNotifications(int $daysAhead = 1): array
    {
        $targetDate = Carbon::today()->addDays($daysAhead)->toDateString();
        $today = Carbon::today()->toDateString();
        
        $results = [
            'eway_bills' => 0,
            'vehicle_docs' => 0,
            'driver_docs' => 0,
            'dms_docs' => 0,
            'total_created' => 0,
        ];

        try {
            // Fetch eligible users to receive notifications (Admins and active users)
            $recipientUserIds = User::where(function($q) {
                    $q->where('status', 'active')
                      ->orWhere('status', '1')
                      ->orWhereNull('status');
                })
                ->pluck('id')
                ->toArray();

            if (empty($recipientUserIds)) {
                $recipientUserIds = User::pluck('id')->toArray();
            }

            if (empty($recipientUserIds)) {
                return $results;
            }

            // 1. CHECK BILTY (LR) E-WAY BILL EXPIRY
            $results['eway_bills'] = self::checkEWayBills($today, $targetDate, $recipientUserIds);

            // 2. CHECK VEHICLE DOCUMENTS EXPIRY (Insurance, Fitness, Permit, Pollution)
            $results['vehicle_docs'] = self::checkVehicleDocuments($today, $targetDate, $recipientUserIds);

            // 3. CHECK DRIVER LICENSE EXPIRY
            $results['driver_docs'] = self::checkDriverDocuments($today, $targetDate, $recipientUserIds);

            // 4. CHECK DMS DOCUMENTS EXPIRY
            $results['dms_docs'] = self::checkDmsDocuments($today, $targetDate, $recipientUserIds);

            $results['total_created'] = $results['eway_bills'] + $results['vehicle_docs'] + $results['driver_docs'] + $results['dms_docs'];
        } catch (\Throwable $e) {
            Log::error('ExpiryNotificationService failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $results;
    }

    /**
     * Check Bilty (LR) E-Way Bills expiring within threshold
     */
    protected static function checkEWayBills(string $today, string $targetDate, array $recipientUserIds): int
    {
        $count = 0;
        
        // Find bulties where expiry_date is between today and targetDate, or expiring within targetDate
        $bulties = Bulty::whereNotNull('expiry_date')
            ->where('status', '!=', 'cancelled')
            ->whereDate('expiry_date', '<=', $targetDate)
            ->whereDate('expiry_date', '>=', Carbon::parse($today)->subDays(2)->toDateString()) // include freshly expired
            ->with(['consignor', 'consignee', 'vehicle', 'branch'])
            ->get();

        foreach ($bulties as $bulty) {
            $expiryDate = Carbon::parse($bulty->expiry_date);
            $expiryFormatted = $expiryDate->format('d M Y');
            $diffDays = Carbon::today()->diffInDays($expiryDate, false);
            
            $urgency = 'Expiring Tomorrow';
            if ($diffDays < 0) {
                $urgency = 'Expired (' . abs($diffDays) . ' days ago)';
            } elseif ($diffDays === 0) {
                $urgency = 'Expiring Today';
            } elseif ($diffDays === 1) {
                $urgency = 'Expiring Tomorrow';
            } else {
                $urgency = "Expiring in {$diffDays} days";
            }

            $ewayNo = !empty($bulty->eway_bill_no) ? $bulty->eway_bill_no : 'N/A';
            $vehicleNo = $bulty->vehicle ? $bulty->vehicle->vehicle_number : ($bulty->vehicle_no ?? 'N/A');
            $lrNo = $bulty->lr_no ?? 'LR-' . $bulty->id;

            // Deterministic notification ID so we don't spam duplicates
            $notificationId = 'eway_' . $bulty->id . '_' . $expiryDate->format('Ymd');

            $title = "⚠️ E-Way Bill {$urgency} [LR: {$lrNo}]";
            $description = "Bilty No: {$lrNo} | Document Type: E-Way Bill | E-Way Bill No: {$ewayNo} | Expiry Date: {$expiryFormatted} | Vehicle: {$vehicleNo}";

            $url = route('admin.transport.bulties.show', $bulty->id);

            $data = [
                'type' => 'eway_bill',
                'bulty_id' => $bulty->id,
                'lr_no' => $lrNo,
                'document_type' => 'E-Way Bill',
                'eway_bill_no' => $ewayNo,
                'expiry_date' => $expiryFormatted,
                'days_left' => $diffDays,
                'vehicle_number' => $vehicleNo,
                'urgency' => $urgency,
            ];

            if (self::saveNotificationRecord($notificationId, 'eway_bill_expiry', 'expiry_alert', $title, $description, $url, $data, $recipientUserIds)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check Vehicle Documents expiring within threshold
     */
    protected static function checkVehicleDocuments(string $today, string $targetDate, array $recipientUserIds): int
    {
        $count = 0;
        
        $docFields = [
            'insurance_expiry' => 'Vehicle Insurance',
            'fitness_expiry' => 'Fitness Certificate',
            'permit_expiry' => 'Vehicle Permit',
            'pollution_expiry' => 'Pollution Certificate (PUC)',
        ];

        $vehicles = Vehicle::where('status', 'active')
            ->orWhereNull('status')
            ->get();

        foreach ($vehicles as $vehicle) {
            foreach ($docFields as $field => $label) {
                if (empty($vehicle->{$field})) {
                    continue;
                }

                $expiryDate = Carbon::parse($vehicle->{$field});
                $expiryDateStr = $expiryDate->toDateString();

                // Check if expiry is <= targetDate and >= 2 days ago
                if ($expiryDateStr <= $targetDate && $expiryDateStr >= Carbon::parse($today)->subDays(2)->toDateString()) {
                    $expiryFormatted = $expiryDate->format('d M Y');
                    $diffDays = Carbon::today()->diffInDays($expiryDate, false);

                    $urgency = 'Expiring Tomorrow';
                    if ($diffDays < 0) {
                        $urgency = 'Expired (' . abs($diffDays) . ' days ago)';
                    } elseif ($diffDays === 0) {
                        $urgency = 'Expiring Today';
                    } elseif ($diffDays === 1) {
                        $urgency = 'Expiring Tomorrow';
                    } else {
                        $urgency = "Expiring in {$diffDays} days";
                    }

                    $vehNo = $vehicle->vehicle_number;
                    $notificationId = 'veh_' . $vehicle->id . '_' . $field . '_' . $expiryDate->format('Ymd');

                    $title = "🚗 {$label} {$urgency} [{$vehNo}]";
                    $description = "Vehicle No: {$vehNo} | Document Type: {$label} | Expiry Date: {$expiryFormatted}";

                    $url = route('admin.masters.vehicles.index', ['search' => $vehNo]);

                    $data = [
                        'type' => 'vehicle_document',
                        'vehicle_id' => $vehicle->id,
                        'vehicle_number' => $vehNo,
                        'document_type' => $label,
                        'document_field' => $field,
                        'expiry_date' => $expiryFormatted,
                        'days_left' => $diffDays,
                        'urgency' => $urgency,
                    ];

                    if (self::saveNotificationRecord($notificationId, 'vehicle_document_expiry', 'expiry_alert', $title, $description, $url, $data, $recipientUserIds)) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Check Driver Licenses expiring within threshold
     */
    protected static function checkDriverDocuments(string $today, string $targetDate, array $recipientUserIds): int
    {
        $count = 0;

        $drivers = Driver::whereNotNull('license_expiry')
            ->where(function($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->get();

        foreach ($drivers as $driver) {
            $expiryDate = Carbon::parse($driver->license_expiry);
            $expiryDateStr = $expiryDate->toDateString();

            if ($expiryDateStr <= $targetDate && $expiryDateStr >= Carbon::parse($today)->subDays(2)->toDateString()) {
                $expiryFormatted = $expiryDate->format('d M Y');
                $diffDays = Carbon::today()->diffInDays($expiryDate, false);

                $urgency = 'Expiring Tomorrow';
                if ($diffDays < 0) {
                    $urgency = 'Expired (' . abs($diffDays) . ' days ago)';
                } elseif ($diffDays === 0) {
                    $urgency = 'Expiring Today';
                } elseif ($diffDays === 1) {
                    $urgency = 'Expiring Tomorrow';
                } else {
                    $urgency = "Expiring in {$diffDays} days";
                }

                $driverName = $driver->name;
                $licenseNo = $driver->license_number ?? 'N/A';
                $notificationId = 'drv_' . $driver->id . '_license_' . $expiryDate->format('Ymd');

                $title = "🪪 Driver License {$urgency} [{$driverName}]";
                $description = "Driver Name: {$driverName} | Document Type: Driving License | License No: {$licenseNo} | Expiry Date: {$expiryFormatted}";

                $url = route('admin.masters.drivers.index', ['search' => $licenseNo ?: $driverName]);

                $data = [
                    'type' => 'driver_document',
                    'driver_id' => $driver->id,
                    'driver_name' => $driverName,
                    'license_number' => $licenseNo,
                    'document_type' => 'Driving License',
                    'expiry_date' => $expiryFormatted,
                    'days_left' => $diffDays,
                    'urgency' => $urgency,
                ];

                if (self::saveNotificationRecord($notificationId, 'driver_document_expiry', 'expiry_alert', $title, $description, $url, $data, $recipientUserIds)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Check DMS Documents expiring within threshold
     */
    protected static function checkDmsDocuments(string $today, string $targetDate, array $recipientUserIds): int
    {
        $count = 0;

        $docs = Document::whereNotNull('expiry_date')
            ->where(function($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->get();

        foreach ($docs as $doc) {
            $expiryDate = Carbon::parse($doc->expiry_date);
            $expiryDateStr = $expiryDate->toDateString();

            if ($expiryDateStr <= $targetDate && $expiryDateStr >= Carbon::parse($today)->subDays(2)->toDateString()) {
                $expiryFormatted = $expiryDate->format('d M Y');
                $diffDays = Carbon::today()->diffInDays($expiryDate, false);

                $urgency = 'Expiring Tomorrow';
                if ($diffDays < 0) {
                    $urgency = 'Expired (' . abs($diffDays) . ' days ago)';
                } elseif ($diffDays === 0) {
                    $urgency = 'Expiring Today';
                } elseif ($diffDays === 1) {
                    $urgency = 'Expiring Tomorrow';
                } else {
                    $urgency = "Expiring in {$diffDays} days";
                }

                $docNo = $doc->document_number ?? 'DOC-' . $doc->id;
                $docName = $doc->name ?? 'Document';
                $notificationId = 'dms_' . $doc->id . '_' . $expiryDate->format('Ymd');

                $title = "📄 Document {$urgency} [{$docNo}]";
                $description = "Document No: {$docNo} | Document Name: {$docName} | Document Type: General Document | Expiry Date: {$expiryFormatted}";

                $url = route('admin.documents.show', $doc->id);

                $data = [
                    'type' => 'dms_document',
                    'document_id' => $doc->id,
                    'document_number' => $docNo,
                    'document_name' => $docName,
                    'document_type' => 'General Document',
                    'expiry_date' => $expiryFormatted,
                    'days_left' => $diffDays,
                    'urgency' => $urgency,
                ];

                if (self::saveNotificationRecord($notificationId, 'document_expiry', 'expiry_alert', $title, $description, $url, $data, $recipientUserIds)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Save notification record and associate with recipient users
     */
    protected static function saveNotificationRecord(
        string $notificationId,
        string $notificationType,
        string $actionType,
        string $title,
        string $description,
        string $url,
        array $data,
        array $recipientUserIds
    ): bool {
        $created = false;
        $notification = Notification::find($notificationId);

        if (!$notification) {
            $notification = new Notification();
            $notification->id = $notificationId;
            $notification->notification_type = $notificationType;
            $notification->action_type = $actionType;
            $notification->title = $title;
            $notification->description = $description;
            $notification->url = $url;
            $notification->data = json_encode($data);
            $notification->save();
            $created = true;
        } else {
            // Update title / description in case urgency changed from 'Tomorrow' to 'Today'
            $notification->title = $title;
            $notification->description = $description;
            $notification->data = json_encode($data);
            $notification->save();
        }

        // Assign to all recipient users
        foreach ($recipientUserIds as $userId) {
            $exists = NotificationUser::where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->exists();

            if (!$exists) {
                NotificationUser::create([
                    'notification_id' => $notificationId,
                    'user_id' => $userId,
                    'read_at' => null,
                ]);
            }
        }

        return $created;
    }
}
