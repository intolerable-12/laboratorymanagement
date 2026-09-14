<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InventoryAlertController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab', 'equipment'), ['equipment', 'chemicals'], true)
            ? $request->query('tab', 'equipment')
            : 'equipment';
        $search = trim((string) $request->query('search', ''));
        $laboratoryId = (string) $request->query('laboratory_id', '');
        $supplierId = (string) $request->query('supplier_id', '');
        $alertStatus = (string) $request->query('alert_status', '');
        $expirationStatus = (string) $request->query('expiration_status', '');

        $laboratories = Laboratory::query()->orderBy('laboratory_name')->get(['id', 'laboratory_name']);
        $suppliers = Supplier::query()->orderBy('supplier_name')->get(['id', 'supplier_name']);

        if ($tab === 'equipment') {
            $items = Equipment::query()
                ->with(['laboratory', 'supplier'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('equipment_name', 'like', '%'.$search.'%')
                            ->orWhere('equipment_code', 'like', '%'.$search.'%')
                            ->orWhere('barcode', 'like', '%'.$search.'%')
                            ->orWhere('brand', 'like', '%'.$search.'%')
                            ->orWhere('model', 'like', '%'.$search.'%')
                            ->orWhere('serial_number', 'like', '%'.$search.'%')
                            ->orWhere('storage_location', 'like', '%'.$search.'%');
                    });
                })
                ->when($laboratoryId !== '', fn ($query) => $query->where('laboratory_id', $laboratoryId))
                ->when($supplierId !== '', fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($alertStatus === 'configured', fn ($query) => $query->whereNotNull('low_stock_threshold'))
                ->when($alertStatus === 'disabled', fn ($query) => $query->whereNull('low_stock_threshold'))
                ->orderBy('equipment_name')
                ->paginate(10)
                ->withQueryString();
        } else {
            $items = Chemical::query()
                ->with(['laboratory', 'supplier'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('chemical_name', 'like', '%'.$search.'%')
                            ->orWhere('chemical_code', 'like', '%'.$search.'%')
                            ->orWhere('barcode', 'like', '%'.$search.'%')
                            ->orWhere('storage_location', 'like', '%'.$search.'%');
                    });
                })
                ->when($laboratoryId !== '', fn ($query) => $query->where('laboratory_id', $laboratoryId))
                ->when($supplierId !== '', fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($alertStatus === 'configured', fn ($query) => $query->whereNotNull('expiration_alert_days'))
                ->when($alertStatus === 'disabled', fn ($query) => $query->whereNull('expiration_alert_days'))
                ->when($expirationStatus === 'with_date', fn ($query) => $query->whereNotNull('expiration_date'))
                ->when($expirationStatus === 'without_date', fn ($query) => $query->whereNull('expiration_date'))
                ->orderBy('chemical_name')
                ->paginate(10)
                ->withQueryString();
        }

        $filters = array_filter([
            'laboratory_id' => $laboratoryId,
            'supplier_id' => $supplierId,
            'alert_status' => $alertStatus,
            'expiration_status' => $tab === 'chemicals' ? $expirationStatus : '',
        ], static fn ($value) => $value !== '' && $value !== null);

        $viewData = compact(
            'tab',
            'items',
            'laboratories',
            'suppliers',
            'search',
            'laboratoryId',
            'supplierId',
            'alertStatus',
            'expirationStatus',
            'filters'
        );

        if ($request->ajax()) {
            return view('users.coordinator.inventory-alerts._results', $viewData);
        }

        return view('users.coordinator.inventory-alerts.index', $viewData);
    }

    public function updateEquipment(Request $request, Equipment $equipment)
    {
        $data = $request->validate([
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ]);

        $equipment->update([
            'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
            'supplier_alert_sent_at' => null,
        ]);

        return back()->with('status', 'Equipment supplier alert setting updated.');
    }

    public function updateChemical(Request $request, Chemical $chemical)
    {
        $data = $request->validate([
            'expiration_alert_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $chemical->update([
            'expiration_alert_days' => $data['expiration_alert_days'] ?? null,
            'supplier_alert_sent_at' => null,
        ]);

        return back()->with('status', 'Chemical supplier alert setting updated.');
    }
}
