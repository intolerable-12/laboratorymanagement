@php
    // Existing rows come from the controller (edit mode). $questionRows may
    // be an array of previously-saved questions. If it isn't an array (e.g.
    // a Collection was passed by mistake), we fall back to [] so the loop
    // below still works. If edit-mode questions render blank, this is the
    // first line to check.
    $existingRows = is_array($questionRows ?? null) ? $questionRows : [];

    // old() is checked BEFORE existing rows so a failed save doesn't lose
    // the user's unsaved edits. Requires the controller to call
    // ->withInput() on validation failure — otherwise this is always null.
    $oldQuestionRows = old('questions');

    $questionnaireCampus = $campus ?? ($questionnaire->campus ?? 'Main');

    // Audience options are campus-scoped — a Main campus questionnaire can't
    // target a satellite campus's users. If the dropdown is empty, check
    // audiencesForCampus() for the given campus in the model.
    $audienceOptions = \App\Models\FeedbackQuestionnaire::audiencesForCampus($questionnaireCampus);
    $selectedAudience = old('audience', $questionnaire->audience ?? \App\Models\FeedbackQuestionnaire::AUDIENCE_BOTH);

    // Guards against a stored audience that's no longer valid for this
    // campus (e.g. campus scope changed since the questionnaire was saved).
    // Falls back to the first available option so the select doesn't render
    // with a phantom value that fails server validation on save.
    if (! in_array($selectedAudience, $audienceOptions, true)) {
        $selectedAudience = $audienceOptions[0];
    }

    // Priority chain: old input > existing rows > single empty row.
    // The final fallback ensures at least one question block is always
    // rendered — the JS "add question" flow depends on that.
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

    {{-- Active toggle. Plain checkbox (not a switch-style hidden+checkbox
         pair) — meaning when unchecked, is_active is simply absent from the
         POST payload. The controller must treat a missing is_active as false
         (e.g. $request->boolean('is_active')). --}}
    <div class="col-lg-4 d-flex align-items-end">
        <div class="form-check form-switch mb-1">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $questionnaire->is_active ?? true))>
            <label class="form-check-label fw-semibold text-dark" for="is_active">
                <i class="fa-solid fa-bolt me-1 text-danger"></i>Active
            </label>
        </div>
    </div>

    <div class="col-12">
        {{-- Rich-text editor partial. Relies on oldKey / errorKey to
             repopulate on validation failure — the partial handles those
             internally, not via old() here. --}}
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

<div class="row g-3">
    {{-- Campus is read-only — questionnaires are always campus-scoped at
         creation. If you ever need to move one between campuses, that would
         be a separate endpoint/action, not this form. --}}
    <div class="col-md-6">
        <label for="campus" class="form-label mb-1">Campus</label>
        <input type="text" id="campus" class="form-control admin-form-control" value="{{ $questionnaireCampus }}" readonly>
        <div class="form-text">This questionnaire is limited to users assigned to this campus.</div>
    </div>

    <div class="col-md-6">
        <label for="audience" class="form-label mb-1">Questionnaire for</label>
        {{-- Options are campus-scoped (see PHP block). "Both" renders with
             a friendly label; others get a naive pluralization with 's'
             ("Student" → "Students", "Instructor" → "Instructors"). If a new
             audience value is ever added that doesn't pluralize with 's', it
             needs an explicit label mapping here. --}}
        <select id="audience" name="audience" class="form-select admin-form-control @error('audience') is-invalid @enderror" required>
            @foreach ($audienceOptions as $audienceOption)
                <option value="{{ $audienceOption }}" @selected($selectedAudience === $audienceOption)>
                    {{ $audienceOption === 'Both' ? 'Students and instructors' : $audienceOption . 's' }}
                </option>
            @endforeach
        </select>
        @error('audience')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
            {{-- Existing rows render server-side so the page works without
                 JS. The JS below only handles ADD and REMOVE; a page reload
                 always rebuilds from these rendered rows. --}}
            @foreach ($questionRows as $index => $row)
                <div class="border rounded-4 p-3 bg-white shadow-sm question-row" data-question-row>
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <div>
                            <div class="small text-uppercase text-secondary" data-question-title>Question #{{ $index + 1 }}</div>
                            <div class="small text-secondary">Keep the prompt concise.</div>
                        </div>
                        {{-- Remove is a plain button — the JS handler at the
                             bottom prevents removal of the last remaining row. --}}
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-question>
                            <i class="fa-solid fa-trash-can me-1"></i>Remove
                        </button>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Type</label>
                            {{-- The name uses $index directly. Adding/removing
                                 rows renumbers indices via the JS renumberRows()
                                 function — if the numbering ever desyncs (e.g.
                                 after a validation error and add), check that
                                 function. --}}
                            <select name="questions[{{ $index }}][question_type]" class="form-select form-select-sm admin-form-control" data-question-type required>
                                <option value="likert" @selected(($row['question_type'] ?? 'likert') === 'likert')>Likert</option>
                                <option value="raw" @selected(($row['question_type'] ?? '') === 'raw')>Raw answer</option>
                            </select>
                        </div>

                        <div class="col-md-8 d-flex align-items-end">
                            <div class="form-check form-switch mb-1">
                                {{-- No hidden input before this checkbox,
                                     unlike other forms in the codebase. So when
                                     unchecked, is_required is absent from the
                                     POST payload. The controller must default
                                     missing is_required to false. --}}
                                <input type="checkbox" name="questions[{{ $index }}][is_required]" value="1" class="form-check-input" @checked(($row['is_required'] ?? true))>
                                <label class="form-check-label fw-semibold text-dark">Required</label>
                            </div>
                        </div>

                        <div class="col-12">
                            {{-- Rich-text editor for question text. fieldName
                                 and errorKey differ from name — the partial
                                 uses fieldName for the actual submitted name
                                 and errorKey for validation lookup. If validation
                                 errors on question_text don't show, confirm the
                                 controller's error key matches 'questions.N.question_text'
                                 exactly (including the array index). --}}
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
                                'required' => true,
                            ])
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
    {{-- Cancel falls back to a computed route prefix if $routePrefix isn't
         set — matches the pattern from the equipment/chemical views. --}}
    <a href="{{ route(($routePrefix ?? (request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator')).'.feedback.questionnaires.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="fa-solid fa-floppy-disk me-2"></i>Save questionnaire
    </button>
