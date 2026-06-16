<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite does not support database partitioning, so we run this only on MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            // 1. Partition activity_log
            // First drop primary key if it exists as single column, since we might be retrying
            try {
                DB::statement('ALTER TABLE activity_log DROP PRIMARY KEY');
            } catch (\Exception $e) {
                // Ignore if already dropped
            }
            DB::statement('ALTER TABLE activity_log MODIFY created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
            DB::statement('ALTER TABLE activity_log ADD PRIMARY KEY (id, created_at)');
            DB::statement('ALTER TABLE activity_log PARTITION BY RANGE (YEAR(created_at)) (
                PARTITION p2025 VALUES LESS THAN (2026),
                PARTITION p2026 VALUES LESS THAN (2027),
                PARTITION p2027 VALUES LESS THAN (2028),
                PARTITION pmax VALUES LESS THAN MAXVALUE
            )');

            // 2. Partition notifications_log
            // Drop foreign key constraints because MySQL partitioned tables cannot have foreign keys
            try {
                DB::statement('ALTER TABLE notifications_log DROP FOREIGN KEY notifications_log_restaurant_id_foreign');
            } catch (\Exception $e) {}
            try {
                DB::statement('ALTER TABLE notifications_log DROP FOREIGN KEY notifications_log_branch_id_foreign');
            } catch (\Exception $e) {}
            try {
                DB::statement('ALTER TABLE notifications_log DROP FOREIGN KEY notifications_log_user_id_foreign');
            } catch (\Exception $e) {}

            try {
                DB::statement('ALTER TABLE notifications_log DROP PRIMARY KEY');
            } catch (\Exception $e) {}

            DB::statement('ALTER TABLE notifications_log MODIFY created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
            DB::statement('ALTER TABLE notifications_log ADD PRIMARY KEY (id, created_at)');
            DB::statement('ALTER TABLE notifications_log PARTITION BY RANGE (YEAR(created_at)) (
                PARTITION p2025 VALUES LESS THAN (2026),
                PARTITION p2026 VALUES LESS THAN (2027),
                PARTITION p2027 VALUES LESS THAN (2028),
                PARTITION pmax VALUES LESS THAN MAXVALUE
            )');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE activity_log REMOVE PARTITIONING');
            DB::statement('ALTER TABLE activity_log DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
            DB::statement('ALTER TABLE activity_log MODIFY created_at TIMESTAMP NULL');

            DB::statement('ALTER TABLE notifications_log REMOVE PARTITIONING');
            DB::statement('ALTER TABLE notifications_log DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
            DB::statement('ALTER TABLE notifications_log MODIFY created_at TIMESTAMP NULL');

            // Restore foreign keys
            DB::statement('ALTER TABLE notifications_log ADD CONSTRAINT notifications_log_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES restaurants (id) ON DELETE CASCADE');
            DB::statement('ALTER TABLE notifications_log ADD CONSTRAINT notifications_log_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES branches (id) ON DELETE CASCADE');
            DB::statement('ALTER TABLE notifications_log ADD CONSTRAINT notifications_log_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        }
    }
};
