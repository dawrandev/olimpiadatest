@extends('layouts.admin.main')

@section('title', __('Upload Students'))

@section('content')
<x-admin.breadcrumb :title="__('Upload Students')">
    <a href="#" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#formatGuideModal">
        <i class="icofont icofont-question-circle"></i>
        {{ __('Format Guide') }}
    </a>
    <a href="#" class="btn btn-success me-2" id="downloadTemplateBtn">
        <i class="icofont icofont-download"></i>
        {{ __('Download Template') }}
    </a>
</x-admin.breadcrumb>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ __('Upload Students from Excel') }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="icofont icofont-info-circle"></i>
                        {{ __('Fakultet va guruh avtomatik ravishda Excel fayldan olinadi. Agar mavjud bo\'lmasa, yangi yaratiladi.') }}
                    </div>

                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf

                        <!-- File Upload -->
                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">{{ __('Excel File') }} <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls">
                            <small class="text-muted d-block mt-1">{{ __('Supported: .xlsx, .xls (Max: 10MB)') }}</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Progress Section -->
                        <div class="mb-3 d-none" id="progressSection">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span id="progressStatus">{{ __('Kutilmoqda...') }}</span>
                                        <span id="progressPercent">0%</span>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar"
                                            role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="small text-muted">
                                        <span id="progressDetails"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="text-end pt-3 border-top">
                            <button type="reset" class="btn btn-outline-secondary me-2" id="resetBtn">
                                <i class="icofont icofont-refresh"></i>
                                {{ __('Reset') }}
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="icofont icofont-upload"></i>
                                {{ __('Upload Students') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Uploads -->
            @if(isset($recentUploads) && $recentUploads->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('So\'nggi yuklamalar') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Fayl') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Natija') }}</th>
                                    <th>{{ __('Vaqt') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentUploads as $upload)
                                <tr>
                                    <td>{{ Str::limit($upload->file_name, 30) }}</td>
                                    <td>
                                        @if($upload->status === 'completed')
                                            <span class="badge bg-success">{{ __('Tugallandi') }}</span>
                                        @elseif($upload->status === 'processing')
                                            <span class="badge bg-warning">{{ __('Jarayonda') }}</span>
                                        @elseif($upload->status === 'failed')
                                            <span class="badge bg-danger">{{ __('Xato') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Kutilmoqda') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <span class="text-success">+{{ $upload->uploaded_count }}</span>
                                            @if($upload->skipped_count > 0)
                                                / <span class="text-warning">~{{ $upload->skipped_count }}</span>
                                            @endif
                                            @if($upload->error_count > 0)
                                                / <span class="text-danger">-{{ $upload->error_count }}</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td><small>{{ $upload->created_at->format('d.m.Y H:i') }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Upload Result') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="resultContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-primary">{{ __('View Students') }}</a>
            </div>
        </div>
    </div>
</div>

<!-- Format Guide Modal -->
<div class="modal fade" id="formatGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="icofont icofont-book-alt"></i>
                    {{ __('Excel Format Guide') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="icofont icofont-info-circle"></i>
                    <strong>{{ __('Important') }}:</strong> {{ __('Excel fayl quyidagi ustunlarni o\'z ichiga olishi kerak. Ustunlar pozitsiyasi bo\'yicha o\'qiladi.') }}
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="80">{{ __('Column') }}</th>
                                <th>{{ __('Column Name') }}</th>
                                <th>{{ __('Saved To') }}</th>
                                <th>{{ __('Example') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center"><strong>A</strong></td>
                                <td><strong>Talaba ID</strong></td>
                                <td>students.student_id</td>
                                <td><code>123456</code></td>
                            </tr>
                            <tr>
                                <td class="text-center"><strong>B</strong></td>
                                <td><strong>To'liq ismi</strong></td>
                                <td>students.full_name</td>
                                <td><code>Sipatdinov Dawranbek Islamatdin uli</code></td>
                            </tr>
                            <tr>
                                <td class="text-center"><strong>C</strong></td>
                                <td><strong>Pasport raqami</strong></td>
                                <td>users.password (hash), students.passport</td>
                                <td><code>AB1234567</code></td>
                            </tr>
                            <tr>
                                <td class="text-center"><strong>D</strong></td>
                                <td><strong>JSHSHIR-kod</strong></td>
                                <td>users.login, students.jshshir</td>
                                <td><code>12345678901234</code></td>
                            </tr>
                            <tr>
                                <td class="text-center"><strong>E</strong></td>
                                <td><strong>Kurs</strong></td>
                                <td>students.course</td>
                                <td><code>1-kurs</code></td>
                            </tr>
                            <tr>
                                <td class="text-center"><strong>F</strong></td>
                                <td><strong>Fakultet</strong></td>
                                <td>faculties.name (topish/yaratish)</td>
                                <td><code>Fizika-matematika fakulteti</code></td>
                            </tr>
                            <tr>
                                <td class="text-center"><strong>G</strong></td>
                                <td><strong>Guruh</strong></td>
                                <td>groups.name (topish/yaratish)</td>
                                <td><code>FM-21</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="text-primary mt-4 mb-3">
                    <i class="icofont icofont-info-circle"></i>
                    {{ __('Muhim ma\'lumotlar') }}:
                </h6>
                <ul class="list-group">
                    <li class="list-group-item">
                        <i class="icofont icofont-check text-success"></i>
                        <strong>Login:</strong> JSHSHIR-kod (D ustuni) login sifatida ishlatiladi
                    </li>
                    <li class="list-group-item">
                        <i class="icofont icofont-check text-success"></i>
                        <strong>Parol:</strong> Pasport raqami (C ustuni) parol sifatida ishlatiladi (hash qilinadi)
                    </li>
                    <li class="list-group-item">
                        <i class="icofont icofont-check text-success"></i>
                        <strong>Fakultet:</strong> Agar fakultet mavjud bo'lmasa, avtomatik yaratiladi
                    </li>
                    <li class="list-group-item">
                        <i class="icofont icofont-check text-success"></i>
                        <strong>Guruh:</strong> Agar guruh mavjud bo'lmasa, fakultetga bog'langan holda yaratiladi
                    </li>
                    <li class="list-group-item">
                        <i class="icofont icofont-check text-success"></i>
                        <strong>Duplikat:</strong> JSHSHIR allaqachon mavjud bo'lsa, o'tkazib yuboriladi (skip)
                    </li>
                    <li class="list-group-item">
                        <i class="icofont icofont-check text-success"></i>
                        <strong>Queue:</strong> Katta fayllar orqa fonda ishlanadi, progress real-time ko'rsatiladi
                    </li>
                </ul>

                <div class="alert alert-warning mt-3">
                    <i class="icofont icofont-light-bulb"></i>
                    <strong>{{ __('Tip') }}:</strong> {{ __('Excel faylda A-G ustunlari ketma-ket joylashgan bo\'lishi kerak') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="icofont icofont-close"></i>
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let uploadId = null;
        let progressInterval = null;

        // Download Template
        $('#downloadTemplateBtn').on('click', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('admin.students.downloadTemplate') }}";
        });

        // Form Submit
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            let formData = new FormData(this);
            let submitBtn = $('#submitBtn');
            let progressSection = $('#progressSection');

            // Disable submit button
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Yuklanmoqda...") }}');
            progressSection.removeClass('d-none');
            updateProgress(0, '{{ __("Fayl yuklanmoqda...") }}', '');

            $.ajax({
                url: "{{ route('admin.students.uploadExcel') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success && response.upload_id) {
                        uploadId = response.upload_id;
                        updateProgress(10, '{{ __("Navbatda...") }}', '{{ __("Fayl qabul qilindi, jarayon boshlanmoqda...") }}');
                        startProgressPolling();
                    }
                },
                error: function(xhr) {
                    progressSection.addClass('d-none');
                    submitBtn.prop('disabled', false).html('<i class="icofont icofont-upload"></i> {{ __("Upload Students") }}');

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        if (errors) {
                            $.each(errors, function(field, messages) {
                                let input = $('[name="' + field + '"]');
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(messages[0]);
                            });
                        }
                        if (xhr.responseJSON.message) {
                            alert(xhr.responseJSON.message);
                        }
                    } else {
                        alert('{{ __("Server error occurred") }}');
                    }
                }
            });
        });

        function startProgressPolling() {
            progressInterval = setInterval(checkProgress, 1000);
        }

        function stopProgressPolling() {
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
            }
        }

        function checkProgress() {
            if (!uploadId) return;

            $.get("{{ url('admin/students/upload-progress') }}/" + uploadId, function(data) {
                let statusText = '';
                let details = '';

                if (data.status === 'pending') {
                    statusText = '{{ __("Navbatda kutilmoqda...") }}';
                } else if (data.status === 'processing') {
                    statusText = '{{ __("Jarayonda...") }}';
                    details = `${data.processed_rows} / ${data.total_rows} {{ __("qator") }}`;
                } else if (data.status === 'completed') {
                    statusText = '{{ __("Tugallandi!") }}';
                    stopProgressPolling();
                    showResultModal(data);
                    resetForm();
                } else if (data.status === 'failed') {
                    statusText = '{{ __("Xatolik!") }}';
                    stopProgressPolling();
                    showErrorModal(data.error_message);
                    resetForm();
                }

                updateProgress(data.progress_percent, statusText, details);

            }).fail(function() {
                stopProgressPolling();
                resetForm();
            });
        }

        function updateProgress(percent, status, details) {
            $('#progressBar').css('width', percent + '%');
            $('#progressPercent').text(percent + '%');
            $('#progressStatus').text(status);
            $('#progressDetails').text(details);
        }

        function resetForm() {
            $('#submitBtn').prop('disabled', false).html('<i class="icofont icofont-upload"></i> {{ __("Upload Students") }}');
            uploadId = null;

            setTimeout(function() {
                $('#progressSection').addClass('d-none');
                $('#uploadForm')[0].reset();
            }, 2000);
        }

        function showErrorModal(message) {
            let html = `<div class="alert alert-danger">
                <i class="icofont icofont-close-circled"></i>
                <strong>{{ __("Xatolik") }}:</strong> ${message}
            </div>`;
            $('#resultContent').html(html);
            $('#resultModal').modal('show');
        }

        function showResultModal(response) {
            let html = '';

            if (response.uploaded_count > 0) {
                html += `<div class="alert alert-success">
                    <i class="icofont icofont-check-circled"></i>
                    <strong>${response.uploaded_count}</strong> {{ __('students uploaded successfully') }}
                </div>`;
            }

            if (response.skipped_count > 0) {
                html += `<div class="alert alert-warning">
                    <i class="icofont icofont-warning-alt"></i>
                    <strong>${response.skipped_count}</strong> {{ __('students skipped (JSHSHIR already exists)') }}
                </div>`;
            }

            if (response.error_count > 0) {
                html += `<div class="alert alert-danger">
                    <i class="icofont icofont-close-circled"></i>
                    <strong>${response.error_count}</strong> {{ __('students with errors') }}
                </div>`;

                if (response.errors && response.errors.length > 0) {
                    html += `<div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th width="100">{{ __('Row #') }}</th>
                                    <th>{{ __('Error') }}</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    response.errors.forEach(function(error) {
                        html += `<tr>
                            <td class="text-center"><strong>${error.row}</strong></td>
                            <td>${error.message}</td>
                        </tr>`;
                    });

                    html += `</tbody></table></div>`;
                }
            }

            if (response.created_faculties && response.created_faculties.length > 0) {
                html += `<div class="alert alert-info mt-3">
                    <i class="icofont icofont-plus-circle"></i>
                    <strong>{{ __('Created faculties') }}:</strong> ${response.created_faculties.join(', ')}
                </div>`;
            }

            if (response.created_groups && response.created_groups.length > 0) {
                html += `<div class="alert alert-info">
                    <i class="icofont icofont-plus-circle"></i>
                    <strong>{{ __('Created groups') }}:</strong> ${response.created_groups.join(', ')}
                </div>`;
            }

            if (response.uploaded_count === 0 && response.skipped_count === 0 && response.error_count === 0) {
                html = `<div class="alert alert-info">
                    <i class="icofont icofont-info-circle"></i>
                    {{ __('Fayl bo\'sh yoki hech qanday ma\'lumot topilmadi') }}
                </div>`;
            }

            $('#resultContent').html(html);
            $('#resultModal').modal('show');
        }

        // Reset button
        $('#resetBtn').on('click', function() {
            stopProgressPolling();
            $('#progressSection').addClass('d-none');
            uploadId = null;
        });
    });
</script>
@endpush

@endsection
