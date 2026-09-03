<x-admin-layout>
    <x-slot name="title">Form Builder — {{ $survey->title }}</x-slot>

    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px">
        <div class="page-header-left">
            <h1 style="display:flex; align-items:center; gap:10px">
                Form Builder: {{ $survey->title }}
            </h1>
            <p>Design questions, add field types (text, choices, file upload), set requirements, and build your survey form</p>
        </div>
        <div style="display:flex; gap:8px; align-items:center">
            <form method="POST" action="{{ route('admin.surveys.toggle-status', $survey) }}" style="display:inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $survey->is_active ? 'btn-success' : 'btn-outline' }}" style="font-weight:600" title="Click to toggle form status">
                    {{ $survey->is_active ? '● Active' : '○ Closed' }}
                </button>
            </form>
            <button type="button" class="btn btn-outline" onclick="copyPublicLink('{{ url('/surveys/' . $survey->slug) }}')">
                Copy Link
            </button>
            <a href="{{ url('/surveys/' . $survey->slug) }}" target="_blank" class="btn btn-outline" style="color:#2563eb">
                Preview Form ↗
            </a>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">
                ← Back to Surveys
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.surveys.builder.save', $survey) }}" id="surveyBuilderForm">
        @csrf
        @method('PUT')

        {{-- Survey Metadata Card (Google Forms Header Card) --}}
        <div class="card" style="margin-bottom:20px; border-top:6px solid #2563eb; padding:24px">
            @if($survey->banner_image)
                <div style="margin-bottom:16px; text-align:center">
                    <img src="{{ $survey->banner_image }}" alt="Survey Banner" style="max-height:180px; width:100%; object-fit:cover; border-radius:8px">
                </div>
            @endif

            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label required" style="font-size:14px; font-weight:700">Survey Title</label>
                <input type="text" name="title" class="form-control" style="font-size:18px; font-weight:700; color:#0f172a" value="{{ old('title', $survey->title) }}" required>
            </div>

            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label" style="font-weight:600">Form Description / Instructions</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Briefly describe the purpose of this survey...">{{ old('description', $survey->description) }}</textarea>
            </div>

            <div style="display:flex; gap:20px; flex-wrap:wrap; align-items:center; border-top:1px solid #f1f5f9; padding-top:14px">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $survey->is_active ? 'checked' : '' }}>
                    <span style="font-weight:600; font-size:13px; color:#059669">Accepting Responses (Active)</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer">
                    <input type="checkbox" name="allow_multiple_responses" value="1" {{ $survey->allow_multiple_responses ? 'checked' : '' }}>
                    <span style="font-weight:600; font-size:13px">Allow Multiple Submissions</span>
                </label>
            </div>
        </div>

        {{-- Dynamic Questions List Container --}}
        <div id="questionsContainer">
            {{-- Question blocks will be rendered here dynamically via JS --}}
        </div>

        {{-- Add Question Floating Action Bar --}}
        <div style="display:flex; justify-content:space-between; align-items:center; background:#ffffff; padding:16px 24px; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.06); border:1px solid #e2e8f0; margin-top:20px">
            <button type="button" class="btn btn-outline" style="border-style:dashed; border-width:2px; font-weight:700; font-size:14px" onclick="addQuestion()">
                Add New Question
            </button>
            <button type="submit" class="btn btn-primary" style="font-size:15px; font-weight:700; padding:10px 28px">
                Save Form Structure
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
    let questionCount = 0;
    const existingFields = @json($survey->fields);

    document.addEventListener('DOMContentLoaded', function () {
        if (existingFields && existingFields.length > 0) {
            existingFields.forEach(field => {
                addQuestion(field);
            });
        } else {
            // Add initial default question
            addQuestion({
                label: 'Untitled Question',
                field_type: 'text',
                is_required: true
            });
        }
    });

    function addQuestion(data = {}) {
        questionCount++;
        const qId = 'q_' + Date.now() + '_' + questionCount;
        const container = document.getElementById('questionsContainer');

        const label = data.label || '';
        const fieldType = data.field_type || 'text';
        const helpText = data.help_text || '';
        const isRequired = data.is_required !== undefined ? data.is_required : false;
        const fieldDatabaseId = data.id || '';
        const options = Array.isArray(data.options) ? data.options : ['Option 1', 'Option 2'];

        const qCard = document.createElement('div');
        qCard.className = 'card question-card';
        qCard.id = qId;
        qCard.style.cssText = 'margin-bottom:16px; padding:20px; border-left:5px solid #3b82f6; position:relative; transition:all .2s;';

        qCard.innerHTML = `
            <input type="hidden" name="fields[${qId}][id]" value="${fieldDatabaseId}">
            <input type="hidden" name="fields[${qId}][sort_order]" class="sort-order-input" value="${questionCount}">

            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px">
                <div style="flex:1">
                    <input type="text" name="fields[${qId}][label]" class="form-control" style="font-size:15px; font-weight:700" placeholder="Question Title (e.g., What is your full name?)" value="${escapeHtml(label)}" required>
                </div>
                <div style="width:200px">
                    <select name="fields[${qId}][field_type]" class="form-control" onchange="toggleFieldOptions('${qId}', this.value)" style="font-weight:600">
                        <option value="text" ${fieldType === 'text' ? 'selected' : ''}>Short Text Answer</option>
                        <option value="textarea" ${fieldType === 'textarea' ? 'selected' : ''}>Paragraph / Textarea</option>
                        <option value="number" ${fieldType === 'number' ? 'selected' : ''}>Number</option>
                        <option value="select" ${fieldType === 'select' ? 'selected' : ''}>▼ Dropdown Select</option>
                        <option value="radio" ${fieldType === 'radio' ? 'selected' : ''}>Radio (Single Choice)</option>
                        <option value="checkbox" ${fieldType === 'checkbox' ? 'selected' : ''}>Checkbox (Multi Choice)</option>
                        <option value="date" ${fieldType === 'date' ? 'selected' : ''}>Date Picker</option>
                        <option value="file" ${fieldType === 'file' ? 'selected' : ''}>File Upload</option>
                        <option value="image" ${fieldType === 'image' ? 'selected' : ''}>Image Upload</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:12px">
                <input type="text" name="fields[${qId}][help_text]" class="form-control" style="font-size:12px" placeholder="Help text or description (optional)" value="${escapeHtml(helpText)}">
            </div>

            {{-- Options Box for select, radio, checkbox --}}
            <div id="${qId}_options_box" class="options-box" style="display: ${['select', 'radio', 'checkbox'].includes(fieldType) ? 'block' : 'none'}; background:#f8fafc; padding:14px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:14px">
                <label style="font-size:12px; font-weight:700; color:#475569; display:block; margin-bottom:8px">Answer Choices / Options</label>
                <div id="${qId}_options_list">
                    ${options.map((opt, i) => renderOptionRow(qId, i, opt)).join('')}
                </div>
                <button type="button" class="btn btn-xs btn-outline" style="margin-top:8px" onclick="addOptionRow('${qId}')">
                    Add Option
                </button>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:12px; margin-top:12px">
                <div style="display:flex; gap:14px; align-items:center">
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer">
                        <input type="checkbox" name="fields[${qId}][is_required]" value="1" ${isRequired ? 'checked' : ''}>
                        <span style="font-size:13px; font-weight:600; color:#dc2626">Required Question</span>
                    </label>
                </div>
                <div style="display:flex; gap:6px">
                    <button type="button" class="btn btn-xs btn-outline" title="Move Up" onclick="moveQuestionUp('${qId}')">▲ Up</button>
                    <button type="button" class="btn btn-xs btn-outline" title="Move Down" onclick="moveQuestionDown('${qId}')">▼ Down</button>
                    <button type="button" class="btn btn-xs btn-outline danger" title="Delete Question" onclick="deleteQuestion('${qId}')"><i class="fa-solid fa-trash"></i> Remove</button>
                </div>
            </div>
        `;

        container.appendChild(qCard);
        updateSortOrders();
    }

    function renderOptionRow(qId, idx, val = '') {
        return `
            <div class="option-row" style="display:flex; align-items:center; gap:8px; margin-bottom:6px">
                <span style="font-size:12px; color:var(--text-muted)">●</span>
                <input type="text" name="fields[${qId}][options][]" class="form-control" style="font-size:13px" placeholder="Option Text" value="${escapeHtml(val)}" required>
                <button type="button" class="btn btn-xs btn-outline danger" style="padding:2px 6px" onclick="this.closest('.option-row').remove()">&times;</button>
            </div>
        `;
    }

    function addOptionRow(qId) {
        const list = document.getElementById(qId + '_options_list');
        const count = list.children.length + 1;
        const div = document.createElement('div');
        div.innerHTML = renderOptionRow(qId, count, 'Option ' + count);
        list.appendChild(div.firstElementChild);
    }

    function toggleFieldOptions(qId, fieldType) {
        const box = document.getElementById(qId + '_options_box');
        if (['select', 'radio', 'checkbox'].includes(fieldType)) {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }

    function deleteQuestion(qId) {
        if (confirm('Delete this question block?')) {
            document.getElementById(qId).remove();
            updateSortOrders();
        }
    }

    function moveQuestionUp(qId) {
        const card = document.getElementById(qId);
        const prev = card.previousElementSibling;
        if (prev) {
            card.parentNode.insertBefore(card, prev);
            updateSortOrders();
        }
    }

    function moveQuestionDown(qId) {
        const card = document.getElementById(qId);
        const next = card.nextElementSibling;
        if (next) {
            card.parentNode.insertBefore(next, card);
            updateSortOrders();
        }
    }

    function updateSortOrders() {
        const cards = document.querySelectorAll('.question-card');
        cards.forEach((card, index) => {
            const input = card.querySelector('.sort-order-input');
            if (input) input.value = index + 1;
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function copyPublicLink(url) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Public link copied to clipboard:\n' + url);
        }).catch(err => {
            prompt('Copy this link:', url);
        });
    }
    </script>
    @endpush
</x-admin-layout>
