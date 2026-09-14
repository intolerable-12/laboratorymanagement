<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $suppliers = Supplier::query()
            ->withCount(['equipment', 'chemicals'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('supplier_name', 'like', '%'.$search.'%')
                        ->orWhere('supplier_code', 'like', '%'.$search.'%')
                        ->orWhere('contact_person', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('supplier_name')
            ->paginate(10);

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('status', 'Active')->count(),
            'assigned' => Supplier::whereHas('equipment')->orWhereHas('chemicals')->count(),
        ];

        return view('users.coordinator.suppliers.index', compact('suppliers', 'stats', 'search', 'status'));
    }

    public function create()
    {
        return view('users.coordinator.suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateSupplier($request);
        $data['supplier_code'] = $this->nextSupplierCode();

        Supplier::create($data);

        return redirect()->route('coordinator.suppliers.index')->with('status', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('users.coordinator.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validateSupplier($request, $supplier);
        $supplier->update($data);

        return redirect()->route('coordinator.suppliers.index')->with('status', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->equipment()->exists() || $supplier->chemicals()->exists()) {
            return redirect()->route('coordinator.suppliers.index')
                ->with('error', 'This supplier is assigned to inventory items. Reassign those items before archiving the supplier.');
        }

        $supplier->delete();

        return redirect()->route('coordinator.suppliers.index')->with('status', 'Supplier archived successfully.');
    }

    private function validateSupplier(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'supplier_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function nextSupplierCode(): string
    {
        $prefix = 'SUP-'.now()->format('y').'-';
        $lastNumber = Supplier::withTrashed()
            ->where('supplier_code', 'like', $prefix.'%')
            ->pluck('supplier_code')
            ->map(fn (string $code): int => ctype_digit(substr($code, strlen($prefix))) ? (int) substr($code, strlen($prefix)) : 0)
            ->max() ?? 0;

        return $prefix.str_pad((string) ($lastNumber + 1), 5, '0', STR_PAD_LEFT);
    }
}
