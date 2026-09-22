<?php

namespace App\Imports;

use App\Models\AdBlueCompany;
use App\Models\Branch;
use App\Models\BiltyAdvanceDetail;
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
use App\Models\Supplier;
use App\Models\Trip;
use App\Models\TripAdBlueDetail;
use App\Models\TripAdvanceDetail;
use App\Models\TripFastTagDetail;
use App\Models\TripFuelDetail;
use App\Models\TripOtherAmountDetail;
use App\Models\Unit;
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

            // 1. Resolve Company & Branch
            $resolvedCompanyId = $defaultCompanyId;
            $companyInput = isset($firstRow['company']) ? trim((string) $firstRow['company']) : (isset($firstRow['company_name']) ? trim((string) $firstRow['company_name']) : null);
            if (!empty($companyInput)) {
                $foundCompany = Company::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($companyInput)])->first()
                    ?? Company::where('name', 'like', "%{$companyInput}%")->first();
                if ($foundCompany) {
                    $resolvedCompanyId = $foundCompany->id;
                }
            }

            $resolvedBranchId = $defaultBranchId;
            $branchInput = isset($firstRow['branch']) ? trim((string) $firstRow['branch']) : (isset($firstRow['branch_name']) ? trim((string) $firstRow['branch_name']) : null);
            if (!empty($branchInput)) {
                $foundBranch = Branch::where('company_id', $resolvedCompanyId)->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($branchInput)])->first()
                    ?? Branch::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($branchInput)])->first()
                    ?? Branch::where('name', 'like', "%{$branchInput}%")->first();
                if ($foundBranch) {
                    $resolvedBranchId = $foundBranch->id;
                }
            }

            // 2. Resolve LR No & Date
            $lrNo = str_starts_with($groupKey, '__AUTO__') ? null : $groupKey;
            $lrDate = $this->parseDate($firstRow['lr_date'] ?? $firstRow['date'] ?? null);

            $paymentTypeRaw = $firstRow['payment_type'] ?? 'topay';
            $paymentType = strtolower(trim((string) $paymentTypeRaw));
            if (!in_array($paymentType, ['paid', 'topay', 'tobill'])) {
                $paymentType = 'topay';
            }

            // 3. Form Number, E-Way Bill Dates & SAP PO Number
            $formNo = isset($firstRow['form_number']) ? trim((string) $firstRow['form_number']) : (isset($firstRow['form_no']) ? trim((string) $firstRow['form_no']) : (isset($firstRow['from_no']) ? trim((string) $firstRow['from_no']) : null));
            $generateDate = $this->parseDate($firstRow['generate_date'] ?? $firstRow['genrate_date'] ?? $firstRow['generation_date'] ?? null);
            $expiryDate = $this->parseDate($firstRow['expiry_date'] ?? null);
            $poNo = isset($firstRow['po_no']) ? trim((string) $firstRow['po_no']) : (isset($firstRow['po_number']) ? trim((string) $firstRow['po_number']) : null);

            // 4. Vehicle Lookup or Creation
            $vehicle = null;
            $vehicleNumber = isset($firstRow['vehicle_number']) ? trim((string) $firstRow['vehicle_number']) : (isset($firstRow['truck_no']) ? trim((string) $firstRow['truck_no']) : null);
            $vehicleType = isset($firstRow['vehicle_type']) ? trim((string) $firstRow['vehicle_type']) : 'Truck';
            if (!empty($vehicleNumber)) {
                $vehicle = Vehicle::whereRaw('REPLACE(LOWER(vehicle_number), " ", "") = ?', [str_replace(' ', '', strtolower($vehicleNumber))])->first()
                    ?: Vehicle::firstOrCreate(
                        ['vehicle_number' => $vehicleNumber],
                        ['status' => 'active', 'vehicle_type' => $vehicleType ?: 'Truck']
                    );
            }

            // 5. Driver Lookup or Creation
            $driver = null;
            $driverName = isset($firstRow['driver_name']) ? trim((string) $firstRow['driver_name']) : null;
            $driverPhone = isset($firstRow['driver_phone']) ? trim((string) $firstRow['driver_phone']) : null;
            if (!empty($driverPhone) || !empty($driverName)) {
                $driverQuery = Driver::query();
                if (!empty($driverPhone) && $driverPhone !== '9999999999') {
                    $driverQuery->where('phone', $driverPhone);
                } elseif (!empty($driverName)) {
                    $driverQuery->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($driverName)]);
                }
                $driver = $driverQuery->first();

                if (!$driver && !empty($driverName)) {
                    $driver = Driver::create([
                        'name' => $driverName,
                        'phone' => $driverPhone ?: '9999999999',
                        'license_number' => 'DL-' . strtoupper(substr(md5($driverName . time() . uniqid()), 0, 8)),
                        'status' => 'active',
                    ]);
                }
            }

            // 6. Consignor Lookup with Exact Name Priority & Dynamic Creation
            $consignor = null;
            $consignorName = isset($firstRow['consignor_name']) ? trim((string) $firstRow['consignor_name']) : (isset($firstRow['consignor']) ? trim((string) $firstRow['consignor']) : (isset($firstRow['consignor_party']) ? trim((string) $firstRow['consignor_party']) : (isset($firstRow['sender_name']) ? trim((string) $firstRow['sender_name']) : null)));
            $consignorPhone = isset($firstRow['consignor_phone']) ? trim((string) $firstRow['consignor_phone']) : null;
            $consignorGstin = isset($firstRow['consignor_gstin']) ? trim((string) $firstRow['consignor_gstin']) : null;
            $consignorAddress = isset($firstRow['consignor_address']) ? trim((string) $firstRow['consignor_address']) : null;

            if (!empty($consignorName) || !empty($consignorGstin)) {
                if (!empty($consignorName)) {
                    $consignor = Consignor::where('company_id', $resolvedCompanyId)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($consignorName)])
                        ->first()
                        ?: Consignor::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($consignorName)])->first();
                }
                if (!$consignor && !empty($consignorGstin)) {
                    $consignor = Consignor::where('gstin', $consignorGstin)->first();
                }

                if (!$consignor && !empty($consignorName)) {
                    $consignor = Consignor::create([
                        'company_id' => $resolvedCompanyId,
                        'name' => $consignorName,
                        'phone' => $consignorPhone ?: '9999999999',
                        'gstin' => $consignorGstin,
                        'address' => $consignorAddress,
                        'status' => 'active',
                    ]);
                }
            }

            // 7. Consignee Lookup with Exact Name Priority & Dynamic Creation (Prevents mismatch e.g. Dholka -> Dhandhuka)
            $consignee = null;
            $consigneeName = isset($firstRow['consignee_name']) ? trim((string) $firstRow['consignee_name']) : (isset($firstRow['consignee']) ? trim((string) $firstRow['consignee']) : (isset($firstRow['consignee_party']) ? trim((string) $firstRow['consignee_party']) : (isset($firstRow['receiver_name']) ? trim((string) $firstRow['receiver_name']) : (isset($firstRow['customer_name']) ? trim((string) $firstRow['customer_name']) : (isset($firstRow['party_name']) ? trim((string) $firstRow['party_name']) : null)))));
            $consigneePhone = isset($firstRow['consignee_phone']) ? trim((string) $firstRow['consignee_phone']) : null;
            $consigneeGstin = isset($firstRow['consignee_gstin']) ? trim((string) $firstRow['consignee_gstin']) : null;
            $consigneeAddress = isset($firstRow['consignee_address']) ? trim((string) $firstRow['consignee_address']) : null;

            if (!empty($consigneeName) || !empty($consigneeGstin)) {
                if (!empty($consigneeName)) {
                    $consignee = Consignee::where('company_id', $resolvedCompanyId)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($consigneeName)])
                        ->first()
                        ?: Consignee::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($consigneeName)])->first();
                }
                if (!$consignee && !empty($consigneeGstin)) {
                    $consignee = Consignee::where('gstin', $consigneeGstin)->first();
                }

                if (!$consignee && !empty($consigneeName)) {
                    $consignee = Consignee::create([
                        'company_id' => $resolvedCompanyId,
                        'name' => $consigneeName,
                        'phone' => $consigneePhone ?: '9999999999',
                        'gstin' => $consigneeGstin,
                        'address' => $consigneeAddress,
                        'status' => 'active',
                    ]);
                }
            }

            // 8. Origin & Destination Cities Lookup
            $originCity = null;
            $fromCityName = isset($firstRow['from_city']) ? trim((string) $firstRow['from_city']) : (isset($firstRow['from']) ? trim((string) $firstRow['from']) : (isset($firstRow['origin_city']) ? trim((string) $firstRow['origin_city']) : null));
            $fromState = isset($firstRow['from_state']) ? trim((string) $firstRow['from_state']) : (isset($firstRow['origin_state']) ? trim((string) $firstRow['origin_state']) : (isset($firstRow['state']) ? trim((string) $firstRow['state']) : 'Gujarat'));
            if (!empty($fromCityName)) {
                $originCity = City::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($fromCityName)])->first()
                    ?: City::create(['name' => $fromCityName, 'state' => $fromState ?: 'Gujarat', 'status' => 'active']);
            }

            $destinationCity = null;
            $toCityName = isset($firstRow['to_city']) ? trim((string) $firstRow['to_city']) : (isset($firstRow['to']) ? trim((string) $firstRow['to']) : (isset($firstRow['destination_city']) ? trim((string) $firstRow['destination_city']) : null));
            $toState = isset($firstRow['to_state']) ? trim((string) $firstRow['to_state']) : (isset($firstRow['destination_state']) ? trim((string) $firstRow['destination_state']) : (isset($firstRow['state']) ? trim((string) $firstRow['state']) : 'Gujarat'));
            if (!empty($toCityName)) {
                $destinationCity = City::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($toCityName)])->first()
                    ?: City::create(['name' => $toCityName, 'state' => $toState ?: 'Gujarat', 'status' => 'active']);
            }

            // 9. Builty Header Amounts, Remarks & Commission
            $freightCharges = $this->parseAmount($firstRow['freight_charges'] ?? $firstRow['freight'] ?? 0);
            $biltyAdvanceAmount = $this->parseAmount($firstRow['bilty_advance_amount'] ?? $firstRow['advance_amount'] ?? $firstRow['bilty_advance'] ?? $firstRow['advance'] ?? 0);
            $biltyTotalAmount = $this->parseAmount($firstRow['bilty_total_amount'] ?? $firstRow['total_amount'] ?? $firstRow['total'] ?? 0);
            $biltyCommission = $this->parseAmount($firstRow['bilty_commission'] ?? $firstRow['commission'] ?? $firstRow['builty_commission'] ?? $firstRow['comm'] ?? 0);
            $biltyRemark = isset($firstRow['remark']) ? trim((string) $firstRow['remark']) : (isset($firstRow['remarks']) ? trim((string) $firstRow['remarks']) : (isset($firstRow['bilty_remark']) ? trim((string) $firstRow['bilty_remark']) : null));

            $bulty = null;
            if (!empty($lrNo)) {
                $bulty = Bulty::where('lr_no', $lrNo)->first();
            }

            $isNewBulty = false;
            if (!$bulty) {
                if (empty($lrNo)) {
                    $lrNo = Bulty::generateLRNumber($resolvedBranchId);
                }

                $initialTotal = $biltyTotalAmount ?: $freightCharges;
                $initialRemaining = max(0, $initialTotal - $biltyAdvanceAmount);

                $bulty = Bulty::create([
                    'company_id' => $resolvedCompanyId,
                    'branch_id' => $resolvedBranchId,
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
                    'from_no' => $formNo,
                    'invoice_number' => $firstRow['invoice_number'] ?? null,
                    'invoice_date' => $this->parseDate($firstRow['invoice_date'] ?? null),
                    'eway_bill_no' => $firstRow['eway_bill_no'] ?? null,
                    'generation_date' => $generateDate,
                    'expiry_date' => $expiryDate,
                    'freight_charges' => $freightCharges,
                    'advance_amount' => $biltyAdvanceAmount,
                    'remaining_amount' => $initialRemaining,
                    'bilty_commission' => $biltyCommission,
                    'remark' => $biltyRemark,
                    'total_amount' => $initialTotal,
                    'status' => 'pending',
                ]);
                $isNewBulty = true;
            } else {
                $updateData = [];
                if ($resolvedCompanyId) $updateData['company_id'] = $resolvedCompanyId;
                if ($resolvedBranchId) $updateData['branch_id'] = $resolvedBranchId;
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
                if (!empty($formNo)) $updateData['from_no'] = $formNo;
                if (!empty($firstRow['invoice_number'])) $updateData['invoice_number'] = $firstRow['invoice_number'];
                if (!empty($firstRow['invoice_date'])) $updateData['invoice_date'] = $this->parseDate($firstRow['invoice_date']);
                if (!empty($firstRow['eway_bill_no'])) $updateData['eway_bill_no'] = $firstRow['eway_bill_no'];
                if ($generateDate) $updateData['generation_date'] = $generateDate;
                if ($expiryDate) $updateData['expiry_date'] = $expiryDate;
                if ($biltyAdvanceAmount > 0) {
                    $updateData['advance_amount'] = $biltyAdvanceAmount;
                    $updateData['remaining_amount'] = max(0, ($bulty->total_amount ?: $freightCharges) - $biltyAdvanceAmount);
                }
                if ($biltyCommission > 0) $updateData['bilty_commission'] = $biltyCommission;
                if (!empty($biltyRemark)) $updateData['remark'] = $biltyRemark;
                if (!empty($updateData)) {
                    $bulty->update($updateData);
                }
            }

            // Sync Bilty Advance Detail if advance amount is provided
            if ($biltyAdvanceAmount > 0) {
                BiltyAdvanceDetail::updateOrCreate(
                    [
                        'bulty_id' => $bulty->id,
                        'company_id' => $resolvedCompanyId,
                    ],
                    [
                        'branch_id' => $resolvedBranchId,
                        'date' => $lrDate ?: now()->format('Y-m-d'),
                        'advance_amount' => $biltyAdvanceAmount,
                        'remarks' => $biltyRemark ?: 'Advance from Excel import',
                    ]
                );
            }

            // 10. Sync Bulty Detail (PO No, Invoice & GRN / Logistics Details)
            $supplier = null;
            $supplierName = $this->getRowValue($firstRow, ['supplier', 'supplier_name', 'supplier_id']);
            if (empty($supplierName)) {
                foreach ($groupRows as $gRow) {
                    $sVal = $this->getRowValue($gRow, ['supplier', 'supplier_name', 'supplier_id']);
                    if (!empty($sVal)) {
                        $supplierName = $sVal;
                        break;
                    }
                }
            }

            if (!empty($supplierName)) {
                if (is_numeric($supplierName)) {
                    $supplier = Supplier::find($supplierName);
                }
                if (!$supplier) {
                    $supplier = Supplier::where('company_id', $resolvedCompanyId)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($supplierName)])
                        ->first()
                        ?: Supplier::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($supplierName)])->first();
                }
                if (!$supplier) {
                    $supplier = Supplier::create([
                        'company_id' => $resolvedCompanyId,
                        'branch_id' => $resolvedBranchId,
                        'name' => $supplierName,
                        'status' => 'active',
                    ]);
                }
            }

            $postingDateRaw = $this->getRowValue($firstRow, ['posting_date', 'posting date', 'post_date']);
            $matDoc = $this->getRowValue($firstRow, ['mat_doc', 'mat doc', 'material_doc', 'material_document']);
            $gateEntryNo = $this->getRowValue($firstRow, ['gate_entry_no', 'gate entry no', 'gate_entry', 'gateentryno']);
            $supplierNo = $this->getRowValue($firstRow, ['supplier_no', 'supplier no', 'supplier_code', 'vendor_no']);
            $challanNo = $this->getRowValue($firstRow, ['challan_no', 'challan no', 'challan_number', 'challan']);
            $gateOutDateRaw = $this->getRowValue($firstRow, ['gate_out_date', 'gate out date', 'gate_out']);
            $poItem = $this->getRowValue($firstRow, ['po_item', 'po item', 'po_item_no', 'item_no']);
            $transporterCode = $this->getRowValue($firstRow, ['transporter_code', 'transporter code', 'trans_code']);
            $transporterName = $this->getRowValue($firstRow, ['transporter_name', 'transporter name', 'transporter']);
            $materialName = $this->getRowValue($firstRow, ['material_name', 'material name', 'material']);
            $challanQtyRaw = $this->getRowValue($firstRow, ['challan_qty', 'challan qty', 'challan_quantity']);
            $finalWgtRaw = $this->getRowValue($firstRow, ['final_wgt', 'final wgt', 'final_weight']);

            foreach ($groupRows as $gRow) {
                if ($postingDateRaw === null) $postingDateRaw = $this->getRowValue($gRow, ['posting_date', 'posting date', 'post_date']);
                if ($matDoc === null) $matDoc = $this->getRowValue($gRow, ['mat_doc', 'mat doc', 'material_doc', 'material_document']);
                if ($gateEntryNo === null) $gateEntryNo = $this->getRowValue($gRow, ['gate_entry_no', 'gate entry no', 'gate_entry', 'gateentryno']);
                if ($supplierNo === null) $supplierNo = $this->getRowValue($gRow, ['supplier_no', 'supplier no', 'supplier_code', 'vendor_no']);
                if ($challanNo === null) $challanNo = $this->getRowValue($gRow, ['challan_no', 'challan no', 'challan_number', 'challan']);
                if ($gateOutDateRaw === null) $gateOutDateRaw = $this->getRowValue($gRow, ['gate_out_date', 'gate out date', 'gate_out']);
                if ($poItem === null) $poItem = $this->getRowValue($gRow, ['po_item', 'po item', 'po_item_no', 'item_no']);
                if ($transporterCode === null) $transporterCode = $this->getRowValue($gRow, ['transporter_code', 'transporter code', 'trans_code']);
                if ($transporterName === null) $transporterName = $this->getRowValue($gRow, ['transporter_name', 'transporter name', 'transporter']);
                if ($materialName === null) $materialName = $this->getRowValue($gRow, ['material_name', 'material name', 'material']);
                if ($challanQtyRaw === null) $challanQtyRaw = $this->getRowValue($gRow, ['challan_qty', 'challan qty', 'challan_quantity']);
                if ($finalWgtRaw === null) $finalWgtRaw = $this->getRowValue($gRow, ['final_wgt', 'final wgt', 'final_weight']);
            }

            $postingDate = $this->parseDate($postingDateRaw);
            $gateOutDate = $this->parseDate($gateOutDateRaw);
            $challanQty = $challanQtyRaw !== null ? $this->parseAmount($challanQtyRaw) : null;
            $finalWgt = $finalWgtRaw !== null ? $this->parseAmount($finalWgtRaw) : null;

            $bultyDetailData = array_filter([
                'po_no' => $poNo,
                'invoice_doc' => $firstRow['invoice_number'] ?? null,
                'invoice_date' => $this->parseDate($firstRow['invoice_date'] ?? null),
                'posting_date' => $postingDate,
                'mat_doc' => $matDoc,
                'gate_entry_no' => $gateEntryNo,
                'supplier_id' => $supplier?->id,
                'supplier_no' => $supplierNo,
                'challan_no' => $challanNo,
                'gate_out_date' => $gateOutDate,
                'po_item' => $poItem,
                'transporter_code' => $transporterCode,
                'transporter_name' => $transporterName,
                'material_name' => $materialName,
                'challan_qty' => $challanQty,
                'final_wgt' => $finalWgt,
            ], fn ($val) => $val !== null && $val !== '');

            if (!empty($bultyDetailData)) {
                $bulty->bultyDetail()->updateOrCreate(['bulty_id' => $bulty->id], $bultyDetailData);
            }

            // 11. Find or Create Trip
            $trip = Trip::where('builty_id', $bulty->id)->first();
            $isNewTrip = false;
            if (!$trip) {
                $trip = Trip::create([
                    'builty_id' => $bulty->id,
                    'status' => 'pending',
                ]);
                $isNewTrip = true;
            }

            // 12. Process Multi-Row Child Records (Items, Toll, Fuel, AdBlue, Other, Advance)
            // If updating existing Bulty/Trip with Excel rows, clean existing child details to prevent duplication
            if (!$isNewBulty) {
                $bulty->bultyItems()->delete();
                $trip->fastTagDetails()->delete();
                $trip->fuelDetails()->delete();
                $trip->adblueDetails()->delete();
                $trip->otherAmountDetails()->delete();
                $trip->advanceDetails()->delete();
            }

            $calcItemsTotal = 0;
            $calcItemsWeight = 0;

            foreach ($groupRows as $row) {
                // Item Entry (Supports Multi-Items per LR)
                $itemName = $this->getRowValue($row, ['item_name', 'item', 'goods_description', 'material_name', 'material']);
                $weight = $this->parseAmount($row['weight'] ?? $row['final_wgt'] ?? $row['final weight'] ?? $row['challan_qty'] ?? $row['challan qty'] ?? 0);
                $articles = isset($row['articles']) ? (int) $row['articles'] : 0;
                $packagingType = isset($row['packaging_type']) ? trim((string) $row['packaging_type']) : null;
                $unit = isset($row['unit']) ? trim((string) $row['unit']) : (isset($row['units']) ? trim((string) $row['units']) : (isset($row['item_unit']) ? trim((string) $row['item_unit']) : (isset($row['uom']) ? trim((string) $row['uom']) : 'Ton')));
                if (empty($unit)) {
                    $unit = 'Ton';
                }

                // Register unit in Unit master if not already present
                Unit::firstOrCreate(
                    ['name' => $unit],
                    ['description' => $unit, 'status' => 'active']
                );

                $freightPerMt = $this->parseAmount($row['freight_per_mt'] ?? $row['rate'] ?? 0);
                // Calculate item amount automatically (weight * freight_per_mt) or parse if given
                $itemAmount = $this->parseAmount($row['item_amount'] ?? ($weight > 0 && $freightPerMt > 0 ? ($weight * $freightPerMt) : 0));

                if (!empty($itemName) || $weight > 0 || $itemAmount > 0) {
                    $itemMaster = !empty($itemName) ? (Item::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($itemName)])->first() ?: Item::create(['name' => $itemName, 'status' => 'active'])) : null;
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

                // FastTag / Toll Entry (Auto-calculated: one_way + return)
                $tollOneWay = $this->parseAmount($row['toll_oneway'] ?? $row['one_way'] ?? 0);
                $tollReturn = $this->parseAmount($row['toll_return'] ?? $row['return'] ?? 0);
                $tollAmount = $this->parseAmount($row['toll_amount'] ?? ($tollOneWay + $tollReturn));
                $tollTime = $this->parseDateTime($row['toll_time'] ?? $row['transaction_time'] ?? null);
                $tollLocation = isset($row['toll_location']) ? trim((string) $row['toll_location']) : (isset($row['location']) ? trim((string) $row['location']) : null);
                $tollTxnId = isset($row['toll_txn_id']) ? trim((string) $row['toll_txn_id']) : (isset($row['transaction_id']) ? trim((string) $row['transaction_id']) : null);
                $tollDesc = isset($row['toll_description']) ? trim((string) $row['toll_description']) : (isset($row['description']) ? trim((string) $row['description']) : null);

                if ($tollAmount > 0 || $tollOneWay > 0 || !empty($tollTxnId) || !empty($tollLocation)) {
                    $trip->fastTagDetails()->create([
                        'builty_id' => $bulty->id,
                        'transaction_time' => $tollTime,
                        'amount' => $tollAmount ?: ($tollOneWay + $tollReturn),
                        'description' => $tollDesc ?: 'Toll payment',
                        'transaction_id' => $tollTxnId,
                        'location' => $tollLocation,
                        'one_way' => $tollOneWay ?: $tollAmount,
                        'return' => $tollReturn,
                    ]);
                }

                // Fuel Entry (Auto-calculated: quantity * rate)
                $fuelQuantity = $this->parseAmount($row['fuel_quantity'] ?? $row['quantity'] ?? 0);
                $fuelRate = $this->parseAmount($row['fuel_rate'] ?? 0);
                $fuelAmount = $this->parseAmount($row['fuel_amount'] ?? ($fuelQuantity * $fuelRate));
                $fuelCompanyName = isset($row['fuel_company_name']) ? trim((string) $row['fuel_company_name']) : (isset($row['fuel_company']) ? trim((string) $row['fuel_company']) : null);
                $fuelPumpName = isset($row['fuel_pump_name']) ? trim((string) $row['fuel_pump_name']) : (isset($row['fuel_pump']) ? trim((string) $row['fuel_pump']) : null);
                $fuelDate = $this->parseDate($row['fuel_date'] ?? null);
                $fuelKm = $this->parseAmount($row['fuel_km'] ?? $row['km'] ?? 0);
                $fuelPaymentType = isset($row['fuel_payment_type']) ? strtolower(trim((string) $row['fuel_payment_type'])) : 'credit';
                $fuelRemark = isset($row['fuel_remark']) ? trim((string) $row['fuel_remark']) : null;

                if ($fuelAmount > 0 || $fuelQuantity > 0 || !empty($fuelPumpName)) {
                    $fuelCompany = !empty($fuelCompanyName) ? (FuelCompany::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($fuelCompanyName)])->first() ?: FuelCompany::create(['name' => $fuelCompanyName, 'status' => 'active'])) : null;
                    $fuelPump = null;
                    if (!empty($fuelPumpName)) {
                        $fuelPump = FuelPump::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($fuelPumpName)])->first()
                            ?: FuelPump::create(
                                ['name' => $fuelPumpName, 'fuel_company_id' => $fuelCompany?->id, 'status' => 'active']
                            );
                    }

                    $trip->fuelDetails()->create([
                        'builty_id' => $bulty->id,
                        'date' => $fuelDate ?: $lrDate ?: now()->format('Y-m-d'),
                        'fuel_company_id' => $fuelCompany?->id,
                        'fuel_pump_id' => $fuelPump?->id,
                        'quantity' => $fuelQuantity,
                        'rate' => $fuelRate ?: ($fuelQuantity > 0 && $fuelAmount > 0 ? ($fuelAmount / $fuelQuantity) : 0),
                        'amount' => $fuelAmount ?: ($fuelQuantity * $fuelRate),
                        'km' => $fuelKm,
                        'payment_type' => in_array($fuelPaymentType, ['credit', 'debit', 'cash']) ? $fuelPaymentType : 'credit',
                        'remark' => $fuelRemark,
                    ]);
                }

                // AdBlue Entry (Auto-calculated: quantity * rate)
                $adblueQuantity = $this->parseAmount($row['adblue_quantity'] ?? 0);
                $adblueRate = $this->parseAmount($row['adblue_rate'] ?? 0);
                $adblueAmount = $this->parseAmount($row['adblue_amount'] ?? $row['adblue_total_amount'] ?? ($adblueQuantity * $adblueRate));
                $adblueCompanyName = isset($row['adblue_company_name']) ? trim((string) $row['adblue_company_name']) : (isset($row['adblue_company']) ? trim((string) $row['adblue_company']) : null);
                $adblueDate = $this->parseDate($row['adblue_date'] ?? null);
                $adblueKm = $this->parseAmount($row['adblue_km'] ?? 0);
                $adbluePaymentType = isset($row['adblue_payment_type']) ? strtolower(trim((string) $row['adblue_payment_type'])) : 'cash';

                if ($adblueAmount > 0 || $adblueQuantity > 0 || !empty($adblueCompanyName)) {
                    $adblueCompany = !empty($adblueCompanyName) ? (AdBlueCompany::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($adblueCompanyName)])->first() ?: AdBlueCompany::create(['name' => $adblueCompanyName, 'status' => 'active'])) : null;

                    $trip->adblueDetails()->create([
                        'builty_id' => $bulty->id,
                        'date' => $adblueDate ?: $lrDate ?: now()->format('Y-m-d'),
                        'adblue_company_id' => $adblueCompany?->id,
                        'quantity' => $adblueQuantity,
                        'rate' => $adblueRate ?: ($adblueQuantity > 0 && $adblueAmount > 0 ? ($adblueAmount / $adblueQuantity) : 0),
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

                // Advance Entry (Supports Multi-Advances per LR)
                $advanceAmount = $this->parseAmount($row['advance_amount'] ?? $row['trip_advance_total_amount'] ?? $row['trip_advance_amount'] ?? 0);
                $advFuelCompanyName = isset($row['advance_fuel_company_name']) ? trim((string) $row['advance_fuel_company_name']) : (isset($row['advance_company']) ? trim((string) $row['advance_company']) : null);
                $advFuelPumpName = isset($row['advance_fuel_pump_name']) ? trim((string) $row['advance_fuel_pump_name']) : (isset($row['advance_pump']) ? trim((string) $row['advance_pump']) : null);
                $advanceDate = $this->parseDate($row['advance_date'] ?? null);
                $advPaymentType = isset($row['advance_payment_type']) ? strtolower(trim((string) $row['advance_payment_type'])) : 'cash';
                $advRemark = isset($row['advance_remark']) ? trim((string) $row['advance_remark']) : null;

                if ($advanceAmount > 0 || !empty($advFuelPumpName)) {
                    $advCompany = !empty($advFuelCompanyName) ? (FuelCompany::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($advFuelCompanyName)])->first() ?: FuelCompany::create(['name' => $advFuelCompanyName, 'status' => 'active'])) : null;
                    $advPump = null;
                    if (!empty($advFuelPumpName)) {
                        $advPump = FuelPump::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($advFuelPumpName)])->first()
                            ?: FuelPump::create(
                                ['name' => $advFuelPumpName, 'fuel_company_id' => $advCompany?->id, 'status' => 'active']
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

            // 13. Recalculate and Synchronize Totals for Trip & Builty
            $tripFastTagSum = (float) $trip->fastTagDetails()->sum('amount');
            $tripFuelSum = (float) $trip->fuelDetails()->sum('amount');
            $tripAdblueSum = (float) $trip->adblueDetails()->sum('amount');
            $tripOtherSum = (float) $trip->otherAmountDetails()->sum('amount');
            $tripAdvanceSum = (float) $trip->advanceDetails()->sum('advance_amount');

            $trip->update([
                'fasttag_total_amount' => $tripFastTagSum,
                'fuel_amount' => $tripFuelSum,
                'adblue_total_amount' => $tripAdblueSum,
                'other_amount' => $tripOtherSum,
                'advance_total_amount' => $tripAdvanceSum,
            ]);

            // Sync Builty Total Amount & Remaining Amount from items
            $finalBuiltyTotal = $calcItemsTotal > 0 ? $calcItemsTotal : ($freightCharges > 0 ? $freightCharges : (float) $bulty->total_amount);
            $finalAdvance = $biltyAdvanceAmount > 0 ? $biltyAdvanceAmount : (float) $bulty->advance_amount;
            $finalRemaining = max(0, $finalBuiltyTotal - $finalAdvance);

            $bulty->update([
                'freight_charges' => $calcItemsTotal > 0 ? $calcItemsTotal : ($bulty->freight_charges ?: $finalBuiltyTotal),
                'total_amount' => $finalBuiltyTotal,
                'advance_amount' => $finalAdvance,
                'remaining_amount' => $finalRemaining,
            ]);

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
            $str = trim((string) $value);
            if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}$/', $str)) {
                $sep = str_contains($str, '/') ? '/' : '-';
                try {
                    return Carbon::createFromFormat("d{$sep}m{$sep}Y", $str)->format('Y-m-d');
                } catch (\Exception $e) {
                    // fallback to standard parse
                }
            }
            return Carbon::parse($str)->format('Y-m-d');
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

    protected function getRowValue($row, array $keys, $default = null)
    {
        if (empty($row)) {
            return $default;
        }

        foreach ($keys as $key) {
            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }

        // Normalized check (ignoring case, spaces, underscores, dots, hyphens)
        $normalizedRow = [];
        foreach ($row as $rKey => $rVal) {
            if ($rVal !== null && trim((string) $rVal) !== '') {
                $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $rKey));
                $normalizedRow[$cleanKey] = trim((string) $rVal);
            }
        }

        foreach ($keys as $key) {
            $cleanLookup = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $key));
            if (isset($normalizedRow[$cleanLookup])) {
                return $normalizedRow[$cleanLookup];
            }
        }

        return $default;
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
