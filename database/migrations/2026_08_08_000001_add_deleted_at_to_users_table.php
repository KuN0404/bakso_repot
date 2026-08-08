<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyamakan skema 'users' di report dengan main app (bakso-malang), yang sejak
 * migration 2026_08_08_000001_add_soft_deletes_to_users_table sudah soft delete.
 *
 * Unique constraint pada username/email SENGAJA diturunkan jadi index biasa,
 * persis seperti perubahan yang sama di main app: baris yang di-soft-delete
 * tetap menempati nilai unique di level DB, sehingga username/email bekas user
 * yang dinonaktifkan tidak akan bisa dipakai lagi oleh user baru selama masih
 * unique constraint. Keunikan untuk baris aktif (deleted_at IS NULL) tetap
 * dijaga di level aplikasi (lihat ReportSyncService/SyncsToReport main app).
 *
 * Idempotent: aman dijalankan berkali-kali atau di DB yang sudah pernah
 * di-patch manual sebelumnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasIndex('users', 'users_username_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_username_unique');
            });
        }
        if (! Schema::hasIndex('users', 'users_username_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('username');
            });
        }

        if (Schema::hasIndex('users', 'users_email_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_unique');
            });
        }
        if (! Schema::hasIndex('users', 'users_email_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_username_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_username_index');
            });
        }
        if (Schema::hasIndex('users', 'users_email_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_email_index');
            });
        }
        if (! Schema::hasIndex('users', 'users_username_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        }
        if (! Schema::hasIndex('users', 'users_email_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        }

        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
