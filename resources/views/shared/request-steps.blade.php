@php
    $currentStep = $currentStep ?? 1;
    $requestType = $requestType ?? 'Borrow';
    $stepRoutes = $stepRoutes ?? [];
    $steps = [
        ['number' => 1, 'label' => $requestType . ' Details'],
        ['number' => 2, 'label' => 'Requested Items'],
        ['number' => 3, 'label' => 'Review & Submit'],
    ];
@endphp

<nav class="request-stepper mb-4" aria-label="Request progress">
    <div class="request-stepper-header">
        <span class="request-stepper-title">Request progress</span>
        <span class="request-stepper-position">Step {{ $currentStep }} of {{ count($steps) }}</span>
    </div>

    <ol class="request-stepper-list">
        @foreach ($steps as $step)
            @php
                $isCurrent = $step['number'] === $currentStep;
                $isComplete = $step['number'] < $currentStep;
                $stepUrl = $stepRoutes[$step['number']] ?? null;
                $stateLabel = $isCurrent ? 'Current' : ($isComplete ? 'Completed' : 'Upcoming');
            @endphp
            <li class="request-stepper-item request-stepper-item--{{ strtolower($stateLabel) }}">
                @if ($stepUrl && !$isCurrent)
                    <a href="{{ $stepUrl }}" class="request-stepper-link">
                @else
                    <div class="request-stepper-link" @if ($isCurrent) aria-current="step" @endif>
                @endif
                    <span class="request-stepper-number">{{ $step['number'] }}</span>
                    <span class="request-stepper-copy">
                        <span class="request-stepper-label">{{ $step['label'] }}</span>
                        <span class="request-stepper-state">{{ $stateLabel }}</span>
                    </span>
                @if ($stepUrl && !$isCurrent)
                    </a>
                @else
                    </div>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
