<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pembayaran;
use App\Models\AlokasiPembayaran;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    /*
    |--------------------------------------------------------------------------
    | STATUS TAGIHAN
    |--------------------------------------------------------------------------
    */

    public const STATUS_BELUM_BAYAR = 'Belum Bayar';
    public const STATUS_SEBAGIAN = 'Sebagian';
    public const STATUS_LUNAS = 'Lunas';

    public const STATUS_JATUH_TEMPO = 'Jatuh Tempo';

    public const STATUS_DIBATALKAN = 'Dibatalkan';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    
    protected $fillable = [

    'pelanggan_id',

    'invoice_no',

    'periode',

    'bulan',

    'tahun',

    'tanggal_tagihan',

    'tanggal_jatuh_tempo',

    'tanggal_bayar',

    'nominal',

    'subtotal',

    'tunggakan',

    'denda',

    'total',

    'dibayar',

    'sisa',

    'status',

    'keterangan',

];

    protected $casts = [

    'tanggal_tagihan'      => 'date',

    'tanggal_jatuh_tempo'  => 'date',

    'tanggal_bayar'        => 'datetime',

    'nominal'              => 'decimal:2',

    'subtotal'             => 'decimal:2',

    'tunggakan'            => 'decimal:2',

    'denda'                => 'decimal:2',

    'total'                => 'decimal:2',

    'dibayar'              => 'decimal:2',

    'sisa'                 => 'decimal:2',

];

    /**
     * Relasi ke Pelanggan
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * Relasi ke Pembayaran
     */
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }
    /**
     * Apakah tagihan sudah lunas.
     */
    public function isLunas(): bool
    {
        return $this->status === self::STATUS_LUNAS;
    }

    /**
     * Apakah pembayaran sebagian.
     */
    public function isSebagian(): bool
    {
        return $this->status === self::STATUS_SEBAGIAN;
    }

    /**
     * Apakah belum ada pembayaran.
     */
    public function isBelumBayar(): bool
    {
        return $this->status === self::STATUS_BELUM_BAYAR;
    }
    /**
     * Riwayat pembayaran yang masuk ke tagihan ini
     */
    public function alokasi(): HasMany
    {
        return $this->hasMany(AlokasiPembayaran::class);
    }
    public function saldoUsages(): HasMany
    {
        return $this->hasMany(SaldoUsage::class);
    }
    /*
    |--------------------------------------------------------------------------
    | HELPER PERHITUNGAN TAGIHAN
    |--------------------------------------------------------------------------
    */

    public function getTotalTagihan(): float
    {
        return (float) (
            $this->total
            ?: $this->subtotal
            ?: $this->nominal
            ?: 0
        );
    }

    public function getTotalDibayar(): float
    {
        $alokasi = $this->alokasi()
            ->whereHas('pembayaran', function ($q) {
                $q->where('status', Pembayaran::STATUS_BERHASIL);
            })
            ->sum('nominal');

        if ($alokasi > 0) {
            return (float) $alokasi;
        }

        $pembayaran = $this->pembayaran()
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->first();

        if ($pembayaran) {
            return (float) (
                $pembayaran->dibayar
                ?: $pembayaran->nominal
                ?: $pembayaran->total_bayar
                ?: 0
            );
        }

        return 0;
    }

    public function getSisaTagihan(): float
    {
        return max(
            0,
            $this->getTotalTagihan() - $this->getTotalDibayar()
        );
    }

    public function refreshStatus(bool $save = true): self
    {
        $total = $this->getTotalTagihan();

        $dibayar = $this->getTotalDibayar();

        $sisa = max(0, $total - $dibayar);

        $this->dibayar = $dibayar;

        $this->sisa = $sisa;

        if ($dibayar <= 0) {

            $this->status = (
                $this->tanggal_jatuh_tempo
                && $this->tanggal_jatuh_tempo->isPast()
            )
                ? self::STATUS_JATUH_TEMPO
                : self::STATUS_BELUM_BAYAR;

            $this->tanggal_bayar = null;

        } elseif ($sisa <= 0.01) {

            $this->status = self::STATUS_LUNAS;

            $this->tanggal_bayar ??= now();

        } else {

            $this->status = self::STATUS_SEBAGIAN;
        }

        if ($save) {
            $this->save();
        }

        return $this;
    }
    
}