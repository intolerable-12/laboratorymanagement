<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($items) ? 'Print Chemical Barcodes' : 'Print Barcode | ' . $chemical->chemical_name }}</title>
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
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 0;
            padding: 0.12in 0.2in 0.1in;
            background: #fff;
            width: min(100%, var(--barcode-card-width, 520px));
            max-width: none;
            height: var(--barcode-card-height, auto);
            box-sizing: border-box;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .barcode-print-content {
            width: 480px;
            max-width: none;
            position: relative;
            left: 50%;
            margin-left: -240px;
            transform: scale(var(--barcode-content-scale, 1));
            transform-origin: top center;
        }

        .barcode-print-label {
            width: 100%;
            margin: 0 auto;
        }

        .barcode-print-label__name {
            font-size: 1.05rem;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #111827;
            text-transform: uppercase;
        }

        .barcode-print-label__barcode {
            margin-top: 0.55rem;
        }

        .barcode-print-item .barcode-svg--label {
            width: 360px;
            margin-inline: auto;
        }

        .barcode-print-label__code {
            margin-top: 0.45rem;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #111827;
            text-transform: uppercase;
        }

        .barcode-print-label__meta {
            margin-top: 0.45rem;
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.86rem;
            color: #475569;
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

        .barcode-print-size-controls {
            padding: 0.75rem 1rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 0.75rem;
            background: #f8fafc;
        }

        .barcode-print-size-controls input[type="range"] {
            width: min(100%, 320px);
        }

        .barcode-print-size-control {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1 1 360px;
            min-width: 0;
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
                max-width: none;
            }
        }
    </style>
</head>
<body class="role-page">
    <div class="barcode-print-shell">
        <div class="barcode-print-card section-card p-4 p-lg-5">
            <div class="barcode-print-toolbar no-print">
                @if (isset($items))
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <h1 class="h4 fw-semibold mb-1">Print chemical barcodes</h1>
                            <p class="mb-0 text-secondary">{{ $items->count() }} selected chemical{{ $items->count() === 1 ? '' : 's' }}</p>
                        </div>

                        <div class="barcode-print-count">
                            {{ $items->count() }} label{{ $items->count() === 1 ? '' : 's' }}
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
                    </div>
                @else
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
                @endif

                <div class="barcode-print-size-controls d-flex flex-wrap align-items-center gap-3 mt-3" data-barcode-size-controls>
                    <div class="barcode-print-size-control">
                        <label for="barcode-size" class="form-label fw-medium mb-0">Card width</label>
                        <input id="barcode-size" type="range" min="240" max="800" step="10" value="520" data-barcode-size aria-describedby="barcode-size-help">
                        <output class="fw-semibold text-primary" data-barcode-size-output for="barcode-size">520px</output>
                    </div>
                    <div class="barcode-print-size-control">
                        <label for="barcode-height" class="form-label fw-medium mb-0">Card height</label>
                        <input id="barcode-height" type="range" min="120" max="360" step="10" value="180" data-barcode-height aria-describedby="barcode-size-help">
                        <output class="fw-semibold text-primary" data-barcode-height-output for="barcode-height">180px</output>
                    </div>
                    <span id="barcode-size-help" class="small text-secondary">Resize the whole barcode card before printing.</span>
                </div>
            </div>

            <div class="barcode-print-grid">
                @if (isset($items))
                    @foreach ($items as $printItem)
                        @php
                            $chemical = $printItem['item'];
                        @endphp
                        <div class="barcode-label barcode-print-label barcode-print-item">
                            <div class="barcode-print-content">
                                <div class="barcode-print-label__name">{{ $chemical->chemical_name }}</div>

                                <div class="barcode-print-label__barcode barcode-svg barcode-svg--label">
                                    {!! $printItem['barcodeSvg'] !!}
                                </div>

                                <div class="barcode-print-label__code text-center">{{ $chemical->barcode }}</div>

                                <div class="barcode-print-label__meta">
                                    <span>Expiry: {{ $chemical->expiration_date?->format('d-M-Y') ?? 'N/A' }}</span>
                                    <span>Loc: {{ $chemical->storage_location ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    @for ($i = 0; $i < $printCount; $i++)
                        <div class="barcode-label barcode-print-label barcode-print-item">
                            <div class="barcode-print-content">
                                <div class="barcode-print-label__name">{{ $chemical->chemical_name }}</div>

                                <div class="barcode-print-label__barcode barcode-svg barcode-svg--label">
                                    {!! $barcodeSvg !!}
                                </div>

                                <div class="barcode-print-label__code text-center">{{ $chemical->barcode }}</div>

                                <div class="barcode-print-label__meta">
                                    <span>Expiry: {{ $chemical->expiration_date?->format('d-M-Y') ?? 'N/A' }}</span>
                                    <span>Loc: {{ $chemical->storage_location ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    @endfor
                @endif
            </div>

            <div class="mt-3 no-print">
                <a href="{{ isset($items) ? route('coordinator.chemicals.index') : route('coordinator.chemicals.show', $chemical) }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>
</body>
</html>
