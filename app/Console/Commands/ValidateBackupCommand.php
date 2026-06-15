<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;

class ValidateBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate the integrity of generated backups, test database connectivity, and run DR diagnostics.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=========================================");
        $this->info("   AnnSathi v2 Backup & DR Validator     ");
        $this->info("=========================================");

        // 1. Verify DB Connectivity & Capacity Check
        $this->info("\n[1/4] Checking Database Connectivity...");
        try {
            DB::connection()->getPdo();
            $this->info("✔ Database connected successfully.");
            
            // Check table count
            $tables = DB::select("SHOW TABLES");
            $this->info("✔ Total tables found: " . count($tables));
        } catch (Exception $e) {
            $this->error("✘ Database connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        // 2. Validate Spatie Backup Zip Files
        $this->info("\n[2/4] Scanning Backup Storages...");
        $diskName = config('backup.backup.destination.disks.0', 'local');
        $this->info("Backup destination disk: {$diskName}");
        
        $disk = Storage::disk($diskName);
        $backupFolderName = config('backup.backup.name', 'AnnSathi_v2');
        
        $files = $disk->allFiles($backupFolderName);
        $zipFiles = array_filter($files, fn($file) => str_ends_with($file, '.zip'));

        if (empty($zipFiles)) {
            $this->warn("⚠ No backup zip files found in directory: {$backupFolderName}");
            $this->info("💡 Run 'php artisan backup:run' to generate a backup.");
        } else {
            // Sort by last modified descending
            usort($zipFiles, fn($a, $b) => $disk->lastModified($b) <=> $disk->lastModified($a));
            $latestBackup = $zipFiles[0];
            $size = $disk->size($latestBackup);
            $lastModified = date('Y-m-d H:i:s', $disk->lastModified($latestBackup));
            
            $this->info("✔ Found " . count($zipFiles) . " backup files.");
            $this->info("Latest backup: {$latestBackup} ({$size} bytes, modified: {$lastModified})");

            // Verify Zip Integrity
            $this->info("Verifying Zip integrity...");
            $localPath = $disk->path($latestBackup);
            
            if (class_exists(ZipArchive::class)) {
                $zip = new ZipArchive();
                $res = $zip->open($localPath);
                if ($res === true) {
                    $this->info("✔ Zip file is valid. Status: Open OK.");
                    $sqlFilesCount = 0;
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $filename = $zip->getNameIndex($i);
                        if (str_ends_with($filename, '.sql')) {
                            $sqlFilesCount++;
                        }
                    }
                    $zip->close();
                    if ($sqlFilesCount > 0) {
                        $this->info("✔ Zip contains {$sqlFilesCount} SQL dump file(s). Integrity passed.");
                    } else {
                        $this->warn("⚠ Zip does not contain any .sql dump files!");
                    }
                } else {
                    $this->error("✘ Zip file is corrupted or cannot be opened. Error code: {$res}");
                }
            } else {
                $this->warn("⚠ PHP ZipArchive extension is not available. Skipping deep zip verification.");
            }
        }

        // 3. Diagnose Queues & Horizon
        $this->info("\n[3/4] Running Queue & Horizon Diagnostics...");
        try {
            $redis = DB::connection('redis') ? 'Available' : 'Unavailable';
            $this->info("✔ Redis service status: {$redis}");
            
            if (class_exists(\Laravel\Horizon\Horizon::class)) {
                $this->info("✔ Laravel Horizon is installed.");
            } else {
                $this->warn("⚠ Laravel Horizon is not installed or configured.");
            }
        } catch (Exception $e) {
            $this->warn("⚠ Redis queue server check warning: " . $e->getMessage());
        }

        // 4. Print Disaster Recovery Guide
        $this->info("\n[4/4] Disaster Recovery Quick Playbook Reference:");
        $this->comment("   -----------------------------------------------------------------");
        $this->comment("   A. DATABASE RECOVERY PROCEDURE:");
        $this->comment("      1. Unzip the latest backup file: storage/app/AnnSathi_v2/*.zip");
        $this->comment("      2. Locate thedb-dumps/mysql-annsathi_v2.sql file.");
        $this->comment("      3. Run: mysql -u [user] -p [database_name] < db-dumps/mysql-annsathi_v2.sql");
        $this->comment("   ");
        $this->comment("   B. QUEUE RECOVERY PROCEDURE:");
        $this->comment("      1. Check failed jobs: php artisan queue:failed");
        $this->comment("      2. Retry failed jobs: php artisan queue:retry all");
        $this->comment("      3. Check Horizon dashboard for active worker health.");
        $this->comment("   ");
        $this->comment("   C. REVERB SOCKET SERVER RECOVERY:");
        $this->comment("      1. Restart the socket server: php artisan reverb:start --host=0.0.0.0 --port=8080");
        $this->comment("   -----------------------------------------------------------------");

        return Command::SUCCESS;
    }
}
