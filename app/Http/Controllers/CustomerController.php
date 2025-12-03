<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerContactPerson;
use App\Models\CustomerEndUser;
use App\Models\CustomerPO;
use App\Models\CustomerFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request)
    {
        $query = Customer::with('user')->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('company_email', 'like', "%{$search}%")
                    ->orWhere('company_phone', 'like', "%{$search}%")
                    ->orWhere('primary_contact', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(100);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        $nextCode = $this->generateNextCustomerCode();

        return view('customers.create', compact('nextCode'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name'               => ['required', 'string', 'max:255'],
            'nature_of_business'          => ['nullable', 'string', 'max:255'],
            'address'                     => ['required', 'string', 'max:1000'],
            'region'                      => ['nullable', 'string', 'max:100'],
            'province'                    => ['nullable', 'string', 'max:100'],
            'company_email'               => ['nullable', 'email', 'max:255'],
            'company_phone'               => ['nullable', 'string', 'max:255'],
            'tin'                         => ['nullable', 'string', 'max:255'],
            'payment_terms'               => ['nullable', 'integer'],
            'vat_type'                    => ['nullable', 'string', 'max:50'],
            'certificate_expiration_days' => ['nullable', 'integer'],
            'company_type'                => ['nullable', 'string', 'max:50'],
            'pos.*.file'                  => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv'],
            'files.*.file'                => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv'],
        ]);

        $validated['code']       = $this->generateNextCustomerCode();
        $validated['created_by'] = Auth::id();

        $customer = Customer::create($validated);

        // save contact persons, end users, PO files, other files
        $this->saveSubTables($request, $customer);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created.');
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        // ---------- CONTACT PERSONS ----------
        $contactsData = $customer->contactPersons()
            ->orderBy('id')
            ->get()
            ->map(function (CustomerContactPerson $c) {
                return [
                    'id'            => $c->id,
                    'salutation'    => $c->salutation,
                    'first_name'    => $c->first_name,
                    'last_name'     => $c->last_name,
                    'position'      => $c->position,
                    'email'         => $c->email,
                    'mobile'        => $c->mobile,
                    'telephone'     => $c->telephone,
                    'is_accounting' => (bool) $c->is_accounting,
                    'is_active'     => (bool) $c->is_active,
                ];
            })
            ->values()
            ->all();

        // You can later compute which one is primary; for now just null
        $primaryContactIndex = null;

        // ---------- END USERS ----------
        $endUsersData = $customer->endUsers()
            ->orderBy('id')
            ->get()
            ->map(function (CustomerEndUser $u) {
                return [
                    'id'            => $u->id,
                    'name'          => $u->name,
                    'address'       => $u->address,
                    'contact_person'=> $u->contact_person,
                    'telephone'     => $u->telephone,
                ];
            })
            ->values()
            ->all();

        // ---------- PO DATA ----------
        $posData = $customer->pos()
            ->orderBy('id')
            ->get()
            ->map(function (CustomerPO $po) {
                return [
                    'id'        => $po->id,
                    'label'     => $po->label,
                    'file_path' => $po->file_path,
                ];
            })
            ->values()
            ->all();

        // ---------- FILES DATA ----------
        $filesData = $customer->files()
            ->orderBy('id')
            ->get()
            ->map(function (CustomerFile $f) {
                return [
                    'id'        => $f->id,
                    'label'     => $f->label,
                    'file_path' => $f->file_path,
                ];
            })
            ->values()
            ->all();

        return view('customers.edit', compact(
            'customer',
            'contactsData',
            'primaryContactIndex',
            'endUsersData',
            'posData',
            'filesData'
        ));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'business_name'               => ['required', 'string', 'max:255'],
            'nature_of_business'          => ['nullable', 'string', 'max:255'],
            'address'                     => ['required', 'string', 'max:1000'],
            'region'                      => ['nullable', 'string', 'max:100'],
            'province'                    => ['nullable', 'string', 'max:100'],
            'company_email'               => ['nullable', 'email', 'max:255'],
            'company_phone'               => ['nullable', 'string', 'max:255'],
            'tin'                         => ['nullable', 'string', 'max:255'],
            'payment_terms'               => ['nullable', 'integer'],
            'vat_type'                    => ['nullable', 'string', 'max:50'],
            'certificate_expiration_days' => ['nullable', 'integer'],
            'company_type'                => ['nullable', 'string', 'max:50'],
            'pos.*.file'                  => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv'],
            'files.*.file'                => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv'],
        ]);

        $customer->update($validated);

        // re-save related tables (contacts, end users, PO, files)
        $this->saveSubTables($request, $customer);

        return redirect()
            ->route('customers.edit', $customer->id)
            ->with('success', 'Customer updated.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->contactPersons()->delete();
        $customer->endUsers()->delete();
        $customer->pos()->delete();
        $customer->files()->delete();
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted.');
    }

    /**
     * Save contact persons, end users, POs, and other files.
     */
    private function saveSubTables(Request $request, Customer $customer): void
    {
        // ---------- CONTACT PERSONS ----------
        $customer->contactPersons()->delete();
        $contacts = $request->input('contacts', []);

        foreach ($contacts as $contact) {
            if (empty($contact['first_name']) && empty($contact['last_name'])) {
                continue;
            }

            $customer->contactPersons()->create([
                'salutation'    => $contact['salutation']    ?? null,
                'first_name'    => $contact['first_name']    ?? null,
                'last_name'     => $contact['last_name']     ?? null,
                'position'      => $contact['position']      ?? null,
                'email'         => $contact['email']         ?? null,
                'mobile'        => $contact['mobile']        ?? null,
                'telephone'     => $contact['telephone']     ?? null,
                'is_accounting' => !empty($contact['is_accounting']),
                'is_active'     => !empty($contact['is_active']),
            ]);
        }

        // (Optional) handle primary_contact here later if you like

        // ---------- END USERS ----------
        $customer->endUsers()->delete();
        $endUsers = $request->input('end_users', []);

        foreach ($endUsers as $user) {
            if (empty($user['name'])) {
                continue;
            }

            $customer->endUsers()->create([
                'name'           => $user['name']           ?? null,
                'address'        => $user['address']        ?? null,
                'contact_person' => $user['contact_person'] ?? null,
                'telephone'      => $user['telephone']      ?? null,
            ]);
        }

        // ---------- PO FILES ----------
        $customer->pos()->delete();
        $posRows = $request->input('pos', []);

        foreach ($posRows as $index => $p) {
            // Skip totally empty rows
            if (empty($p['label']) && empty($p['file']) && empty($p['file_path'])) {
                continue;
            }

            $upload = $request->file("pos.$index.file");
            $path = null;

            if ($upload instanceof UploadedFile) {
                $path = $upload->store('customer_pos', 'public');
            }

            $customer->pos()->create([
                'label'     => $p['label'] ?? null,
                'file_path' => $path ?? ($p['file_path'] ?? null),
            ]);
        }

        // ---------- OTHER FILES ----------
        $customer->files()->delete();
        $fileRows = $request->input('files', []);

        foreach ($fileRows as $index => $f) {
            // Skip totally empty rows
            if (empty($f['label']) && empty($f['file']) && empty($f['file_path'])) {
                continue;
            }

            $upload = $request->file("files.$index.file");
            $path = null;

            if ($upload instanceof UploadedFile) {
                $path = $upload->store('customer_files', 'public');
            }

            $customer->files()->create([
                'label'     => $f['label'] ?? null,
                'file_path' => $path ?? ($f['file_path'] ?? null),
            ]);
        }
    }

    /**
     * Generate the next customer code in the format 1MC-CUS-XXXX.
     */
    private function generateNextCustomerCode(): string
    {
        $prefix = '1MC-CUS-';

        $lastCustomer = Customer::where('code', 'LIKE', $prefix.'%')
            ->orderBy('code', 'desc')
            ->first();

        $nextNumber = $lastCustomer
            ? ((int) substr($lastCustomer->code, -4)) + 1
            : 1;

        $numberPart = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $prefix.$numberPart;
    }
}
