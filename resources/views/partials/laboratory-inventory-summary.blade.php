<section class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="fa-solid fa-flask-vial text-success"></i>
        <h3 class="h4 fw-semibold mb-0 text-dark">Inventory in Laboratories</h3>
    </div>
    <div class="row g-3 g-xl-4">
        @forelse ($laboratoryInventory as $laboratory)
            <div class="col-12 col-md-6 col-xxl-4">
                <div class="card metric-card border-0 h-100">
                    <div class="card-body p-4">
                        <h4 class="h5 fw-semibold text-dark mb-3">{{ $laboratory['name'] }}</h4>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Equipment</div>
                            <div class="h3 fw-semibold text-dark mb-1">{{ number_format($laboratory['equipment_quantity']) }} unit(s)</div>
                            <div class="small text-secondary">{{ number_format($laboratory['equipment_records']) }} equipment record(s)</div>
                        </div>
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Chemicals</div>
                            <div class="fw-semibold text-dark mb-2">{{ number_format($laboratory['chemical_records']) }} chemical record(s)</div>
                            @forelse ($laboratory['chemical_quantities'] as $quantity)
                                <div class="d-flex justify-content-between align-items-center gap-3 py-1">
                                    <span class="badge rounded-pill text-bg-light border text-secondary">{{ $quantity['unit'] }}</span>
                                    <span class="fw-semibold text-dark text-end">{{ $quantity['value'] }} {{ $quantity['unit'] }}</span>
                                </div>
                            @empty
                                <div class="small text-secondary">No chemical stock</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card metric-card border-0">
                    <div class="card-body p-4 text-center text-secondary">No laboratories are available for this campus.</div>
                </div>
            </div>
        @endforelse
    </div>
    </div>
</section>
