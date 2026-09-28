<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_block_update');
        DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_block_delete');
        DB::unprepared("
            CREATE TRIGGER audit_logs_block_update
            BEFORE UPDATE ON audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs are append-only';
            END
        ");
        DB::unprepared("
            CREATE TRIGGER audit_logs_block_delete
            BEFORE DELETE ON audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs cannot be deleted';
            END
        ");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_block_update');
        DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_block_delete');
    }
};
