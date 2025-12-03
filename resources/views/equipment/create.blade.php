@extends('layouts.app')

@section('content')
    <h1 class="h3 mb-3">Add Equipment</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('equipment.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Description *</label>
                <input type="text" name="description" class="form-control"
                       value="{{ old('description') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Manufacturer</label>
                <input type="text" name="manufacturer" class="form-control"
                       value="{{ old('manufacturer') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Model</label>
                <input type="text" name="model" class="form-control"
                       value="{{ old('model') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Serial Number</label>
                <input type="text" name="serial_number" class="form-control"
                       value="{{ old('serial_number') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Owner (Lab/Customer)</label>
                <input type="text" name="owner" class="form-control"
                       value="{{ old('owner') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Range</label>
                <input type="text" name="range" class="form-control"
                       value="{{ old('range') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="retired" {{ old('status') == 'retired' ? 'selected' : '' }}>Retired</option>
                    <option value="out_of_service" {{ old('status') == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Last Calibration Date</label>
                <input type="date" name="last_cal_date" class="form-control"
                       value="{{ old('last_cal_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Next Calibration Date</label>
                <input type="date" name="next_cal_date" class="form-control"
                       value="{{ old('next_cal_date') }}">
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
