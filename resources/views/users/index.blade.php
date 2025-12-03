<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Accounts
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-100 text-green-700 px-4 py-2">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex justify-end">
                <a href="{{ route('users.create') }}"
                   class="rounded-md bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold">
                    + New User
                </a>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">

                @if($users->isEmpty())
                    <p class="text-gray-500">
                        No users found. Use <strong>New User</strong> to create one.
                    </p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        @foreach($users as $user)
                            <div class="flex justify-between items-start p-4 border rounded-lg">

                                <div class="flex items-start gap-3">

                                    {{-- Avatar Initial --}}
                                    <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-lg font-bold text-gray-700">
                                        {{ strtoupper(substr($user->display_name ?? $user->name, 0, 1)) }}
                                    </div>

                                    <div>
                                        <div class="font-semibold text-gray-900">
                                            {{ $user->display_name ?? $user->name }}
                                        </div>
                                        <div class="text-gray-500 text-sm">{{ $user->email }}</div>

                                        @if($user->position)
                                            <div class="text-gray-500 text-sm">{{ $user->position }}</div>
                                        @endif

                                        @if($user->mobile)
                                            <div class="text-gray-400 text-xs">{{ $user->mobile }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col items-end">

                                    <span class="text-xs px-2 py-1 rounded-full
                                        {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>

                                    <div class="flex gap-3 mt-3">
                                        <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>

                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                              onsubmit="return confirm('Delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                Delete
                                            </button>
                                        </form>
                                    </div>

                                </div>

                            </div>
                        @endforeach

                    </div>
                @endif

            </div>

        </div>
    </div>

</x-app-layout>
