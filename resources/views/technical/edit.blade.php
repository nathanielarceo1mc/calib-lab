<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Unit Under Test
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                @if(session('success'))
                    <div class="rounded-md bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="text-lg font-semibold text-gray-800">Edit Unit Under Test</div>

                <div class="border border-slate-200 rounded-md p-4 bg-slate-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-slate-700">
                        <div>
                            <div class="font-semibold">Reference Number</div>
                            <div>{{ $unit->reference_no ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Date</div>
                            <div>{{ optional($workOrder->order_date)->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Customer</div>
                            <div>{{ $workOrder->customer_name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Address</div>
                            <div>{{ $workOrder->address ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Telephone No.</div>
                            <div>{{ $workOrder->telephone ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">E-mail Address</div>
                            <div>{{ $workOrder->email ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Contact</div>
                            <div>{{ $workOrder->contact_person ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Designation</div>
                            <div>{{ $workOrder->designation ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="font-semibold">Use Details From</div>
                            <div>{{ $workOrder->use_details_from ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('technical.update', $unit->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Name</label>
                            <textarea name="name" class="w-full border border-slate-300 rounded-md px-3 py-2" rows="2">{{ old('name', $unit->name ?? $unit->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Method / Procedure *</label>
                            <input type="text" name="method_procedure" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('method_procedure', $unit->method_procedure) }}">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Manufacturer</label>
                                <input type="text" name="manufacturer" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('manufacturer', $unit->manufacturer) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Model</label>
                                <input type="text" name="model" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('model', $unit->model) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Serial Number</label>
                                <input type="text" name="serial_number" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('serial_number', $unit->serial_number) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Identification</label>
                                <input type="text" name="identification" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('identification', $unit->identification) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Service Interval</label>
                                <input type="number" name="service_interval" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('service_interval', $unit->service_interval) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Service Interval Type</label>
                                <div class="flex items-center gap-4 text-sm text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="service_interval_type" value="Month" {{ old('service_interval_type', $unit->service_interval_type)=='Month' ? 'checked' : '' }}> Month
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="service_interval_type" value="Year" {{ old('service_interval_type', $unit->service_interval_type)=='Year' ? 'checked' : '' }}> Year
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Sub-Contracted UUT</label>
                                <div class="flex items-center gap-4 text-sm text-slate-700">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="sub_contracted" value="1" {{ old('sub_contracted', $unit->sub_contracted) ? 'checked' : '' }}> Yes
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="sub_contracted" value="0" {{ !old('sub_contracted', $unit->sub_contracted) ? 'checked' : '' }}> No
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Accessories</label>
                                <input type="text" name="accessories" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('accessories', $unit->accessories) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Type</label>
                                <select name="type" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="On-site" {{ old('type', $unit->type)=='On-site' ? 'selected' : '' }}>On-site</option>
                                    <option value="In-house" {{ old('type', $unit->type)=='In-house' ? 'selected' : '' }}>In-house</option>
                                    <option value="Pull-out" {{ old('type', $unit->type)=='Pull-out' ? 'selected' : '' }}>Pull-out</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Receiving Method</label>
                                <select name="receiving_method" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="Walk-in" {{ old('receiving_method', $unit->receiving_method)=='Walk-in' ? 'selected' : '' }}>Walk-in</option>
                                    <option value="Pick-up" {{ old('receiving_method', $unit->receiving_method)=='Pick-up' ? 'selected' : '' }}>Pick-up</option>
                                    <option value="Courier" {{ old('receiving_method', $unit->receiving_method)=='Courier' ? 'selected' : '' }}>Courier</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tracking No.</label>
                                <input type="text" name="tracking_no" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('tracking_no', $unit->tracking_no) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Received Date</label>
                                <input type="date" name="received_date" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('received_date', optional($unit->received_date)->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Estimated Completion Date</label>
                                <input type="date" name="estimated_completion_date" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('estimated_completion_date', optional($unit->estimated_completion_date)->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Onsite Date</label>
                                <input type="date" name="onsite_date" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('onsite_date', optional($unit->onsite_date)->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Receiving Conditions</label>
                            <input type="text" name="receiving_condition" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('receiving_condition', $unit->receiving_condition) }}">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Statements of conformance?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="require_conformance" value="1" {{ old('require_conformance', $unit->require_conformance) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="require_conformance" value="0" {{ !old('require_conformance', $unit->require_conformance) ? 'checked' : '' }}> No
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Opinions/interpretations based on results?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="require_opinion" value="1" {{ old('require_opinion', $unit->require_opinion) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="require_opinion" value="0" {{ !old('require_opinion', $unit->require_opinion) ? 'checked' : '' }}> No
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Deviate from methods selected?</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="deviate_methods" value="1" {{ old('deviate_methods', $unit->deviate_methods) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="deviate_methods" value="0" {{ !old('deviate_methods', $unit->deviate_methods) ? 'checked' : '' }}> No
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <div class="font-semibold w-full">Request New Method</div>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="request_new_method" value="1" {{ old('request_new_method', $unit->request_new_method) ? 'checked' : '' }}> Yes
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="request_new_method" value="0" {{ !old('request_new_method', $unit->request_new_method) ? 'checked' : '' }}> No
                                </label>
                            </div>
                        </div>
                        <div>
                            <textarea name="request_details" class="w-full border border-slate-300 rounded-md px-3 py-2" rows="2" placeholder="Insert details here..." {{ old('request_new_method', $unit->request_new_method) ? '' : 'disabled' }}>{{ old('request_details', $unit->request_details) }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Delivery method of certificate/report</label>
                                <select name="delivery_method" class="w-full border border-slate-300 rounded-md px-3 py-2">
                                    <option value="">-</option>
                                    <option value="Hand-carry / Hard copy" {{ old('delivery_method', $unit->delivery_method)=='Hand-carry / Hard copy' ? 'selected' : '' }}>Hand-carry / Hard copy</option>
                                    <option value="Courier" {{ old('delivery_method', $unit->delivery_method)=='Courier' ? 'selected' : '' }}>Courier</option>
                                    <option value="Customer Portal" {{ old('delivery_method', $unit->delivery_method)=='Customer Portal' ? 'selected' : '' }}>Customer Portal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Email, PDF</label>
                                <input type="text" name="delivery_contact" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('delivery_contact', $unit->delivery_contact) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Special Instructions</label>
                                <input type="text" name="special_instructions" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('special_instructions', $unit->special_instructions) }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Test Points / Nominal Values / Parameter</label>
                            <textarea name="test_points" class="w-full border border-slate-300 rounded-md px-3 py-2" rows="2">{{ old('test_points', $unit->test_points) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Approver's Remarks</label>
                            <input type="text" name="approver_remarks" class="w-full border border-slate-300 rounded-md px-3 py-2" value="{{ old('approver_remarks', $unit->approver_remarks) }}">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
