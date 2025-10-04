<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\TransaksiKeuangan;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransaksiKeuanganRequest;
use App\Http\Requests\UpdateTransaksiKeuanganRequest;
use App\Http\Resources\TransaksiKeuanganResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TransaksiKeuanganController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:transaksi-keuangan.index'], only: ['index', 'dashboard', 'chartData', 'monthlySummary']),
            new Middleware(['permission:transaksi-keuangan.create'], only: ['store']),
            new Middleware(['permission:transaksi-keuangan.edit'], only: ['update']),
            new Middleware(['permission:transaksi-keuangan.delete'], only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $query = TransaksiKeuangan::with(['profileMasjid', 'createdBy', 'updatedBy'])
            ->byMasjid($profileMasjidId);

        // Filter berdasarkan type (income/expense)
        if ($request->filled('type')) {
            if ($request->type === 'income') {
                $query->income();
            } elseif ($request->type === 'expense') {
                $query->expense();
            }
        }

        // Filter berdasarkan kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', 'like', '%' . $request->kategori . '%');
        }

        // Filter berdasarkan tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Filter bulanan
        if ($request->filled('year') && $request->filled('month')) {
            $query->byMonth($request->year, $request->month);
        }

        // Filter mingguan
        if ($request->filled('year') && $request->filled('week')) {
            $query->byWeek($request->year, $request->week);
        }

        $transaksi = $query->latest('tanggal')->paginate(15);

        // Jika ada filter bulanan, tambahkan summary income/expense bulan tersebut
        $monthlySummary = null;
        if ($request->filled('year') && $request->filled('month')) {
            $year = $request->year;
            $month = $request->month;

            // Hitung total income dan expense untuk bulan tersebut
            $monthlyIncome = TransaksiKeuangan::byMasjid($profileMasjidId)
                ->income()
                ->byMonth($year, $month)
                ->sum('jumlah');

            $monthlyExpense = TransaksiKeuangan::byMasjid($profileMasjidId)
                ->expense()
                ->byMonth($year, $month)
                ->sum('jumlah');

            $monthlySummary = [
                'year' => (int) $year,
                'month' => (int) $month,
                'month_name' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'total_income' => (float) $monthlyIncome,
                'total_expense' => (float) $monthlyExpense,
                'net_balance' => (float) ($monthlyIncome - $monthlyExpense),
                'total_transactions' => $transaksi->total()
            ];
        }

        $responseData = [
            'transactions' => TransaksiKeuanganResource::collection($transaksi),
            'pagination' => [
                'current_page' => $transaksi->currentPage(),
                'last_page' => $transaksi->lastPage(),
                'per_page' => $transaksi->perPage(),
                'total' => $transaksi->total(),
                'from' => $transaksi->firstItem(),
                'to' => $transaksi->lastItem(),
            ]
        ];

        // Tambahkan monthly summary jika ada
        if ($monthlySummary) {
            $responseData['monthly_summary'] = $monthlySummary;
        }

        return response()->json([
            'success' => true,
            'message' => 'List data transaksi keuangan',
            'data' => $responseData
        ]);
    }

    public function store(StoreTransaksiKeuanganRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $buktiTransaksi = null;
        if ($request->hasFile('bukti_transaksi')) {
            $buktiTransaksi = time() . '.' . $request->file('bukti_transaksi')->getClientOriginalExtension();
            $request->file('bukti_transaksi')->storeAs('public/bukti-transaksi', $buktiTransaksi);
        }

        $transaksi = TransaksiKeuangan::create([
            'profile_masjid_id' => $profileMasjidId,
            'bukti_transaksi' => $buktiTransaksi,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            ...$validated
        ]);

        return response()->json(
            TransaksiKeuanganResource::customResponse(true, 'Data transaksi keuangan berhasil disimpan.', new TransaksiKeuanganResource($transaksi->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function show(TransaksiKeuangan $transaksiKeuangan)
    {
        return response()->json(
            TransaksiKeuanganResource::customResponse(true, 'Detail data transaksi keuangan.', new TransaksiKeuanganResource($transaksiKeuangan->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function update(UpdateTransaksiKeuanganRequest $request, TransaksiKeuangan $transaksiKeuangan)
    {
        $validated = $request->validated();
        $user = $request->user();

        $buktiTransaksi = $transaksiKeuangan->bukti_transaksi;

        if ($request->hasFile('bukti_transaksi')) {
            // Hapus file lama jika ada
            if ($transaksiKeuangan->bukti_transaksi) {
                Storage::delete('public/bukti-transaksi/' . $transaksiKeuangan->bukti_transaksi);
            }

            $buktiTransaksi = time() . '.' . $request->file('bukti_transaksi')->getClientOriginalExtension();
            $request->file('bukti_transaksi')->storeAs('public/bukti-transaksi', $buktiTransaksi);
        }

        $transaksiKeuangan->update([
            'bukti_transaksi' => $buktiTransaksi,
            'updated_by' => $user->id,
            ...$validated
        ]);

        return response()->json(
            TransaksiKeuanganResource::customResponse(true, 'Data transaksi keuangan berhasil diupdate.', new TransaksiKeuanganResource($transaksiKeuangan->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function destroy(TransaksiKeuangan $transaksiKeuangan)
    {
        // Hapus file bukti transaksi jika ada
        if ($transaksiKeuangan->bukti_transaksi) {
            Storage::delete('public/bukti-transaksi/' . $transaksiKeuangan->bukti_transaksi);
        }

        $transaksiKeuangan->delete();

        return response()->json(
            TransaksiKeuanganResource::customResponse(true, 'Data transaksi keuangan berhasil dihapus.', null)
        );
    }

    /**
     * Dashboard summary keuangan masjid
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        // Total keseluruhan (dari awal)
        $totalSaldo = TransaksiKeuangan::getTotalSaldo($profileMasjidId);
        $totalIncome = TransaksiKeuangan::getTotalIncome($profileMasjidId);
        $totalExpense = TransaksiKeuangan::getTotalExpense($profileMasjidId);

        // Data bulan ini
        $currentMonth = Carbon::now();
        $monthlyIncome = TransaksiKeuangan::getTotalIncome(
            $profileMasjidId,
            $currentMonth->startOfMonth()->toDateString(),
            $currentMonth->endOfMonth()->toDateString()
        );
        $monthlyExpense = TransaksiKeuangan::getTotalExpense(
            $profileMasjidId,
            $currentMonth->copy()->startOfMonth()->toDateString(),
            $currentMonth->copy()->endOfMonth()->toDateString()
        );

        // Data chart bulanan (12 bulan terakhir)
        $chartData = TransaksiKeuangan::getMonthlyChartData($profileMasjidId, $currentMonth->year);

        // Transaksi terbaru (5 terakhir)
        $recentTransactions = TransaksiKeuangan::with(['createdBy'])
            ->byMasjid($profileMasjidId)
            ->latest('tanggal')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_saldo' => (float) $totalSaldo,
                    'total_income' => (float) $totalIncome,
                    'total_expense' => (float) $totalExpense,
                    'monthly_income' => (float) $monthlyIncome,
                    'monthly_expense' => (float) $monthlyExpense,
                    'monthly_saldo' => (float) ($monthlyIncome - $monthlyExpense),
                ],
                'chart_data' => $chartData,
                'recent_transactions' => $recentTransactions,
            ]
        ]);
    }

    /**
     * Get chart data untuk periode tertentu
     */
    public function chartData(Request $request)
    {
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $year = $request->get('year', Carbon::now()->year);
        $chartData = TransaksiKeuangan::getMonthlyChartData($profileMasjidId, $year);

        return response()->json([
            'success' => true,
            'data' => $chartData
        ]);
    }

    /**
     * Get monthly summary untuk bulan tertentu
     */
    public function monthlySummary(Request $request)
    {
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        // Hitung total income dan expense untuk bulan tersebut
        $monthlyIncome = TransaksiKeuangan::byMasjid($profileMasjidId)
            ->income()
            ->byMonth($year, $month)
            ->sum('jumlah');

        $monthlyExpense = TransaksiKeuangan::byMasjid($profileMasjidId)
            ->expense()
            ->byMonth($year, $month)
            ->sum('jumlah');

        // Hitung jumlah transaksi
        $totalTransactions = TransaksiKeuangan::byMasjid($profileMasjidId)
            ->byMonth($year, $month)
            ->count();

        $summary = [
            'year' => (int) $year,
            'month' => (int) $month,
            'month_name' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
            'total_income' => (float) $monthlyIncome,
            'total_expense' => (float) $monthlyExpense,
            'net_balance' => (float) ($monthlyIncome - $monthlyExpense),
            'total_transactions' => $totalTransactions,
            'breakdown' => [
                'income_count' => TransaksiKeuangan::byMasjid($profileMasjidId)->income()->byMonth($year, $month)->count(),
                'expense_count' => TransaksiKeuangan::byMasjid($profileMasjidId)->expense()->byMonth($year, $month)->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Monthly summary retrieved successfully',
            'data' => $summary
        ]);
    }

    /**
     * Get profile masjid ID berdasarkan role user
     */
    private function getProfileMasjidId($user, $request)
    {
        if ($user->roles->contains('name', 'superadmin')) {
            return $request->get('profile_masjid_id');
        }

        // Untuk admin dan takmir, gunakan method getMasjidProfile untuk konsistensi
        $profileMasjid = $user->getMasjidProfile();
        return $profileMasjid ? $profileMasjid->id : null;
    }
}
