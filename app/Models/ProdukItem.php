<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukItem extends Model
{
    public const PROGRAMS = [
        'senirupa' => 'Bimbel Gambar Senirupa Desain',
        'arsitektur' => 'Bimbel Gambar Arsitektur',
        'kelas_gambar_anak' => 'Program Kelas Gambar Anak',
    ];

    public const TAHUN_AJARAN = [
        '2025 - 2026',
        '2026 - 2027',
        '2027 - 2028',
    ];

    public const SUBTITLES = [
        'kelas_gambar_anak' => 'Program Kursus Gambar SD, SMP, Workshop dan Program Liburan',
    ];

    protected $fillable = [
        'program',
        'tahun_ajaran',
        'kode_1',
        'kode_2',
        'kode_3',
        'kode_4',
        'nama',
        'biaya_daftar',
        'biaya_pendidikan',
        'discount',
        'siswa',
        'omzet',
    ];

    protected $casts = [
        'biaya_daftar' => 'integer',
        'biaya_pendidikan' => 'integer',
        'discount' => 'integer',
        'siswa' => 'integer',
        'omzet' => 'integer',
    ];

    public static function programOptions(): array
    {
        return self::PROGRAMS;
    }

    public static function tahunAjaranOptions(): array
    {
        return self::TAHUN_AJARAN;
    }

    public static function programLabels(): array
    {
        return self::PROGRAMS;
    }

    public static function programSubtitle(string $key): ?string
    {
        return self::SUBTITLES[$key] ?? null;
    }
}
