{{-- resources/views/customers/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Customers - Edit')

@section('header')
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Edit Customer</h2>
        <div class="text-xs text-slate-500 mt-0.5">
            Home / Operations / Customers / Edit
        </div>
    </div>
@endsection

@section('content')
    <div class="py-6">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
            <div x-data="customerEditForm()">

                <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- ================= TOP GENERAL INFO ================= --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm mb-5">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">

                            {{-- Breadcrumb + title inside the card --}}
                            <div>
                                <div class="text-[11px] text-gray-400">
                                    <a href="{{ route('dashboard') }}" class="hover:underline hover:text-emerald-600">
                                        Home
                                    </a>
                                    <span class="mx-1">/</span>
                                    <a href="{{ route('customers.index') }}" class="hover:underline hover:text-emerald-600">
                                        Customers
                                    </a>
                                    <span class="mx-1">/</span>
                                    <span class="font-semibold text-gray-600">
                                        Edit Customer
                                    </span>
                                </div>
                                <div class="mt-1 text-lg font-semibold text-gray-800">
                                    Edit: {{ $customer->business_name }}
                                </div>
                            </div>

                            <a href="{{ route('customers.index') }}"
                               class="inline-flex items-center px-4 py-1.5 rounded-md border border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50">
                                ← Back to list
                            </a>
                        </div>

                        <div class="px-4 py-5 space-y-4">
                            {{-- first row: Code / Business Name / Nature of Business --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Code --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        Code *
                                    </label>
                                    <input type="text"
                                           value="{{ $customer->code }}"
                                           readonly
                                           class="block w-full rounded-md border-gray-300 bg-gray-100 text-sm text-gray-700 shadow-sm">
                                    <input type="hidden" name="code" value="{{ $customer->code }}">
                                    <p class="mt-1 text-[11px] text-gray-400">
                                        Auto-generated on create; read-only.
                                    </p>
                                </div>

                                {{-- Business Name --}}
                                <div>
                                    <label for="business_name" class="block text-xs font-semibold text-gray-600 mb-1">
                                        Business Name *
                                    </label>
                                    <input id="business_name" name="business_name" type="text"
                                           value="{{ old('business_name', $customer->business_name) }}"
                                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('business_name')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Nature of Business --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        Nature of Business
                                    </label>
                                    <input type="text" name="nature_of_business"
                                           value="{{ old('nature_of_business', $customer->nature_of_business) }}"
                                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            {{-- Address --}}
                            <div>
                                <label for="address" class="block text-xs font-semibold text-gray-600 mb-1">
                                    Address *
                                </label>
                                <input id="address" name="address" type="text"
                                       value="{{ old('address', $customer->address) }}"
                                       class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('address') border-rose-400 @enderror">
                                @error('address')
                                <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Region / Province / Company Email / Company Telephone --}}
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                {{-- Region --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        Region
                                    </label>
                                  <select name="region" x-model="region"
        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    <option value="">Select region</option>
    <template x-for="(label, key) in regions" :key="key">
        <option :value="key"
                x-text="label"
                :selected="key === region"></option>
    </template>
</select>

                                </div>

                                {{-- Province --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        Province
                                    </label>
<select name="province" x-model="province"
        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    <option value="">Select province</option>
    <template x-for="prov in filteredProvinces" :key="prov">
        <option :value="prov"
                x-text="prov"
                :selected="prov === province"></option>
    </template>
</select>

                                </div>

                                {{-- Company Email --}}
                                <div>
                                    <label for="company_email" class="block text-xs font-semibold text-gray-600 mb-1">
                                        Company Email
                                    </label>
                                    <input id="company_email" name="company_email" type="email"
                                           value="{{ old('company_email', $customer->company_email) }}"
                                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('company_email')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Company Phone --}}
                                <div>
                                    <label for="company_phone" class="block text-xs font-semibold text-gray-600 mb-1">
                                        Company Telephone
                                    </label>
                                    <input id="company_phone" name="company_phone" type="text"
                                           value="{{ old('company_phone', $customer->company_phone) }}"
                                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('company_phone')
                                    <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- TIN / Payment terms / VAT type / Cert expiration --}}
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                {{-- TIN --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        TIN
                                    </label>
                                    <input type="text" name="tin"
                                           value="{{ old('tin', $customer->tin) }}"
                                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>

                                {{-- Payment terms --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        Payment Terms (Days)
                                    </label>
                                    <input type="number" name="payment_terms"
                                           value="{{ old('payment_terms', $customer->payment_terms ?? 30) }}"
                                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>

                                {{-- VAT type --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        VAT Type *
                                    </label>
                                    <select name="vat_type"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">Select VAT type</option>
                                        <option value="VATable" @selected(old('vat_type', $customer->vat_type) === 'VATable')>VATable</option>
                                        <option value="VAT Exempt" @selected(old('vat_type', $customer->vat_type) === 'VAT Exempt')>VAT Exempt</option>
                                        <option value="VAT Zero Rated" @selected(old('vat_type', $customer->vat_type) === 'VAT Zero Rated')>VAT Zero Rated</option>
                                    </select>
                                </div>

                                {{-- Certificate Expiration --}}
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        Certificate Expiration (Days) *
                                    </label>
                                    <input type="number" name="certificate_expiration_days"
                                           value="{{ old('certificate_expiration_days', $customer->certificate_expiration_days ?? 365) }}"
                                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            {{-- Company Type --}}
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                                        Company Type *
                                    </label>
                                    <select name="company_type"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">Select type</option>
                                        <option value="Private" @selected(old('company_type', $customer->company_type) === 'Private')>Private</option>
                                        <option value="Government" @selected(old('company_type', $customer->company_type) === 'Government')>Government</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= CONTACT PERSONS ================= --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm mb-5">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">Contact Persons</span>
                            <button type="button"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold"
                                    @click="addContact">
                                + Add
                            </button>
                        </div>

                        <div class="px-4 py-3 overflow-x-auto">
                            <table class="min-w-full text-xs border border-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Salutation</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">First Name</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Last Name</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Position</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Email</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Mobile</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Telephone</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-center">Accounting</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-center">Primary</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-center">Active</th>
                                    <th class="px-2 py-1 border-b border-gray-200"></th>
                                </tr>
                                </thead>

                                <tbody>
                                <template x-for="(contact, index) in contacts" :key="index">
                                    <tr class="odd:bg-white even:bg-gray-50">
                                        {{-- hidden id (if you decide to use it later for update) --}}
                                        <input type="hidden"
                                               :name="'contacts['+index+'][id]'"
                                               x-model="contact.id">

                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <select class="w-full rounded-md border-gray-300 text-xs"
                                                    :name="'contacts['+index+'][salutation]'"
                                                    x-model="contact.salutation">
                                                <option value="">-</option>
                                                <option>Mr.</option>
                                                <option>Ms.</option>
                                                <option>Mrs.</option>
                                            </select>
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'contacts['+index+'][first_name]'"
                                                   x-model="contact.first_name">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'contacts['+index+'][last_name]'"
                                                   x-model="contact.last_name">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'contacts['+index+'][position]'"
                                                   x-model="contact.position">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="email" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'contacts['+index+'][email]'"
                                                   x-model="contact.email">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'contacts['+index+'][mobile]'"
                                                   x-model="contact.mobile">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'contacts['+index+'][telephone]'"
                                                   x-model="contact.telephone">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200 text-center">
                                            <input type="checkbox"
                                                   :name="'contacts['+index+'][is_accounting]'"
                                                   value="1"
                                                   x-model="contact.is_accounting">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200 text-center">
                                            <input type="radio"
                                                   name="primary_contact_index"
                                                   :value="index"
                                                   x-model="primary_contact_index">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200 text-center">
                                            <input type="checkbox"
                                                   :name="'contacts['+index+'][is_active]'"
                                                   value="1"
                                                   x-model="contact.is_active">
                                        </td>

                                        <td class="px-2 py-1 border-b border-gray-200 text-right">
                                            <button type="button"
                                                    class="inline-flex items-center px-2 py-1 rounded bg-rose-500 hover:bg-rose-600 text-white text-[11px]"
                                                    @click="removeContact(index)">
                                                🗑
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="contacts.length === 0">
                                    <td colspan="11" class="px-3 py-4 text-center text-[11px] text-gray-500">
                                        No contact persons yet. Click <strong>+ Add</strong>.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ================= END USERS ================= --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm mb-5">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">End Users</span>
                            <button type="button"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold"
                                    @click="addEndUser">
                                + Add
                            </button>
                        </div>

                        <div class="px-4 py-3 overflow-x-auto">
                            <table class="min-w-full text-xs border border-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Name</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Address</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Contact Person</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Telephone</th>
                                    <th class="px-2 py-1 border-b border-gray-200"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="(user, index) in endUsers" :key="index">
                                    <tr class="odd:bg-white even:bg-gray-50">
                                        <input type="hidden"
                                               :name="'end_users['+index+'][id]'"
                                               x-model="user.id">

                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'end_users['+index+'][name]'"
                                                   x-model="user.name">
                                        </td>
                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'end_users['+index+'][address]'"
                                                   x-model="user.address">
                                        </td>
                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'end_users['+index+'][contact_person]'"
                                                   x-model="user.contact_person">
                                        </td>
                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'end_users['+index+'][telephone]'"
                                                   x-model="user.telephone">
                                        </td>
                                        <td class="px-2 py-1 border-b border-gray-200 text-right">
                                            <button type="button"
                                                    class="inline-flex items-center px-2 py-1 rounded bg-rose-500 hover:bg-rose-600 text-white text-[11px]"
                                                    @click="removeEndUser(index)">
                                                🗑
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="endUsers.length === 0">
                                    <td colspan="5" class="px-3 py-4 text-center text-[11px] text-gray-500">
                                        No end users yet. Click <strong>+ Add</strong>.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ================= PO FILES ================= --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm mb-5">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">PO</span>
                            <button type="button"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold"
                                    @click="addPO">
                                + Add
                            </button>
                        </div>

                        <div class="px-4 py-3 overflow-x-auto">
                            <table class="min-w-full text-xs border border-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Label</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">File</th>
                                    <th class="px-2 py-1 border-b border-gray-200"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="(po, index) in pos" :key="index">
                                    <tr class="odd:bg-white even:bg-gray-50">
                                        <input type="hidden"
                                               :name="'pos['+index+'][id]'"
                                               x-model="po.id">
                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'pos['+index+'][label]'"
                                                   x-model="po.label">
                                        </td>
                                       <td class="px-2 py-1 border-b border-gray-200">
    <input type="file"
           class="block w-full text-xs"
           :name="'pos['+index+'][file]'"
           accept=".pdf,.doc,.docx,.xls,.xlsx,.csv">

    <div x-show="po.file_path" class="mt-1 text-[10px] text-gray-500">
        Existing:
        <a :href="'/storage/' + po.file_path" target="_blank" class="underline">
            View file
        </a>
        <input type="hidden"
               :name="'pos['+index+'][file_path]'"
               x-model="po.file_path">
    </div>
</td>


                                        <td class="px-2 py-1 border-b border-gray-200 text-right">
                                            <button type="button"
                                                    class="inline-flex items-center px-2 py-1 rounded bg-rose-500 hover:bg-rose-600 text-white text-[11px]"
                                                    @click="removePO(index)">
                                                🗑
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="pos.length === 0">
                                    <td colspan="3" class="px-3 py-4 text-center text-[11px] text-gray-500">
                                        No PO files yet. Click <strong>+ Add</strong>.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ================= OTHER FILES ================= --}}
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm mb-5">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">Files</span>
                            <button type="button"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold"
                                    @click="addFile">
                                + Add
                            </button>
                        </div>

                        <div class="px-4 py-3 overflow-x-auto">
                            <table class="min-w-full text-xs border border-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">Label</th>
                                    <th class="px-2 py-1 border-b border-gray-200 text-left">File</th>
                                    <th class="px-2 py-1 border-b border-gray-200"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="(fileRow, index) in files" :key="index">
                                    <tr class="odd:bg-white even:bg-gray-50">
                                        <input type="hidden"
                                               :name="'files['+index+'][id]'"
                                               x-model="fileRow.id">
                                        <td class="px-2 py-1 border-b border-gray-200">
                                            <input type="text" class="w-full rounded-md border-gray-300 text-xs"
                                                   :name="'files['+index+'][label]'"
                                                   x-model="fileRow.label">
                                        </td>
                                      <td class="px-2 py-1 border-b border-gray-200">
    <input type="file"
           class="block w-full text-xs"
           :name="'files['+index+'][file]'"
           accept=".pdf,.doc,.docx,.xls,.xlsx,.csv">

    <div x-show="fileRow.file_path" class="mt-1 text-[10px] text-gray-500">
        Existing:
        <a :href="'/storage/' + fileRow.file_path" target="_blank" class="underline">
            View file
        </a>
        <input type="hidden"
               :name="'files['+index+'][file_path]'"
               x-model="fileRow.file_path">
    </div>
</td>


                                        <td class="px-2 py-1 border-b border-gray-200 text-right">
                                            <button type="button"
                                                    class="inline-flex items-center px-2 py-1 rounded bg-rose-500 hover:bg-rose-600 text-white text-[11px]"
                                                    @click="removeFile(index)">
                                                🗑
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="files.length === 0">
                                    <td colspan="3" class="px-3 py-4 text-center text-[11px] text-gray-500">
                                        No files yet. Click <strong>+ Add</strong>.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ================= FOOTER ================= --}}
                    <div class="flex items-center justify-between mb-6">
                        <div class="text-[11px] text-gray-500">
                            Created Date: {{ optional($customer->created_at)->format('d M Y H:i') }}<br>
                            Updated Date: {{ optional($customer->updated_at)->format('d M Y H:i') }}
                        </div>

                        <button type="submit"
                                class="inline-flex items-center px-5 py-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow">
                            💾 Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   @push('scripts')
<script>
    function customerEditForm() {

        // ---- get initial values from PHP (customer) ----
        const initialRegion   = @json(old('region', $customer->region));
        const initialProvince = @json(old('province', $customer->province));

        console.log('Initial region/province from server:', initialRegion, initialProvince);

        return {
            // ========== REGION / PROVINCE ==========
            region:   initialRegion  ?? '',
            province: initialProvince ?? '',

            regions: {
                'NCR': 'NCR - National Capital Region',
                'CAR': 'CAR - Cordillera Administrative Region',
                'Region I': 'Region I - Ilocos Region',
                'Region II': 'Region II - Cagayan Valley',
                'Region III': 'Region III - Central Luzon',
                'Region IV-A': 'Region IV-A - CALABARZON',
                'MIMAROPA': 'MIMAROPA Region',
                'Region V': 'Region V - Bicol Region',
                'Region VI': 'Region VI - Western Visayas',
                'Region VII': 'Region VII - Central Visayas',
                'Region VIII': 'Region VIII - Eastern Visayas',
                'Region IX': 'Region IX - Zamboanga Peninsula',
                'Region X': 'Region X - Northern Mindanao',
                'Region XI': 'Region XI - Davao Region',
                'Region XII': 'Region XII - SOCCSKSARGEN',
                'Region XIII': 'Region XIII - Caraga',
                'BARMM': 'Bangsamoro Autonomous Region in Muslim Mindanao',
            },

            provincesByRegion: {
                'NCR': ['Metro Manila'],
                'CAR': ['Abra','Apayao','Benguet','Ifugao','Kalinga','Mountain Province'],
                'Region I': ['Ilocos Norte','Ilocos Sur','La Union','Pangasinan'],
                'Region II': ['Batanes','Cagayan','Isabela','Nueva Vizcaya','Quirino'],
                'Region III': ['Aurora','Bataan','Bulacan','Nueva Ecija','Pampanga','Tarlac','Zambales'],
                'Region IV-A': ['Batangas','Cavite','Laguna','Quezon','Rizal'],
                'MIMAROPA': ['Marinduque','Occidental Mindoro','Oriental Mindoro','Palawan','Romblon'],
                'Region V': ['Albay','Camarines Norte','Camarines Sur','Catanduanes','Masbate','Sorsogon'],
                'Region VI': ['Aklan','Antique','Capiz','Guimaras','Iloilo','Negros Occidental'],
                'Region VII': ['Bohol','Cebu','Negros Oriental','Siquijor'],
                'Region VIII': ['Biliran','Eastern Samar','Leyte','Northern Samar','Samar','Southern Leyte'],
                'Region IX': ['Zamboanga del Norte','Zamboanga del Sur','Zamboanga Sibugay'],
                'Region X': ['Bukidnon','Camiguin','Lanao del Norte','Misamis Occidental','Misamis Oriental'],
                'Region XI': ['Davao de Oro','Davao del Norte','Davao del Sur','Davao Occidental','Davao Oriental'],
                'Region XII': ['Cotabato','Sarangani','South Cotabato','Sultan Kudarat'],
                'Region XIII': ['Agusan del Norte','Agusan del Sur','Dinagat Islands','Surigao del Norte','Surigao del Sur'],
                'BARMM': ['Basilan','Lanao del Sur','Maguindanao del Norte','Maguindanao del Sur','Sulu','Tawi-Tawi'],
            },

            get filteredProvinces() {
                if (!this.region || !this.provincesByRegion[this.region]) return [];
                return this.provincesByRegion[this.region];
            },

            // ========== CONTACT PERSONS / END USERS / PO / FILES ==========
            contacts:  @json(old('contacts', $contactsData ?? [])),
            primary_contact_index: @json(old('primary_contact_index', $primaryContactIndex ?? null)),

            addContact() {
                this.contacts.push({
                    id: null,
                    salutation: '',
                    first_name: '',
                    last_name: '',
                    position: '',
                    email: '',
                    mobile: '',
                    telephone: '',
                    is_accounting: false,
                    is_active: true,
                });
            },
            removeContact(index) {
                this.contacts.splice(index, 1);
            },

            endUsers: @json(old('end_users', $endUsersData ?? [])),
            addEndUser() {
                this.endUsers.push({
                    id: null,
                    name: '',
                    address: '',
                    contact_person: '',
                    telephone: '',
                });
            },
            removeEndUser(index) {
                this.endUsers.splice(index, 1);
            },

            pos: @json(old('pos', $posData ?? [])),
            addPO() {
                this.pos.push({ id: null, label: '', file_path: null });
            },
            removePO(index) {
                this.pos.splice(index, 1);
            },

            files: @json(old('files', $filesData ?? [])),
            addFile() {
                this.files.push({ id: null, label: '', file_path: null });
            },
            removeFile(index) {
                this.files.splice(index, 1);
            },
        }
    }
</script>
@endpush

@endsection
