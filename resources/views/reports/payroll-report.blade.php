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
    @php
        $karyawanShare = $totalRevenueSetelahInfak * 0.35;
        $pemilikShare = $totalRevenueSetelahInfak * 0.65;
        $totalKehadiran = array_sum(array_column($gajiKaryawan, 'jumlah_kehadiran'));
        $totalGajiKotor = array_sum(array_column($gajiKaryawan, 'gaji'));
        $gajiPerKehadiran = $totalKehadiran > 0 ? $totalGajiKotor / $totalKehadiran : 0;
    @endphp
    <div class="mb-3">
        <strong>Periode:</strong> {{ $periodeLabel }}<br>
        <strong>Total Pemasukan:</strong> Rp {{ number_format($totalRevenue, 0, ',', '.') }}<br>
        <strong>Bagian Karyawan (35%):</strong> Rp {{ number_format($karyawanShare, 0, ',', '.') }}<br>
        <strong>Bagian Pemilik (65%):</strong> Rp {{ number_format($pemilikShare, 0, ',', '.') }}<br>
        <strong>Jumlah Karyawan yang Berhak Gaji:</strong> {{ $employees->count() }}<br>
        <strong>Total Karyawan Masuk:</strong> {{ $employees->count() }}<br>
        <strong>Gaji per Kehadiran (hari aktif):</strong> Rp {{ number_format($gajiPerKehadiran, 0, ',', '.') }}<br>
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
                    <th>Jumlah Kehadiran</th>
                    <th>Gaji per Kehadiran</th>
                    <th>Gaji Kotor</th>
                    <th>Pinjaman</th>
                    <th>Gaji Bersih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gajiKaryawan as $row)
                <tr>
                    <td>
                        <a href="#" class="show-gaji-detail" data-employee-id="{{ $row['employee']->id }}">{{ $row['employee']->name }}</a>
                    </td>
                    <td>{{ $row['jumlah_kehadiran'] }}</td>
                    <td>Rp {{ number_format($gajiPerKehadiran, 0, ',', '.') }}</td>
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

{{-- Modal untuk rincian gaji per hari --}}
<div class="modal fade" id="gajiDetailModal" tabindex="-1" aria-labelledby="gajiDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="gajiDetailModalLabel">Rincian Gaji Per Hari</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="gaji-detail-content"></div>
      </div>
    </div>
  </div>
</div>

<script>
    const employees = @json($employees);
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.show-gaji-detail').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const empId = this.getAttribute('data-employee-id');
                const emp = employees.find(e => e.id == empId);
                // Ambil periode dari input form
                const dateStart = document.getElementById('date_start') ? document.getElementById('date_start').value : '';
                const dateEnd = document.getElementById('date_end') ? document.getElementById('date_end').value : '';
                let url = '';
                if(dateStart && dateEnd) {
                    url = `/reports/payroll/detail/${empId}/${dateStart}/${dateEnd}`;
                } else {
                    // fallback ke bulan aktif
                    const month = document.getElementById('month') ? document.getElementById('month').value : '';
                    const start = month ? month+'-01' : '';
                    const end = month ? month+'-31' : '';
                    url = `/reports/payroll/detail/${empId}/${start}/${end}`;
                }
                fetch(url)
                    .then(res => res.json())
                    .then(detail => {
                        let html = `<strong>Nama:</strong> ${emp ? emp.name : '-'}<br><br>`;
                        if (detail && detail.length > 0) {
                            html += `<table class='table table-bordered'><thead><tr><th>Tanggal</th><th>Gaji Hari Itu</th><th>Galon Kirim</th><th>Total Transaksi</th><th>Pembayaran Hutang</th><th>Infak</th><th>Operasional</th><th>Pendapatan</th><th>Bersih</th></tr></thead><tbody>`;
                            detail.forEach(function(d) {
                                html += `<tr>
                                    <td>${d.tanggal}</td>
                                    <td>Rp ${parseInt(d.gaji).toLocaleString('id-ID')}</td>
                                    <td>${d.galon_in}</td>
                                    <td>Rp ${parseInt(d.total_transaksi).toLocaleString('id-ID')}</td>
                                    <td>Rp ${parseInt(d.total_pembayaran_hutang).toLocaleString('id-ID')}</td>
                                    <td>Rp ${parseInt(d.total_infak).toLocaleString('id-ID')}</td>
                                    <td>Rp ${parseInt(d.total_operasional).toLocaleString('id-ID')}</td>
                                    <td>Rp ${parseInt(d.total_pendapatan).toLocaleString('id-ID')}</td>
                                    <td>Rp ${parseInt(d.total_bersih).toLocaleString('id-ID')}</td>
                                </tr>`;
                            });
                            html += '</tbody></table>';
                        } else {
                            html += '<em>Tidak ada data kehadiran pada hari aktif.</em>';
                        }
                        document.getElementById('gaji-detail-content').innerHTML = html;
                        var modal = new bootstrap.Modal(document.getElementById('gajiDetailModal'));
                        modal.show();
                    });
            });
        });
    });
</script>
@endsection 