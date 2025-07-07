@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Laporan Stok Galon per Customer</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Customer</th>
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <th>Stok Galon</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $row)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>{{ $row['phone_number'] }}</td>
                    <td>{{ $row['address'] }}</td>
                    <td>{{ $row['stok_galon'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 