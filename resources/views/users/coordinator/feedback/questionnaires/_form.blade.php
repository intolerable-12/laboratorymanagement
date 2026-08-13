@php
    $existingRows = is_array($questionRows ?? null) ? $questionRows : [];
    $oldQuestionRows = old('questions');

    if (is_array($oldQuestionRows) && count($oldQuestionRows) > 0) {
        $questionRows = $oldQuestionRows;
    } else {
        $questionRows = count($existingRows) > 0
            ? $existingRows
            : [['question_type' => 'likert', 'question_text' => '', 'is_required' => true]];
    }
@endphp

<div class="row g-3">
    <div class="col-lg-8">
        <label class="form-label mb-1" for="topic">Topic</label>
        <input type="text" id="topic" name="topic" value="{{ old('topic', $questionnaire->topic ?? '') }}" class="form-control admin-form-control @error('topic') is-invalid @enderror" maxlength="150" required>
        @error('topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 d-flex align-items-end">
        <div class="form-check form-switch mb-1">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $questionnaire->is_active ?? true))>
            <label class="form-check-label fw-semibold text-dark" for="is_active">
                <i class="fa-solid fa-bolt me-1 text-danger"></i>Active
            </label>
        </div>
    </div>

    <div class="col-12">
        @include('partials.rich-text-editor', [
            'name' => 'description',
            'label' => 'Description',
            'id' => 'description',
            'fieldName' => 'description',
            'oldKey' => 'description',
            'value' => $questionnaire->description ?? '',
            'placeholder' => 'Explain what the questionnaire is about.',
            'hint' => 'Use this to give the questionnaire a short context.',
            'compact' => true,
        ])
    </div>
</div>

<div class="section-card">
    <div class="card-header bg-white border-0 pt-3 px-3 px-lg-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <div>
                <h3 class="h5 fw-semibold mb-1">
                    <i class="fa-solid fa-list-check me-2"></i>Questions
                </h3>
                <div class="small text-secondary">Add as many questions as needed.</div>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addQuestionBtn">
                <i class="fa-solid fa-circle-plus me-1"></i>Add question
            </button>
        </div>
    </div>
    <div class="card-body p-3 p-lg-4">
        <div id="questionRows" class="vstack gap-3" data-question-rows>
            @foreach ($questionRows as $index => $row)
                <div class="border rounded-4 p-3 bg-white shadow-sm question-row" data-question-row>
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <div>
                            <div class="small text-uppercase text-secondary" data-question-title>Question #{{ $index + 1 }}</div>
                            <div class="small text-secondary">Keep the prompt concise.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-question>
                            <i class="fa-solid fa-trash-can me-1"></i>Remove
                        </button>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Type</label>
                            <select name="questions[{{ $index }}][question_type]" class="form-select form-select-sm admin-form-control" data-question-type required>
                                <option value="likert" @selected(($row['question_type'] ?? 'likert') === 'likert')>Likert</option>
                                <option value="raw" @selected(($row['question_type'] ?? '') === 'raw')>Raw answer</option>
                            </select>
                        </div>

                        <div class="col-md-8 d-flex align-items-end">
                            <div class="form-check form-switch mb-1">
                                <input type="checkbox" name="questions[{{ $index }}][is_required]" value="1" class="form-check-input" @checked(($row['is_required'] ?? true))>
                                <label class="form-check-label fw-semibold text-dark">Required</label>
                            </div>
                        </div>

                        <div class="col-12">
                            @include('partials.rich-text-editor', [
                                'name' => 'question_text_' . $index,
                                'label' => 'Question text',
                                'id' => 'question_text_' . $index,
                                'fieldName' => 'questions[' . $index . '][question_text]',
                                'oldKey' => 'questions.' . $index . '.question_text',
                                'errorKey' => 'questions.' . $index . '.question_text',
                                'value' => $row['question_text'] ?? '',
                                'placeholder' => 'Write the question prompt here.',
                                'hint' => 'Use rich text for emphasis or short lists.',
                                'compact' => true,
                            ])
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
    <a href="{{ route('coordinator.feedback.questionnaires.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="fa-solid fa-floppy-disk me-2"></i>Save questionnaire
    </button>
</div>

<template id="questionRowTemplate">
    <div class="border rounded-4 p-3 bg-white shadow-sm question-row" data-question-row>
        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
            <div>
                <div class="small text-uppercase text-secondary" data-question-title>Question</div>
                <div class="small text-secondary">Keep the prompt concise.</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-question>
                <i class="fa-solid fa-trash-can me-1"></i>Remove
            </button>
        </div>

        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label mb-1">Type</label>
                <select name="questions[__INDEX__][question_type]" class="form-select form-select-sm admin-form-control" data-question-type required>
                    <option value="likert">Likert</option>
                    <option value="raw">Raw answer</option>
                </select>
            </div>

            <div class="col-md-8 d-flex align-items-end">
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" name="questions[__INDEX__][is_required]" value="1" class="form-check-input" checked>
                    <label class="form-check-label fw-semibold text-dark">Required</label>
                </div>
            </div>

            <div class="col-12">
                <div class="rich-text-editor rich-text-editor--compact" data-rich-text-editor>
                    <div class="d-flex justify-content-between align-items-end gap-3 mb-2">
                        <label class="form-label fw-semibold text-dark mb-0">Question text</label>
                        <div class="small text-secondary">Rich text enabled</div>
                    </div>
                    <textarea name="questions[__INDEX__][question_text]" class="d-none" data-rich-text-input></textarea>
                    <div class="rich-text-editor__frame">
                        <div class="rich-text-editor__surface" data-rich-text-surface data-placeholder="Write the question prompt here."></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
@endpush

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rowsContainer = document.getElementById('questionRows');
        const addButton = document.getElementById('addQuestionBtn');
        const template = document.getElementById('questionRowTemplate');

        if (!rowsContainer || !addButton || !template) {
            return;
        }

        const renumberRows = () => {
            const rows = rowsContainer.querySelectorAll('[data-question-row]');
            rows.forEach((row, index) => {
                const label = row.querySelector('[data-question-title]');
                if (label) {
                    label.textContent = `Question #${index + 1}`;
                }

                row.querySelectorAll('input, select, textarea').forEach((field) => {
                    if (!field.name) {
                        return;
                    }

                    field.name = field.name.replace(/questions\[\d+\]/, `questions[${index}]`);

                    if (field.id) {
                        field.id = field.id.replace(/_\d+$/, `_${index}`);
                    }
                });

            });
        };

        addButton.addEventListener('click', () => {
            const index = rowsContainer.querySelectorAll('[data-question-row]').length;
            const html = template.innerHTML.replaceAll('__INDEX__', String(index));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const newRow = wrapper.firstElementChild;

            if (!newRow) {
                return;
            }

            rowsContainer.appendChild(newRow);
            renumberRows();

            if (window.initRichTextEditors) {
                window.initRichTextEditors(newRow);
            }
        });

        rowsContainer.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-question]');
            if (!removeButton) {
                return;
            }

            const rows = rowsContainer.querySelectorAll('[data-question-row]');
            if (rows.length <= 1) {
                return;
            }

            removeButton.closest('[data-question-row]')?.remove();
            renumberRows();
        });
    });
</script>
