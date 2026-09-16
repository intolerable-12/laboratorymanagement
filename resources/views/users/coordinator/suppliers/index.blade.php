@extends('users.coordinator.layouts.app')

@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="small text-uppercase text-secondary">Inventory contacts</div>
            <div class="text-secondary">Manage the suppliers available in equipment and chemical forms.</div>
        </div>
        <a href="{{ route('coordinator.suppliers.create') }}" class="btn btn-primary px-4"><i class="fa-solid fa-plus me-2"></i>Add supplier</a>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-4"><div class="card metric-card h-100"><div class="card-body"><div class="small text-uppercase text-secondary mb-2">Total suppliers</div><div class="display-6 fw-semibold text-dark">{{ $stats['total'] }}</div><div class="small text-secondary">Registered supplier records</div></div></div></div>
        <div class="col-12 col-sm-4"><div class="card metric-card h-100"><div class="card-body"><div class="small text-uppercase text-secondary mb-2">Active</div><div class="display-6 fw-semibold text-dark">{{ $stats['active'] }}</div><div class="small text-secondary">Shown in inventory dropdowns</div></div></div></div>
        <div class="col-12 col-sm-4"><div class="card metric-card h-100"><div class="card-body"><div class="small text-uppercase text-secondary mb-2">Assigned</div><div class="display-6 fw-semibold text-dark">{{ $stats['assigned'] }}</div><div class="small text-secondary">Suppliers linked to inventory</div></div></div></div>
    </div>

    <div class="section-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('coordinator.suppliers.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-6"><label class="form-label" for="search">Search</label><input type="search" id="search" name="search" value="{{ $search }}" placeholder="Supplier, code, contact, or email" class="form-control admin-form-control"></div>
                <div class="col-12 col-lg-3"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select admin-form-control"><option value="">All statuses</option><option value="Active" @selected($status === 'Active')>Active</option><option value="Inactive" @selected($status === 'Inactive')>Inactive</option></select></div>
                <div class="col-12 col-lg-auto d-flex gap-2"><button class="btn btn-primary px-4">Search</button><a href="{{ route('coordinator.suppliers.index') }}" class="btn btn-outline-secondary px-4">Clear</a></div>
            </form>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-dark ps-4">Supplier</th>
                    <th class="text-dark">Contact</th>
                    <th class="text-dark">Email</th>
                    <th class="text-dark">Status</th>
                    <th class="text-dark">Assigned items</th>
                    <th class="text-center text-dark pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="ps-4"><div class="fw-semibold text-dark">{{ $supplier->supplier_name }}</div><div class="small text-secondary">{{ $supplier->supplier_code }}</div></td>
                        <td>{{ $supplier->contact_person ?: '—' }}<div class="small text-secondary">{{ $supplier->contact_number ?: '' }}</div></td>
                        <td>{{ $supplier->email }}</td>
                        <td><span class="badge text-bg-{{ $supplier->status === 'Active' ? 'success' : 'secondary' }}">{{ $supplier->status }}</span></td>
                        <td>{{ $supplier->equipment_count }} equipment · {{ $supplier->chemicals_count }} chemicals</td>
                        <td class="text-center pe-4"><div class="btn-group action-buttons"><a href="{{ route('coordinator.suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a><form action="{{ route('coordinator.suppliers.destroy', $supplier) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Archive this supplier?');" title="Archive"><i class="fa-solid fa-box-archive"></i></button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary py-5">No suppliers found.</td></tr>
                @endforelse
            </tbody>
        </table></div></div>
    </div>
    <div class="mt-4">{{ $suppliers->withQueryString()->links('pagination::bootstrap-5') }}</div>
@endsection
