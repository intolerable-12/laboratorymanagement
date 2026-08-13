@php
    $fieldId = $id ?? $name;
    $fieldName = $fieldName ?? $name;
    $oldKey = $oldKey ?? $name;
    $errorKey = $errorKey ?? $name;
    $compact = $compact ?? false;
    $currentValue = old($oldKey, $value ?? '');
@endphp

<div class="rich-text-editor {{ $compact ? 'rich-text-editor--compact' : '' }}" data-rich-text-editor>
    <div class="d-flex justify-content-between align-items-end gap-3 mb-2">
        <label for="{{ $fieldId }}" class="form-label fw-semibold text-dark mb-0">{{ $label }}</label>
        @if (! empty($hint))
            <div class="small text-secondary">{{ $hint }}</div>
        @endif
    </div>

    <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" class="d-none" data-rich-text-input>{{ $currentValue }}</textarea>

    <div class="rich-text-editor__frame">
        <div class="rich-text-editor__surface" data-rich-text-surface data-placeholder="{{ $placeholder ?? 'Write something meaningful...' }}"></div>
    </div>

    @error($errorKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
