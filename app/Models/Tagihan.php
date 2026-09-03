<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | STATUS TAGIHAN
    |--------------------------------------------------------------------------
    */

    public const STATUS_BELUM_BAYAR = 'Belum Bayar';
    public const STATUS_LUNAS = 'Lunas';
    public const STATUS_JATUH_TEMPO = 'Jatuh Tempo';
    public const STATUS_DIBATALKAN = 'Dibatalkan';
    public const STATUS_SEBAGIAN = 'Sebagian';

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
        'nominal',
        'denda',
        'total',
        'dibayar',
        'sisa',
        'status',
        'tanggal_bayar',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_tagihan' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'datetime',
        'nominal' => 'decimal:2',
        'denda' => 'decimal:2',
        'total' => 'decimal:2',
        'dibayar' => 'decimal:2',
        'sisa' => 'decimal:2',
    ];

    /**
     * Relasi ke Pelanggan.
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * Alokasi pembayaran untuk tagihan ini.
     */
    public function alokasi(): HasMany
    {
        return $this->hasMany(AlokasiPembayaran::class);
    }

    /**
     * Pembayaran langsung/legacy untuk tagihan ini.
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Total nilai tagihan yang menjadi kewajiban pelanggan.
     */
    public function getTotalTagihan(): float
    {
        $total = (float) ($this->total ?? 0);

        if ($total > 0) {
            return $total;
        }

        return (float) ($this->nominal ?? 0) + (float) ($this->denda ?? 0);
    }

    /**
     * Total pembayaran yang benar-benar dialokasikan ke tagihan.
     * Alokasi menjadi sumber utama; data Pembayaran lama tetap didukung.
     */
    public function getTotalDibayar(): float
    {
        if ($this->relationLoaded('alokasi')) {
            $allocated = (float) $this->alokasi->sum('nominal');
            if ($allocated > 0) {
                return $allocated;
            }
        } else {
            $allocated = (float) $this->alokasi()->sum('nominal');
            if ($allocated > 0) {
                return $allocated;
            }
        }

        if ($this->relationLoaded('pembayaran')) {
            return (float) $this->pembayaran
                ->where('status', Pembayaran::STATUS_BERHASIL)
                ->sum(function ($pembayaran) {
                    return (float) ($pembayaran->dibayar ?: $pembayaran->nominal ?: $pembayaran->total_bayar ?: 0);
                });
        }

        $pembayaran = $this->pembayaran()
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->latest('id')
            ->first();

        if ($pembayaran) {
            return (float) ($pembayaran->dibayar ?: $pembayaran->nominal ?: $pembayaran->total_bayar ?: 0);
        }

        return 0.0;
    }

    /**
     * Sisa tagihan setelah pembayaran/alokasi.
     */
    public function getSisaTagihan(): float
    {
        return max(0, $this->getTotalTagihan() - $this->getTotalDibayar());
    }

    /**
     * Sinkronkan status dan nilai pembayaran berdasarkan data aktual.
     */
    public function refreshStatus(): self
    {
        $total = $this->getTotalTagihan();
        $dibayar = min($total, $this->getTotalDibayar());
        $sisa = max(0, $total - $dibayar);

        if ($this->status === self::STATUS_DIBATALKAN) {
            $this->update([
                'dibayar' => $dibayar,
                'sisa' => $sisa,
            ]);
            return $this->fresh();
        }

        if ($sisa <= 0) {
            $status = self::STATUS_LUNAS;
            $tanggalBayar = $this->tanggal_bayar ?? now();
        } elseif ($dibayar > 0) {
            $status = self::STATUS_SEBAGIAN;
            $tanggalBayar = null;
        } elseif ($this->tanggal_jatuh_tempo && $this->tanggal_jatuh_tempo->isPast()) {
            $status = self::STATUS_JATUH_TEMPO;
            $tanggalBayar = null;
        } else {
            $status = self::STATUS_BELUM_BAYAR;
            $tanggalBayar = null;
        }

        $this->update([
            'dibayar' => $dibayar,
            'sisa' => $sisa,
            'status' => $status,
            'tanggal_bayar' => $tanggalBayar,
        ]);

        return $this->fresh();
    }
}
