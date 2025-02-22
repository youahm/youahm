<?php

namespace Modules\Recruit\Http\Controllers;

use App\Models\Team;
use App\Helper\Files;
use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\CompanyAddress;
use Modules\Recruit\Entities\RecruitJob;
use Modules\Recruit\Entities\RecruitSkill;
use Modules\Recruit\Entities\RecruitSetting;
use App\Http\Controllers\AccountBaseController;
use App\Models\Currency;
use Carbon\Carbon;
use Modules\Recruit\Entities\ApplicationSource;
use Modules\Recruit\Entities\RecruitJobApplication;
use Modules\Recruit\Entities\RecruitApplicationFile;
use Modules\Recruit\Entities\RecruitApplicationSkill;
use Modules\Recruit\Entities\RecruitApplicationStatus;
use Modules\Recruit\DataTables\JobApplicationsDataTable;
use Modules\Recruit\Entities\RecruitCandidateFollowUp;
use Modules\Recruit\Entities\RecruitInterviewSchedule;
use Modules\Recruit\Entities\RecruitJobAddress;
use Modules\Recruit\Entities\RecruitJobCustomAnswer;
use Modules\Recruit\Events\JobApplicationStatusChangeEvent;
use Modules\Recruit\Http\Requests\JobApplication\StoreJobApplication;
use Modules\Recruit\Http\Requests\JobApplication\StoreQuickApplication;
use Modules\Recruit\Http\Requests\JobApplication\UpdateJobApplication;
use PhpParser\Node\Expr\Empty_;

