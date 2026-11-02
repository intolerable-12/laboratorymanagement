<div class="modal fade" id="bulk-import-{{ $resourceSlug }}-modal" tabindex="-1" aria-labelledby="bulk-import-{{ $resourceSlug }}-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5 fw-semibold" id="bulk-import-{{ $resourceSlug }}-title">Bulk import {{ $resourceLabel }}</h2>
                    <div class="small text-secondary mt-1">Use the CSV columns and value formats shown below.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <p class="mb-0 small text-secondary">
                        Category and laboratory values must match existing records. Laboratory names must belong to your campus.
                        Leave <code>supplier_name</code> blank if no supplier applies.
                    </p>
                    <a href="{{ route($routePrefix.'.'.$resourceSlug.'.bulk-import.template') }}" class="btn btn-outline-primary btn-sm flex-shrink-0">
                        <i class="fa-solid fa-download me-1"></i>Download CSV template
                    </a>
                </div>

                <div class="table-responsive border rounded-3 mb-4">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                @foreach ($csvHeaders as $header)
                                    <th class="text-nowrap"><code>{{ $header }}</code></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach ($csvSampleRow as $value)
                                    <td class="text-nowrap">{{ $value !== '' ? $value : '—' }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route($routePrefix.'.'.$resourceSlug.'.bulk-import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="bulk-import-{{ $resourceSlug }}-file" class="form-label fw-semibold">CSV file</label>
                    <input
                        type="file"
                        name="file"
                        id="bulk-import-{{ $resourceSlug }}-file"
                        class="form-control"
                        accept=".csv,text/csv"
                        required
                    >
                    <div class="form-text">Maximum file size: 10 MB. All rows are validated before any records are imported.</div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-file-import me-1"></i>Import {{ $resourceLabel }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
