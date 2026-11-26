<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExpiryNotificationService;

class CheckExpiringDocumentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:expiring-documents {--days=1 : Days ahead to check for expiry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and generate notifications for expiring documents (Vehicles, Drivers, DMS) and Bilty E-Way bills (1 day prior)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Scanning for documents and E-Way bills expiring within {$days} day(s)...");

        $results = ExpiryNotificationService::checkAndGenerateNotifications($days);

        $this->info("Scan completed successfully!");
        $this->table(
            ['Category', 'New Alerts Generated'],
            [
                ['E-Way Bills', $results['eway_bills']],
                ['Vehicle Documents', $results['vehicle_docs']],
                ['Driver Licenses', $results['driver_docs']],
                ['DMS Documents', $results['dms_docs']],
                ['Total New Alerts', $results['total_created']],
            ]
        );

        return Command::SUCCESS;
    }
}
