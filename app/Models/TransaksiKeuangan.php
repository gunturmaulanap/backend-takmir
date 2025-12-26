<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TransaksiKeuangan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_keuangan';

    protected $fillable = [
        'profile_masjid_id',
        'type',
        'kategori',
        'jumlah',
        'tanggal',
        'keterangan',
        'bukti_transaksi',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal' => 'date',
    ];

    // Relasi ke ProfileMasjid
    public function profileMasjid(): BelongsTo
    {
        return $this->belongsTo(ProfileMasjid::class);
    }

    // Relasi ke User (creator)
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke User (updater)
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scope untuk pemasukan
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    // Scope untuk pengeluaran
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    // Scope untuk filter berdasarkan masjid
    public function scopeByMasjid($query, $profileMasjidId)
    {
        return $query->where('profile_masjid_id', $profileMasjidId);
    }

    // Scope untuk filter bulanan
    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);
    }

    // Scope untuk filter mingguan
    public function scopeByWeek($query, $year, $week)
    {
        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();

        return $query->whereBetween('tanggal', [$startOfWeek, $endOfWeek]);
    }

    // Scope untuk filter range tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    // Static method untuk hitung total saldo masjid
    public static function getTotalSaldo($profileMasjidId)
    {
        $totalIncome = self::byMasjid($profileMasjidId)->income()->sum('jumlah');
        $totalExpense = self::byMasjid($profileMasjidId)->expense()->sum('jumlah');

        return $totalIncome - $totalExpense;
    }

    // Static method untuk hitung total pemasukan
    public static function getTotalIncome($profileMasjidId, $startDate = null, $endDate = null)
    {
        $query = self::byMasjid($profileMasjidId)->income();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->sum('jumlah');
    }

    // Static method untuk hitung total pengeluaran
    public static function getTotalExpense($profileMasjidId, $startDate = null, $endDate = null)
    {
        $query = self::byMasjid($profileMasjidId)->expense();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->sum('jumlah');
    }

    // Method untuk data chart bulanan
    public static function getMonthlyChartData($profileMasjidId, $year)
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $income = self::byMasjid($profileMasjidId)
                ->income()
                ->byMonth($year, $month)
                ->sum('jumlah');

            $expense = self::byMasjid($profileMasjidId)
                ->expense()
                ->byMonth($year, $month)
                ->sum('jumlah');

            $data[] = [
                'month' => Carbon::create($year, $month)->locale('id')->translatedFormat('F'),
                'income' => (float) $income,
                'expense' => (float) $expense,
                'saldo' => (float) ($income - $expense),
            ];
        }

        return $data;
    }
}
