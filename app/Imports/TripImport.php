<?php

namespace App\Imports;

use App\Models\AdBlueCompany;
use App\Models\Branch;
use App\Models\Bulty;
use App\Models\BultyDetail;
use App\Models\BultyItem;
use App\Models\City;
use App\Models\Company;
use App\Models\Consignee;
use App\Models\Consignor;
use App\Models\Driver;
use App\Models\FuelCompany;
use App\Models\FuelPump;
use App\Models\Item;
use App\Models\Trip;
use App\Models\TripAdBlueDetail;
use App\Models\TripAdvanceDetail;
use App\Models\TripFastTagDetail;
use App\Models\TripFuelDetail;
use App\Models\TripOtherAmountDetail;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TripImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
{
    use Importable;

    protected $failures = [];
    protected $imported = 0;
    protected $updated = 0;
    protected $skipped = 0;
    protected $unmatchedLrs = [];
    protected $headings = [];

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->headings = $event->getSheet()->getDelegate()->toArray()[0] ?? [];
            },
        ];
    }

    public function collection(Collection $rows)
    {
        $user = auth()->user();
        $defaultCompanyId = $user?->company_id ?: (Company::where('status', 'active')->value('id') ?: Company::value('id') ?: 1);
        $defaultBranchId = $user?->branch_id ?: (Branch::where('status', 'active')->value('id') ?: Branch::value('id') ?: 1);

        // Group rows by LR Number to seamlessly support multi-items, multi-fuel, multi-toll per LR
        $grouped = $rows->groupBy(function ($row) use ($defaultBranchId) {
            $lr = $row['lr_no'] ?? $row['lr_number'] ?? $row['bilty_no'] ?? $row['lr'] ?? null;
            if ($lr !== null && trim((string) $lr) !== '') {
                return trim((string) $lr);
            }
            return '__AUTO__' . uniqid();
        });

        foreach ($grouped as $groupKey => $groupRows) {
            $firstRow = $groupRows->first();

            // 1. Resolve LR No & Date
            $lrNo = str_starts_with($groupKey, '__AUTO__') ? null : $groupKey;
            $lrDate = $this->parseDate($firstRow['lr_date'] ?? $firstRow['date'] ?? null);

            $paymentTypeRaw = $firstRow['payment_type'] ?? 'topay';
            $paymentType = strtolower(trim((string) $paymentTypeRaw));
            if (!in_array($paymentType, ['paid', 'topay', 'tobill'])) {
                $paymentType = 'topay';
            }

            // 2. Vehicle Lookup or Creation
            $vehicle = null;
            $vehicleNumber = isset($firstRow['vehicle_number']) ? trim((string) $firstRow['vehicle_number']) : (isset($firstRow['truck_no']) ? trim((string) $firstRow['truck_no']) : null);
            $vehicleType = isset($firstRow['vehicle_type']) ? trim((string) $firstRow['vehicle_type']) : 'Truck';
            if (!empty($vehicleNumber)) {
                $vehicle = Vehicle::firstOrCreate(
                    ['vehicle_number' => $vehicleNumber],
                    ['status' => 'active', 'vehicle_type' => $vehicleType ?: 'Truck']
                );
            }

            // 3. Driver Lookup or Creation
            $driver = null;
            $driverName = isset($firstRow['driver_name']) ? trim((string) $firstRow['driver_name']) : null;
            $driverPhone = isset($firstRow['driver_phone']) ? trim((string) $firstRow['driver_phone']) : null;
            if (!empty($driverPhone) || !empty($driverName)) {
                $driverQuery = Driver::query();
                if (!empty($driverPhone)) {
                    $driverQuery->where('phone', $driverPhone);
                } else {
                    $driverQuery->where('name', $driverName);
                }
                $driver = $driverQuery->first();

                if (!$driver && !empty($driverName)) {
                    $driver = Driver::create([
                        'company_id' => $defaultCompanyId,
                        'branch_id' => $defaultBranchId,
                        'name' => $driverName,
                        'phone' => $driverPhone ?: '9999999999',
                        'license_number' => 'DL-' . strtoupper(substr(md5($driverName . time()), 0, 8)),
                        'status' => 'active',
                    ]);
                }
            }

            // 4. Consignor Lookup or Creation
            $consignor = null;
            $consignorName = isset($firstRow['consignor_name']) ? trim((string) $firstRow['consignor_name']) : (isset($firstRow['consignor']) ? trim((string) $firstRow['consignor']) : null);
            $consignorPhone = isset($firstRow['consignor_phone']) ? trim((string) $firstRow['consignor_phone']) : null;
            $consignorGstin = isset($firstRow['consignor_gstin']) ? trim((string) $firstRow['consignor_gstin']) : null;
            $consignorAddress = isset($firstRow['consignor_address']) ? trim((string) $firstRow['consignor_address']) : null;
            if (!empty($consignorName)) {
                $consignor = Consignor::firstOrCreate(
                    ['name' => $consignorName, 'company_id' => $defaultCompanyId],
                    [
                        'phone' => $consignorPhone ?: '9999999999',
                        'gstin' => $consignorGstin,
                        'address' => $consignorAddress,
                        'status' => 'active',
                    ]
                );
            }

            // 5. Consignee Lookup or Creation
            $consignee = null;
            $consigneeName = isset($firstRow['consignee_name']) ? trim((string) $firstRow['consignee_name']) : (isset($firstRow['consignee']) ? trim((string) $firstRow['consignee']) : null);
            $consigneePhone = isset($firstRow['consignee_phone']) ? trim((string) $firstRow['consignee_phone']) : null;
            $consigneeGstin = isset($firstRow['consignee_gstin']) ? trim((string) $firstRow['consignee_gstin']) : null;
            $consigneeAddress = isset($firstRow['consignee_address']) ? trim((string) $firstRow['consignee_address']) : null;
            if (!empty($consigneeName)) {
                $consignee = Consignee::firstOrCreate(
                    ['name' => $consigneeName, 'company_id' => $defaultCompanyId],
                    [
                        'phone' => $consigneePhone ?: '9999999999',
                        'gstin' => $consigneeGstin,
                        'address' => $consigneeAddress,
                        'status' => 'active',
                    ]
                );
            }

            // 6. Origin & Destination Cities
            $originCity = null;
            $fromCityName = isset($firstRow['from_city']) ? trim((string) $firstRow['from_city']) : (isset($firstRow['from']) ? trim((string) $firstRow['from']) : (isset($firstRow['origin_city']) ? trim((string) $firstRow['origin_city']) : null));
            if (!empty($fromCityName)) {
                $originCity = City::firstOrCreate(['name' => $fromCityName], ['status' => 'active']);
            }

            $destinationCity = null;
            $toCityName = isset($firstRow['to_city']) ? trim((string) $firstRow['to_city']) : (isset($firstRow['to']) ? trim((string) $firstRow['to']) : (isset($firstRow['destination_city']) ? trim((string) $firstRow['destination_city']) : null));
            if (!empty($toCityName)) {
                $destinationCity = City::firstOrCreate(['name' => $toCityName], ['status' => 'active']);
            }

            // 7. Builty Header Data
            $freightCharges = $this->parseAmount($firstRow['freight_charges'] ?? $firstRow['freight'] ?? 0);
            $biltyAdvanceAmount = $this->parseAmount($firstRow['bilty_advance_amount'] ?? $firstRow['advance_amount'] ?? $firstRow['bilty_advance'] ?? 0);
            $biltyTotalAmount = $this->parseAmount($firstRow['bilty_total_amount'] ?? $firstRow['total_amount'] ?? $firstRow['total'] ?? 0);

            $biltyStatusRaw = $firstRow['bilty_status'] ?? $firstRow['status'] ?? 'pending';
            $biltyStatus = strtolower(trim((string) $biltyStatusRaw));
            if (!in_array($biltyStatus, ['pending', 'planned', 'dispatched', 'in_transit', 'delivered', 'partially_delivered', 'rejected'])) {
                $biltyStatus = 'pending';
            }

            $bulty = null;
            if (!empty($lrNo)) {
                $bulty = Bulty::where('lr_no', $lrNo)->first();
            }

            $isNewBulty = false;
            if (!$bulty) {
                if (empty($lrNo)) {
                    $lrNo = Bulty::generateLRNumber($defaultBranchId);
                }

                $bulty = Bulty::create([
                    'company_id' => $defaultCompanyId,
                    'branch_id' => $defaultBranchId,
                    'lr_no' => $lrNo,
                    'lr_date' => $lrDate ?: now()->format('Y-m-d'),
                    'payment_type' => $paymentType,
                    'vehicle_id' => $vehicle?->id,
                    'driver_id' => $driver?->id,
                    'consignor_id' => $consignor?->id,
                    'consignee_id' => $consignee?->id,
                    'from_city' => $originCity?->id,
                    'to_city' => $destinationCity?->id,
                    'order_number' => $firstRow['order_number'] ?? null,
                    'delivery_number' => $firstRow['delivery_number'] ?? null,
                    'invoice_number' => $firstRow['invoice_number'] ?? null,
                    'invoice_date' => $this->parseDate($firstRow['invoice_date'] ?? null),
                    'eway_bill_no' => $firstRow['eway_bill_no'] ?? null,
                    'freight_charges' => $freightCharges,
                    'advance_amount' => $biltyAdvanceAmount,
                    'total_amount' => $biltyTotalAmount ?: $freightCharges,
                    'status' => $biltyStatus,
                ]);
                $isNewBulty = true;
            } else {
                $updateData = [];
                if ($lrDate) $updateData['lr_date'] = $lrDate;
                if ($paymentType) $updateData['payment_type'] = $paymentType;
                if ($vehicle) $updateData['vehicle_id'] = $vehicle->id;
                if ($driver) $updateData['driver_id'] = $driver->id;
                if ($consignor) $updateData['consignor_id'] = $consignor->id;
                if ($consignee) $updateData['consignee_id'] = $consignee->id;
                if ($originCity) $updateData['from_city'] = $originCity->id;
                if ($destinationCity) $updateData['to_city'] = $destinationCity->id;
                if (!empty($firstRow['order_number'])) $updateData['order_number'] = $firstRow['order_number'];
                if (!empty($firstRow['delivery_number'])) $updateData['delivery_number'] = $firstRow['delivery_number'];
                if (!empty($firstRow['invoice_number'])) $updateData['invoice_number'] = $firstRow['invoice_number'];
                if (!empty($firstRow['invoice_date'])) $updateData['invoice_date'] = $this->parseDate($firstRow['invoice_date']);
                if (!empty($firstRow['eway_bill_no'])) $updateData['eway_bill_no'] = $firstRow['eway_bill_no'];
                if ($biltyAdvanceAmount > 0) $updateData['advance_amount'] = $biltyAdvanceAmount;
                if (!empty($updateData)) {
                    $bulty->update($updateData);
                }
            }

            // 8. Sync Bulty Detail (SAP / Material Documents / Challan / PO / GRN)
            $bultyDetailData = array_filter([
                'mat_doc' => $firstRow['mat_doc'] ?? null,
                'po_no' => $firstRow['po_no'] ?? null,
                'challan_no' => $firstRow['challan_no'] ?? null,
                'challan_date' => $this->parseDate($firstRow['challan_date'] ?? null),
                'grn_no' => $firstRow['grn_no'] ?? null,
                'grn_date' => $this->parseDate($firstRow['grn_date'] ?? null),
                'invoice_doc' => $firstRow['invoice_doc'] ?? ($firstRow['invoice_number'] ?? null),
                'invoice_date' => $this->parseDate($firstRow['invoice_date'] ?? null),
            ]);
            if (!empty($bultyDetailData)) {
                $bulty->bultyDetail()->updateOrCreate(['bulty_id' => $bulty->id], $bultyDetailData);
            }

            // 9. Find or Create Trip
            $trip = Trip::where('builty_id', $bulty->id)->first();
            $isNewTrip = false;
            if (!$trip) {
                $trip = Trip::create([
                    'builty_id' => $bulty->id,
                    'status' => 'pending',
                ]);
                $isNewTrip = true;
            }

            // 10. Process Multi-Row Child Records (Items, Toll, Fuel, AdBlue, Other, Advance)
            $calcItemsTotal = 0;
            $calcItemsWeight = 0;

            foreach ($groupRows as $row) {
                // Item Entry
                $itemName = isset($row['item_name']) ? trim((string) $row['item_name']) : (isset($row['goods_description']) ? trim((string) $row['goods_description']) : null);
                $weight = $this->parseAmount($row['weight'] ?? 0);
                $articles = isset($row['articles']) ? (int) $row['articles'] : 0;
                $packagingType = isset($row['packaging_type']) ? trim((string) $row['packaging_type']) : null;
                $unit = isset($row['unit']) ? trim((string) $row['unit']) : 'MT';
                $freightPerMt = $this->parseAmount($row['freight_per_mt'] ?? 0);
                $itemAmount = $this->parseAmount($row['item_amount'] ?? ($weight * $freightPerMt));

                if (!empty($itemName) || $weight > 0 || $itemAmount > 0) {
                    $itemMaster = !empty($itemName) ? Item::firstOrCreate(['name' => $itemName], ['status' => 'active']) : null;
                    $bulty->bultyItems()->create([
                        'item_id' => $itemMaster?->id,
                        'item_name' => $itemName ?: 'General Goods',
                        'packaging_type' => $packagingType,
                        'articles' => $articles,
                        'weight' => $weight,
                        'unit' => $unit,
                        'freight_per_mt' => $freightPerMt,
                        'amount' => $itemAmount,
                    ]);
                    $calcItemsTotal += $itemAmount;
                    $calcItemsWeight += $weight;
                }

                // FastTag / Toll Entry
                $tollAmount = $this->parseAmount($row['toll_amount'] ?? $row['fasttag_total_amount'] ?? $row['fasttag_amount'] ?? 0);
                $tollTime = $this->parseDateTime($row['toll_time'] ?? $row['transaction_time'] ?? null);
                $tollLocation = isset($row['toll_location']) ? trim((string) $row['toll_location']) : (isset($row['location']) ? trim((string) $row['location']) : null);
                $tollTxnId = isset($row['toll_txn_id']) ? trim((string) $row['toll_txn_id']) : (isset($row['transaction_id']) ? trim((string) $row['transaction_id']) : null);
                $tollOneWay = $this->parseAmount($row['toll_oneway'] ?? $row['one_way'] ?? $tollAmount);
                $tollReturn = $this->parseAmount($row['toll_return'] ?? $row['return'] ?? 0);
                $tollDesc = isset($row['toll_description']) ? trim((string) $row['toll_description']) : (isset($row['description']) ? trim((string) $row['description']) : null);

                if ($tollAmount > 0 || !empty($tollTxnId) || !empty($tollLocation)) {
                    $trip->fastTagDetails()->create([
                        'builty_id' => $bulty->id,
                        'transaction_time' => $tollTime,
                        'amount' => $tollAmount ?: ($tollOneWay + $tollReturn),
                        'description' => $tollDesc ?: 'Toll payment',
                        'transaction_id' => $tollTxnId,
                        'location' => $tollLocation,
                        'one_way' => $tollOneWay,
                        'return' => $tollReturn,
                    ]);
                }

                // Fuel Entry
                $fuelAmount = $this->parseAmount($row['fuel_amount'] ?? 0);
                $fuelQuantity = $this->parseAmount($row['fuel_quantity'] ?? $row['quantity'] ?? 0);
                $fuelRate = $this->parseAmount($row['fuel_rate'] ?? $row['rate'] ?? ($fuelQuantity > 0 ? ($fuelAmount / $fuelQuantity) : 0));
                $fuelCompanyName = isset($row['fuel_company_name']) ? trim((string) $row['fuel_company_name']) : (isset($row['fuel_company']) ? trim((string) $row['fuel_company']) : null);
                $fuelPumpName = isset($row['fuel_pump_name']) ? trim((string) $row['fuel_pump_name']) : (isset($row['fuel_pump']) ? trim((string) $row['fuel_pump']) : null);
                $fuelDate = $this->parseDate($row['fuel_date'] ?? null);
                $fuelKm = $this->parseAmount($row['fuel_km'] ?? $row['km'] ?? 0);
                $fuelPaymentType = isset($row['fuel_payment_type']) ? strtolower(trim((string) $row['fuel_payment_type'])) : 'credit';
                $fuelRemark = isset($row['fuel_remark']) ? trim((string) $row['fuel_remark']) : null;

                if ($fuelAmount > 0 || $fuelQuantity > 0 || !empty($fuelPumpName)) {
                    $fuelCompany = !empty($fuelCompanyName) ? FuelCompany::firstOrCreate(['name' => $fuelCompanyName], ['status' => 'active']) : null;
                    $fuelPump = null;
                    if (!empty($fuelPumpName)) {
                        $fuelPump = FuelPump::firstOrCreate(
                            ['name' => $fuelPumpName],
                            ['fuel_company_id' => $fuelCompany?->id, 'status' => 'active']
                        );
                    }

                    $trip->fuelDetails()->create([
                        'builty_id' => $bulty->id,
                        'date' => $fuelDate ?: $lrDate ?: now()->format('Y-m-d'),
                        'fuel_company_id' => $fuelCompany?->id,
                        'fuel_pump_id' => $fuelPump?->id,
                        'quantity' => $fuelQuantity,
                        'rate' => $fuelRate,
                        'amount' => $fuelAmount ?: ($fuelQuantity * $fuelRate),
                        'km' => $fuelKm,
                        'payment_type' => in_array($fuelPaymentType, ['credit', 'debit', 'cash']) ? $fuelPaymentType : 'credit',
                        'remark' => $fuelRemark,
                    ]);
                }

                // AdBlue Entry
                $adblueAmount = $this->parseAmount($row['adblue_amount'] ?? $row['adblue_total_amount'] ?? 0);
                $adblueQuantity = $this->parseAmount($row['adblue_quantity'] ?? 0);
                $adblueRate = $this->parseAmount($row['adblue_rate'] ?? ($adblueQuantity > 0 ? ($adblueAmount / $adblueQuantity) : 0));
                $adblueCompanyName = isset($row['adblue_company_name']) ? trim((string) $row['adblue_company_name']) : (isset($row['adblue_company']) ? trim((string) $row['adblue_company']) : null);
                $adblueDate = $this->parseDate($row['adblue_date'] ?? null);
                $adblueKm = $this->parseAmount($row['adblue_km'] ?? 0);
                $adbluePaymentType = isset($row['adblue_payment_type']) ? strtolower(trim((string) $row['adblue_payment_type'])) : 'cash';

                if ($adblueAmount > 0 || $adblueQuantity > 0 || !empty($adblueCompanyName)) {
                    $adblueCompany = !empty($adblueCompanyName) ? AdBlueCompany::firstOrCreate(['name' => $adblueCompanyName], ['status' => 'active']) : null;

                    $trip->adblueDetails()->create([
                        'builty_id' => $bulty->id,
                        'date' => $adblueDate ?: $lrDate ?: now()->format('Y-m-d'),
                        'adblue_company_id' => $adblueCompany?->id,
                        'quantity' => $adblueQuantity,
                        'rate' => $adblueRate,
                        'amount' => $adblueAmount ?: ($adblueQuantity * $adblueRate),
                        'km' => $adblueKm,
                        'payment_type' => in_array($adbluePaymentType, ['credit', 'debit', 'cash']) ? $adbluePaymentType : 'cash',
                    ]);
                }

                // Other Expense Entry
                $otherTitle = isset($row['other_expense_title']) ? trim((string) $row['other_expense_title']) : (isset($row['other_title']) ? trim((string) $row['other_title']) : null);
                $otherAmount = $this->parseAmount($row['other_expense_amount'] ?? $row['other_amount'] ?? 0);
                $otherDate = $this->parseDate($row['other_expense_date'] ?? null);
                $otherRemark = isset($row['other_expense_remark']) ? trim((string) $row['other_expense_remark']) : null;

                if ($otherAmount > 0 || !empty($otherTitle)) {
                    $trip->otherAmountDetails()->create([
                        'builty_id' => $bulty->id,
                        'title' => $otherTitle ?: 'Miscellaneous',
                        'amount' => $otherAmount,
                        'date' => $otherDate ?: $lrDate ?: now()->format('Y-m-d'),
                        'remark' => $otherRemark,
                    ]);
                }

                // Advance Entry
                $advanceAmount = $this->parseAmount($row['advance_amount'] ?? $row['trip_advance_total_amount'] ?? $row['trip_advance_amount'] ?? 0);
                $advFuelCompanyName = isset($row['advance_fuel_company_name']) ? trim((string) $row['advance_fuel_company_name']) : (isset($row['advance_company']) ? trim((string) $row['advance_company']) : null);
                $advFuelPumpName = isset($row['advance_fuel_pump_name']) ? trim((string) $row['advance_fuel_pump_name']) : (isset($row['advance_pump']) ? trim((string) $row['advance_pump']) : null);
                $advanceDate = $this->parseDate($row['advance_date'] ?? null);
                $advPaymentType = isset($row['advance_payment_type']) ? strtolower(trim((string) $row['advance_payment_type'])) : 'cash';
                $advRemark = isset($row['advance_remark']) ? trim((string) $row['advance_remark']) : null;

                if ($advanceAmount > 0 || !empty($advFuelPumpName)) {
                    $advCompany = !empty($advFuelCompanyName) ? FuelCompany::firstOrCreate(['name' => $advFuelCompanyName], ['status' => 'active']) : null;
                    $advPump = null;
                    if (!empty($advFuelPumpName)) {
                        $advPump = FuelPump::firstOrCreate(
                            ['name' => $advFuelPumpName],
                            ['fuel_company_id' => $advCompany?->id, 'status' => 'active']
                        );
                    }

                    $trip->advanceDetails()->create([
                        'builty_id' => $bulty->id,
                        'date' => $advanceDate ?: $lrDate ?: now()->format('Y-m-d'),
                        'fuel_company_id' => $advCompany?->id,
                        'fuel_pump_id' => $advPump?->id,
                        'advance_amount' => $advanceAmount,
                        'payment_type' => in_array($advPaymentType, ['credit', 'debit', 'cash']) ? $advPaymentType : 'cash',
                        'remark' => $advRemark,
                    ]);
                }
            }

            // 11. Recalculate and Synchronize Totals for Trip & Builty
            $tripFastTagSum = (float) $trip->fastTagDetails()->sum('amount');
            $tripFuelSum = (float) $trip->fuelDetails()->sum('amount');
            $tripAdblueSum = (float) $trip->adblueDetails()->sum('amount');
            $tripOtherSum = (float) $trip->otherAmountDetails()->sum('amount');
            $tripAdvanceSum = (float) $trip->advanceDetails()->sum('advance_amount');

            $tripStatusRaw = $firstRow['trip_status'] ?? $firstRow['status'] ?? 'pending';
            $tripStatus = strtolower(trim((string) $tripStatusRaw));
            if (!in_array($tripStatus, ['pending', 'complete', 'reject'])) {
                $tripStatus = 'pending';
            }

            $trip->update([
                'fasttag_total_amount' => $tripFastTagSum ?: $this->parseAmount($firstRow['fasttag_total_amount'] ?? 0),
                'fuel_amount' => $tripFuelSum ?: $this->parseAmount($firstRow['fuel_amount'] ?? 0),
                'adblue_total_amount' => $tripAdblueSum ?: $this->parseAmount($firstRow['adblue_total_amount'] ?? 0),
                'other_amount' => $tripOtherSum ?: $this->parseAmount($firstRow['other_amount'] ?? 0),
                'advance_total_amount' => $tripAdvanceSum ?: $this->parseAmount($firstRow['trip_advance_total_amount'] ?? $firstRow['advance_total_amount'] ?? 0),
                'status' => $tripStatus,
            ]);

            // Sync Builty Total Amount if items sum exists
            if ($calcItemsTotal > 0 && ($bulty->total_amount == 0 || $freightCharges == 0)) {
                $bulty->update([
                    'freight_charges' => $calcItemsTotal,
                    'total_amount' => $calcItemsTotal,
                ]);
            }

            if ($isNewTrip || $isNewBulty) {
                $this->imported++;
            } else {
                $this->updated++;
            }
        }
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
            }
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    protected function parseAmount($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (is_string($value)) {
            $clean = preg_replace('/[^0-9.]/', '', $value);
            return is_numeric($clean) ? (float) $clean : 0.0;
        }
        return 0.0;
    }

    public function rules(): array
    {
        return [];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    public function getUnmatchedLrs(): array
    {
        return $this->unmatchedLrs;
    }

    public function getHeadings(): array
    {
        return $this->headings;
    }
}
