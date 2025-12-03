@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Receiving – Edit Work Order</h1>
            <div class="text-muted small">
                Home / Receiving / Edit Order
            </div>
        </div>

        <div>
            @if($job->status === 'posted')
                <span class="badge text-bg-success">Posted</span>
            @else
                <span class="badge text-bg-secondary">Receiving</span>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success d-flex justify-content-between align-items-center">
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header card --}}
    <div class="card mb-4">
        <div class="card-header">
            Edit Order
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Work Order No.</label>
                    <input type="text" class="form-control" value="{{ $job->order_number }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" value="{{ $job->order_date }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <input type="text" class="form-control" value="{{ $job->customer_name }}" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" value="{{ $job->address }}" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Telephone</label>
                    <input type="text" class="form-control" value="{{ $job->telephone }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">E-mail</label>
                    <input type="text" class="form-control" value="{{ $job->email }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" class="form-control" value="{{ $job->contact_person }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Designation</label>
                    <input type="text" class="form-control" value="{{ $job->designation }}" readonly>
                </div>
            </div>
        </div>
    </div>

    {{-- Unit Under Test – editable table --}}
    <form method="POST" action="{{ route('receiving.update-units', $job) }}">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Unit Under Test</span>
                <button type="submit" class="btn btn-sm btn-primary">
                    Save Unit Details
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference No.</th>
                            <th>Description</th>
                            <th>Manufacturer</th>
                            <th>Model</th>
                            <th>Serial Number</th>
                            <th>Range</th>
                            <th>Assigned To</th>
                            <th>Remarks</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $row = 1; @endphp
                        @forelse($job->items as $item)
                            @foreach($item->units as $index => $unit)
                                <tr>
                                    <td>{{ $row++ }}</td>

                                    {{-- hidden ID array --}}
                                    <input type="hidden" name="unit_id[]" value="{{ $unit->id }}">

                                    <td>
                                        <input type="text"
                                               class="form-control form-control-sm"
                                               value="{{ $unit->reference_no }}"
                                               readonly>
                                    </td>

                                    <td style="min-width: 160px;">
                                        <input type="text"
                                               class="form-control form-control-sm"
                                               value="{{ $unit->name }}"
                                               readonly>
                                    </td>

                                    <td>
                                        <input type="text"
                                               name="manufacturer[]"
                                               class="form-control form-control-sm"
                                               value="{{ old('manufacturer.' . $loop->parent->index . $loop->index, $unit->manufacturer) }}">
                                    </td>

                                    <td>
                                        <input type="text"
                                               name="model[]"
                                               class="form-control form-control-sm"
                                               value="{{ old('model.' . $loop->parent->index . $loop->index, $unit->model) }}">
                                    </td>

                                    <td>
                                        <input type="text"
                                               name="serial_number[]"
                                               class="form-control form-control-sm"
                                               value="{{ old('serial_number.' . $loop->parent->index . $loop->index, $unit->serial_number) }}">
                                    </td>

                                    <td>
                                        <input type="text"
                                               name="range[]"
                                               class="form-control form-control-sm"
                                               value="{{ old('range.' . $loop->parent->index . $loop->index, $unit->range) }}">
                                    </td>

                                    <td>
                                        <input type="text"
                                               name="assigned_to[]"
                                               class="form-control form-control-sm"
                                               placeholder="Approver / Technician"
                                               value="{{ old('assigned_to.' . $loop->parent->index . $loop->index, $unit->assigned_to) }}">
                                    </td>

                                    <td style="min-width: 160px;">
                                        <input type="text"
                                               name="remarks[]"
                                               class="form-control form-control-sm"
                                               value="{{ old('remarks.' . $loop->parent->index . $loop->index, $unit->remarks) }}">
                                    </td>

                                    <td>
                                        @if($unit->status === 'posted')
                                            <span class="badge text-bg-success">Posted</span>
                                        @else
                                            <span class="badge text-bg-secondary">{{ $unit->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    No unit under test records found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

    {{-- Footer buttons --}}
    <div class="d-flex justify-content-between">
        <a href="{{ route('receiving.index') }}" class="btn btn-outline-secondary">
            ← Back to list
        </a>

        @if($job->status !== 'posted')
            <form method="POST"
                  action="{{ route('receiving.post', $job) }}"
                  onsubmit="return confirm('Post Order?\n\nAre you sure you want to post this record?');">
                @csrf
                <button type="submit" class="btn btn-success">
                    Post Order
                </button>
            </form>
        @endif
    </div>
@endsection
