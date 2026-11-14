<?php

namespace App\Console\Commands;

use App\Jobs\SyncFastagTransactionsJob;
use App\Services\BlackBuckFastagService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncFastagTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fastag:sync 
                            {--vehicle= : Specific vehicle registration number} 
                            {--from= : From date (YYYY-MM-DD)} 
                            {--to= : To date (YYYY-MM-DD)} 
                            {--queue : Queue the synchronization job in the background}';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize BlackBuck FASTag toll transactions for vehicles';

    /**
     * Execute the console command.
     */
    public function handle(BlackBuckFastagService $service): int
    {
        $vehicle = $this->option('vehicle');
        $from = $this->option('from') ?? Carbon::now()->subDays(config('blackbuck.sync_days_lookback', 3))->format('Y-m-d');
        $to = $this->option('to') ?? Carbon::now()->format('Y-m-d');
        $useQueue = $this->option('queue');

        $this->info("Starting FASTag synchronization...");
        $this->info("Target: " . ($vehicle ?: 'ALL VEHICLES'));
        $this->info("Date range: {$from} to {$to}");

        if ($useQueue) {
            SyncFastagTransactionsJob::dispatch($vehicle, $from, $to);
            $this->info("Synchronization job has been queued successfully.");
            return Command::SUCCESS;
        }

        if (!empty($vehicle)) {
            $this->line("Fetching transactions for {$vehicle}...");
            $result = $service->syncVehicleTransactions($vehicle, $from, $to);

            if ($result['success']) {
                $this->info("Successfully synchronized {$vehicle}!");
                $this->line("Received: {$result['records_received']}, Saved: {$result['records_saved']}");
                return Command::SUCCESS;
            } else {
                $this->error("Failed to sync {$vehicle}: " . ($result['error'] ?? 'Unknown error'));
                return Command::FAILURE;
            }
        }

        $this->line("Synchronizing all registered vehicles...");
        $result = $service->syncAllVehicles($from, $to);

        $this->info("Sync finished with status: {$result['status']}");
        $this->line("Vehicles processed: {$result['vehicles_processed']}");
        $this->line("Total records received: {$result['total_records_received']}");
        $this->line("Total records saved: {$result['total_records_saved']}");

        if (!empty($result['errors'])) {
            $this->warn("Encountered errors for " . count($result['errors']) . " vehicles.");
        }

        return $result['success'] ? Command::SUCCESS : Command::FAILURE;
    }
}
