<!-- Sequence Question Section -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-primary">
            <i class="icofont icofont-numbered me-2"></i>{{ __('Sequence Items') }}
            <small class="text-muted">({{ __('Arrange in correct order') }})</small>
        </h5>
        <button type="button" class="btn btn-success btn-sm" id="addSequenceItemBtn">
            <i class="icofont icofont-plus me-1"></i> {{ __('Add Item') }}
        </button>
    </div>

    <div id="sequenceItemsContainer">
        @php
        $sequenceItems = $question->answers()->orderBy('id')->get();
        @endphp

        @foreach($sequenceItems as $index => $item)
        <div class="sequence-item mb-3" data-index="{{ $index }}">
            <div class="row align-items-start">
                <!-- Display Number -->
                <div class="col-auto">
                    <span class="key-badge bg-info text-white">{{ $index + 1 }})</span>
                </div>

                <!-- Item Text -->
                <div class="col">
                    <textarea name="sequence_items[{{ $index }}][text]"
                        class="form-control sequence-text-input"
                        rows="2"
                        placeholder="{{ __('Item text (supports LaTeX)') }}"
                        required>{{ old("sequence_items.$index.text", $item->text) }}</textarea>
                    <input type="hidden" name="sequence_items[{{ $index }}][id]" value="{{ $item->id }}">
                </div>

                <!-- Correct Order -->
                <div class="col-auto">
                    <label class="form-label small">{{ __('Correct Order') }}</label>
                    <input type="number"
                        name="sequence_items[{{ $index }}][order]"
                        class="form-control form-control-sm"
                        min="1"
                        max="99"
                        value="{{ old("sequence_items.$index.order", $item->order) }}"
                        style="width: 70px;"
                        required>
                </div>

                <!-- Remove Button -->
                <div class="col-auto">
                    <button type="button" class="btn btn-danger btn-sm remove-sequence-item">
                        <i class="icofont icofont-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Help Text -->
    <div class="alert alert-info mt-3">
        <i class="icofont icofont-info-circle me-2"></i>
        <strong>{{ __('Instructions') }}:</strong>
        <ul class="mb-0 mt-2">
            <li>{{ __('Items will be displayed in random order to students') }}</li>
            <li>{{ __('Enter the correct order numbers (1, 2, 3, etc.) for each item') }}</li>
            <li>{{ __('Students must arrange items in the correct sequence') }}</li>
        </ul>
    </div>

    @error('sequence_items')
    <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<!-- Sequence Item Template -->
<template id="sequence-item-template">
    <div class="sequence-item mb-3" data-index="{index}">
        <div class="row align-items-start">
            <div class="col-auto">
                <span class="key-badge bg-info text-white">{display})</span>
            </div>
            <div class="col">
                <textarea name="sequence_items[{index}][text]"
                    class="form-control sequence-text-input"
                    rows="2"
                    placeholder="{{ __('Item text (supports LaTeX)') }}"
                    required></textarea>
                <input type="hidden" name="sequence_items[{index}][id]" value="">
            </div>
            <div class="col-auto">
                <label class="form-label small">{{ __('Correct Order') }}</label>
                <input type="number"
                    name="sequence_items[{index}][order]"
                    class="form-control form-control-sm"
                    min="1"
                    max="99"
                    value="{order}"
                    style="width: 70px;"
                    required>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger btn-sm remove-sequence-item">
                    <i class="icofont icofont-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    (function() {
        const container = document.getElementById('sequenceItemsContainer');
        const addBtn = document.getElementById('addSequenceItemBtn');
        const template = document.getElementById('sequence-item-template').innerHTML;

        let index = container.querySelectorAll('.sequence-item').length;

        function reindexItems() {
            const items = container.querySelectorAll('.sequence-item');
            items.forEach((item, i) => {
                item.dataset.index = i;

                // Update display number badge
                const badge = item.querySelector('.key-badge');
                if (badge) badge.textContent = (i + 1) + ')';

                // Update textarea name
                const textarea = item.querySelector('.sequence-text-input');
                if (textarea) {
                    textarea.name = `sequence_items[${i}][text]`;
                }

                // Update hidden id name
                const hiddenId = item.querySelector('input[type="hidden"]');
                if (hiddenId) {
                    hiddenId.name = `sequence_items[${i}][id]`;
                }

                // Update order input name
                const orderInput = item.querySelector('input[type="number"]');
                if (orderInput) {
                    orderInput.name = `sequence_items[${i}][order]`;
                }
            });

            index = items.length;
        }

        // Add new sequence item
        addBtn.addEventListener('click', function() {
            const displayNumber = index + 1;
            const suggestedOrder = index + 1;

            const html = template
                .replaceAll('{index}', index)
                .replaceAll('{display}', displayNumber)
                .replaceAll('{order}', suggestedOrder);

            container.insertAdjacentHTML('beforeend', html);

            // Add math preview listener
            const newItem = container.lastElementChild;
            const textarea = newItem.querySelector('.sequence-text-input');
            const preview = newItem.querySelector('.sequence-text-preview');

            if (textarea && preview) {
                textarea.addEventListener('input', function() {
                    updateMathPreview(textarea, preview);
                });
            }

            reindexItems();
        });

        // Remove sequence item
        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-sequence-item')) {
                const items = container.querySelectorAll('.sequence-item');

                if (items.length <= 2) {
                    alert('{{ __("At least 2 items are required for sequence") }}');
                    return;
                }

                e.target.closest('.sequence-item').remove();
                reindexItems();
            }
        });

        // Validate order numbers on form submit
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            const orderInputs = container.querySelectorAll('input[type="number"]');
            const orders = Array.from(orderInputs).map(input => parseInt(input.value));

            // Check for duplicates
            const hasDuplicates = orders.some((order, idx) => orders.indexOf(order) !== idx);

            if (hasDuplicates) {
                e.preventDefault();
                alert('{{ __("Order numbers must be unique! Please check for duplicates.") }}');
                return false;
            }

            // Check if all orders are filled
            if (orders.some(order => isNaN(order) || order < 1)) {
                e.preventDefault();
                alert('{{ __("All items must have a valid order number (1 or greater)") }}');
                return false;
            }
        });

        // Initialize math previews for existing items
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = container.querySelectorAll('.sequence-text-input');
            textareas.forEach((textarea) => {
                const preview = textarea.closest('.sequence-item').querySelector('.sequence-text-preview');

                if (preview) {
                    textarea.addEventListener('input', function() {
                        updateMathPreview(textarea, preview);
                    });

                    // Initial render
                    if (window.MathJax) {
                        MathJax.typesetPromise([preview]);
                    }
                }
            });
        });
    })();
</script>
@endpush