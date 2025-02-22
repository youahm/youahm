<style>
    .imgnew {
        height: 150px !important;
        width: 150px !important;
    }

    .new {
        height: 100% !important;
        width: 100% !important;
    }
</style>
@php
    $editApplicationPermission = user()->permission('edit_job_application');
    $deleteApplicationPermission = user()->permission('delete_job_application');
@endphp

<div id="task-detail-section">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-9 col-10">
                            <h1 class="heading-h1">
                                {{ ($application->full_name) }}</h1>
                        </div>

                        <div class="col-lg-3 col-md-2 col-2 text-right">
                            @if ($editApplicationPermission == 'all'
                                || ($editApplicationPermission == 'added' && $application->added_by == user()->id)
                                || ($editApplicationPermission == 'owned' && user()->id == $application->job->recruiter_id)
                                || ($editApplicationPermission == 'both' && user()->id == $application->job->recruiter_id
                                || $application->added_by == user()->id) ||
                                ($deleteApplicationPermission == 'all'
                                || ($deleteApplicationPermission == 'added' && $application->added_by == user()->id)
                                || ($deleteApplicationPermission == 'owned' && user()->id == $application->job->recruiter_id)
                                || ($deleteApplicationPermission == 'both' && user()->id == $application->job->recruiter_id) || $application->added_by == user()->id))
                                <div class="dropdown">
                                    <button
                                        class="btn btn-lg f-14 px-2 py-1 text-dark-grey text-capitalize rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                         aria-labelledby="dropdownMenuLink" tabindex="0">
                                        @if ($editApplicationPermission == 'all'
                                            || ($editApplicationPermission == 'added' && $application->added_by == user()->id)
                                            || ($editApplicationPermission == 'owned' && user()->id == $application->job->recruiter_id)
                                            || ($editApplicationPermission == 'both' && user()->id == $application->job->recruiter_id)
                                            || $application->added_by == user()->id)
                                            <a class="dropdown-item"
                                               id="archive_job">@lang('recruit::modules.jobApplication.archiveApplication')</a>
                                        @endif
                                        @if ($editApplicationPermission == 'all'
                                            || ($editApplicationPermission == 'added' && $application->added_by == user()->id)
                                            || ($editApplicationPermission == 'owned' && user()->id == $application->job->recruiter_id)
                                            || ($editApplicationPermission == 'both' && user()->id == $application->job->recruiter_id)
                                            || $application->added_by == user()->id)
                                            <a class="dropdown-item openRightModal"
                                               href="{{ route('job-applications.edit', $application->id) }}">@lang('app.edit')</a>
                                        @endif
                                        @if ($deleteApplicationPermission == 'all'
                                            || ($deleteApplicationPermission == 'added' && $application->added_by == user()->id)
                                            || ($deleteApplicationPermission == 'owned' && user()->id == $application->job->recruiter_id)
                                            || ($deleteApplicationPermission == 'both' && user()->id == $application->job->recruiter_id)
                                            || $application->added_by == user()->id)
                                            <a class="dropdown-item delete-table-row">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-9 col-lg-8 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">

                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('recruit::modules.job.jobTitle')</p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    {{ ($application->job->title) ?? '--' }}
                                </p>
                            </div>

                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('recruit::modules.jobApplication.applicantEmail')
                                </p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    {{ ($application->email ?? '--') }}
                                </p>
                            </div>

                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('recruit::modules.jobApplication.applicantPhone')
                                </p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    {{ $application->phone ?? '--' }}
                                </p>
                            </div>
                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('recruit::modules.jobApplication.location')
                                </p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    {{ $application->location->location }}
                                </p>
                            </div>
                            @if ($application->date_of_birth)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('recruit::modules.jobApplication.dateOfBirth')
                                    </p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ $application->date_of_birth->format($company->date_format) }}
                                    </p>
                                </div>
                            @endif

                            @if ($application->gender)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('recruit::modules.jobApplication.gender')
                                    </p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ ($application->gender ?? '--') }}
                                    </p>
                                </div>
                            @endif

                            @if ($application->total_experience)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('recruit::modules.jobApplication.experience')
                                    </p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        @if ($application->total_experience == 'fresher')
                                            {{ ($application->total_experience) }}
                                        @else
                                            {{ ($application->total_experience) }} @lang('recruit::modules.jobApplication.years')
                                        @endif

                                    </p>
                                </div>
                            @endif

                            @if ($application->current_location)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('recruit::modules.jobApplication.currentLocation')
                                    </p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ ($application->current_location ?? '--') }}
                                    </p>
                                </div>
                            @endif

                            @if ($application->current_ctc)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('recruit::modules.jobApplication.currentCtc')
                                    </p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ currency_format($application->current_ctc, $currencySymbol->id) }}
                                        @lang('recruit::modules.joboffer.per') {{ $application->currenct_ctc_rate }}
                                    </p>
                                </div>
                            @endif

                            @if ($application->expected_ctc)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('recruit::modules.jobApplication.expectedCtc')
                                    </p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ currency_format($application->expected_ctc, $currencySymbol->id) }}
                                        @lang('recruit::modules.joboffer.per') {{ $application->expected_ctc_rate }}
                                    </p>
                                </div>
                            @endif

                            @if ($application->notice_period)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('recruit::modules.jobApplication.noticePeriod')
                                    </p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ $application->notice_period ?? '--' }} @lang('recruit::modules.jobApplication.days')
                                    </p>
                                </div>
                            @endif

                            @if ($application->source_id)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('recruit::modules.front.applicationSource')
                                    </p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ $application->source->application_source ?? '--' }}
                                    </p>
                                </div>
                            @endif

                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('recruit::modules.jobApplication.appliedAt')
                                </p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    {{ $application->created_at->format($company->date_format) }}
                                </p>
                            </div>
                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('app.status')</p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    @if (!is_null($application->recruit_application_status_id))
                                        <i class="fa fa-circle mr-1 text-blue f-10"
                                           style='color: {{ $application->applicationStatus->color }}'>
                                        </i>{{ ($application->applicationStatus->status) }}
                                    @endif
                                </p>
                            </div>
                            @if($application->remark)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('app.remark')</p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ ($application->remark) ?? '--' }}
                                    </p>
                                </div>
                            @endif
                            @if($application->rejection_remark)
                                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                        @lang('app.remark')</p>
                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                        {{ ($application->rejection_remark) ?? '--' }}
                                    </p>
                                </div>
                            @endif
                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('recruit::modules.jobApplication.coverLetter')</p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    {{ $application->cover_letter ?? '--' }}
                                </p>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
                            <div class="media">
                                @if (!is_null($application->photo))
                                    <div class="jobApplicationImg mr-1">
                                        <div class="imgnew">
                                            <img data-toggle="tooltip" class="new"
                                                 data-original-title="{{ $application->name }}"
                                                 src="{{ $application->image_url }}">
                                        </div>
                                    </div>
                                @else
                                    <img src="{{ asset('img/avatar.png') }}"
                                         class="align-self-start ml-5 jobApplicationImg rounded">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TASK TABS START -->
            <div class="bg-additional-grey rounded my-3">

                <div class="s-b-inner s-b-notifications bg-white b-shadow-4 rounded">

                    <x-tab-section class="task-tabs">
                        <x-tab-item class="ajax-tab" :active="(request('view') === 'skill' || !request('view'))"
                                    :link="route('job-applications.show', $application->id).'?view=skill'">
                            @lang('recruit::app.menu.skills')</x-tab-item>

                        <x-tab-item class="ajax-tab" :active="(request('view') === 'applicant_notes')"
                                    :link="route('job-applications.show', $application->id).'?view=applicant_notes'">
                            @lang('recruit::app.menu.applicantNotes')</x-tab-item>

                        <x-tab-item class="ajax-tab" :active="(request('view') === 'resume')"
                                    :link="route('job-applications.show', $application->id).'?view=resume'">
                            @lang('recruit::modules.jobApplication.resume')</x-tab-item>

                        <x-tab-item class="ajax-tab" :active="(request('view') === 'custom')"
                                    :link="route('job-applications.show', $application->id).'?view=custom'">
                            @lang('recruit::modules.jobApplication.additionalInfo')</x-tab-item>

                        <x-tab-item class="ajax-tab" :active="(request('view') === 'follow-up')"
                                    :link="route('job-applications.show', $application->id).'?view=follow-up'">
                            @lang('modules.lead.followUp')</x-tab-item>

                        <x-tab-item class="ajax-tab" :active="(request('view') === 'interview-schedule')"
                                    :link="route('job-applications.show', $application->id).'?view=interview-schedule'">
                            @lang('recruit::modules.front.interviewSchedule')</x-tab-item>
                    </x-tab-section>

                    <div class="s-b-n-content">
                        <div class="tab-content" id="nav-tabContent">
                            @include($tab)
                        </div>
                    </div>
                </div>
            </div>
            <!-- TASK TABS END -->
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $(".ajax-tab").click(function(event) {
            event.preventDefault();

            $('.task-tabs .ajax-tab').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: ($(RIGHT_MODAL).hasClass('in') ? false : true),
                data: {
                    'json': true
                },
                success: function(response) {
                    if (response.status == "success") {
                        $('#nav-tabContent').html(response.html);
                    }
                }
            });
        });

        $('body').on('click', '.delete-table-row', function () {
            var id = $(this).data('user-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('job-applications.destroy', $application->id) }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function (response) {
                            if ($(RIGHT_MODAL).hasClass('in')) {
                                document.getElementById('close-task-detail').click();
                                if ($('#job-applications-table').length) {
                                    window.LaravelDataTables["job-applications-table"].draw(false);
                                } else {
                                    window.location.href = response.redirectUrl;
                                }
                            } else {
                                window.location.href = response.redirectUrl;
                            }
                        }
                    });
                }
            });
        });

        $("#selectSkill").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('recruit::messages.skillsSelected') }} ";
            }
        });
        init(RIGHT_MODAL);

    });

    $('#archive_job').on('click', function () {

        var url = "{{ route('candidate-database.store') }}";
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: url,
            type: "POST",
            data: {
                '_token': token,
                row_id: {{ $application->id }}
            },
            success: function (response) {
                if (response.status == 'success') {

                    if ($(RIGHT_MODAL).hasClass('in')) {
                        document.getElementById('close-task-detail').click();
                        if ($('#job-applications-table').length) {
                            window.LaravelDataTables["job-applications-table"].draw(false);
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                }
            }
        });
    });
</script>
