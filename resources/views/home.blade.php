@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Summary Pendapatan Total dan Perbulan -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Summary Pendapatan Total dan Perbulan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-end">Total Transaksi</th>
                                    <th class="text-end">Galon Kirim</th>
                                    <th class="text-end">Galon Tarik</th>
                                    <th class="text-end">Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody id="incomeSummaryTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light" id="incomeSummaryTableFoot">
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pelanggan 10 Hari Tidak Dikirim Galon -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pelanggan 10 Hari Tidak Dikirim Galon</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Pelanggan</th>
                                    <th>Telepon</th>
                                    <th>Alamat</th>
                                    <th>Transaksi Terakhir</th>
                                    <th>Hari Tidak Dikirim</th>
                                    <th>Stok Galon</th>
                                </tr>
                            </thead>
                            <tbody id="inactiveCustomersTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(value);
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    }

    // Load Summary Pendapatan
    function loadIncomeSummary() {
        fetch('/reports/monthly-table-data')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('incomeSummaryTableBody');
                const tfoot = document.getElementById('incomeSummaryTableFoot');
                
                let totalTransactions = 0;
                let totalGalonIn = 0;
                let totalGalonOut = 0;
                let totalRevenue = 0;
                
                let html = '';
                data.forEach(item => {
                    totalTransactions += parseInt(item.transaction_count);
                    totalGalonIn += parseInt(item.total_galon_in);
                    totalGalonOut += parseInt(item.total_galon_out);
                    totalRevenue += parseInt(item.total_price);
                    
                    html += `
                        <tr>
                            <td>${item.month}</td>
                            <td class="text-end">${formatNumber(item.transaction_count)}</td>
                            <td class="text-end">${formatNumber(item.total_galon_in)}</td>
                            <td class="text-end">${formatNumber(item.total_galon_out)}</td>
                            <td class="text-end">${formatCurrency(item.total_price)}</td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
                tfoot.innerHTML = `
                    <tr>
                        <th>Total</th>
                        <th class="text-end">${formatNumber(totalTransactions)}</th>
                        <th class="text-end">${formatNumber(totalGalonIn)}</th>
                        <th class="text-end">${formatNumber(totalGalonOut)}</th>
                        <th class="text-end">${formatCurrency(totalRevenue)}</th>
                    </tr>
                `;
            })
            .catch(error => {
                console.error('Error loading income summary:', error);
                document.getElementById('incomeSummaryTableBody').innerHTML = 
                    '<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>';
            });
    }

    // Load Pelanggan 10 Hari Tidak Dikirim
    function loadInactiveCustomers() {
        fetch('/reports/inactive-customers')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('inactiveCustomersTableBody');
                
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-success">Tidak ada pelanggan yang tidak aktif</td></tr>';
                    return;
                }
                
                let html = '';
                data.forEach(customer => {
                    const daysInactive = customer.days_inactive || 0;
                    const rowClass = daysInactive > 15 ? 'table-danger' : daysInactive > 10 ? 'table-warning' : '';
                    
                    html += `
                        <tr class="${rowClass}">
                            <td>${customer.name}</td>
                            <td>${customer.phone_number || '-'}</td>
                            <td>${customer.address || '-'}</td>
                            <td>${customer.last_transaction || '-'}</td>
                            <td>${daysInactive} hari</td>
                            <td>${customer.stok_galon || 0}</td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading inactive customers:', error);
                document.getElementById('inactiveCustomersTableBody').innerHTML = 
                    '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>';
            });
    }



    // Initial load
    loadIncomeSummary();
    loadInactiveCustomers();

    // Refresh every 5 minutes
    setInterval(() => {
        loadIncomeSummary();
        loadInactiveCustomers();
    }, 300000);
});
</script>
@endsection 