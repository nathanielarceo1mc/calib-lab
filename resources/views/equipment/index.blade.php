@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Equipment List</h1>
        <a href="{{ route('equipment.create') }}" class="btn btn-primary">
            + Add Equipment
        </a>
    </div>

    @if ($equipment->isEmpty())
        <div class="alert alert-info">
            No equipment found. Click "Add Equipment" to create your first record.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Manufacturer</th>
                        <th>Model</th>
                        <th>Serial No.</th>
                        <th>Owner</th>
                        <th>Range</th>
                        <th>Last Cal</th>
                        <th>Next Cal</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipment as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->manufacturer }}</td>
                            <td>{{ $item->model }}</td>
                            <td>{{ $item->serial_number }}</td>
                            <td>{{ $item->owner }}</td>
                            <td>{{ $item->range }}</td>
                            <td>{{ $item->last_cal_date }}</td>
                            <td>{{ $item->next_cal_date }}</td>
                            <td>{{ $item->status }}</td>
                            <td>
                                <a href="{{ route('equipment.edit', $item) }}" class="btn btn-sm btn-warning">
                                    Edit
                                </a>
                                <form action="{{ route('equipment.destroy', $item) }}"
                                      method="POST"
                                      style="display:inline-block;"
                                      onsubmit="return confirm('Delete this equipment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
