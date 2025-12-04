<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit User Account
        </h2>
    </x-slot>

    <style>
        .section-card {
            background: #f5f5f5;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
        }
        .label {
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
        }
        .field {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            background: #fff;
        }
    </style>

    <div class="py-6 max-w-screen-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold text-gray-800">User Accounts</div>
                <a href="{{ route('users.index') }}" class="text-sm text-emerald-600 hover:underline">Back to list</a>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="section-card">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="label">First Name *</label>
                                <input type="text" name="first_name" class="field" required value="{{ old('first_name', $user->first_name) }}">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="label">Middle Name</label>
                                    <input type="text" name="middle_name" class="field" value="{{ old('middle_name', $user->middle_name) }}">
                                </div>
                                <div>
                                    <label class="label">Middle Initial</label>
                                    <input type="text" name="middle_initial" class="field" value="{{ old('middle_initial', $user->middle_initial) }}">
                                </div>
                            </div>
                            <div>
                                <label class="label">Last Name *</label>
                                <input type="text" name="last_name" class="field" required value="{{ old('last_name', $user->last_name) }}">
                            </div>
                            <div>
                                <label class="label">Position</label>
                                <input type="text" name="position" class="field" value="{{ old('position', $user->position) }}">
                            </div>
                            <div>
                                <label class="label">Employee ID</label>
                                <input type="text" name="employee_id" class="field" value="{{ old('employee_id', $user->employee_id) }}">
                            </div>
                        </div>

                        <div class="flex justify-center items-center">
                            <div class="w-48 h-48 bg-gray-200 rounded-md flex items-center justify-center border border-gray-300">
                                <span class="text-gray-500">Photo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="label">Gender</label>
                            <select name="gender" class="field">
                                <option value="">Select...</option>
                                <option value="Male" {{ old('gender', $user->gender)=='Male'?'selected':'' }}>Male</option>
                                <option value="Female" {{ old('gender', $user->gender)=='Female'?'selected':'' }}>Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Birth Date</label>
                            <input type="date" name="birth_date" class="field" value="{{ old('birth_date', $user->birth_date) }}">
                        </div>
                        <div>
                            <label class="label">Marital Status</label>
                            <input type="text" name="marital_status" class="field" value="{{ old('marital_status', $user->marital_status) }}">
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="label">Home Address</label>
                            <input type="text" name="home_address" class="field" value="{{ old('home_address', $user->home_address) }}">
                        </div>
                        <div>
                            <label class="label">Mobile</label>
                            <input type="text" name="mobile" class="field" value="{{ old('mobile', $user->mobile) }}">
                        </div>
                        <div>
                            <label class="label">Email *</label>
                            <input type="email" name="email" class="field" required value="{{ old('email', $user->email) }}">
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Roles</label>
                            <div class="grid grid-cols-2 md:grid-cols-2 gap-2 mt-1">
                                @foreach($roleOptions as $role)
                                    @php
                                        $selectedRoles = collect(explode(',', old('roles', $user->roles ?? '')))->map(fn($r) => trim($r))->filter();
                                    @endphp
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="roles[]" value="{{ $role }}" {{ $selectedRoles->contains($role) ? 'checked' : '' }}>
                                        <span>{{ $role }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="label">Groups</label>
                            <input type="text" name="groups" class="field" value="{{ old('groups', $user->groups) }}">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="label">Service Types</label>
                        <input type="text" name="service_types" class="field" value="{{ old('service_types', $user->service_types) }}">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full md:w-2/3">
                        <div>
                            <label class="label">New Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="field" autocomplete="new-password">
                        </div>
                        <div>
                            <label class="label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="field" autocomplete="new-password">
                        </div>
                    </div>
                    <label class="flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <span>Active</span>
                    </label>
                    <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
