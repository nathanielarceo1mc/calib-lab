<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the equipment.
     */
    public function index()
    {
        $equipment = Equipment::orderBy('id', 'desc')->get();

        return view('equipment.index', compact('equipment'));
    }

    /**
     * Show the form for creating new equipment.
     */
    public function create()
    {
        return view('equipment.create');
    }

    /**
     * Store newly created equipment in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'description'    => 'required|string|max:255',
            'manufacturer'   => 'nullable|string|max:255',
            'model'          => 'nullable|string|max:255',
            'serial_number'  => 'nullable|string|max:255',
            'owner'          => 'nullable|string|max:255',
            'range'          => 'nullable|string|max:255',
            'last_cal_date'  => 'nullable|date',
            'next_cal_date'  => 'nullable|date',
            'status'         => 'required|string|max:50',
        ]);

        Equipment::create($data);

        return redirect()->route('equipment.index')
            ->with('success', 'Equipment added successfully.');
    }

    /**
     * Show the form for editing existing equipment.
     */
    public function edit(Equipment $equipment)
    {
        return view('equipment.edit', compact('equipment'));
    }

    /**
     * Update the specified equipment.
     */
    public function update(Request $request, Equipment $equipment)
    {
        $data = $request->validate([
            'description'    => 'required|string|max:255',
            'manufacturer'   => 'nullable|string|max:255',
            'model'          => 'nullable|string|max:255',
            'serial_number'  => 'nullable|string|max:255',
            'owner'          => 'nullable|string|max:255',
            'range'          => 'nullable|string|max:255',
            'last_cal_date'  => 'nullable|date',
            'next_cal_date'  => 'nullable|date',
            'status'         => 'required|string|max:50',
        ]);

        $equipment->update($data);

        return redirect()->route('equipment.index')
            ->with('success', 'Equipment updated successfully.');
    }

    /**
     * Remove the specified equipment.
     */
    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return redirect()->route('equipment.index')
            ->with('success', 'Equipment deleted successfully.');
    }
}
