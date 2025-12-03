<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create User
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white shadow-sm sm:rounded-lg p-6">

            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                {{-- Names --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium">First Name *</label>
                        <input type="text" name="first_name" class="form-input w-full" required value="{{ old('first_name') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Middle Name</label>
                        <input type="text" name="middle_name" class="form-input w-full" value="{{ old('middle_name') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Middle Initial</label>
                        <input type="text" name="middle_initial" class="form-input w-full" value="{{ old('middle_initial') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Last Name *</label>
                        <input type="text" name="last_name" class="form-input w-full" required value="{{ old('last_name') }}">
                    </div>
                </div>

                {{-- Employment --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium">Employee ID</label>
                        <input type="text" name="employee_id" class="form-input w-full" value="{{ old('employee_id') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Position</label>
                        <input type="text" name="position" class="form-input w-full" value="{{ old('position') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Birth Date</label>
                        <input type="date" name="birth_date" class="form-input w-full" value="{{ old('birth_date') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Gender</label>
                        <select name="gender" class="form-input w-full">
                            <option value="">Select...</option>
                            <option value="Male" {{ old('gender')=='Male'?'selected':'' }}>Male</option>
                            <option value="Female" {{ old('gender')=='Female'?'selected':'' }}>Female</option>
                        </select>
                    </div>
                </div>

                {{-- Contact and IDs --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium">Home Address</label>
                        <input type="text" name="home_address" class="form-input w-full" value="{{ old('home_address') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Home Phone</label>
                        <input type="text" name="home_phone" class="form-input w-full" value="{{ old('home_phone') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Mobile</label>
                        <input type="text" name="mobile" class="form-input w-full" value="{{ old('mobile') }}">
                    </div>
                </div>

                {{-- Account Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium">Email *</label>
                        <input type="email" name="email" class="form-input w-full" required value="{{ old('email') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Roles</label>
                        <input type="text" name="roles" class="form-input w-full" value="{{ old('roles') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Groups</label>
                        <input type="text" name="groups" class="form-input w-full" value="{{ old('groups') }}">
                    </div>
                </div>

                {{-- Service types --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium">Service Types</label>
                    <input type="text" name="service_types" class="form-input w-full" value="{{ old('service_types') }}">
                </div>

                <div class="flex justify-end">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                        Create User
                    </button>
                </div>

            </form>

        </div>

    </div>

</x-app-layout>
