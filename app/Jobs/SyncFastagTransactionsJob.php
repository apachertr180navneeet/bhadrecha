<?php

namespace App\Jobs;

use App\Services\BlackBuckFastagService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncFastagTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600; // 10 minutes

    protected ?string $vehicleNumber;
    protected ?string $fromDate;
    protected ?string $toDate;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $vehicleNumber = null, ?string $fromDate = null, ?string $toDate = null)
    {
        $this->vehicleNumber = $vehicleNumber;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    /**
     * Execute the job.
     */
    public function handle(BlackBuckFastagService $service): void
    {
        Log::info('[SyncFastagTransactionsJob] Starting FASTag synchronization', [
            'vehicle_number' => $this->vehicleNumber ?: 'ALL_VEHICLES',
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
        ]);

        if (!empty($this->vehicleNumber)) {
            $result = $service->syncVehicleTransactions($this->vehicleNumber, $this->fromDate, $this->toDate);
        } else {
            $result = $service->syncAllVehicles($this->fromDate, $this->toDate);
        }

        Log::info('[SyncFastagTransactionsJob] FASTag synchronization completed', [
            'result' => $result,
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[SyncFastagTransactionsJob] Synchronization job failed: ' . $exception->getMessage(), [
            'vehicle' => $this->vehicleNumber,
            'exception' => $exception->getTraceAsString(),
        ]);
    }
}
