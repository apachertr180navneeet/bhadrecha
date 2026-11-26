<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\City;
use App\Models\Consignee;
use App\Models\Consignor;
use App\Models\Driver;
use App\Models\Item;
use App\Models\Packaging;
use App\Models\Unit;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class BultyDocumentParserService
{
    /**
     * Parse extracted OCR/PDF text and match existing Consignor, Consignee,
     * and Item masters WITHOUT writing anything to the database.
     */
    public function parseAndMatchMasters(string $rawText, ?int $companyId = null, ?int $branchId = null): array
    {
        if (!$companyId && $branchId) {
            $companyId = Branch::find($branchId)?->company_id;
        }

        $normalizedText = $this->normalizeText($rawText);
        $extracted = $this->extractFields($normalizedText);

        $masters = [
            'consignor' => null,
            'consignee' => null,
            'items' => [],
        ];

        if (!empty($extracted['consignor']['name']) || !empty($extracted['consignor']['gstin']) || !empty($extracted['consignor']['phone'])) {
            $masters['consignor'] = $this->matchConsignorOnly($extracted['consignor'], $companyId);
        }

        if (!empty($extracted['consignee']['name']) || !empty($extracted['consignee']['gstin']) || !empty($extracted['consignee']['phone'])) {
            $masters['consignee'] = $this->matchConsigneeOnly($extracted['consignee'], $companyId);
        }

        $resolvedItems = [];
        foreach ($extracted['items'] as $itemData) {
            $matched = $this->matchItemOnly($itemData);
            if ($matched) {
                $resolvedItems[] = $matched;
                $masters['items'][] = [
                    'id' => $matched['item_id'],
                    'name' => $matched['item_name'],
                    'status' => $matched['master_status'],
                ];
            }
        }
        $extracted['items'] = $resolvedItems;

        return [
            'fields' => $extracted,
            'masters' => $masters,
        ];
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = array_map(fn($line) => trim(preg_replace('/[ \t]+/', ' ', $line)), explode("\n", $text));
        return implode("\n", array_filter($lines, fn($line) => $line !== ''));
    }

    private function extractFields(string $text): array
    {
        $fields = [
            'lr_no' => $this->extractLrNumber($text),
            'e_lr_no' => $this->matchLabelValue($text, ['E-LR\s*No\.?', 'ELR\s*No\.?']),
            'lr_date' => $this->extractDateByLabels($text, ['LR\s*Date', 'L\.R\.?\s*Date', 'Bilty\s*Date', 'Bulty\s*Date', 'GR\s*Date', 'Consignment\s*Date']),
            'invoice_number' => $this->matchLabelValue($text, ['Tax\s*Invoice\s*No\.?', 'Invoice\s*No\.?', 'Inv\.?\s*No\.?', 'Bill\s*No\.?']),
            'invoice_date' => $this->extractDateByLabels($text, ['Invoice\s*Date', 'Inv\.?\s*Date', 'Bill\s*Date']),
            'eway_bill_no' => $this->extractEwayBill($text),
            'generation_date' => $this->extractDateByLabels($text, ['Generation\s*Date', 'EWB\s*Date', 'E-Way\s*Date']),
            'expiry_date' => $this->extractDateByLabels($text, ['Expiry\s*Date', 'Valid\s*Upto', 'Valid\s*Until']),
            'order_number' => $this->matchLabelValue($text, ['Order\s*No\.?', 'Sales\s*Order\s*No\.?']),
            'delivery_number' => $this->matchLabelValue($text, ['Delivery\s*No\.?', 'D\.O\.?\s*No\.?', 'DN\s*No\.?']),
            'challan_no' => $this->matchLabelValue($text, ['Challan\s*No\.?', 'D\.C\.?\s*No\.?']),
            'po_no' => $this->matchLabelValue($text, ['P\.?O\.?\s*No\.?', 'Purchase\s*Order\s*No\.?']),
            'payment_type' => $this->extractPaymentType($text),
            'from_city' => null,
            'to_city' => null,
            'from_city_name' => $this->matchLabelValue($text, ['From\s*City', 'Origin', 'Dispatch\s*From', 'From']),
            'to_city_name' => $this->matchLabelValue($text, ['To\s*City', 'Destination', 'Delivery\s*At', 'To']),
            'vehicle_id' => null,
            'vehicle_number' => $this->extractVehicleNumber($text),
            'driver_id' => null,
            'driver_name' => $this->matchLabelValue($text, ['Driver\s*Name', 'Driver']),
            'driver_mobile' => $this->extractPhoneNearLabel($text, ['Driver\s*Mobile', 'Driver\s*Phone', 'Driver\s*Contact']),
            'freight_charges' => $this->extractAmountByLabels($text, ['Freight\s*Charges?', 'Total\s*Freight', 'Basic\s*Freight', 'Freight']),
            'other_charges' => $this->extractAmountByLabels($text, ['Other\s*Charges?', 'Hamali', 'Loading\s*Charges?', 'Misc\.?\s*Charges?']),
            'declared_value' => $this->extractAmountByLabels($text, ['Declared\s*Value', 'Invoice\s*Value', 'Goods\s*Value']),
            'consignor' => $this->extractPartyBlock($text, [
                'Consignor(?:\s*\(Sender\))?(?:\s*Name)?(?:\s*&\s*Address)?',
                'Consigner(?:\s*\(Sender\))?(?:\s*Name)?(?:\s*&\s*Address)?',
                'Sender(?:\s*Details|\s*Name)?',
                'Shipper(?:\s*Details|\s*Name)?',
                'Sold\s*By',
                'Billed\s*By',
                'Supplier(?:\s*Details|\s*Name)?',
                'Dispatch(?:ed)?\s*From',
                'From\s*\(Consignor\)',
            ], [
                'Consignee', 'Consigner', 'Receiver', 'Buyer', 'Ship\s*To', 'Bill\s*To', 'Delivered\s*To',
                'Item', 'Description\s*of\s*Goods', 'Particulars', 'Goods', 'Invoice\s*No', 'Vehicle', 'L\.?R\.?\s*No', 'Bilty\s*No',
            ]),
            'consignee' => $this->extractPartyBlock($text, [
                'Consignee(?:\s*\(Receiver\))?(?:\s*Name)?(?:\s*&\s*Address)?',
                'Receiver(?:\s*Details|\s*Name)?',
                'Buyer(?:\s*\(Bill\s*To\))?(?:\s*Details|\s*Name)?',
                'Ship\s*To(?:\s*\(Consignee\))?',
                'Bill\s*To(?:\s*\(Buyer\))?',
                'Delivered\s*To',
                'Recipient',
                'To\s*\(Consignee\)',
            ], [
                'Item', 'Description\s*of\s*Goods', 'Particulars', 'Goods', 'Sr\.?\s*No', 'S\.?\s*No',
                'Invoice\s*No', 'Vehicle', 'Driver', 'Freight', 'Total', 'Remarks', 'Terms', 'Declaration',
            ]),
            'items' => [],
        ];

        // Fallback LR date to general Date if specific LR date was not found
        if (empty($fields['lr_date'])) {
            $fields['lr_date'] = $this->extractDateByLabels($text, ['Date', 'Dated']);
        }

        // Match cities in DB
        $fields['from_city'] = $this->matchCityId($fields['from_city_name'] ?? $fields['consignor']['city'] ?? null);
        $fields['to_city'] = $this->matchCityId($fields['to_city_name'] ?? $fields['consignee']['city'] ?? null);

        // Match vehicle / driver if already in DB
        if (!empty($fields['vehicle_number'])) {
            $cleanVeh = strtoupper(preg_replace('/[\s-]+/', '', $fields['vehicle_number']));
            $vehicle = Vehicle::whereRaw("REPLACE(REPLACE(UPPER(vehicle_number), ' ', ''), '-', '') = ?", [$cleanVeh])->first();
            if ($vehicle) {
                $fields['vehicle_id'] = $vehicle->id;
                $fields['vehicle_number'] = $vehicle->vehicle_number;
            }
        }

        if (!empty($fields['driver_mobile']) || !empty($fields['driver_name'])) {
            $driver = null;
            if (!empty($fields['driver_mobile'])) {
                $driver = Driver::where('phone', 'like', '%' . substr($fields['driver_mobile'], -10))->first();
            }
            if (!$driver && !empty($fields['driver_name'])) {
                $driver = Driver::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($fields['driver_name']))])->first();
            }
            if ($driver) {
                $fields['driver_id'] = $driver->id;
                $fields['driver_name'] = $driver->name;
                $fields['driver_mobile'] = $driver->phone;
            }
        }

        $fields['items'] = $this->extractItems($text);

        return $fields;
    }

    private function extractLrNumber(string $text): ?string
    {
        // Match "LR No: ABC-123", "L.R. No. 1234", "Bilty No 456", "GR No : 789", or next-line value
        if (preg_match('/(?<!E-)(?<!E)\b(?:L\.?\s*R\.?|Bilty|Bulty|G\.?\s*R\.?|C\.?\s*N\.?|Consignment\s*Note|Docket)\s*(?:No\.?|Number|#)\s*[:\-]?\s*\n?\s*([A-Z0-9][A-Z0-9\-\/]{1,29})/i', $text, $m)) {
            $val = trim($m[1]);
            if (!preg_match('/^(?:date|and|is|no|number)$/i', $val)) {
                return $val;
            }
        }
        return null;
    }

    private function extractEwayBill(string $text): ?string
    {
        if (preg_match('/\b(?:E-?Way\s*Bill|EWB)\s*(?:No\.?|Number)?\s*[:\-]?\s*(\d{10,15})\b/i', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function extractPaymentType(string $text): ?string
    {
        if (preg_match('/\bTo\s*Bill\b/i', $text)) {
            return 'tobill';
        }
        if (preg_match('/\bTo\s*Pay\b/i', $text)) {
            return 'topay';
        }
        if (preg_match('/\b(?:Freight\s*)?Paid\b/i', $text)) {
            return 'paid';
        }
        return null;
    }

    private function extractVehicleNumber(string $text): ?string
    {
        if (preg_match('/\b([A-Z]{2}[\s-]?\d{1,2}[\s-]?[A-Z]{1,3}[\s-]?\d{4})\b/', strtoupper($text), $m)) {
            return preg_replace('/\s+/', ' ', trim($m[1]));
        }
        return null;
    }

    private function matchLabelValue(string $text, array $labels): ?string
    {
        $pattern = '/(?:' . implode('|', $labels) . ')\s*[:\-]\s*([^\n\r]+)/i';
        if (preg_match($pattern, $text, $m)) {
            $val = trim($m[1]);
            // Stop before another common label on the same line
            $val = preg_split('/\s{2,}|\s+(?:Date|GSTIN|Phone|Mobile|Tel|Vehicle|E-?Way|Invoice|LR)\b\s*[:\-]/i', $val)[0];
            $val = trim($val, " \t\n\r\0\x0B:,-");
            return $val !== '' ? mb_substr($val, 0, 200) : null;
        }
        return null;
    }

    private function extractDateByLabels(string $text, array $labels): ?string
    {
        $pattern = '/(?:' . implode('|', $labels) . ')\s*[:\-]?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}|\d{4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2}|\d{1,2}[\s\-][A-Za-z]{3,9}[\s\-]\d{2,4})/i';
        if (preg_match($pattern, $text, $m)) {
            return $this->normalizeDate($m[1]);
        }
        return null;
    }

    private function normalizeDate(string $raw): ?string
    {
        $raw = trim(str_replace('.', '/', $raw));
        // Handle DD/MM/YYYY or DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $raw, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if ($year < 100) {
                $year += 2000;
            }
            if ($month > 12 && $day <= 12) {
                [$day, $month] = [$month, $day];
            }
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }
        $ts = strtotime($raw);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    private function extractAmountByLabels(string $text, array $labels): ?float
    {
        $pattern = '/(?:' . implode('|', $labels) . ')\s*[:\-]?\s*(?:₹|Rs\.?|INR)?\s*([\d,]+(?:\.\d{1,2})?)/i';
        if (preg_match($pattern, $text, $m)) {
            $num = (float) str_replace(',', '', $m[1]);
            return $num > 0 ? $num : null;
        }
        return null;
    }

    private function extractPhoneNearLabel(string $text, array $labels): ?string
    {
        $pattern = '/(?:' . implode('|', $labels) . ')\s*[:\-]?\s*(?:\+91[\s\-]?)?([6-9]\d{9})\b/i';
        if (preg_match($pattern, $text, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractPartyBlock(string $text, array $startLabels, array $stopLabels): array
    {
        $result = [
            'name' => null,
            'gstin' => null,
            'phone' => null,
            'address' => null,
            'city' => null,
            'pincode' => null,
        ];

        $startRegex = '/(?:^|\n|\s{2,})\s*(?:' . implode('|', $startLabels) . ')\s*[:\-]?\s*/i';
        if (!preg_match($startRegex, $text, $startMatch, PREG_OFFSET_CAPTURE)) {
            return $result;
        }

        $startPos = $startMatch[0][1] + strlen($startMatch[0][0]);
        $remaining = substr($text, $startPos, 650);

        $stopRegex = '/(?:\n|\s{2,})\s*(?:' . implode('|', $stopLabels) . ')\b\s*[:\-]?/i';
        if (preg_match($stopRegex, $remaining, $stopMatch, PREG_OFFSET_CAPTURE)) {
            $block = substr($remaining, 0, $stopMatch[0][1]);
        } else {
            $block = implode("\n", array_slice(explode("\n", $remaining), 0, 5));
        }

        $block = trim($block);
        if ($block === '') {
            return $result;
        }

        // GSTIN
        if (preg_match('/\b(\d{2}[A-Z]{5}\d{4}[A-Z][A-Z\d]Z[A-Z\d])\b/i', $block, $gm)) {
            $result['gstin'] = strtoupper($gm[1]);
        }

        // Phone
        if (preg_match('/(?:\+91[\s\-]?)?\b([6-9]\d{9})\b/', $block, $pm)) {
            $result['phone'] = $pm[1];
        }

        // Pincode
        if (preg_match('/\b(\d{6})\b/', $block, $pinM)) {
            $result['pincode'] = $pinM[1];
        }

        $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), fn($l) => $l !== ''));
        if (!empty($lines)) {
            $firstLine = preg_replace('/^(?:Name|M\/s\.?)\s*[:\-]\s*/i', '', $lines[0]);
            $firstLine = preg_replace('/\b(?:GSTIN|GST|Phone|Mob|Mobile|Ph)\b.*$/i', '', $firstLine);
            $firstLine = trim($firstLine, " \t:,-");
            if ($firstLine !== '') {
                $result['name'] = mb_substr($firstLine, 0, 200);
            }

            // Address: remaining lines excluding pure GSTIN/Phone labels
            $addrLines = [];
            foreach (array_slice($lines, 1) as $line) {
                $cleaned = preg_replace('/\b(?:GSTIN|GST\s*No\.?|Phone|Mobile|Mob|Ph|Tel)\s*[:\-]?\s*[A-Z0-9\+\-\s]+/i', '', $line);
                $cleaned = trim($cleaned, " \t:,-");
                if ($cleaned !== '' && mb_strlen($cleaned) > 2) {
                    $addrLines[] = $cleaned;
                }
            }
            if (!empty($addrLines)) {
                $result['address'] = mb_substr(implode(', ', $addrLines), 0, 500);
            }
        }

        return $result;
    }

    private function extractItems(string $text): array
    {
        $items = [];
        $lines = explode("\n", $text);

        // Strategy 1: Table-like rows e.g. "1  OPC Cement  100  5.5  MT  500" or "PPC Cement 200 Bags 10 MT"
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^(?:\d+[\.\)]?\s+)?([A-Za-z][A-Za-z0-9\s\-\/\(\)\.&]{2,65}?)\s+(\d{1,6})\s*(?:BAGS?|PCS|BOX|NOS|PKGS?)?\s+(\d+(?:\.\d{1,3})?)\s*(MT|KG|KGS|TON|TONS|QUINTAL|QTL)?(?:\s+(\d+(?:\.\d{1,2})?))?$/i', $trimmed, $m)) {
                $candidateName = trim($m[1]);
                if (!preg_match('/^(?:total|grand\s*total|sub\s*total|sr\.?\s*no|s\.?\s*no|item|particulars|description|date|invoice|lr|eway|gstin|phone|mobile|vehicle)/i', $candidateName)) {
                    $items[] = [
                        'item_name' => $candidateName,
                        'articles' => (int) $m[2],
                        'weight' => (float) $m[3],
                        'unit' => !empty($m[4]) ? $this->normalizeUnit($m[4]) : null,
                        'freight_per_mt' => isset($m[5]) ? (float) $m[5] : 0,
                        'packaging_type' => null,
                    ];
                }
            }
        }

        if (!empty($items)) {
            return $items;
        }

        // Strategy 2: Label-based single item extraction (same line or next line)
        $itemName = $this->matchLabelValue($text, [
            'Item\s*Name',
            'Description\s*of\s*Goods',
            'Goods\s*Description',
            'Packing\s*(?:&|and)?\s*Description',
            'Material\s*Name',
            'Material',
            'Commodity',
            'Product(?:\s*Name)?',
            'Particulars',
        ]);

        if (!$itemName) {
            // Check if a table header like "Description of Goods" is followed by item text on the next line
            if (preg_match('/(?:Description\s*of\s*Goods|Particulars|Item\s*Name|Material\s*Description)\b[^\n]*\n\s*(?:\d+[\.\)]?\s+)?([A-Za-z][A-Za-z0-9\s\-\/\(\)\.&]{2,60})/i', $text, $hm)) {
                $cand = trim($hm[1]);
                if (!preg_match('/^(?:total|sr\.?\s*no|qty|quantity|weight|rate|amount)/i', $cand)) {
                    $itemName = $cand;
                }
            }
        }

        $articles = null;
        if (preg_match('/(?:No\.?\s*of\s*(?:Articles|Pkgs|Packages|Bags)|Articles|Packages|Pkgs|Bags|Qty|Quantity)\s*[:\-]?\s*(\d+)/i', $text, $m)) {
            $articles = (int) $m[1];
        } elseif (preg_match('/\b(\d{1,5})\s*(?:Bags|Pkgs|Packages|Boxes|Pcs)\b/i', $text, $m)) {
            $articles = (int) $m[1];
        }

        $weight = null;
        $unit = null;
        if (preg_match('/(?:Total\s*Weight|Net\s*Weight|Gross\s*Weight|Actual\s*Weight|Charged\s*Weight|Weight|Wt\.?)\s*[:\-]?\s*(\d+(?:\.\d{1,3})?)\s*(MT|KG|KGS|TON|TONS|QTL|QUINTAL)?/i', $text, $m)) {
            $weight = (float) $m[1];
            if (!empty($m[2])) {
                $unit = $this->normalizeUnit($m[2]);
            }
        } elseif (preg_match('/\b(\d+(?:\.\d{1,3})?)\s*(MT|M\.T\.|TONS?|KGS?|QUINTAL|QTL)\b/i', $text, $m)) {
            $weight = (float) $m[1];
            $unit = $this->normalizeUnit(str_replace('.', '', $m[2]));
        }

        $rate = $this->extractAmountByLabels($text, ['Freight\s*per\s*MT', 'Rate\s*per\s*MT', 'Rate']);
        $packaging = $this->matchLabelValue($text, ['Packaging\s*Type', 'Packing', 'Packaging']);

        if ($itemName || $articles !== null || $weight !== null) {
            $items[] = [
                'item_name' => $itemName ?: 'General Goods',
                'articles' => $articles ?? 0,
                'weight' => $weight ?? 0,
                'unit' => $unit,
                'freight_per_mt' => $rate ?? 0,
                'packaging_type' => $this->matchPackaging($packaging),
            ];
        }

        return $items;
    }

    private function normalizeUnit(string $raw): ?string
    {
        $rawLower = mb_strtolower(trim($raw));
        $units = Unit::where('status', 'active')->pluck('name');
        foreach ($units as $u) {
            if (mb_strtolower($u) === $rawLower) {
                return $u;
            }
        }
        $aliases = [
            'kgs' => 'kg',
            'tons' => 'mt',
            'ton' => 'mt',
            'metric ton' => 'mt',
            'qtl' => 'quintal',
        ];
        $mapped = $aliases[$rawLower] ?? $rawLower;
        foreach ($units as $u) {
            if (mb_strtolower($u) === $mapped) {
                return $u;
            }
        }
        return null;
    }

    private function matchPackaging(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }
        $rawLower = mb_strtolower(trim($raw));
        $packagings = Packaging::where('status', 'active')->pluck('name');
        foreach ($packagings as $p) {
            if (mb_strtolower($p) === $rawLower || str_contains($rawLower, mb_strtolower($p))) {
                return $p;
            }
        }
        return null;
    }

    private function matchCityId(?string $cityText): ?int
    {
        if (!$cityText) {
            return null;
        }
        $clean = mb_strtolower(trim(preg_replace('/\(.*?\)/', '', $cityText)));
        if ($clean === '') {
            return null;
        }

        $exact = City::where('status', 'active')
            ->whereRaw('LOWER(TRIM(name)) = ?', [$clean])
            ->first();
        if ($exact) {
            return $exact->id;
        }

        $partial = City::where('status', 'active')
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $clean . '%'])
            ->first();

        return $partial?->id;
    }

    private function matchConsignorOnly(array $data, ?int $companyId): ?array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $gstin = !empty($data['gstin']) ? strtoupper(trim($data['gstin'])) : null;
        $phone = !empty($data['phone']) ? preg_replace('/\D+/', '', $data['phone']) : null;

        if ($name === '' && !$gstin && !$phone) {
            return null;
        }

        $existing = $this->findExistingConsignor($name, $gstin, $phone, $companyId);
        if ($existing) {
            return [
                'id' => $existing->id,
                'name' => $existing->name,
                'phone' => $existing->phone,
                'gstin' => $existing->gstin,
                'address' => $existing->address,
                'status' => 'existing',
            ];
        }

        return [
            'id' => null,
            'name' => $name ?: ($gstin ?: 'Consignor ' . $phone),
            'phone' => $phone ?: '',
            'gstin' => $gstin,
            'address' => $data['address'] ?? '',
            'status' => 'new_pending',
        ];
    }

    private function matchConsigneeOnly(array $data, ?int $companyId): ?array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $gstin = !empty($data['gstin']) ? strtoupper(trim($data['gstin'])) : null;
        $phone = !empty($data['phone']) ? preg_replace('/\D+/', '', $data['phone']) : null;

        if ($name === '' && !$gstin && !$phone) {
            return null;
        }

        $existing = $this->findExistingConsignee($name, $gstin, $phone, $companyId);
        if ($existing) {
            return [
                'id' => $existing->id,
                'name' => $existing->name,
                'phone' => $existing->phone,
                'gstin' => $existing->gstin,
                'address' => $existing->address,
                'status' => 'existing',
            ];
        }

        return [
            'id' => null,
            'name' => $name ?: ($gstin ?: 'Consignee ' . $phone),
            'phone' => $phone ?: '',
            'gstin' => $gstin,
            'address' => $data['address'] ?? '',
            'status' => 'new_pending',
        ];
    }

    private function matchItemOnly(array $itemData): ?array
    {
        $name = trim(preg_replace('/\s+/', ' ', (string) ($itemData['item_name'] ?? '')));
        if ($name === '') {
            return null;
        }

        $existing = Item::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();
        if ($existing) {
            $itemData['item_id'] = $existing->id;
            $itemData['item_name'] = $existing->name;
            $itemData['master_status'] = 'existing';
            return $itemData;
        }

        $itemData['item_id'] = null;
        $itemData['item_name'] = mb_substr($name, 0, 255);
        $itemData['master_status'] = 'new_pending';
        return $itemData;
    }

    private function findExistingConsignor(string $name, ?string $gstin, ?string $phone, ?int $companyId): ?Consignor
    {
        $query = Consignor::withoutGlobalScopes();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($gstin) {
            $found = (clone $query)->whereRaw('UPPER(TRIM(gstin)) = ?', [$gstin])->first();
            if ($found) return $found;
        }
        if ($phone && strlen($phone) >= 10) {
            $found = (clone $query)->where('phone', 'like', '%' . substr($phone, -10))->first();
            if ($found) return $found;
        }
        if ($name !== '') {
            return (clone $query)->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();
        }
        return null;
    }

    private function findExistingConsignee(string $name, ?string $gstin, ?string $phone, ?int $companyId): ?Consignee
    {
        $query = Consignee::withoutGlobalScopes();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($gstin) {
            $found = (clone $query)->whereRaw('UPPER(TRIM(gstin)) = ?', [$gstin])->first();
            if ($found) return $found;
        }
        if ($phone && strlen($phone) >= 10) {
            $found = (clone $query)->where('phone', 'like', '%' . substr($phone, -10))->first();
            if ($found) return $found;
        }
        if ($name !== '') {
            return (clone $query)->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();
        }
        return null;
    }

    public function resolveOrCreateConsignor(array $data, ?int $companyId, ?int $branchId): ?Consignor
    {
        $name = trim((string) ($data['name'] ?? ''));
        $gstin = !empty($data['gstin']) ? strtoupper(trim($data['gstin'])) : null;
        $phone = !empty($data['phone']) ? preg_replace('/\D+/', '', $data['phone']) : null;

        if ($name === '' && !$gstin && !$phone) {
            return null;
        }

        $existing = $this->findExistingConsignor($name, $gstin, $phone, $companyId);
        if ($existing) {
            return $existing;
        }

        return Consignor::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => $name ?: ($gstin ?: 'Consignor ' . $phone),
            'phone' => $phone ?: '',
            'gstin' => $gstin,
            'address' => $data['address'] ?? '',
            'pincode' => $data['pincode'] ?? null,
            'status' => 'active',
        ]);
    }

    public function resolveOrCreateConsignee(array $data, ?int $companyId, ?int $branchId): ?Consignee
    {
        $name = trim((string) ($data['name'] ?? ''));
        $gstin = !empty($data['gstin']) ? strtoupper(trim($data['gstin'])) : null;
        $phone = !empty($data['phone']) ? preg_replace('/\D+/', '', $data['phone']) : null;

        if ($name === '' && !$gstin && !$phone) {
            return null;
        }

        $existing = $this->findExistingConsignee($name, $gstin, $phone, $companyId);
        if ($existing) {
            return $existing;
        }

        return Consignee::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => $name ?: ($gstin ?: 'Consignee ' . $phone),
            'phone' => $phone ?: '',
            'gstin' => $gstin,
            'address' => $data['address'] ?? '',
            'pincode' => $data['pincode'] ?? null,
            'status' => 'active',
        ]);
    }

    public function resolveOrCreateItemByName(string $rawName): ?Item
    {
        $name = trim(preg_replace('/\s+/', ' ', $rawName));
        if ($name === '') {
            return null;
        }

        $existing = Item::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();
        if ($existing) {
            return $existing;
        }

        return Item::create([
            'name' => mb_substr($name, 0, 255),
            'status' => 'active',
        ]);
    }
}
