<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiKeuangan;
use App\Models\ProfileMasjid;
use Carbon\Carbon;

class TransaksiKeuanganTableSeeder extends Seeder
{
    public function run(): void
    {
        $masjids = ProfileMasjid::all();

        $kategoriIncome = [
            'Infaq Jumat',
            'Donasi',
            'Zakat Fitrah',
            'Zakat Mal',
            'Infaq Ramadhan',
            'Donasi Renovasi',
            'Sumbangan Jamaah',
        ];

        $kategoriExpense = [
            'Operasional',
            'Listrik dan Air',
            'Pemeliharaan',
            'Sound System',
            'Renovasi',
            'Kebersihan',
            'ATK Masjid',
        ];

        foreach ($masjids as $masjid) {
            $userId = $masjid->user_id;

            // Generate transaksi untuk 6 bulan terakhir
            for ($monthBack = 5; $monthBack >= 0; $monthBack--) {
                $currentDate = Carbon::now()->subMonths($monthBack);

                // Generate 3-5 transaksi pemasukan per bulan
                $incomeCount = rand(3, 5);
                for ($i = 0; $i < $incomeCount; $i++) {
                    $randomDay = rand(1, $currentDate->daysInMonth);
                    $tanggal = $currentDate->copy()->day($randomDay);

                    TransaksiKeuangan::create([
                        'profile_masjid_id' => $masjid->id,
                        'type' => 'income',
                        'kategori' => $kategoriIncome[array_rand($kategoriIncome)],
                        'jumlah' => rand(500000, 5000000), // 500rb - 5jt
                        'tanggal' => $tanggal,
                        'keterangan' => 'Pemasukan bulan ' . $tanggal->format('F Y'),
                        'bukti_transaksi' => null,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }

                // Generate 2-4 transaksi pengeluaran per bulan
                $expenseCount = rand(2, 4);
                for ($i = 0; $i < $expenseCount; $i++) {
                    $randomDay = rand(1, $currentDate->daysInMonth);
                    $tanggal = $currentDate->copy()->day($randomDay);

                    TransaksiKeuangan::create([
                        'profile_masjid_id' => $masjid->id,
                        'type' => 'expense',
                        'kategori' => $kategoriExpense[array_rand($kategoriExpense)],
                        'jumlah' => rand(200000, 2000000), // 200rb - 2jt
                        'tanggal' => $tanggal,
                        'keterangan' => 'Pengeluaran untuk ' . $kategoriExpense[array_rand($kategoriExpense)],
                        'bukti_transaksi' => null,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }
            }

            // Tambahkan beberapa transaksi besar (tahunan)
            $yearlyTransactions = [
                [
                    'type' => 'income',
                    'kategori' => 'Donasi Renovasi',
                    'jumlah' => rand(10000000, 50000000), // 10jt - 50jt
                    'keterangan' => 'Donasi besar untuk renovasi masjid',
                ],
                [
                    'type' => 'expense',
                    'kategori' => 'Renovasi',
                    'jumlah' => rand(15000000, 40000000), // 15jt - 40jt
                    'keterangan' => 'Biaya renovasi atap dan sound system',
                ],
            ];

            foreach ($yearlyTransactions as $transaction) {
                TransaksiKeuangan::create([
                    'profile_masjid_id' => $masjid->id,
                    'type' => $transaction['type'],
                    'kategori' => $transaction['kategori'],
                    'jumlah' => $transaction['jumlah'],
                    'tanggal' => Carbon::now()->subMonths(rand(1, 6)),
                    'keterangan' => $transaction['keterangan'],
                    'bukti_transaksi' => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
