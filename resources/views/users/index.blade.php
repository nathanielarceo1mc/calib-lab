<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Accounts
        </h2>
    </x-slot>

    <style>
        .user-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            gap: 16px;
            padding: 16px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .user-card .avatar {
            width: 120px;
            height: 120px;
            border-radius: 999px;
            object-fit: cover;
            background: #e5e7eb;
        }
        .user-actions .btn-icon {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
        }
        .btn-edit { background: #1d9bf0; }
        .btn-delete { background: #ef4444; }
    </style>

    <div class="py-6">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-100 text-green-700 px-4 py-2">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-5 flex justify-end">
                <a href="{{ route('users.create') }}"
                   class="inline-flex items-center px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow">
                    + Create new user
                </a>
            </div>

            @if($users->isEmpty())
                <div class="bg-white rounded-md p-6 text-gray-500 shadow-sm">
                    No users found. Use <strong>Create new user</strong> to add one.
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($users as $user)
                        <div class="user-card">
                            <div class="flex-shrink-0">
                                <img src="{{ $user->profile_photo_url ?? '' }}"
                                     onerror="this.src='https://via.placeholder.com/120x120?text=+'"
                                     alt="Avatar"
                                     class="avatar border border-gray-200">
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-lg font-semibold text-gray-900">
                                            {{ $user->display_name ?? $user->name }}
                                        </div>
                                        <div class="text-gray-600 text-sm">
                                            {{ $user->email ?? 'n/a' }}
                                        </div>
                                        @if($user->mobile)
                                            <div class="text-gray-600 text-sm">{{ $user->mobile }}</div>
                                        @endif
                                        @if($user->position)
                                            <div class="text-gray-700 font-semibold mt-2">{{ $user->position }}</div>
                                        @endif
                                        @if($user->roles)
                                            <div class="mt-2 flex flex-wrap gap-1 text-xs">
                                                @foreach(explode(',', $user->roles) as $role)
                                                    <span class="px-2 py-1 bg-slate-200 text-slate-700 rounded-full">{{ trim($role) }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="user-actions space-x-2">
                                        <a href="{{ route('users.edit', $user) }}" class="btn-icon btn-edit" title="Edit">
                                            ✎
                                        </a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon btn-delete" title="Delete">🗑</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
