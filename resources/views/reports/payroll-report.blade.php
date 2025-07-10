@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Laporan Penggajian</h1>
    <form method="GET" class="mb-3 d-flex align-items-end gap-2">
        <div>
            <label for="month">Bulan:</label>
            <input type="month" name="month" id="month" value="{{ $month }}">
        </div>
        <div>
            <label for="date_start">Dari Tanggal:</label>
            <input type="date" name="date_start" id="date_start" value="{{ $dateStart ?? '' }}">
        </div>
        <div>
            <label for="date_end">Sampai Tanggal:</label>
            <input type="date" name="date_end" id="date_end" value="{{ $dateEnd ?? '' }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
    </form>
    <div class="mb-3">
        <strong>Periode:</strong> {{ $periodeLabel }}<br>
        <strong>Total Pemasukan:</strong> Rp {{ number_format($totalRevenue, 0, ',', '.') }}<br>
        <strong>Bagian Karyawan (35%):</strong> Rp {{ number_format($karyawanShare, 0, ',', '.') }}<br>
        <strong>Bagian Pemilik (65%):</strong> Rp {{ number_format($pemilikShare, 0, ',', '.') }}<br>
        <strong>Jumlah Karyawan yang Berhak Gaji:</strong> {{ $employees->count() }}<br>
        <strong>Total Karyawan Masuk:</strong> {{ $employees->count() }}<br>
        <strong>Gaji per Karyawan:</strong> Rp {{ number_format($gajiPerKaryawan, 0, ',', '.') }}<br>
        <strong>Total Galon Kirim:</strong> {{ $totalGalonIn ?? 0 }}<br>
        <strong>Biaya Service (Rp 1.000/galon kirim):</strong> Rp {{ number_format($totalInfak ?? 0, 0, ',', '.') }}<br>
        <strong>Total Pemasukan Setelah Biaya Service:</strong> Rp {{ number_format($totalRevenueSetelahInfak ?? 0, 0, ',', '.') }}<br>
        <strong>Total Biaya Operasional:</strong> Rp {{ number_format($totalOperational ?? 0, 0, ',', '.') }}<br>
        <strong>Total Pembayaran Hutang:</strong> Rp {{ number_format($totalDebtPayment ?? 0, 0, ',', '.') }}<br>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Gaji Kotor</th>
                    <th>Pinjaman</th>
                    <th>Gaji Bersih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gajiKaryawan as $row)
                <tr>
                    <td>{{ $row['employee']->name }}</td>
                    <td>Rp {{ number_format($row['gaji'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['pinjaman'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['gaji_bersih'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada karyawan yang absen pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-responsive mt-4">
        <h5>Rincian Pinjaman Karyawan</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                <tr>
                    <td>{{ $employees->where('id', $loan->employee_id)->first()->name ?? '-' }}</td>
                    <td>{{ $loan->date }}</td>
                    <td>Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                    <td>{{ $loan->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada pinjaman pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-responsive mt-4">
        <h5>Rincian Biaya Operasional</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operationalExpenses as $expense)
                <tr>
                    <td>{{ $expense->date }}</td>
                    <td>{{ $expense->description }}</td>
                    <td>Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada biaya operasional pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-responsive mt-4">
        <h5>Rincian Pembayaran Hutang</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Pelanggan</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($debtPayments as $pay)
                <tr>
                    <td>{{ $pay->customer_name }}</td>
                    <td>{{ $pay->payment_date }}</td>
                    <td>Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                    <td>{{ $pay->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada pembayaran hutang pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection 