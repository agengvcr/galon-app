@extends('layouts.app')

@section('content')
<div class="container">
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
                            <tbody>
                                @foreach($monthlyData as $data)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $data->month)->format('F Y') }}</td>
                                    <td class="text-end">{{ number_format($data->transaction_count) }}</td>
                                    <td class="text-end">{{ number_format($data->total_galon_in) }}</td>
                                    <td class="text-end">{{ number_format($data->total_galon_out) }}</td>
                                    <td class="text-end">Rp {{ number_format($data->total_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end">{{ number_format(collect($monthlyData)->sum('transaction_count')) }}</th>
                                    <th class="text-end">{{ number_format(collect($monthlyData)->sum('total_galon_in')) }}</th>
                                    <th class="text-end">{{ number_format(collect($monthlyData)->sum('total_galon_out')) }}</th>
                                    <th class="text-end">Rp {{ number_format(collect($monthlyData)->sum('total_price'), 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 