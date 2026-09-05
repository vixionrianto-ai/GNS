<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    use HasFactory;

    public const STATUS_BELUM_BAYAR = 'Belum Bayar';
    public const STATUS_LUNAS = 'Lunas';
    public const STATUS_JATUH_TEMPO = 'Jatuh Tempo';
    public const STATUS_DIBATALKAN = 'Dibatalkan';
    public const STATUS_SEBAGIAN = 'Sebagian';

    protected $fillable = [
        'pelanggan_id', 'invoice_no', 'periode', 'bulan', 'tahun',
        'tanggal_tagihan', 'tanggal_jatuh_tempo', 'nominal', 'denda',
        'total', 'dibayar', 'sisa', 'status', 'tanggal_bayar', 'keterangan',
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

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function alokasi(): HasMany
    {
        return $this->hasMany(AlokasiPembayaran::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function getTotalTagihan(): float
    {
        $total = (float) ($this->total ?? 0);

        return $total > 0
            ? $total
            : (float) ($this->nominal ?? 0) + (float) ($this->denda ?? 0);
    }

    public function getTotalDibayar(): float
    {
        $allocatedPaymentIds = $this->alokasi()
            ->whereHas('pembayaran', function ($query) {
                $query->where('status', Pembayaran::STATUS_BERHASIL);
            })
            ->pluck('pembayaran_id');

        $allocated = (float) $this->alokasi()
            ->whereHas('pembayaran', function ($query) {
                $query->where('status', Pembayaran::STATUS_BERHASIL);
            })
            ->sum('nominal');

        $legacyDirect = (float) $this->pembayaran()
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->whereNotIn('id', $allocatedPaymentIds)
            ->get()
            ->sum(fn ($pembayaran) =>
                (float) ($pembayaran->dibayar ?: $pembayaran->nominal ?: $pembayaran->total_bayar ?: 0)
            );

        return $allocated + $legacyDirect;
    }

    public function getSisaTagihan(): float
    {
        return max(0, $this->getTotalTagihan() - $this->getTotalDibayar());
    }

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
