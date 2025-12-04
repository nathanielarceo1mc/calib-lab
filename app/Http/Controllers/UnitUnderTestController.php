<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnitUnderTest;
use App\Models\WorkOrder;

class UnitUnderTestController extends Controller
{
    public function index()
    {
        $units = UnitUnderTest::with([
            'workOrder',
            'approver',
            'personnel',
        ])
        ->orderByDesc('id')
        ->get();

        return view('technical.index', compact('units'));
    }

    public function edit(string $id)
    {
        $unit = UnitUnderTest::with(['workOrder', 'approver', 'personnel'])->findOrFail($id);
        return view('technical.edit', [
            'unit' => $unit,
            'workOrder' => $unit->workOrder,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $unit = UnitUnderTest::findOrFail($id);

        $data = $request->validate([
            'name'                          => ['nullable', 'string', 'max:255'],
            'method_procedure'              => ['nullable', 'string', 'max:255'],
            'manufacturer'                  => ['nullable', 'string', 'max:255'],
            'model'                         => ['nullable', 'string', 'max:255'],
            'serial_number'                 => ['nullable', 'string', 'max:255'],
            'identification'                => ['nullable', 'string', 'max:255'],
            'sub_contracted'                => ['nullable', 'boolean'],
            'service_interval'              => ['nullable', 'integer', 'min:0'],
            'service_interval_type'         => ['nullable', 'string', 'max:20'],
            'type'                          => ['nullable', 'string', 'max:50'],
            'receiving_method'              => ['nullable', 'string', 'max:50'],
            'tracking_no'                   => ['nullable', 'string', 'max:255'],
            'received_date'                 => ['nullable', 'date'],
            'estimated_completion_date'     => ['nullable', 'date'],
            'onsite_date'                   => ['nullable', 'date'],
            'accessories'                   => ['nullable', 'string', 'max:255'],
            'receiving_condition'           => ['nullable', 'string', 'max:500'],
            'require_conformance'           => ['nullable', 'boolean'],
            'require_opinion'               => ['nullable', 'boolean'],
            'deviate_methods'               => ['nullable', 'boolean'],
            'request_new_method'            => ['nullable', 'boolean'],
            'request_details'               => ['nullable', 'string'],
            'delivery_method'               => ['nullable', 'string', 'max:255'],
            'delivery_contact'              => ['nullable', 'string', 'max:255'],
            'special_instructions'          => ['nullable', 'string'],
            'test_points'                   => ['nullable', 'string'],
            'approver_remarks'              => ['nullable', 'string'],
        ]);

        $unit->update($data);

        return redirect()->route('technical.edit', $unit->id)
            ->with('success', 'Unit updated.');
    }
}
