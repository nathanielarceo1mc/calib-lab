@extends('layouts.app')

@section('content')
    <h1 class="h4 mb-3">Receiving – New Work Order</h1>

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

    <form action="{{ route('receiving.store') }}" method="POST">
        @csrf

        {{-- ORDER INFO CARD --}}
        <div class="card mb-4">
            <div class="card-header fw-bold">Order Information</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Work Order No. *</label>
                        <input type="text" name="order_number" class="form-control"
                               value="{{ old('order_number', $orderNumber) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date *</label>
                        <input type="date" name="order_date" class="form-control"
                               value="{{ old('order_date', $today) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Customer *</label>
                        <input type="text" name="customer_name" class="form-control"
                               value="{{ old('customer_name') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control"
                           value="{{ old('address') }}">
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Telephone</label>
                        <input type="text" name="telephone" class="form-control"
                               value="{{ old('telephone') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control"
                               value="{{ old('contact_person') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control"
                               value="{{ old('designation') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">PO No.</label>
                        <input type="text" name="po_number" class="form-control"
                               value="{{ old('po_number') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Discount %</label>
                        <input type="number" name="discount_percent" class="form-control"
                               value="{{ old('discount_percent', 0) }}" step="0.01" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quotation No.</label>
                        <input type="text" name="quotation_number" class="form-control"
                               value="{{ old('quotation_number') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End User</label>
                        <input type="text" name="end_user" class="form-control"
                               value="{{ old('end_user') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- UNIT UNDER TEST CARD --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Unit Under Test</span>
                <button type="button" class="btn btn-success" id="add-row-btn">+ Add</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="uut-table">
                        <thead class="table-light">
                            <tr>
                                <th>Accreditation</th>
                                <th>Service Type</th>
                                <th>Capability</th>
                                <th>Description / Specification / Calibration</th>
                                <th>Type</th>
                                <th>Receiving Method</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Lead Time (days)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- rows inserted by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Work Order</button>
    </form>
@endsection

@section('scripts')
<script>
    function newRow() {
        return `
        <tr>
            <td>
                <select name="accreditation[]" class="form-select">
                    <option value="">-- Select --</option>
                    <option value="PAB ISO/IEC 17025:2017">PAB ISO/IEC 17025:2017</option>
                    <option value="PAB ISO/IEC 17020:2012">PAB ISO/IEC 17020:2012</option>
                    <option value="PAB ISO/IEC 17020:2012 and NSF Certifier">PAB ISO/IEC 17020:2012 and NSF Certifier</option>
                    <option value="Traceable">Traceable</option>
                    <option value="NSF Certifier">NSF Certifier</option>
                    <option value="Sub-Contract">Sub-Contract</option>
                    <option value="Add-on">Add-on</option>
                    <option value="Out of Scope">Out of Scope</option>
                    <option value="For Further Evaluation">For Further Evaluation</option>
                </select>
            </td>

            <td>
                <select name="service_type[]" class="form-select">
                    <option value="">-- Select --</option>
                    <option value="Calibration">Calibration</option>
                    <option value="Inspection">Inspection</option>
                </select>
            </td>

            <td>
                <input type="text" name="capability[]" class="form-control">
            </td>

            <td>
                <textarea name="uut_description[]" class="form-control" rows="1"></textarea>
            </td>

            {{-- TYPE --}}
            <td>
                <select name="uut_type[]" class="form-select type-select">
                    <option value="">-- Select --</option>
                    <option value="On-site">On-site</option>
                    <option value="In-house">In-house</option>
                    <option value="Pull-out">Pull-out</option>
                </select>
            </td>

            {{-- RECEIVING METHOD (only for In-house) --}}
            <td>
                <select name="receiving_method[]" class="form-select receiving-select" disabled>
                    <option value="">-- Select --</option>
                    <option value="Walk-in">Walk-in</option>
                    <option value="Pick-up">Pick-up</option>
                    <option value="Courier">Courier</option>
                </select>
            </td>

            <td>
                <input type="number" name="qty[]" class="form-control" value="1" min="1">
            </td>

            <td>
                <input type="text" name="unit[]" class="form-control">
            </td>

            {{-- LEAD TIME --}}
            <td>
                <select name="lead_time[]" class="form-select">
                    <option value="">-- Select --</option>
                    <option value="3-5 working days">3 to 5 working days</option>
                    <option value="10-15 working days">10 to 15 working days</option>
                </select>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-row-btn">&times;</button>
            </td>
        </tr>
        `;
    }

       document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.querySelector('#uut-table tbody');
        const addBtn = document.getElementById('add-row-btn');

        // First row by default
        tbody.insertAdjacentHTML('beforeend', newRow());

        addBtn.addEventListener('click', function () {
            tbody.insertAdjacentHTML('beforeend', newRow());
        });

        // Remove row
        tbody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row-btn')) {
                e.target.closest('tr').remove();
            }
        });

        // Enable Receiving Method only when Type = "In-house"
        tbody.addEventListener('change', function (e) {
            if (e.target.classList.contains('type-select')) {
                const row = e.target.closest('tr');
                const recvSelect = row.querySelector('.receiving-select');

                if (e.target.value === 'In-house') {
                    recvSelect.disabled = false;
                } else {
                    recvSelect.value = '';
                    recvSelect.disabled = true;
                }
            }
        });
    });
</script>
@endsection

