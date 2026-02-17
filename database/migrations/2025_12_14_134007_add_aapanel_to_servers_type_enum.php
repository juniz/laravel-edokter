<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // MySQL tidak mendukung alter enum secara langsung
        // Kita perlu alter table dengan MODIFY COLUMN
        DB::statement("ALTER TABLE servers MODIFY COLUMN type ENUM('cpanel', 'directadmin', 'proxmox', 'aapanel') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Remove aapanel dari enum
        DB::statement("ALTER TABLE servers MODIFY COLUMN type ENUM('cpanel', 'directadmin', 'proxmox') NOT NULL");
    }
};
