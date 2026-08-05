<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Print Barcode | {{ $chemical->chemical_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: auto;
            margin: 0.6in;
        }

        body {
            background: #f5f7fb;
        }

        .barcode-print-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1rem;
        }

        .barcode-print-card {
            width: min(100%, 760px);
        }

        .barcode-print-toolbar {
            width: min(100%, 760px);
            margin: 0 auto 1rem;
        }

        .barcode-print-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.22in 0;
            justify-items: center;
        }

        .barcode-print-item {
            break-inside: avoid;
            page-break-inside: avoid;
            border: 1px dashed rgba(148, 163, 184, 0.45);
            border-radius: 0.9rem;
            padding: 0.65in 0.3in 0.45in;
            background: #fff;
            width: min(100%, 520px);
        }

        .barcode-print-label {
            width: 100%;
            margin: 0 auto;
        }

        .barcode-print-label__code {
            margin-top: 0.35rem;
        }

        .barcode-print-count {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: #eef2ff;
            color: #2742d5;
            font-weight: 600;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .barcode-print-shell {
                min-height: auto;
                padding: 0;
            }

            .barcode-print-card {
                width: 100%;
                box-shadow: none !important;
                border: 0 !important;
            }

            .barcode-print-label {
                width: 100%;
                max-width: 520px;
            }
        }
    </style>
</head>
<body class="role-page">
    <div class="barcode-print-shell">
        <div class="barcode-print-card section-card p-4 p-lg-5">
            <div class="barcode-print-toolbar no-print">
                <form method="GET" action="{{ route('coordinator.chemicals.barcode-print', $chemical) }}" class="row g-2 align-items-end">
                    <div class="col-sm-7 col-md-5 col-lg-4">
                        <label for="count" class="form-label fw-medium mb-1">Labels to print</label>
                        <input
                            type="number"
                            id="count"
                            name="count"
                            min="1"
                            max="50"
                            value="{{ $printCount }}"
                            class="form-control admin-form-control"
                        >
                    </div>

                    <div class="col-sm-auto d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">Update labels</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                    <div>
                        <h1 class="h4 fw-semibold mb-1">Print barcode</h1>
                        <p class="mb-0 text-secondary">{{ $chemical->chemical_name }} · {{ $chemical->chemical_code }}</p>
                    </div>

                    <div class="barcode-print-count">
                        {{ $printCount }} label{{ $printCount === 1 ? '' : 's' }}
                    </div>
                </div>
            </div>

            <div class="barcode-print-grid">
                @for ($i = 0; $i < $printCount; $i++)
                    <div class="barcode-label barcode-print-label barcode-print-item">
                        <div class="barcode-svg barcode-svg--label">
                            {!! $barcodeSvg !!}
                        </div>

                        <div class="barcode-code barcode-print-label__code">{{ $chemical->barcode }}</div>
                    </div>
                @endfor
            </div>

            <div class="mt-3 no-print">
                <a href="{{ route('coordinator.chemicals.show', $chemical) }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>
</body>
</html>