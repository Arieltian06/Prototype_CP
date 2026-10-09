<?php

namespace App\Support;

final class PublicContent
{
    /** Remove legacy seed annotations only at the public presentation boundary. */
    public static function text(?string $value): string
    {
        return trim(str_replace([
            ' Data Prototype: koordinat, biaya, jam, deskripsi, aktivitas, dan nilai kesesuaian belum diverifikasi; bukan informasi resmi kunjungan.',
            ' — titik contoh.',
            ' — area mangrove simulasi untuk prototype.',
            ' — lokasi simulasi.',
            ' (Demo)',
            'Simulasi 08.00–17.00 WIB (belum diverifikasi)',
            ' Titik lokasi ini merupakan simulasi, bukan penetapan akses kawasan.',
            'sebagai contoh ruang pembelajaran',
            'Area contoh untuk memahami',
            'konteks contoh pembelajaran',
            'sebagai contoh titik pertemuan',
            'Contoh lokasi pengamatan',
        ], [
            '', '', '', '', '', '08.00–17.00 WIB', '',
            'sebagai ruang pembelajaran', 'Area untuk memahami',
            'konteks pembelajaran', 'sebagai titik pertemuan',
            'Lokasi pengamatan',
        ], $value ?? ''));
    }
}
