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
            'lr_date' => $this->extractDateByLabels($text, [
                'Lr\s*Dt\.?', 'LR\s*Date', 'L\.R\.?\s*Date', 'Bilty\s*Date', 'Bulty\s*Date',
                'GR\s*Date', 'DC\s*Date', 'Document\s*Date', 'ShptDt', 'Dated',
            ]),
            'invoice_number' => $this->matchLabelValue($text, [
                'Tax\s*Invoice\s*No\.?', 'Gst\s*Inv\s*No\.?', 'Invoice\s*No\.?', 'Inv\.?\s*No\.?',
                'DC\s*Number', 'Document\s*No\.?',
            ]),
            'invoice_date' => $this->extractDateByLabels($text, [
                'Invoice\s*Date', 'Inv\.?\s*Date', 'DC\s*Date', 'Document\s*Date', 'Dated',
            ]),
            'eway_bill_no' => $this->extractEwayBill($text),
            'generation_date' => $this->extractDateByLabels($text, ['Generation\s*Date', 'E-?Way\s*Bill\s*Date', 'EWB\s*Date', 'Valid\s*From']),
            'expiry_date' => $this->extractDateByLabels($text, ['Expiry\s*Date', 'Valid\s*Until', 'Valid\s*Upto']),
            'order_number' => $this->matchLabelValue($text, ['Order\s*No\.?', 'Buyer\'?s\s*Order\s*No\.?', 'Sales\s*Order\s*No\.?']),
            'delivery_number' => $this->matchLabelValue($text, ['Delivery\s*No\.?', 'D\.I\.?\s*No\.?', 'D\.O\.?\s*No\.?', 'Shipment\s*No\.?', 'DN\s*No\.?']),
            'challan_no' => $this->matchLabelValue($text, ['Challan\s*No\.?', 'DC\s*Number', 'D\.C\.?\s*No\.?']),
            'po_no' => $this->matchLabelValue($text, ['P\.?O\.?\s*No\.?', 'Purchase\s*Order\s*No\.?']),
            'payment_type' => $this->extractPaymentType($text),
            'from_city' => null,
            'to_city' => null,
            'from_city_name' => $this->extractLocationValue($text, ['Place\s*of\s*Dispatch', 'From\s*City', 'Dispatch\s*From', 'From']),
            'to_city_name' => $this->extractLocationValue($text, ['Place\s*of\s*Delivery', 'To\s*City', 'Destination', 'Delivery\s*At', 'Place\s*Of\s*Supply', 'To']),
            'vehicle_id' => null,
            'vehicle_number' => $this->extractVehicleNumber($text),
            'driver_id' => null,
            'driver_name' => $this->matchLabelValue($text, ['Driver\s*Name']),
            'driver_mobile' => $this->extractPhoneNearLabel($text, ['Driver\s*Mobile', 'Driver\s*Phone', 'Driver\s*Contact']),
            'freight_charges' => $this->extractAmountByLabels($text, ['Total\s*Freight', 'TotalFreight', 'FREIGHT\s*Amt', 'Freight\s*Charges?', 'Basic\s*Freight']),
            'other_charges' => $this->extractAmountByLabels($text, ['Del\.?\s*Chrgs\.?', 'Other\s*Charges?', 'Hamali', 'Loading\s*Charges?']),
            'declared_value' => $this->extractAmountByLabels($text, ['Value\s*of\s*Goods', 'Declared\s*Value', 'Net\s*Amt', 'Total\s*Amount', 'Taxable\s*Value']),
            'consignor' => $this->extractConsignor($text),
            'consignee' => $this->extractConsignee($text),
            'items' => [],
        ];

        if (empty($fields['lr_date'])) {
            $fields['lr_date'] = $this->extractDateByLabels($text, ['Date', 'Dt\.?']);
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
        // Match LR/RR No., L.R. No.:LR0000432819, LR No. : 1901-BHLA-27/277, Bilty No, etc.
        if (preg_match('/(?<!E-)(?<!E)\b(?:L\.?\s*R\.?\s*\/?\s*R\.?\s*R\.?|L\.?\s*R\.?|Bilty|Bulty|G\.?\s*R\.?|R\.?\s*R\.?\s*\/?\s*T\.?\s*R\.?|C\.?\s*N\.?|Docket)\s*(?:No\.?|Number|#)\s*[:\-]?\s*\n?\s*([A-Z0-9][A-Z0-9\-\/]{2,29})/i', $text, $m)) {
            $val = trim($m[1]);
            // Avoid matching "dt." or dates or label words
            if (!preg_match('/^(?:dt\.?|date|and|is|no|number|truck|wagon)$/i', $val) && !preg_match('/^\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}$/', $val)) {
                return $val;
            }
        }
        return null;
    }

    private function extractEwayBill(string $text): ?string
    {
        if (preg_match('/\b(?:E-?Way\s*Bill|EWB)\s*(?:No\.?|Number)?\s*[:\-]?\s*\n?\s*([\d\s]{10,18})/i', $text, $m)) {
            $digits = preg_replace('/\D+/', '', $m[1]);
            if (strlen($digits) >= 10 && strlen($digits) <= 15) {
                return $digits;
            }
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
        if (preg_match('/\bFreight\s*Paid\b/i', $text)) {
            return 'paid';
        }
        return null;
    }

    private function extractVehicleNumber(string $text): ?string
    {
        if (preg_match('/\b([A-Z]{2}[\s-]?\d{1,2}[\s-]?[A-Z]{1,3}[\s-]?\d{4})\b/', strtoupper($text), $m)) {
            return preg_replace('/\s+/', '', trim($m[1]));
        }
        return null;
    }

    private function extractLocationValue(string $text, array $labels): ?string
    {
        $val = $this->matchLabelValue($text, $labels);
        if (!$val) {
            return null;
        }
        // Strip common prefixes like "TEHSIL:" and take city before comma/pincode
        $val = preg_replace('/^(?:TEHSIL|DIST\.?|DISTRICT|CITY)\s*[:\-]\s*/i', '', $val);
        $parts = preg_split('/[,\-\(]/', $val);
        $city = trim($parts[0] ?? '');
        return $city !== '' ? $city : null;
    }

    private function matchLabelValue(string $text, array $labels): ?string
    {
        $pattern = '/(?:' . implode('|', $labels) . ')\s*[:\-]\s*\n?\s*([^\n\r]+)/i';
        if (preg_match($pattern, $text, $m)) {
            $val = trim($m[1]);
            $val = preg_split('/\s{2,}|\s+(?:Date|Dt\.?|GSTIN|GST\s*No|Phone|Mobile|Tel|Vehicle|Truck|E-?Way|Invoice|LR|PO\s*No)\b\s*[:\-]?/i', $val)[0];
            $val = trim($val, " \t\n\r\0\x0B:,-");
            if ($val !== '' && !preg_match('/^(?:NA|N\/A|NIL|-+)$/i', $val)) {
                return mb_substr($val, 0, 200);
            }
        }
        return null;
    }

    private function extractDateByLabels(string $text, array $labels): ?string
    {
        $pattern = '/(?:' . implode('|', $labels) . ')\s*[:\-]?\s*\n?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}|\d{4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2}|\d{1,2}[\s\-][A-Za-z]{3,9}[\s\-]\d{2,4})/i';
        if (preg_match($pattern, $text, $m)) {
            return $this->normalizeDate($m[1]);
        }
        return null;
    }

    private function normalizeDate(string $raw): ?string
    {
        $raw = trim(str_replace('.', '/', $raw));
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
        $pattern = '/(?:' . implode('|', $labels) . ')\s*[:\-]?\s*\n?\s*(?:₹|Rs\.?|INR)?\s*([\d,]+(?:\.\d{1,2})?)/i';
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

    private function extractConsignor(string $text): array
    {
        // 1. E-Way Bill format: "GSTIN of Supplier 23AAECR5184K1Z5,ROYAL UNIFORCE ROOFINGS PRIVATE LIMITED"
        if (preg_match('/GSTIN\s+of\s+Supplier\s+([0-9A-Z\s]{15,20})\s*,\s*([^\n\r]+)/i', $text, $m)) {
            return [
                'name' => trim($m[2]),
                'gstin' => strtoupper(preg_replace('/\s+/', '', $m[1])),
                'phone' => null,
                'address' => $this->matchLabelValue($text, ['Place\s*of\s*Dispatch']),
                'city' => null,
                'pincode' => null,
            ];
        }

        // 2. Explicit Consignor / Consigner / Sender / Shipper / Sold By / Supplier label
        $block = $this->extractPartyBlock($text, [
            'Consignor(?:\'?s)?(?:\s*\(Sender\))?(?:\s*Name)?(?:\s*&\s*Address)?',
            'Consigner(?:\'?s)?(?:\s*\(Sender\))?(?:\s*Name)?(?:\s*&\s*Address)?',
            'Sender(?:\s*Details|\s*Name)?',
            'Shipper(?:\s*Details|\s*Name)?',
            'Sold\s*By',
            'Billed\s*By',
            'Supplier(?:\s*Details|\s*Name)?',
        ], [
            'Consignee', 'Consigner', 'Receiver', 'Buyer', 'Ship\s*To', 'Bill\s*To', 'Delivered\s*To',
            'From\s*:', 'To\s*:', 'Description', 'Particulars', 'Goods', 'Invoice\s*No', 'L\.?R\.?\s*No', 'Bilty\s*No',
        ]);

        if (!empty($block['name']) || !empty($block['gstin'])) {
            return $block;
        }

        // 3. Fallback for Tax Invoice / Delivery Challan: issuer company at top (before Consignee/Buyer/Recipient)
        $topChunk = $text;
        if (preg_match('/\n\s*(?:Consignee|Name\s*&\s*Address\s*of\s*(?:Consignee|Recipient)|Details\s*of\s*(?:Receiver|Consignee)|Buyer\s*\(Bill\s*to\))/i', $text, $cut, PREG_OFFSET_CAPTURE)) {
            $topChunk = substr($text, 0, $cut[0][1]);
        }

        $name = null;
        $gstin = null;
        if (preg_match('/\b(\d{2}[A-Z]{5}\d{4}[A-Z][A-Z\d]Z[A-Z\d])\b/i', $topChunk, $gm)) {
            $gstin = strtoupper($gm[1]);
        }
        foreach (explode("\n", $topChunk) as $line) {
            $l = trim($line);
            if (preg_match('/\b(?:PVT\.?\s*LTD\.?|PRIVATE\s+LIMITED|LIMITED|LTD\.?|CEMENT\s+WORKS|ROOFINGS|ENTERPRISES|INDUSTRIES|TRADERS|CORPORATION)\b/i', $l)
                && !preg_match('/^(?:for\s+|generated\s+by|tax\s+invoice|delivery\s+challan|original\s+for)/i', $l)) {
                $name = preg_replace('/\s*-\s*\d{4}-\d{2}$/', '', $l);
                break;
            }
        }

        return [
            'name' => $name ? mb_substr(trim($name), 0, 200) : null,
            'gstin' => $gstin,
            'phone' => null,
            'address' => null,
            'city' => null,
            'pincode' => null,
        ];
    }

    private function extractConsignee(string $text): array
    {
        // 1. E-Way Bill format: "GSTIN of Recipient 09BXC PC385 9H1ZE ,KASHYAP TRADERS"
        if (preg_match('/GSTIN\s+of\s+Recipient\s+([0-9A-Z\s]{15,22})\s*,\s*([^\n\r]+)/i', $text, $m)) {
            return [
                'name' => trim($m[2]),
                'gstin' => strtoupper(preg_replace('/\s+/', '', $m[1])),
                'phone' => null,
                'address' => $this->matchLabelValue($text, ['Place\s*of\s*Delivery']),
                'city' => null,
                'pincode' => null,
            ];
        }

        // 2. Explicit Consignee / Recipient / Buyer / Ship To / Bill To labels
        return $this->extractPartyBlock($text, [
            'Name\s*&\s*Address\s*of\s*Consignee',
            'Name\s*&\s*Address\s*of\s*Recipient',
            'Details\s*of\s*Consignee\s*(?:\(Ship\.?\s*to\))?',
            'Details\s*of\s*Receiver\s*(?:\(Billed\s*to\))?',
            'Consignee\s*\(Ship\s*to\)',
            'Consignee(?:\'?s)?(?:\s*\(Receiver\))?(?:\s*Name)?(?:\s*&\s*Address)?',
            'Buyer\s*\(Bill\s*to\)',
            'Ship\s*To',
            'Bill\s*To',
            'Delivered\s*To',
            'Recipient',
        ], [
            'Name\s*&\s*Address\s*of\s*Delivery', 'Buyer\s*\(Bill\s*to\)', 'Details\s*of\s*Consignee',
            'Actg\s*Ctrl', 'Invoice\s*No', 'Gst\s*Inv', 'From\s*:', 'To\s*:',
            'Commodity', 'Description', 'Particulars', 'Sl\s*No', 'Sr\.?\s*No', 'Goods',
            'Vehicle', 'Driver', 'Freight', 'Total', 'Remarks', 'Terms', 'Declaration',
        ]);
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

        // GSTIN (allow optional spaces from OCR/EWB)
        if (preg_match('/\b(\d{2}\s*[A-Z]{5}\s*\d{4}\s*[A-Z]\s*[A-Z\d]\s*Z\s*[A-Z\d])\b/i', $block, $gm)) {
            $result['gstin'] = strtoupper(preg_replace('/\s+/', '', $gm[1]));
        }

        // Phone (10-digit Indian mobile or landline after PH:)
        if (preg_match('/(?:\+91[\s\-]?)?\b([6-9]\d{9})\b/', $block, $pm)) {
            $result['phone'] = $pm[1];
        }

        // Pincode
        if (preg_match('/\b(\d{6})\b/', $block, $pinM)) {
            $result['pincode'] = $pinM[1];
        }

        $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), fn($l) => $l !== ''));
        if (!empty($lines)) {
            // Skip pure label lines like "TIN: GST NO :"
            $nameLine = '';
            $nameIdx = 0;
            foreach ($lines as $idx => $line) {
                $cand = preg_replace('/^(?:Name|M\/s\.?)\s*[:\-]\s*/i', '', $line);
                $cand = preg_replace('/\b(?:TIN|GSTIN|GST\s*NO|GST|Phone|Mob|Mobile|Ph)\b.*$/i', '', $cand);
                $cand = trim($cand, " \t:,-");
                if ($cand !== '' && mb_strlen($cand) > 1) {
                    $nameLine = $cand;
                    $nameIdx = $idx;
                    break;
                }
            }
            if ($nameLine !== '') {
                $result['name'] = mb_substr($nameLine, 0, 200);
            }

            $addrLines = [];
            foreach (array_slice($lines, $nameIdx + 1) as $line) {
                $cleaned = preg_replace('/\b(?:TIN|GSTIN\/UIN|GSTIN|GST\s*No\.?|State\s*Name|State\s*Code|Phone|Mobile|Mob|Ph|Tel)\s*[:\-]?\s*.*$/i', '', $line);
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
        $ratePerMt = $this->extractAmountByLabels($text, ['Freight\/MT', 'Freight\s*per\s*MT', 'Freight\s*Rate\s*Rs\s*PMT', 'Rate\s*per\s*MT']);

        // Strategy 1: Bhadrecha LR rows: "PRECOTE 30 KG NT 500 15.000" or "TILESTIX INTERO : TX1 20 KG (TKN) NT 350 7.000"
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^([A-Za-z][A-Za-z0-9\s\-\/\(\)\.:&]{2,70}?)\s+(\d{1,5})\s+(\d+\.\d{1,3})$/', $trimmed, $m)) {
                $candidateName = trim($m[1]);
                if (!preg_match('/^(?:total|grand\s*total|sub\s*total|sr\.?\s*no|s\.?\s*no|rj\d{2}|truck|wagon|freight)/i', $candidateName)) {
                    $items[] = [
                        'item_name' => $candidateName,
                        'articles' => (int) $m[2],
                        'weight' => (float) $m[3],
                        'unit' => $this->normalizeUnit('MT'),
                        'freight_per_mt' => $ratePerMt ?? 0,
                        'packaging_type' => $this->matchPackaging('Bag'),
                    ];
                }
            }
        }
        if (!empty($items)) {
            return $items;
        }

        // Strategy 2: Tax Invoice rows: "1 A.C.CORRUGATED SHEET 3.60 MTR 68114010 43 Nos. 76.02 Nos. 3,268.93"
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^\d+\s+([A-Za-z][A-Za-z0-9\s\-\/\.\(\)&]{2,65}?)\s+\d{6,8}\s+([\d,]+)\s*(Nos\.?|Pcs|Bags?|MT|Kg|Box)\b/i', $trimmed, $m)) {
                $items[] = [
                    'item_name' => trim($m[1]),
                    'articles' => (int) str_replace(',', '', $m[2]),
                    'weight' => 0,
                    'unit' => $this->normalizeUnit($m[3]),
                    'freight_per_mt' => 0,
                    'packaging_type' => null,
                ];
            }
        }
        if (!empty($items)) {
            return $items;
        }

        // Strategy 3: UltraTech / Birla White DC pattern: "<Name> <Weight>\n<Product Desc> <Bags>"
        for ($i = 0; $i < count($lines) - 1; $i++) {
            $l1 = trim($lines[$i]);
            $l2 = trim($lines[$i + 1]);
            if (preg_match('/^([A-Za-z][A-Za-z0-9\s\+\-\.]{2,40})\s+(\d+\.\d{2,3})$/', $l1, $m1)
                && preg_match('/^([A-Za-z][A-Za-z0-9\s:\-\(\)\+]{4,60})\s+(\d+(?:\.\d{1,2})?)$/', $l2, $m2)
                && !preg_match('/^(?:total|basic\s*amt|round\s*off|net\s*amt|transit|cgst|sgst)/i', $l1)) {
                $items[] = [
                    'item_name' => trim($m2[1]),
                    'articles' => (int) round((float) $m2[2]),
                    'weight' => (float) $m1[2],
                    'unit' => $this->normalizeUnit('MT'),
                    'freight_per_mt' => 0,
                    'packaging_type' => $this->matchPackaging('Bag'),
                ];
            }
        }
        if (!empty($items)) {
            return $items;
        }

        // Strategy 4: Label-based (JK Cement "Description of Commodity : PPC", or general LR)
        $itemName = $this->matchLabelValue($text, [
            'Description\s*of\s*Commodity',
            'Item\s*Name',
            'Description\s*of\s*Goods',
            'Goods\s*Description',
            'Material\s*Name',
            'Commodity',
            'Product(?:\s*Name)?',
            'Particulars',
        ]);

        if (!$itemName && preg_match('/HSN\s*Code\s*\d{6,8}\s*-\s*([^\n\r]+)/i', $text, $hm)) {
            $itemName = trim(preg_replace('/\(\s*\+\d+\s*\)/', '', $hm[1]));
        }

        $articles = null;
        if (preg_match('/(?:QTY\s*in\s*BAG|No\.?\s*of\s*(?:Articles|Pkgs|Packages|Bags)|Bags\(Nos\.?\)|Articles|Bags|Qty)\s*[:\-]?\s*(\d+)/i', $text, $m)) {
            $articles = (int) $m[1];
        }

        $weight = null;
        $unit = null;
        if (preg_match('/(?:QTY\s*in\s*MT|Quantity\(MT\.?\)|Total\s*Weight|Weight\s*\(MT\)|Weight)\s*[:\-]?\s*(\d+(?:\.\d{1,3})?)\s*(MT|KG|KGS|TON|TONS|QTL|QUINTAL)?/i', $text, $m)) {
            $weight = (float) $m[1];
            $unit = !empty($m[2]) ? $this->normalizeUnit($m[2]) : $this->normalizeUnit('MT');
        }

        if ($itemName || $articles !== null || $weight !== null) {
            $items[] = [
                'item_name' => $itemName ?: 'General Goods',
                'articles' => $articles ?? 0,
                'weight' => $weight ?? 0,
                'unit' => $unit,
                'freight_per_mt' => $ratePerMt ?? 0,
                'packaging_type' => $this->matchPackaging('Bag'),
            ];
        }

        return $items;
    }

    private function normalizeUnit(string $raw): ?string
    {
        $rawLower = rtrim(mb_strtolower(trim($raw)), '.');
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
            'nos' => 'pcs',
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
