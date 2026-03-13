<!-- Matching Question Section -->
<div class="matching-container mb-4">
    <h5 class="text-primary mb-4">
        <i class="icofont icofont-link me-2"></i>{{ __('Matching Elements') }}
    </h5>

    <!-- Titles -->
    <div class="row mb-4">
        <div class="col-md-6">
            <label class="form-label fw-bold">{{ __('Left Side Title') }}</label>
            <input type="text"
                name="left_items_title"
                class="form-control"
                value="{{ old('left_items_title', $question->left_items_title) }}"
                placeholder="{{ __('e.g., Questions') }}"
                required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">{{ __('Right Side Title') }}</label>
            <input type="text"
                name="right_items_title"
                class="form-control"
                value="{{ old('right_items_title', $question->right_items_title) }}"
                placeholder="{{ __('e.g., Answers') }}"
                required>
        </div>
    </div>

    <div class="row">
        <!-- Left Side -->
        <div class="col-md-6">
            <div class="matching-side">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-primary mb-0">{{ __('Left Side Items') }}</h6>
                    <button type="button" class="btn btn-success btn-sm" id="addLeftItemBtn">
                        <i class="icofont icofont-plus me-1"></i> {{ __('Add Item') }}
                    </button>
                </div>

                <div id="leftItemsContainer">
                    @php
                    $leftItems = $question->matchingPairs()
                    ->where('side', 'left')
                    ->orderBy('order')
                    ->get();
                    @endphp

                    @foreach($leftItems as $index => $item)
                    <div class="matching-item mb-2" data-index="{{ $index }}">
                        <div class="d-flex align-items-start gap-2">
                            <span class="key-badge bg-primary text-white mt-2">{{ $item->key }}</span>
                            <div class="flex-grow-1">
                                <textarea name="left_items[{{ $index }}][text]"
                                    class="form-control left-item-input"
                                    rows="2"
                                    placeholder="{{ __('Item text') }}"
                                    required>{{ old("left_items.$index.text", $item->text) }}</textarea>

                                <input type="hidden" name="left_items[{{ $index }}][key]" value="{{ $item->key }}">
                                <input type="hidden" name="left_items[{{ $index }}][id]" value="{{ $item->id }}">
                                <input type="hidden" name="left_items[{{ $index }}][order]" value="{{ $index }}">
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-left-item mt-2">
                                <i class="icofont icofont-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Side -->
        <div class="col-md-6">
            <div class="matching-side">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-success mb-0">{{ __('Right Side Items') }}</h6>
                    <button type="button" class="btn btn-success btn-sm" id="addRightItemBtn">
                        <i class="icofont icofont-plus me-1"></i> {{ __('Add Item') }}
                    </button>
                </div>

                <div id="rightItemsContainer">
                    @php
                    $rightItems = $question->matchingPairs()
                    ->where('side', 'right')
                    ->orderBy('order')
                    ->get();
                    @endphp

                    @foreach($rightItems as $index => $item)
                    <div class="matching-item mb-2" data-index="{{ $index }}">
                        <div class="d-flex align-items-start gap-2">
                            <span class="key-badge bg-success text-white mt-2">{{ $item->key }}</span>
                            <div class="flex-grow-1">
                                <textarea name="right_items[{{ $index }}][text]"
                                    class="form-control right-item-input"
                                    rows="2"
                                    placeholder="{{ __('Item text') }}"
                                    required>{{ old("right_items.$index.text", $item->text) }}</textarea>

                                <input type="hidden" name="right_items[{{ $index }}][key]" value="{{ $item->key }}">
                                <input type="hidden" name="right_items[{{ $index }}][id]" value="{{ $item->id }}">
                                <input type="hidden" name="right_items[{{ $index }}][order]" value="{{ $index }}">
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-right-item mt-2">
                                <i class="icofont icofont-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Answer Variants - RADIO BUTTON (faqat bitta to'g'ri javob) -->
    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-info">
                <i class="icofont icofont-check-circled me-2"></i>{{ __('Answer Variants') }}
                <small class="text-muted">({{ __('e.g., А-1, Б-2, В-3') }})</small>
            </h6>
            <button type="button" class="btn btn-info btn-sm" id="addAnswerVariantBtn">
                <i class="icofont icofont-plus me-1"></i> {{ __('Add Variant') }}
            </button>
        </div>

        <div id="answerVariantsContainer">
            @foreach($question->answers as $index => $answer)
            <div class="answer-row mb-2" data-index="{{ $index }}">
                <div class="d-flex align-items-center gap-2">
                    <!-- RADIO BUTTON - faqat bitta to'g'ri javob -->
                    <input type="radio"
                        class="form-check-input correct-variant"
                        name="correct_variants"
                        value="{{ $index }}"
                        id="variant_{{ $index }}"
                        {{ $answer->is_correct ? 'checked' : '' }}>

                    <input type="text"
                        name="answer_variants[{{ $index }}][text]"
                        class="form-control"
                        placeholder="{{ __('e.g., А-1, Б-2, В-3') }}"
                        value="{{ old("answer_variants.$index.text", $answer->text) }}"
                        required>

                    <input type="hidden" name="answer_variants[{{ $index }}][id]" value="{{ $answer->id }}">

                    <button type="button" class="btn btn-danger btn-sm remove-variant">
                        <i class="icofont icofont-trash"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Templates -->
