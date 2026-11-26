<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    /**
     * Display the database backup management page.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Only super administrators can access backups.');
        }

        $connectionConfig = config('database.connections.mysql', []);
        $databaseName = $connectionConfig['database'] ?? env('DB_DATABASE', 'bhadrecha');
        $dbHost = $connectionConfig['host'] ?? env('DB_HOST', '127.0.0.1');
        $dbPort = $connectionConfig['port'] ?? env('DB_PORT', '3306');
        $dbUsername = $connectionConfig['username'] ?? env('DB_USERNAME', 'root');

        $isConnected = false;
        $connectionError = null;
        $tables = [];
        $totalSizeMb = 0;
        $totalRows = 0;

        try {
            DB::connection()->getPdo();
            $isConnected = true;

            $tableStatus = DB::select('SHOW TABLE STATUS');
            foreach ($tableStatus as $table) {
                $dataLength = (int) ($table->Data_length ?? 0);
                $indexLength = (int) ($table->Index_length ?? 0);
                $rows = (int) ($table->Rows ?? 0);
                $tableSizeMb = round(($dataLength + $indexLength) / (1024 * 1024), 2);

                $tables[] = [
                    'name' => $table->Name,
                    'engine' => $table->Engine ?? 'InnoDB',
                    'rows' => $rows,
                    'size_mb' => $tableSizeMb,
                    'collation' => $table->Collation ?? '',
                ];

                $totalSizeMb += ($dataLength + $indexLength);
                $totalRows += $rows;
            }

            $totalSizeMb = round($totalSizeMb / (1024 * 1024), 2);
        } catch (\Throwable $e) {
            $isConnected = false;
            $connectionError = $e->getMessage();
        }

        // Scan local backups directory
        $storedBackups = [];
        $backupsDir = base_path('backups');
        if (is_dir($backupsDir)) {
            $files = scandir($backupsDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $filePath = $backupsDir . DIRECTORY_SEPARATOR . $file;
                if (is_file($filePath)) {
                    $storedBackups[] = [
                        'name' => $file,
                        'size' => round(filesize($filePath) / (1024 * 1024), 2), // MB
                        'created_at' => filemtime($filePath),
                        'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                    ];
                }
            }

            usort($storedBackups, function ($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });
        }

        return view('admin.database-backup.index', compact(
            'databaseName',
            'dbHost',
            'dbPort',
            'dbUsername',
            'isConnected',
            'connectionError',
            'tables',
            'totalSizeMb',
            'totalRows',
            'storedBackups'
        ));
    }

    /**
     * Download live SQL dump of the database.
     */
    public function download()
    {
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $connectionConfig = config('database.connections.mysql', []);
        $database = $connectionConfig['database'] ?? env('DB_DATABASE', 'bhadrecha');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "{$database}_backup_{$timestamp}.sql";

        try {
            $pdo = DB::connection()->getPdo();
            $tables = DB::select('SHOW TABLES');
            $tableKey = "Tables_in_{$database}";

            $response = new StreamedResponse(function () use ($tables, $tableKey, $database, $pdo) {
                $handle = fopen('php://output', 'w');

                // Header
                fwrite($handle, "-- -----------------------------------------------------\n");
                fwrite($handle, "-- Database Backup: {$database}\n");
                fwrite($handle, "-- Generated At: " . now()->toDateTimeString() . "\n");
                fwrite($handle, "-- -----------------------------------------------------\n\n");
                fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
                fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
                fwrite($handle, "SET AUTOCOMMIT=0;\n");
                fwrite($handle, "START TRANSACTION;\n\n");

                foreach ($tables as $table) {
                    $tableName = $table->$tableKey ?? current((array) $table);

                    fwrite($handle, "-- -----------------------------------------------------\n");
                    fwrite($handle, "-- Table structure for `{$tableName}`\n");
                    fwrite($handle, "-- -----------------------------------------------------\n");
                    fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

                    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                    if (!empty($createTable)) {
                        $createSql = $createTable[0]->{'Create Table'} ?? '';
                        fwrite($handle, $createSql . ";\n\n");
                    }

                    fwrite($handle, "-- Dumping data for table `{$tableName}`\n");
                    $offset = 0;
                    $chunkSize = 500;

                    while (true) {
                        $rows = DB::select("SELECT * FROM `{$tableName}` LIMIT {$chunkSize} OFFSET {$offset}");
                        if (empty($rows)) {
                            break;
                        }

                        foreach ($rows as $row) {
                            $values = [];
                            foreach ((array) $row as $val) {
                                if (is_null($val)) {
                                    $values[] = 'NULL';
                                } else {
                                    $values[] = $pdo->quote((string) $val);
                                }
                            }

                            $columns = array_map(function ($col) {
                                return "`{$col}`";
                            }, array_keys((array) $row));

                            fwrite($handle, "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n");
                        }

                        $offset += $chunkSize;
                    }

                    fwrite($handle, "\n");
                }

                fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
                fwrite($handle, "COMMIT;\n");

                fclose($handle);
            });

            $response->headers->set('Content-Type', 'application/sql');
            $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

            return $response;
        } catch (\Throwable $e) {
            return redirect()->route('admin.database-backup')
                ->with('error', 'Failed to generate database backup: ' . $e->getMessage());
        }
    }

    /**
     * Download stored archive/backup file from backups directory.
     */
    public function downloadStoredBackup(string $filename)
    {
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $safeFilename = basename($filename);
        $filePath = base_path('backups' . DIRECTORY_SEPARATOR . $safeFilename);

        if (!file_exists($filePath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($filePath, $safeFilename);
    }
}