</div>

{{-- Template for new rows. The JS below clones this, replaces __INDEX__ with
     the current row count, and appends. IMPORTANT: this template contains a
     HAND-WRITTEN copy of the rich-text editor markup rather than reusing the
     partial. If the partial's markup ever changes (classes, data-* attributes),
     this template must be updated to match, or newly-added rows will render
     an editor that looks/works differently from server-rendered rows. --}}
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
                {{-- Hand-written rich-text editor markup, matching the partial's
                     output. Any change to the partial's DOM structure must be
                     mirrored here or dynamically-added rows will look different. --}}
                <div class="rich-text-editor rich-text-editor--compact" data-rich-text-editor data-required-field="true">
                    <div class="d-flex justify-content-between align-items-end gap-3 mb-2">
                    <label class="form-label fw-semibold text-dark mb-0">Question text<span class="required-indicator text-danger" aria-hidden="true">*</span><span class="visually-hidden"> (required)</span></label>
                        <div class="small text-secondary">Rich text enabled</div>
                    </div>
                    <textarea name="questions[__INDEX__][question_text]" class="d-none" data-rich-text-input aria-required="true"></textarea>
                    <div class="rich-text-editor__frame">
                        <div class="rich-text-editor__surface" data-rich-text-surface data-placeholder="Write the question prompt here."></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Empty push — likely a leftover from an earlier version that pushed a
     script here. Safe to remove, but harmless if left. --}}
@push('scripts')
@endpush

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rowsContainer = document.getElementById('questionRows');
        const addButton = document.getElementById('addQuestionBtn');
        const template = document.getElementById('questionRowTemplate');

        // Bail out cleanly if any of the three required elements is missing —
        // makes this script a no-op on pages that don't render the questionnaire form.
        if (!rowsContainer || !addButton || !template) {
            return;
        }

        // Renumbers both the visible "Question #N" labels and every input's
        // name attribute. Any new field added to the row markup must also be
        // covered here (via the generic input/select/textarea loop below —
        // which does cover it automatically as long as the field name matches
        // the questions[N] pattern).
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

                    // Only renumbers the FIRST questions[N] segment. Nested
                    // arrays inside would need a more complex regex.
                    field.name = field.name.replace(/questions\[\d+\]/, `questions[${index}]`);

                    // Renumbers id attributes like "question_text_3" → "question_text_1".
                    // Only matches trailing _N, so ids like "row_3_label" would
                    // NOT be renumbered — potential collision if you add multiple
                    // trailing-number ids to the row markup.
                    if (field.id) {
                        field.id = field.id.replace(/_\d+$/, `_${index}`);
                    }
                });
            });
        };

        // Add a new row from the template. After appending, calls the shared
        // window.initRichTextEditors (defined in app.js or similar) to wire up
        // the editor on the new row's textarea. If new rows render a plain
        // textarea instead of the WYSIWYG surface, that function is missing.
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

        // Delegated remove handler so it survives new rows being added.
        // Refuses to remove the last remaining row — that guard prevents
        // an empty questionnaire from being submitted.
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