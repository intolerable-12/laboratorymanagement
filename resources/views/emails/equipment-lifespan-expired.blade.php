<p>Hello {{ $supplierName }},</p>

<p>The recorded lifespan of this equipment supplied to Lourdes College has been reached:</p>

<ul>
    <li><strong>Equipment name:</strong> {{ $equipmentName }}</li>
    <li><strong>Supplier name:</strong> {{ $supplierName }}</li>
    <li><strong>Brand:</strong> {{ $brand ?: 'Not specified' }}</li>
    <li><strong>Model:</strong> {{ $model ?: 'Not specified' }}</li>
    <li><strong>Serial number:</strong> {{ $serialNumber ?: 'Not specified' }}</li>
    <li><strong>Manufacturing date:</strong> {{ $manufacturingDate ?: 'Not specified' }}</li>
</ul>

@if (count($campusContactEmails) > 0)
    <p>For follow-up, contact the campus laboratory staff:</p>
    <ul>
        @foreach ($campusContactEmails as $email)
            <li>{{ $email }}</li>
        @endforeach
    </ul>
@endif

<p>Please contact the campus laboratory staff if replacement, maintenance, or further assessment is required.</p>
