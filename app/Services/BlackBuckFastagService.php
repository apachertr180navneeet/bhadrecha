<?php

namespace App\Services;

use App\Models\FastagSyncLog;
use App\Models\FastagTransaction;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlackBuckFastagService
{
    protected array $config;
    protected string $baseUrl;
    protected string $transactionEndpoint;
    protected ?string $token;
    protected int $phoneNumber;
    protected int $timeout;
    protected int $retryCount;
    protected int $retryDelayMs;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? config('blackbuck', []);
        $this->baseUrl = rtrim((string) ($this->config['base_url'] ?? env('BLACKBUCK_BASE_URL', 'https://api-fms.blackbuck.com')), '/');
        $this->transactionEndpoint = '/' . ltrim((string) ($this->config['transaction_endpoint'] ?? env('BLACKBUCK_TRANSACTION_ENDPOINT', '/fmstoll/api/lfo-transaction-details/transactions')), '/');
        
        $rawToken = (string) ($this->config['token'] ?? env('BLACKBUCK_TOKEN'));
        if (preg_match('/^(?:Token|Bearer)\s+(.*)$/i', trim($rawToken), $m)) {
            $rawToken = $m[1];
        }
        $this->token = trim($rawToken) !== '' ? trim($rawToken) : null;

        $this->phoneNumber = (int) ($this->config['phone_number'] ?? env('BLACKBUCK_PHONE_NUMBER', 9829894925));
        $this->timeout = (int) ($this->config['timeout'] ?? env('BLACKBUCK_TIMEOUT', 60));
        $this->retryCount = (int) ($this->config['retry_count'] ?? 3);
        $this->retryDelayMs = (int) ($this->config['retry_delay_ms'] ?? 1000);
    }

    /**
     * Validate that BlackBuck credentials are configured.
     */
    public function validateConfiguration(): array
    {
        $missing = [];

        if (empty($this->baseUrl)) {
            $missing[] = 'BLACKBUCK_BASE_URL';
        }

        if (empty($this->token)) {
            $missing[] = 'BLACKBUCK_TOKEN';
        }

        if (empty($this->phoneNumber)) {
            $missing[] = 'BLACKBUCK_PHONE_NUMBER';
        }

        return [
            'is_valid' => empty($missing),
            'missing' => $missing,
            'base_url' => $this->baseUrl,
            'transaction_endpoint' => $this->transactionEndpoint,
            'phone_number' => $this->phoneNumber,
            'auth_header' => 'Authorization: Token {BLACKBUCK_TOKEN}',
            'auth_type' => config('blackbuck.auth_type', 'Token'),
        ];
    }

    /**
     * Convert human date or Carbon instance to Unix timestamp in milliseconds.
     * Guaranteed: Keeps milliseconds (13 digits), never converts to seconds.
     */
    public function toMilliseconds($dateOrTimestamp): int
    {
        if (is_numeric($dateOrTimestamp) && (int) $dateOrTimestamp > 1000000000000) {
            return (int) $dateOrTimestamp;
        }

        if (is_numeric($dateOrTimestamp)) {
            return (int) $dateOrTimestamp * 1000;
        }

        if ($dateOrTimestamp instanceof Carbon) {
            return (int) ($dateOrTimestamp->timestamp * 1000);
        }

        return (int) (Carbon::parse($dateOrTimestamp)->timestamp * 1000);
    }

    /**
     * Fetch transactions from BlackBuck FASTag API using exact POST specification:
     *
     * URL: https://api-fms.blackbuck.com/fmstoll/api/lfo-transaction-details/transactions
     * Method: POST
     * Headers:
     *   Authorization: Token {BLACKBUCK_TOKEN}
     *   Content-Type: application/json
     *   Accept: application/json
     *
     * Body:
     * {
     *   "phone_number": 9829894925,
     *   "from_time": 1794444318000,
     *   "to_time": 1794465918000,
     *   "truck_filter": "SELECTIVE",
     *   "truck_numbers": ["RJ19GJ3506"]
     * }
     */
    public function fetchTransactions(array $truckNumbers, $fromTime = null, $toTime = null): array
    {
        $validation = $this->validateConfiguration();
        if (!$validation['is_valid']) {
            return [
                'success' => false,
                'error' => 'Incomplete BlackBuck configuration: ' . implode(', ', $validation['missing']),
                'missing_config' => $validation['missing'],
                'data' => [],
                'raw_response' => null,
            ];
        }

        // Clean & format truck numbers array
        $cleanTrucks = array_values(array_filter(array_map(function ($tn) {
            return strtoupper(trim($tn));
        }, $truckNumbers)));

        if (empty($cleanTrucks)) {
            return [
                'success' => false,
                'error' => 'No truck numbers provided for transaction fetch.',
                'data' => [],
                'raw_response' => null,
            ];
        }

        // Default: lookback window from config
        $fromTime = $fromTime ? ($fromTime instanceof Carbon ? $fromTime : Carbon::parse($fromTime)) : Carbon::now()->subDays($this->config['sync_days_lookback'] ?? 3)->startOfDay();
        $toTime = $toTime ? ($toTime instanceof Carbon ? $toTime : Carbon::parse($toTime)) : Carbon::now()->endOfDay();

        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();

        // If the entire requested window is older than 30 days, BlackBuck API cannot provide this data
        if ($toTime->lt($thirtyDaysAgo)) {
            $msg = "BlackBuck API only retains transaction data for the last 30 days. Transactions between {$fromTime->format('d-M-Y')} and {$toTime->format('d-M-Y')} cannot be fetched from the API.";
            $this->safeLog('info', "[BlackBuckFastag] {$msg}");
            return [
                'success' => false,
                'error' => $msg,
                'data' => [],
                'raw_response' => null,
                'is_retention_limit' => true,
            ];
        }

        // If fromTime is older than 30 days, clamp it to 30 days ago to avoid 400 BAD_REQUEST
        if ($fromTime->lt($thirtyDaysAgo)) {
            $this->safeLog('info', "[BlackBuckFastag] Requested from_time ({$fromTime->toDateTimeString()}) is older than 30 days limit. Clamping from_time to {$thirtyDaysAgo->toDateTimeString()}.");
            $fromTime = $thirtyDaysAgo->copy();
        }

        $fromMs = $this->toMilliseconds($fromTime);
        $toMs = $this->toMilliseconds($toTime);

        // BlackBuck API strictly enforces: "Time difference is more than 6 hours" (21,600,000 ms).
        // If range is within 6 hours, execute a single direct call.
        $maxWindowMs = 21500000;
        if (($toMs - $fromMs) <= $maxWindowMs) {
            return $this->fetchSingleWindow($cleanTrucks, $fromMs, $toMs);
        }

        // For ranges > 6 hours, automatically chunk into 5.9-hour consecutive slices to fetch all transactions.
        $chunkStepMs = 21240000; // 5.9 hours
        $allRecords = [];
        $currentStart = $fromMs;
        $anySuccess = false;
        $lastError = null;
        $rawResponses = [];

        while ($currentStart < $toMs) {
            $currentEnd = min($currentStart + $chunkStepMs, $toMs);
            $res = $this->fetchSingleWindow($cleanTrucks, $currentStart, $currentEnd);

            if ($res['success']) {
                $anySuccess = true;
                $rawResponses[] = $res['raw_response'] ?? null;
                foreach ($res['data'] as $rec) {
                    $allRecords[] = $rec;
                }
            } else {
                $lastError = $res['error'] ?? 'API error';
                // If it's a 400 Bad Request (such as 30-day retention or invalid truck), stop further chunks
                if (str_contains($lastError, 'last30 days') || str_contains($lastError, '400') || str_contains($lastError, 'BAD_REQUEST')) {
                    break;
                }
            }

            $currentStart = $currentEnd;
        }

        if (!$anySuccess && $lastError) {
            return [
                'success' => false,
                'error' => $lastError,
                'data' => [],
                'raw_response' => null,
            ];
        }

        // Deduplicate records by unique provider transaction number / ID
        $uniqueRecords = [];
        $seen = [];
        foreach ($allRecords as $r) {
            $num = $r['transaction_number'] ?? $r['transaction_id'] ?? $r['id'] ?? null;
            if ($num && isset($seen[$num])) {
                continue;
            }
            if ($num) {
                $seen[$num] = true;
            }
            $uniqueRecords[] = $r;
        }

        return [
            'success' => true,
            'status_code' => 200,
            'data' => $uniqueRecords,
            'count' => count($uniqueRecords),
            'raw_response' => ['chunks' => $rawResponses],
            'request_payload' => [
                'phone_number' => $this->phoneNumber,
                'from_time' => $fromMs,
                'to_time' => $toMs,
                'truck_filter' => 'SELECTIVE',
                'truck_numbers' => $cleanTrucks,
            ],
        ];
    }

    /**
     * Fetch a single time window (<= 6 hours) from BlackBuck API.
     */
    protected function fetchSingleWindow(array $cleanTrucks, int $fromMs, int $toMs): array
    {
        $url = $this->baseUrl . $this->transactionEndpoint;

        $payload = [
            'phone_number' => $this->phoneNumber,
            'from_time' => $fromMs,
            'to_time' => $toMs,
            'truck_filter' => 'SELECTIVE',
            'truck_numbers' => $cleanTrucks,
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryCount, $this->retryDelayMs, function ($exception, $request) {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }
                    if ($exception instanceof RequestException && in_array($exception->response->status(), [429, 500, 502, 503, 504])) {
                        return true;
                    }
                    return false;
                })
                ->withHeaders([
                    'Authorization' => 'Token ' . $this->token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            return $this->validateApiResponse($response, $payload);
        } catch (ConnectionException $e) {
            $msg = 'BlackBuck FASTag API timeout or network connection error: ' . $e->getMessage();
            $this->safeLog('error', $msg);
            return [
                'success' => false,
                'error' => $msg,
                'data' => [],
                'raw_response' => null,
            ];
        } catch (\Throwable $e) {
            $rawMsg = $e->getMessage();
            $cleanMsg = $rawMsg;
            if (preg_match('/\{.*"(?:message|error)".*\}/s', $rawMsg, $matches)) {
                $decoded = json_decode($matches[0], true);
                if (is_array($decoded)) {
                    $cleanMsg = $decoded['message'] ?? $decoded['error'] ?? $rawMsg;
                }
            }
            $this->safeLog('error', 'BlackBuck API communication error: ' . $cleanMsg);
            return [
                'success' => false,
                'error' => $cleanMsg,
                'data' => [],
                'raw_response' => null,
            ];
        }
    }

    /**
     * Validate and parse BlackBuck API response.
     */
    public function validateApiResponse(Response $response, array $requestPayload = []): array
    {
        $status = $response->status();
        $body = $response->json();

        if ($response->successful()) {
            $records = $this->extractTransactionsList($body);

            return [
                'success' => true,
                'status_code' => $status,
                'data' => $records,
                'count' => count($records),
                'raw_response' => $body,
                'request_payload' => $requestPayload,
            ];
        }

        // Extract error message safely
        $errorMsg = $this->extractErrorMessage($response);
        $this->safeLog('error', "BlackBuck API returned HTTP {$status}: {$errorMsg}", [
            'status' => $status,
            'trucks' => $requestPayload['truck_numbers'] ?? [],
        ]);

        return [
            'success' => false,
            'status_code' => $status,
            'error' => "BlackBuck API Error [{$status}]: {$errorMsg}",
            'data' => [],
            'raw_response' => $body ?: $response->body(),
            'request_payload' => $requestPayload,
        ];
    }

    /**
     * Extract transactions array from various BlackBuck payload wrapper variants.
     */
    protected function extractTransactionsList($body): array
    {
        if (!is_array($body)) {
            return [];
        }

        // Actual BlackBuck API wrapper key
        if (isset($body['requested_transactions']) && is_array($body['requested_transactions'])) {
            return $body['requested_transactions'];
        }

        // Check if data is wrapped inside data/transactions/records
        if (isset($body['data']) && is_array($body['data'])) {
            if (isset($body['data']['requested_transactions']) && is_array($body['data']['requested_transactions'])) {
                return $body['data']['requested_transactions'];
            }
            if (isset($body['data']['transactions']) && is_array($body['data']['transactions'])) {
                return $body['data']['transactions'];
            }
            if (isset($body['data']['records']) && is_array($body['data']['records'])) {
                return $body['data']['records'];
            }
            if (isset($body['data']['items']) && is_array($body['data']['items'])) {
                return $body['data']['items'];
            }
            if (array_is_list($body['data'])) {
                return $body['data'];
            }
        }

        if (isset($body['transactions']) && is_array($body['transactions'])) {
            return $body['transactions'];
        }

        if (isset($body['records']) && is_array($body['records'])) {
            return $body['records'];
        }

        if (array_is_list($body)) {
            return $body;
        }

        return [];
    }

    /**
     * Normalize individual raw transaction from BlackBuck into consistent internal format.
     */
    public function normalizeTransaction(array $item, ?Vehicle $vehicle = null, ?string $fallbackTruck = null): array
    {
        // Transaction ID variants (BlackBuck uses transaction_number)
        $transactionId = $item['transaction_number']
            ?? $item['transaction_id']
            ?? $item['txn_id']
            ?? $item['transactionId']
            ?? $item['id']
            ?? $item['reference_id']
            ?? $item['toll_transaction_id']
            ?? null;

        // Truck / Vehicle registration number variants
        $vehicleNumber = $item['truck_number']
            ?? $item['vehicle_number']
            ?? $item['vehicle_no']
            ?? $item['truck_no']
            ?? $item['vehicleNumber']
            ?? $item['registration_number']
            ?? ($vehicle ? $vehicle->vehicle_number : $fallbackTruck);

        // Tag ID variants
        $tagId = $item['tag_id']
            ?? $item['tag_no']
            ?? $item['tagId']
            ?? $item['tagNumber']
            ?? $item['tagNo']
            ?? null;

        // Toll Plaza name variants
        $tollPlazaName = $item['plaza_name']
            ?? $item['toll_plaza_name']
            ?? $item['toll_name']
            ?? $item['plaza']
            ?? $item['tollPlazaName']
            ?? $item['location']
            ?? 'Unknown Plaza';
        $tollPlazaName = trim(preg_replace('/\s+/', ' ', (string) $tollPlazaName));

        // Amount variants (BlackBuck uses transaction_amount)
        $rawAmount = $item['transaction_amount']
            ?? $item['amount']
            ?? $item['toll_amount']
            ?? $item['debit_amount']
            ?? $item['txn_amount']
            ?? $item['fare']
            ?? 0;
        $amount = (float) abs($rawAmount);

        // Transaction date variants (supports millisecond unix timestamp or ISO date string)
        $rawDate = $item['transaction_date']
            ?? $item['txn_time']
            ?? $item['transaction_time']
            ?? $item['dateTime']
            ?? $item['time']
            ?? $item['created_at']
            ?? now()->toDateTimeString();

        try {
            if (is_numeric($rawDate) && strlen((string) $rawDate) >= 13) {
                $transactionDate = Carbon::createFromTimestampMs((int) $rawDate)->toDateTimeString();
            } elseif (is_numeric($rawDate)) {
                $transactionDate = Carbon::createFromTimestamp((int) $rawDate)->toDateTimeString();
            } else {
                $transactionDate = Carbon::parse($rawDate)->toDateTimeString();
            }
        } catch (\Throwable $e) {
            $transactionDate = now()->toDateTimeString();
        }

        // Transaction Type variants (DEBIT / CREDIT / REFUND)
        $rawType = strtoupper((string) ($item['transaction_type'] ?? $item['type'] ?? $item['txn_type'] ?? 'DEBIT'));
        $transactionType = in_array($rawType, ['CREDIT', 'REFUND']) ? $rawType : 'DEBIT';

        // Transaction Status variants (SUCCESS / FAILED / PENDING / DISPUTED)
        $rawStatus = strtoupper((string) ($item['transaction_status'] ?? $item['status'] ?? $item['txn_status'] ?? 'SUCCESS'));
        $transactionStatus = match ($rawStatus) {
            'FAILURE', 'FAIL', 'FAILED' => 'FAILED',
            'PENDING', 'PROCESSING' => 'PENDING',
            'DISPUTED', 'DISPUTE' => 'DISPUTED',
            default => 'SUCCESS',
        };

        return [
            'transaction_id' => (string) $transactionId,
            'vehicle_number' => strtoupper(trim((string) $vehicleNumber)),
            'tag_id' => $tagId ? (string) $tagId : null,
            'toll_plaza_name' => trim((string) $tollPlazaName),
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'transaction_date' => $transactionDate,
            'transaction_status' => $transactionStatus,
            'lane_code' => $item['lane_direction'] ?? $item['lane_code'] ?? $item['lane_no'] ?? $item['lane'] ?? null,
            'plaza_code' => $item['plaza_code'] ?? $item['toll_id'] ?? null,
            'raw_response' => $item,
        ];
    }

    /**
     * Batch normalize transaction records.
     */
    public function normalizeTransactions(array $rawTransactions, ?Vehicle $vehicle = null, ?string $fallbackTruck = null): array
    {
        $normalized = [];
        foreach ($rawTransactions as $item) {
            if (is_array($item)) {
                $norm = $this->normalizeTransaction($item, $vehicle, $fallbackTruck);
                if (!empty($norm['transaction_id'])) {
                    $normalized[] = $norm;
                }
            }
        }
        return $normalized;
    }

    /**
     * Save normalized transactions safely into database using updateOrCreate.
     * Prevents duplicate transactions during repeated synchronization.
     */
    public function saveTransactions(array $normalizedTransactions): int
    {
        $savedCount = 0;

        DB::transaction(function () use ($normalizedTransactions, &$savedCount) {
            foreach ($normalizedTransactions as $item) {
                if (empty($item['transaction_id'])) {
                    continue;
                }

                // Match with vehicle in existing vehicle master
                $vehicleId = null;
                if (!empty($item['vehicle_number'])) {
                    $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $item['vehicle_number']));
                    $found = Vehicle::where('vehicle_number', $item['vehicle_number'])
                        ->orWhereRaw("UPPER(REPLACE(REPLACE(vehicle_number, ' ', ''), '-', '')) = ?", [$clean])
                        ->first();
                    if ($found) {
                        $vehicleId = $found->id;
                    }
                }

                FastagTransaction::updateOrCreate(
                    [
                        'transaction_id' => $item['transaction_id'],
                    ],
                    [
                        'vehicle_id' => $vehicleId,
                        'vehicle_number' => $item['vehicle_number'],
                        'tag_id' => $item['tag_id'] ?? null,
                        'toll_plaza_name' => $item['toll_plaza_name'] ?? 'Unknown Plaza',
                        'transaction_type' => $item['transaction_type'] ?? 'DEBIT',
                        'amount' => $item['amount'] ?? 0.00,
                        'transaction_date' => $item['transaction_date'],
                        'transaction_status' => $item['transaction_status'] ?? 'SUCCESS',
                        'lane_code' => $item['lane_code'] ?? null,
                        'plaza_code' => $item['plaza_code'] ?? null,
                        'raw_response' => $item['raw_response'] ?? null,
                    ]
                );

                $savedCount++;
            }
        });

        return $savedCount;
    }

    /**
     * Synchronize a specific list of vehicles across a date/time range.
     */
    public function syncVehicles(array $truckNumbers, $fromTime = null, $toTime = null): array
    {
        $truckNumbers = array_values(array_filter(array_map('trim', $truckNumbers)));
        $displayTruck = count($truckNumbers) === 1
            ? mb_substr($truckNumbers[0], 0, 50)
            : mb_strimwidth(implode(',', $truckNumbers), 0, 50, '...');

        $startedAt = now();
        $fromParsed = $fromTime ? Carbon::parse($fromTime) : now()->subDays($this->config['sync_days_lookback'] ?? 3)->startOfDay();
        $toParsed = $toTime ? Carbon::parse($toTime) : now()->endOfDay();

        $syncLog = FastagSyncLog::create([
            'vehicle_number' => $displayTruck,
            'sync_from' => $fromParsed,
            'sync_to' => $toParsed,
            'sync_status' => 'PENDING',
            'total_records_received' => 0,
            'total_records_saved' => 0,
            'started_at' => $startedAt,
        ]);

        try {
            $apiResult = $this->fetchTransactions($truckNumbers, $fromParsed, $toParsed);

            if (!$apiResult['success']) {
                $status = !empty($apiResult['is_retention_limit']) ? 'SKIPPED' : 'FAILED';
                $syncLog->update([
                    'sync_status' => $status,
                    'error_message' => $apiResult['error'],
                    'completed_at' => now(),
                ]);

                return [
                    'success' => false,
                    'status' => $status,
                    'is_retention_limit' => !empty($apiResult['is_retention_limit']),
                    'truck_numbers' => $truckNumbers,
                    'records_received' => 0,
                    'records_saved' => 0,
                    'error' => $apiResult['error'],
                    'sync_log_id' => $syncLog->id,
                ];
            }

            $rawRecords = $apiResult['data'] ?? [];
            $receivedCount = count($rawRecords);
            $savedCount = 0;

            if ($receivedCount > 0) {
                $normalized = $this->normalizeTransactions($rawRecords, null, $truckNumbers[0] ?? null);
                $savedCount = $this->saveTransactions($normalized);
            }

            $syncLog->update([
                'sync_status' => 'SUCCESS',
                'total_records_received' => $receivedCount,
                'total_records_saved' => $savedCount,
                'error_message' => null,
                'completed_at' => now(),
            ]);

            return [
                'success' => true,
                'status' => 'SUCCESS',
                'truck_numbers' => $truckNumbers,
                'records_received' => $receivedCount,
                'records_saved' => $savedCount,
                'sync_log_id' => $syncLog->id,
            ];
        } catch (\Throwable $e) {
            $this->safeLog('error', "Failed during syncVehicles: " . $e->getMessage());

            $syncLog->update([
                'sync_status' => 'FAILED',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return [
                'success' => false,
                'status' => 'FAILED',
                'truck_numbers' => $truckNumbers,
                'records_received' => 0,
                'records_saved' => 0,
                'error' => $e->getMessage(),
                'sync_log_id' => $syncLog->id,
            ];
        }
    }

    /**
     * Backward-compatible alias for single vehicle sync.
     */
    public function syncVehicleTransactions(string $vehicleNumber, $fromTime = null, $toTime = null): array
    {
        return $this->syncVehicles([$vehicleNumber], $fromTime, $toTime);
    }

    /**
     * Synchronize all active registered vehicles from vehicle master.
     */
    public function syncAllVehicles($fromTime = null, $toTime = null): array
    {
        $vehicles = Vehicle::where('status', 'active')
            ->orWhereNull('status')
            ->pluck('vehicle_number')
            ->filter()
            ->values()
            ->toArray();

        if (empty($vehicles)) {
            return [
                'success' => false,
                'status' => 'FAILED',
                'error' => 'No active vehicles registered in vehicle master.',
                'vehicles_processed' => 0,
                'total_records_received' => 0,
                'total_records_saved' => 0,
            ];
        }

        // Chunk vehicles into batches of 20 to avoid exceeding payload limits
        $chunks = array_chunk($vehicles, 20);
        $totalReceived = 0;
        $totalSaved = 0;
        $errors = [];

        foreach ($chunks as $batch) {
            $res = $this->syncVehicles($batch, $fromTime, $toTime);
            $totalReceived += $res['records_received'] ?? 0;
            $totalSaved += $res['records_saved'] ?? 0;

            if (!$res['success'] && !empty($res['error'])) {
                $errors[] = implode(',', $batch) . ': ' . $res['error'];
            }
        }

        $status = empty($errors) ? 'SUCCESS' : ($totalSaved > 0 ? 'PARTIAL' : 'FAILED');

        return [
            'success' => $status === 'SUCCESS',
            'status' => $status,
            'vehicles_processed' => count($vehicles),
            'total_records_received' => $totalReceived,
            'total_records_saved' => $totalSaved,
            'errors' => $errors,
        ];
    }

    /**
     * Extract error message safely from response.
     */
    protected function extractErrorMessage(Response $response): string
    {
        $body = $response->json();
        if (is_array($body)) {
            if (isset($body['error']['text'])) {
                return $body['error']['text'];
            }
            if (isset($body['error']['message'])) {
                return $body['error']['message'];
            }
            if (isset($body['message'])) {
                // If message itself is stringified JSON, unpack it
                $nested = json_decode((string) $body['message'], true);
                if (is_array($nested) && isset($nested['error']['text'])) {
                    return $nested['error']['text'];
                }
                return $body['message'];
            }
            return json_encode($body);
        }
        return $response->body() ?: 'HTTP status ' . $response->status();
    }

    /**
     * Safe logger that never exposes credentials or tokens.
     */
    protected function safeLog(string $level, string $message, array $context = []): void
    {
        $cleanContext = array_map(function ($value) {
            if (is_string($value)) {
                return preg_replace('/(Token\s+)[A-Za-z0-9_\-\.]{6,}/i', '$1[REDACTED]', $value);
            }
            return $value;
        }, $context);

        Log::log($level, '[BlackBuckFastag] ' . $message, $cleanContext);
    }
}
