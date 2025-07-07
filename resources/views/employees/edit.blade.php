@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Karyawan</h1>
    <form id="editEmployeeForm" action="{{ route('employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $employee->name }}" required>
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Telepon</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $employee->phone_number }}">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Alamat</label>
            <textarea class="form-control" id="address" name="address">{{ $employee->address }}</textarea>
        </div>
        <div class="mb-3">
            <label for="position" class="form-label">Jabatan</label>
            <input type="text" class="form-control" id="position" name="position" value="{{ $employee->position }}">
        </div>
        <button type="submit" class="btn btn-primary">Update Karyawan</button>
    </form>
</div>
@endsection 