<template id="left-item-template">
    <div class="matching-item mb-2" data-index="{index}">
        <div class="d-flex align-items-start gap-2">
            <span class="key-badge bg-primary text-white mt-2">{key}</span>
            <div class="flex-grow-1">
                <textarea name="left_items[{index}][text]"
                    class="form-control left-item-input"
                    rows="2"
                    placeholder="{{ __('Item text') }}"
                    required></textarea>

                <input type="hidden" name="left_items[{index}][key]" value="{key}">
                <input type="hidden" name="left_items[{index}][id]" value="">
                <input type="hidden" name="left_items[{index}][order]" value="{index}">
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-left-item mt-2">
                <i class="icofont icofont-trash"></i>
            </button>
        </div>
    </div>
</template>

<template id="right-item-template">
    <div class="matching-item mb-2" data-index="{index}">
        <div class="d-flex align-items-start gap-2">
            <span class="key-badge bg-success text-white mt-2">{key}</span>
            <div class="flex-grow-1">
                <textarea name="right_items[{index}][text]"
                    class="form-control right-item-input"
                    rows="2"
                    placeholder="{{ __('Item text') }}"
                    required></textarea>

                <input type="hidden" name="right_items[{index}][key]" value="{key}">
                <input type="hidden" name="right_items[{index}][id]" value="">
                <input type="hidden" name="right_items[{index}][order]" value="{index}">
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-right-item mt-2">
                <i class="icofont icofont-trash"></i>
            </button>
        </div>
    </div>
</template>

<template id="answer-variant-template">
    <div class="answer-row mb-2" data-index="{index}">
        <div class="d-flex align-items-center gap-2">
            <!-- RADIO BUTTON -->
            <input type="radio"
                class="form-check-input correct-variant"
                name="correct_variants"
                value="{index}"
                id="variant_{index}">

            <input type="text"
                name="answer_variants[{index}][text]"
                class="form-control"
                placeholder="{{ __('e.g., А-1, Б-2, В-3') }}"
                required>

            <input type="hidden" name="answer_variants[{index}][id]" value="">

            <button type="button" class="btn btn-danger btn-sm remove-variant">
                <i class="icofont icofont-trash"></i>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
    (function() {
        const leftContainer = document.getElementById('leftItemsContainer');
        const rightContainer = document.getElementById('rightItemsContainer');
        const variantsContainer = document.getElementById('answerVariantsContainer');

        const addLeftBtn = document.getElementById('addLeftItemBtn');
        const addRightBtn = document.getElementById('addRightItemBtn');
        const addVariantBtn = document.getElementById('addAnswerVariantBtn');

        const leftTemplate = document.getElementById('left-item-template').innerHTML;
        const rightTemplate = document.getElementById('right-item-template').innerHTML;
        const variantTemplate = document.getElementById('answer-variant-template').innerHTML;

        const russianLetters = 'АБВГДЕЖЗИКЛМНОПРСТУФХЦЧШЩЭЮЯ';

        let leftIndex = leftContainer.querySelectorAll('.matching-item').length;
        let rightIndex = rightContainer.querySelectorAll('.matching-item').length;
        let variantIndex = variantsContainer.querySelectorAll('.answer-row').length;

        // Add Left Item
        addLeftBtn.addEventListener('click', function() {
            const key = russianLetters[leftIndex] || (leftIndex + 1).toString();
            const html = leftTemplate
                .replaceAll('{index}', leftIndex)
                .replaceAll('{key}', key);

            leftContainer.insertAdjacentHTML('beforeend', html);
            leftIndex++;
        });

        // Add Right Item
        addRightBtn.addEventListener('click', function() {
            const key = (rightIndex + 1).toString();
            const html = rightTemplate
                .replaceAll('{index}', rightIndex)
                .replaceAll('{key}', key);

            rightContainer.insertAdjacentHTML('beforeend', html);
            rightIndex++;
        });

        // Add Answer Variant
        addVariantBtn.addEventListener('click', function() {
            const html = variantTemplate.replaceAll('{index}', variantIndex);
            variantsContainer.insertAdjacentHTML('beforeend', html);
            variantIndex++;
        });

        // Remove handlers
        leftContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-left-item')) {
                if (leftContainer.querySelectorAll('.matching-item').length <= 1) {
                    alert('{{ __("At least 1 item is required") }}');
                    return;
                }
                e.target.closest('.matching-item').remove();
            }
        });

        rightContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-right-item')) {
                if (rightContainer.querySelectorAll('.matching-item').length <= 1) {
                    alert('{{ __("At least 1 item is required") }}');
                    return;
                }
                e.target.closest('.matching-item').remove();
            }
        });

        variantsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-variant')) {
                if (variantsContainer.querySelectorAll('.answer-row').length <= 1) {
                    alert('{{ __("At least 1 variant is required") }}');
                    return;
                }
                e.target.closest('.answer-row').remove();
            }
        });

        // Form validation - at least one correct variant must be selected
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            const checkedRadio = variantsContainer.querySelector('input[name="correct_variants"]:checked');
            if (!checkedRadio) {
                e.preventDefault();
                alert('{{ __("Please select the correct answer variant") }}');
                return false;
            }
        });
    })();
</script>
@endpush