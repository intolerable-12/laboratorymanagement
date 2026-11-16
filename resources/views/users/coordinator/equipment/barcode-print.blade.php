<!doctype html>
<html lang="en">
@php
    // Route prefix used by the form actions and Back link. Must match the
    // route group this page renders under.
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Title switches based on mode: bulk print ($items) vs single-equipment
         print ($equipment). --}}
    <title>{{ isset($items) ? 'Print Equipment Barcodes' : 'Print Barcode | ' . $equipment->equipment_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* @page and @media are written with @@ to escape Blade's directive
           parsing. If print rules ever stop applying, a dropped @@ is the
           likely cause. */
        @@page {
            size: auto;
            margin: 0.6in;
        }

        /* Screen-only backdrop. Overridden to white in print mode. */
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

        /* Grid of print labels. Gap expressed in inches so spacing translates
           correctly to paper. If labels position oddly, this and the item's
           padding are the first numbers to check. */
        .barcode-print-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.22in 0;
            justify-items: center;
        }

        /* Individual label card. break-inside/page-break-inside prevent
           splitting a label across pages. Width/height come from CSS variables
           set by the toolbar sliders. */
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

        /* Fixed-width inner content, centered via negative margin. The
           transform: scale() lets the toolbar resize cards without changing
           layout width — this is what keeps cards from overflowing paper
           when scaled. */
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

        /* Meta row: manufactured, acquired, location. Uses space-between so
           the three items spread across the label. */
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

        /* Print-mode overrides. no-print hides the toolbar. The card loses
           its shadow/border so labels start cleanly at the top of the page. */
        @@media print {
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
            {{-- Toolbar — hidden on print via .no-print. --}}
            <div class="barcode-print-toolbar no-print">
                @if (isset($items))
                    {{-- Bulk mode header: shows total selected items and
                         provides a Print button. --}}
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <h1 class="h4 fw-semibold mb-1">Print equipment barcodes</h1>
                            <p class="mb-0 text-secondary">{{ $items->count() }} selected equipment item{{ $items->count() === 1 ? '' : 's' }}</p>
                        </div>

                        <div class="barcode-print-count">
                            {{ $items->count() }} label{{ $items->count() === 1 ? '' : 's' }}
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
                    </div>
                @else
                {{-- Single-equipment mode header. GET form lets the user change
                     label count, which re-renders the page with a new
                     $printCount from the controller. --}}
                <form method="GET" action="{{ route($routePrefix.'.equipment.barcode-print', $equipment) }}" class="row g-2 align-items-end">
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
                        <p class="mb-0 text-secondary">{{ $equipment->equipment_name }} · {{ $equipment->equipment_code }}</p>
                    </div>

                    <div class="barcode-print-count">
                        {{ $printCount }} label{{ $printCount === 1 ? '' : 's' }}
                    </div>
                </div>
                @endif

                {{-- Card size sliders. The data-* attributes are hooks for JS
                     (not in this file — likely in app.js) that writes the
                     CSS variables --barcode-card-width, --barcode-card-height,
                     and --barcode-content-scale. If resizing does nothing,
                     the JS binding is missing. --}}
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

            {{-- Label grid. Bulk mode renders one card per item; single mode
                 renders $printCount copies of the same equipment. --}}
            <div class="barcode-print-grid">
                @if (isset($items))
                    @foreach ($items as $printItem)
                        @php
                            // $printItem must be shaped as
                            // ['item' => Equipment, 'barcodeSvg' => string].
                            // The $equipment reassignment is intentional so the
                            // blade below can reuse the same variable name as
                            // the single branch. Anything after this loop
                            // using $equipment will see the last item, not the
                            // original. --}}
                            $equipment = $printItem['item'];
                        @endphp
                        <div class="barcode-label barcode-print-label barcode-print-item">
                            <div class="barcode-print-content">
                                <div class="barcode-print-label__name">{{ $equipment->equipment_name }}</div>

                                {{-- SVG is pre-rendered server-side and injected
                                     raw via {!! !!}. Safe because it comes from a
                                     barcode library, not user input. --}}
                                <div class="barcode-print-label__barcode barcode-svg barcode-svg--label">
                                    {!! $printItem['barcodeSvg'] !!}
                                </div>

                                <div class="barcode-print-label__code text-center">{{ $equipment->barcode }}</div>
                                <div class="barcode-print-label__meta">
                                    <span>Manufactured: {{ $equipment->manufacturing_date?->format('d-M-Y') ?? 'N/A' }}</span>
                                    <span>Acquired date: {{ $equipment->purchase_date?->format('d-M-Y') ?? 'N/A' }}</span>
                                    <span>Loc: {{ $equipment->storage_location ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Single mode: renders $printCount copies. If changing the
                         count doesn't produce more labels, check the controller
                         is honoring ?count= and passing the resulting value
                         back as $printCount. --}}
                    @for ($i = 0; $i < $printCount; $i++)
                        <div class="barcode-label barcode-print-label barcode-print-item">
                            <div class="barcode-print-content">
                                <div class="barcode-print-label__name">{{ $equipment->equipment_name }}</div>

                                <div class="barcode-print-label__barcode barcode-svg barcode-svg--label">
                                    {!! $barcodeSvg !!}
                                </div>

                                <div class="barcode-print-label__code text-center">{{ $equipment->barcode }}</div>
                                <div class="barcode-print-label__meta">
                                    <span>Manufactured: {{ $equipment->manufacturing_date?->format('d-M-Y') ?? 'N/A' }}</span>
                                    <span>Acquired date: {{ $equipment->purchase_date?->format('d-M-Y') ?? 'N/A' }}</span>
                                    <span>Loc: {{ $equipment->storage_location ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    @endfor
                @endif
            </div>

            {{-- Back link, hidden on print. --}}
            <div class="mt-3 no-print">
                <a href="{{ isset($items) ? route($routePrefix.'.equipment.index') : route($routePrefix.'.equipment.show', $equipment) }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>
</body>
</html>