class JobApplicationController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('recruit::app.menu.jobApplication');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(RecruitSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    public function index(JobApplicationsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_job_application');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->applicationStatus = RecruitApplicationStatus::select('id', 'status', 'position', 'color')->orderBy('position')->get();
        $this->applicationSources = ApplicationSource::all();
        $this->jobs = RecruitJob::where('status', 'open')->get();
        $this->locations = CompanyAddress::all();
        $this->jobLocations = RecruitJobAddress::with('location')->where('recruit_job_id', request()->id)->get();
        $this->jobApp = RecruitJob::where('id', request()->id)->first();

        $this->locations = CompanyAddress::all();
        $this->currentLocations = RecruitJobApplication::select('current_location')->where('current_location', '!=', null)->distinct()->get();

        $settings = RecruitSetting::select('form_settings')->first();
        $this->formSettings = collect([]);

        if ($settings) {
            $formSettings = $settings->form_settings;

            foreach ($formSettings as $form) {
                if ($form['status'] == true) {
                    $this->formSettings->push($form);
                }
            }

        }

        $this->formFields = $this->formSettings->pluck('name')->toArray();

        return $dataTable->render('recruit::job-applications.table', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $addPermission = user()->permission('add_job_application');
        abort_403(!in_array($addPermission, ['all', 'added']));
        $this->jobId = request()->id ?? null;

        $this->pageTitle = __('recruit::modules.jobApplication.addJobApplications');

        $this->applicationStatus = RecruitApplicationStatus::select('id', 'status', 'position', 'color')->orderBy('position')->get();
        $this->applicationSources = ApplicationSource::all();
        $this->jobs = RecruitJob::where('status', 'open')->get();
        $this->locations = CompanyAddress::all();
        $this->jobLocations = RecruitJobAddress::with('location')->where('recruit_job_id', request()->id)->get();
        $this->jobApp = RecruitJob::where('id', request()->id)->first();
        $this->statusId = request()->column_id;

        if (request()->ajax()) {
            $html = view('recruit::job-applications.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'recruit::job-applications.ajax.create';

        return view('recruit::job-applications.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreJobApplication $request)
    {
        $addPermission = user()->permission('add_job_application');
        abort_403(!in_array($addPermission, ['all', 'added']));
        $jobApp = new RecruitJobApplication();
        $jobApp->recruit_job_id = $request->job_id;
        $jobApp->full_name = $request->full_name;
        $jobApp->email = $this->emailValidation($request);
        $jobApp->phone = $request->phone;

        if ($request->has('gender')) {
            $jobApp->gender = $request->gender;
        }

        if ($request->date_of_birth != null) {
            if ($request->has('date_of_birth')) {
                $date_of_birth = Carbon::createFromFormat($this->company->date_format, $request->date_of_birth)->format('Y-m-d');
                $jobApp->date_of_birth = $date_of_birth;
            }
        }

        $jobApp->application_source_id = $request->source;
        $jobApp->cover_letter = $request->cover_letter;
        $jobApp->location_id = $request->location_id;
        $jobApp->total_experience = $request->total_experience;
        $jobApp->current_location = $request->current_location;
        $jobApp->current_ctc = $request->current_ctc;
        $jobApp->currenct_ctc_rate = $request->currenct_ctc_rate;
        $jobApp->expected_ctc = $request->expected_ctc;
        $jobApp->expected_ctc_rate = $request->expected_ctc_rate;
        $jobApp->notice_period = $request->notice_period;
        $jobApp->recruit_application_status_id = $request->status_id;
        $jobApp->application_sources = 'addedByUser';
        $jobApp->column_priority = 0;

        if ($request->hasFile('photo')) {
            Files::deleteFile($jobApp->photo, 'avatar');
            $jobApp->photo = Files::uploadLocalOrS3($request->photo, 'avatar', 300);
        }

        $jobApp->save();

        if (request()->hasFile('resume')) {
            $file = new RecruitApplicationFile();
            $file->recruit_job_application_id = $jobApp->id;
            Files::deleteFile($jobApp->resume, 'application-files/');
            $filename = Files::uploadLocalOrS3(request()->resume, 'application-files/' . $jobApp->id);
            $file->filename = request()->resume->getClientOriginalName();
            $file->hashname = $filename;
            $file->size = request()->resume->getSize();
            $file->save();
        }

        if (request()->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('recruit::messages.applicationAdded'), ['html' => $html, 'add_more' => true]);
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('job-applications.index');
        }

        return Reply::dataOnly(['redirectUrl' => $redirectUrl, 'application_id' => $jobApp->id]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $interviewer = [];
        $this->application = RecruitJobApplication::with('job', 'applicationStatus', 'location', 'source', 'comments', 'comments.user', 'files')->find($id);
        $scheduleData = RecruitInterviewSchedule::with('employees')->where('recruit_job_application_id', $id)->first();

        $this->currencySymbol = Currency::where('id', '=', $this->application->job->currency_id)->first();

        if ($scheduleData) {
            $interviewer = $scheduleData->employees->pluck('id')->toArray();
        }

        $this->viewPermission = user()->permission('view_job_application');
        $this->interviewViewPermission = user()->permission('view_interview_schedule');
        abort_403(!($this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->application->added_by == user()->id)
            || ($this->viewPermission == 'owned' && user()->id == $this->application->job->recruiter_id)
            || ($this->viewPermission == 'owned' && in_array(user()->id, $interviewer))
            || ($this->viewPermission == 'both' && user()->id == $this->application->job->recruiter_id
                || $this->application->added_by == user()->id) || (in_array(user()->id, $interviewer))
            || ($this->interviewViewPermission == 'owned')));
        $this->departments = Team::all();
        $this->recruit_skills = RecruitApplicationSkill::where('recruit_job_application_id', $id)->get();
        $this->selected_skills = $this->recruit_skills->pluck('recruit_skill_id')->toArray();
        $this->skills = RecruitSkill::select('id', 'name')->get();
        $this->allAnswers = RecruitJobCustomAnswer::where('recruit_job_application_id', $this->application->id)->get();
        $this->followUps = RecruitCandidateFollowUp::where('recruit_job_application_id', $id)->get();

        $tab = request('view');

        switch ($tab) {
        case 'applicant_notes':
            $this->tab = 'recruit::job-applications.notes.notes';
            break;
        case 'resume':
            $this->tab = 'recruit::job-applications.ajax.resume';
            break;
        case 'custom':
            $this->tab = 'recruit::job-applications.ajax.custom-question';
            break;
        case 'follow-up':
            $this->tab = 'recruit::job-applications.ajax.follow-up';
            break;
        case 'interview-schedule':
            $this->editInterviewSchedulePermission = user()->permission('edit_interview_schedule');
            $this->deleteInterviewSchedulePermission = user()->permission('delete_interview_schedule');
            $this->viewInterviewSchedulePermission = user()->permission('view_interview_schedule');
            $this->reschedulePermission = user()->permission('reschedule_interview');
            $this->applicationStatus = ['pending', 'hired', 'canceled','completed','rejected'];

            $this->interviewSchedule = RecruitInterviewSchedule::with(['employeesData', 'employeesData.user'])->select('recruit_interview_schedules.id', 'recruit_interview_schedules.recruit_job_application_id', 'recruit_interview_schedules.interview_type', 'recruit_interview_schedules.recruit_interview_stage_id', 'recruit_interview_employees.user_id as employee_id', 'recruit_interview_employees.user_accept_status', 'recruit_interview_employees.id as emp_id', 'recruit_job_applications.full_name', 'recruit_interview_schedules.status', 'recruit_interview_schedules.schedule_date', 'recruit_interview_stages.name')->where('recruit_job_application_id', $id)
            ->leftjoin('recruit_job_applications', 'recruit_job_applications.id', 'recruit_interview_schedules.recruit_job_application_id')
            ->leftjoin('recruit_interview_stages', 'recruit_interview_stages.id', 'recruit_interview_schedules.recruit_interview_stage_id')
            ->leftjoin('recruit_interview_employees', 'recruit_interview_employees.recruit_interview_schedule_id', 'recruit_interview_schedules.id')->groupBy('recruit_interview_schedules.id')->get();
            $this->tab = 'recruit::job-applications.ajax.interview-schedule';
            break;
        default:
            $this->tab = 'recruit::job-applications.ajax.skill';
            break;
        }

        if (request()->ajax()) {
            if (request('json') == true) {
                $html = view($this->tab, $this->data)->render();

                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
            }

            $html = view('recruit::job-applications.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'recruit::job-applications.ajax.show';

        return view('recruit::job-applications.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $this->jobApplication = RecruitJobApplication::findOrFail($id);
        $this->jobId = null;
        $this->job = RecruitJob::where('id', $this->jobApplication->recruit_job_id)->get();
        $this->currency = $this->job ? Currency::where('id', '=', $this->jobApplication->job->currency_id)->first() : null;
        $this->editPermission = user()->permission('edit_job_application');
        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->jobApplication->added_by == user()->id)
            || ($this->editPermission == 'owned' && user()->id == $this->job->recruiter_id)
            || ($this->editPermission == 'both' && user()->id == $this->job->recruiter_id)
            || $this->jobApplication->added_by == user()->id));

        $this->jobApplictionFile = RecruitApplicationFile::where('recruit_job_application_id', $id)->first();
        $this->jobs = RecruitJob::all();
        $this->applicationSources = ApplicationSource::all();
        $this->locations = CompanyAddress::all();
        $this->applicationStatus = RecruitApplicationStatus::select('id', 'status', 'position', 'color')->orderBy('position')->get();

        if (request()->ajax()) {
            $html = view('recruit::job-applications.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'recruit::job-applications.ajax.edit';

        return view('recruit::job-applications.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(UpdateJobApplication $request, $id)
    {
        $this->editPermission = user()->permission('edit_job_application');
        $jobApp = RecruitJobApplication::with('job')->findOrFail($id);

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $jobApp->added_by == user()->id)
            || ($this->editPermission == 'owned' && user()->id == $jobApp->job->recruiter_id)
            || ($this->editPermission == 'both' && user()->id == $jobApp->job->recruiter_id)
            || $jobApp->added_by == user()->id));
        $status = $jobApp->status_id;
        $statusId = $request->status_id;
        $jobApp->recruit_job_id = $request->job_id;
        $jobApp->full_name = $request->full_name;
        $jobApp->email = $request->email;
        $jobApp->phone = $request->phone;
        $jobApp->location_id = $request->location_id;
        $jobApp->total_experience = $request->total_experience;
        $jobApp->current_location = $request->current_location;
        $jobApp->current_ctc = $request->current_ctc;
        $jobApp->currenct_ctc_rate = $request->currenct_ctc_rate;
        $jobApp->expected_ctc = $request->expected_ctc;
        $jobApp->expected_ctc_rate = $request->expected_ctc_rate;
        $jobApp->notice_period = $request->notice_period;

        if ($request->has('gender')) {
            $jobApp->gender = $request->gender;
        }

        if ($request->date_of_birth != null) {
            if ($request->has('date_of_birth')) {
                $date_of_birth = Carbon::createFromFormat($this->company->date_format, $request->date_of_birth)->format('Y-m-d');
                $jobApp->date_of_birth = $date_of_birth;
            }
        }

        $jobApp->recruit_application_status_id = $request->status_id;
        $jobApp->application_source_id = $request->source;
        $jobApp->cover_letter = $request->cover_letter;

        if ($request->photo_delete == 'yes') {
            Files::deleteFile($jobApp->photo, 'avatar');
            $jobApp->photo = null;
        }

        if ($request->hasFile('photo')) {
            Files::deleteFile($jobApp->photo, 'avatar');
            $jobApp->photo = Files::uploadLocalOrS3($request->photo, 'avatar', 300);
        }

        $jobApp->save();

        if (request()->hasFile('resume')) {
            $file = RecruitApplicationFile::where('recruit_job_application_id', $jobApp->id)->first() ?? new RecruitApplicationFile;
            $file->recruit_job_application_id ? Files::deleteFile($file->recruit_job_application_id, 'application-files/' . $jobApp->id) : '';
            $file->recruit_job_application_id = $jobApp->id;
            $filename = Files::uploadLocalOrS3(request()->resume, 'application-files/' . $jobApp->id);
            $file->filename = request()->resume->getClientOriginalName();
            $file->hashname = $filename;
            $file->size = request()->resume->getSize();
            $file->save();
        }

        if ($status != $statusId) {
            $send = $this->statusForMailSend($statusId);

            if ($send == true) {
                event(new JobApplicationStatusChangeEvent($jobApp));
            }
        }

        return Reply::successWithData(__('recruit::modules.message.updateSuccess'), ['redirectUrl' => route('job-applications.index'), 'application_id' => $jobApp->id]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $jobApp = RecruitJobApplication::with('job')->findOrFail($id);

        $this->deletePermission = user()->permission('delete_job_application');
        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $jobApp->added_by == user()->id)
            || ($this->deletePermission == 'owned' && user()->id == $jobApp->job->recruiter_id)
            || ($this->deletePermission == 'both' && user()->id == $jobApp->job->recruiter_id)
            || $jobApp->added_by == user()->id));

        RecruitJobApplication::withTrashed()->find($id)->forceDelete();

        return Reply::successWithData(__('recruit::modules.message.deleteSuccess'), ['redirectUrl' => route('job-applications.index')]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_job_application') != 'all');

        RecruitJobApplication::withTrashed()->whereIn('id', explode(',', $request->row_ids))->forceDelete();

        return true;
    }

    public function changeStatus(Request $request)
    {
        abort_403(user()->permission('edit_job_application') != 'all');
        $interviewPermission = user()->permission('add_interview_schedule');
        $offerLetterPermission = user()->permission('add_offer_letter');

        $item = explode(',', $request->row_ids);

        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        $statusId = $request->status;
        $status = RecruitApplicationStatus::with('category')->where('id', $statusId)->first();

        $send = $this->statusForMailSend($statusId);
        RecruitJobApplication::whereIn('id', $item)->update(['recruit_application_status_id' => $request->status]);

        foreach($item as $id){
            $mail = RecruitJobApplication::findOrFail($id);

            if ($send == true) {
                event(new JobApplicationStatusChangeEvent($mail));
            }

            $mail->recruit_application_status_id = $request->status;
            $mail->save();
        }


        return Reply::dataOnly(['status' => 'success', 'status' => $status, 'interviewPermission' => $interviewPermission, 'offerLetterPermission' => $offerLetterPermission]);
    }

    public function statusForMailSend($id)
    {
        $settings = RecruitSetting::first();
        $mail = $settings->mail_setting;

        foreach ($mail as $mailDetails) {
            if ($mailDetails['id'] == $id && $mailDetails['status'] == true) {
                return true;
            }
        }
    }

    public function getLocation(Request $request)
    {
        $this->data = RecruitJob::with('address')->findOrFail($request->job_id);
        $this->locations = RecruitJobAddress::with('location')->where('recruit_job_id', $request->job_id)->get();
        $view = view('recruit::job-applications.location', $this->data)->render();
        $job = RecruitJob::findOrFail($request->job_id);
        $currencySymbol = $job->currency_id ? Currency::where('id', '=', $job->currency_id)->first() : company()->currency;

        return Reply::dataOnly(['status' => 'success', 'locations' => $view, 'job' => $job, 'id' => $request->job_id, 'currencySymbol' => $currencySymbol]);
    }

    public function emailValidation($request)
    {
        $jobApps = RecruitJobApplication::where('recruit_job_id', $request->job_id)->get();

        if (count($jobApps) > 0) {
            foreach ($jobApps as $jobApp) {
                $mail = $jobApp->where('recruit_job_id', $request->job_id)->whereNotNull('email')->pluck('email')->toArray();
            }

            if (in_array($request->email, $mail)) {
                $this->validate($request, [
                    'email' => 'unique:recruit_job_applications|email'
                ]);
            }
            else {
                return $request->email;
            }
        }
        else {
            return $request->email;
        }
    }

    public function quickAddFormStore(StoreQuickApplication $request)
    {
        $addPermission = user()->permission('add_job_application');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $jobApp = new RecruitJobApplication();
        $jobApp->recruit_job_id = $request->job_id;
        $jobApp->full_name = $request->full_name;
        $jobApp->email = $this->emailValidation($request);
        $jobApp->phone = $request->phone;

        if ($request->has('gender')) {
            $jobApp->gender = $request->gender;
        }

        $jobApp->application_source_id = $request->source;
        $jobApp->cover_letter = $request->cover_letter;
        $jobApp->location_id = $request->location_id;
        $jobApp->total_experience = $request->total_experience;
        $jobApp->current_location = $request->current_location;
        $jobApp->current_ctc = $request->current_ctc;
        $jobApp->expected_ctc = $request->expected_ctc;
        $jobApp->notice_period = $request->notice_period;
        $jobApp->recruit_application_status_id = $request->status_id ?? 1;
        $jobApp->application_sources = 'addedByUser';
        $jobApp->column_priority = 0;

        $jobApp->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('job-applications.index');
        }

        return Reply::dataOnly(['redirectUrl' => $redirectUrl,'application_id' => $jobApp->id]);
    }

}
