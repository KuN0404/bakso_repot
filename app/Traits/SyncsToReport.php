<?php

namespace App\Traits;

/**
 * Dummy SyncsToReport Trait
 *
 * Di web bakso-report (aplikasi laporan), data dibaca langsung
 * dari database laporan pusat. Trait ini sengaja dibuat kosong
 * agar model-model yang di-copy dari POS utama tidak melakukan
 * operasi sinkronisasi tambahan (mencegah error loop koneksi).
 */
trait SyncsToReport
{
    public static function bootSyncsToReport(): void
    {
        // Sengaja dikosongkan karena bakso-report adalah aplikasi read-only laporan
    }
}
