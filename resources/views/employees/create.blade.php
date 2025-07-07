@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Karyawan</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Telepon</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Alamat</label>
            <textarea class="form-control" id="address" name="address">{{ old('address') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="position" class="form-label">Jabatan</label>
            <input type="text" class="form-control" id="position" name="position" value="{{ old('position') }}">
        </div>
        <button type="submit" class="btn btn-primary">Tambah Karyawan</button>
    </form>
</div>
@endsection 