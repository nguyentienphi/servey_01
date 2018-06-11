<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Survey\SurveyInterface;
use App\Repositories\Section\SectionInterface;
use App\Repositories\Question\QuestionInterface;
use App\Repositories\Answer\AnswerInterface;
use App\Repositories\Setting\SettingInterface;
use App\Repositories\Media\MediaInterface;
use App\Repositories\Result\ResultInterface;
use App\Repositories\Invite\InviteInterface;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Section;
use Exception, Auth, Datatables, Session, DB, Storage;
use Carbon\Carbon;
use App\Traits\ManageSurvey;
use App\Traits\DoSurvey;

class ManagementSurvey extends Controller
{
    use ManageSurvey, DoSurvey;

    protected $surveyRepository;
    protected $sectionRepository;
    protected $questionRepository;
    protected $answerRepository;
    protected $settingRepository;
    protected $mediaRepository;
    protected $resultRepository;
    protected $inviteRepository;

    public function __construct(
        SurveyInterface $surveyRepository,
        QuestionInterface $questionRepository,
        SectionInterface $sectionRepository,
        AnswerInterface $answerRepository,
        SettingInterface $settingRepository,
        MediaInterface $mediaRepository,
        ResultInterface $resultRepository,
        InviteInterface $inviteRepository
    ) {
        $this->surveyRepository = $surveyRepository;
        $this->questionRepository = $questionRepository;
        $this->sectionRepository = $sectionRepository;
        $this->answerRepository = $answerRepository;
        $this->settingRepository = $settingRepository;
        $this->mediaRepository = $mediaRepository;
        $this->resultRepository = $resultRepository;
        $this->inviteRepository = $inviteRepository;
    }

    public function getOverviewSurvey(Request $request, $tokenManage)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
            ]);
        }
        
        $survey = $this->surveyRepository->getSurveyFromTokenManage($tokenManage); 
        $results = $this->getOverview($survey);

        $html = view('clients.survey.management.overview', compact('results'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function settingSurvey(Request $request, $token)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
            ]);
        }

        if (Session::has('url_current')) {
            Session::forget('url_current');
        }
        
        $survey = $this->surveyRepository->getSurvey($token);
        $numOfSection = $survey->sections->count();
        // at line 42 of file app/Traits/DoSurvey.php
        $data = $this->getDetailSurvey($survey, $numOfSection);

        $html = view('clients.survey.management.setting_survey', compact([
            'data',
            'survey',
        ]))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function deleteSurvey(Request $request, $tokenManage)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
            ]);
        }

        DB::beginTransaction();
        try {
            $survey = $this->surveyRepository->getSurveyFromTokenManage($tokenManage);

            if (Auth::user()->cannot('edit', $survey)) {
                throw new Exception("Not permitted edit!", 403);
            }

            $this->delete($survey);

            DB::commit();

            return response()->json([
                'success' => true,
                'url_redirect' => route('survey.survey.show-surveys'),
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => trans('lang.process_failed'),
                'redirect' => ($e->getCode() == 403) ? route('403') : '',
            ]);
        }
    }

    public function closeSurvey(Request $request, $tokenManage)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
            ]);
        }

        DB::beginTransaction();
        try {
            $survey = $this->surveyRepository->getSurveyFromTokenManage($tokenManage);

            if (Auth::user()->cannot('edit', $survey)) {
                throw new Exception("Not permitted edit!", 403);
            }

            $this->close($survey);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => trans('lang.close_survey_success'),
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => trans('lang.process_failed'),
                'redirect' => ($e->getCode() == 403) ? route('403') : '',
            ]);
        }
    }

    public function openSurvey(Request $request, $tokenManage)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
            ]);
        }
        
        DB::beginTransaction();
        try {
            $survey = $this->surveyRepository->withTrashed()->where('token_manage', $tokenManage)->first();

            if (Auth::user()->cannot('edit', $survey)) {
                throw new Exception("Not permitted edit!", 403);
            }

            $this->open($survey);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => trans('lang.open_survey_success'),
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => trans('lang.process_failed'),
                'redirect' => ($e->getCode() == 403) ? route('403') : '',
            ]);
        }
    }

    public function updateTokenSurvey(UpdateTokenRequest $request)
    {
        try {
            if (!$request->ajax()) {
                throw new Exception("Error Processing Ajax Request", 1);
            }

            $survey = $this->surveyRepository->getSurveyFromToken($request->old_token);

            if (Auth::user()->cannot('edit', $survey)) {
                throw new Exception("Not permitted edit!", 403);
            }

            $array['token'] = $request->input('token');
            $surveyId = $request->input('survey_id');
            $survey = $this->surveyRepository->update($surveyId, $array);
            $newToken = $survey->token;

            return response()->json([
                'success' => true,
                'new_token' => $newToken,
                'next_section_url' => route('survey.create.do-survey', $newToken),
                'setting_url' => route('ajax-setting-survey', $newToken),
            ]);
        } catch (Exception $e) {
            return [
                'success' => false,
                'redirect' => ($e->getCode() == 403) ? route('403') : '',
            ];
        }

        
    }

    public function updateTokenManageSurvey(UpdateTokenManageRequest $request)
    {
        try {
            if (!$request->ajax()) {
                throw new Exception("Error Processing Ajax Request", 1);
            }

            $survey = $this->surveyRepository->getSurveyFromTokenManage($request->old_token_manage);

            if (Auth::user()->cannot('edit', $survey)) {
                throw new Exception("Not permitted edit!", 403);
            }

            $array['token_manage'] = $request->input('token_manage');
            $surveyId = $request->input('survey_id');
            $survey = $this->surveyRepository->update($surveyId, $array);
            $newTokenManage = $survey->token_manage;

            return response()->json([
                'success' => true,
                'new_token_manage' => $newTokenManage,
                'delete_survey_url' => route('ajax-survey-delete', $newTokenManage),
                'edit_survey_url' => route('surveys.edit', $newTokenManage),
                'close_survey_url' => route('ajax-survey-close', $newTokenManage),
                'open_survey_url' => route('ajax-survey-open', $newTokenManage),
                'overview_url' => route('ajax-get-overview', $newTokenManage),
                'result_url' => route('survey.result.index', $newTokenManage),
            ]);
        } catch (Exception $e) {
            return [
                'success' => false,
                'redirect' => ($e->getCode() == 403) ? route('403') : '',
            ];
        }
    }
    
    public function cloneSurvey(Request $request, $tokenManage)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
            ]);
        }
        
        DB::beginTransaction();
        try {
            $survey = $this->surveyRepository->getSurveyForClone($tokenManage);

            if (Auth::user()->cannot('edit', $survey)) {
                throw new Exception("Not permitted edit!", 403);
            }

            $newSurvey = $this->clone($survey);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => trans('lang.clone_survey_success'),
                'redirect' => route('survey.management', $newSurvey->token_manage),
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => trans('lang.process_failed'),
                'redirect' => ($e->getCode() == 403) ? route('403') : '',
            ]);
        }
    }
}