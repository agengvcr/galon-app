@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Galon Transaction Chart -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Galon Transactions</h5>
                </div>
                <div class="card-body">
                    <canvas id="transactionChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Monthly Revenue (Last 3 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="priceChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary Table -->
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Monthly Transaction Summary (Last 3 Months)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th class="text-end">Total Transactions</th>
                                    <th class="text-end">Galon In</th>
                                    <th class="text-end">Galon Out</th>
                                    <th class="text-end">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="monthlyTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light" id="monthlyTableFoot">
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let transactionChart;
    let priceChart;

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(value);
    }

    function loadTransactionChart() {
        fetch('/reports/chart-data')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('transactionChart').getContext('2d');
                
                if (transactionChart) {
                    transactionChart.destroy();
                }

                transactionChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Galon In',
                                data: data.galon_in,
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.1
                            },
                            {
                                label: 'Galon Out',
                                data: data.galon_out,
                                borderColor: 'rgb(255, 99, 132)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                tension: 0.1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Daily Galon Transactions'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Quantity'
                                }
                            }
                        }
                    }
                });
            });
    }

    function loadPriceChart() {
        fetch('/reports/price-chart-data')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('priceChart').getContext('2d');
                
                if (priceChart) {
                    priceChart.destroy();
                }

                priceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Total Revenue',
                                data: data.total_price,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgb(75, 192, 192)',
                                borderWidth: 1,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Transaction Count',
                                data: data.transaction_count,
                                type: 'line',
                                borderColor: 'rgb(255, 99, 132)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.label === 'Total Revenue') {
                                            return context.dataset.label + ': ' + formatCurrency(context.raw);
                                        }
                                        return context.dataset.label + ': ' + context.raw;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Revenue (IDR)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return formatCurrency(value);
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Transaction Count'
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        }
                    }
                });
            });
    }

    function loadMonthlyTable() {
        fetch('/reports/monthly-table-data')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('monthlyTableBody');
                const tfoot = document.getElementById('monthlyTableFoot');
                
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
                            <td class="text-end">${new Intl.NumberFormat().format(item.transaction_count)}</td>
                            <td class="text-end">${new Intl.NumberFormat().format(item.total_galon_in)}</td>
                            <td class="text-end">${new Intl.NumberFormat().format(item.total_galon_out)}</td>
                            <td class="text-end">${formatCurrency(item.total_price)}</td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
                tfoot.innerHTML = `
                    <tr>
                        <th>Total</th>
                        <th class="text-end">${new Intl.NumberFormat().format(totalTransactions)}</th>
                        <th class="text-end">${new Intl.NumberFormat().format(totalGalonIn)}</th>
                        <th class="text-end">${new Intl.NumberFormat().format(totalGalonOut)}</th>
                        <th class="text-end">${formatCurrency(totalRevenue)}</th>
                    </tr>
                `;
            });
    }

    // Initial load
    loadTransactionChart();
    loadPriceChart();
    loadMonthlyTable();

    // Refresh every 5 minutes
    setInterval(() => {
        loadTransactionChart();
        loadPriceChart();
        loadMonthlyTable();
    }, 300000);
});
</script>
@endsection 