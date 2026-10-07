<?php

namespace App\Http\Controllers\Practice;

use App\Http\Controllers\Controller;
use App\Services\ExamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PracticeController extends Controller
{
    protected ExamService $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    protected function getUserId(): int
    {
        return Auth::id() ?? 1;
    }

    public function practiceConfig()
    {
        return view('practice_config');
    }

    public function practiceFull(Request $request)
    {
        try {
            $data = $request->only(['difficulty', 'subject_ids']);

            $result = $this->examService->configureExam($this->getUserId(), array_merge([
                'exam_type' => 'practice',
                'difficulty' => 'medium',
                'subject_ids' => [],
            ], $data));

            $questions = $this->examService->getQuestions($result['attempt']->id, $this->getUserId());

            return view('practice_full', [
                'attempt' => $result['attempt'],
                'config' => $result['config'],
                'questions' => $questions,
            ]);
        } catch (Throwable $e) {
            return view('practice_full', ['error' => 'No se pudo iniciar la práctica: ' . $e->getMessage()]);
        }
    }

    public function practiceResults($id)
    {
        try {
            $results = $this->examService->getExamResults((int) $id, $this->getUserId());

            return view('practice_results', ['results' => $results]);
        } catch (Throwable $e) {
            return view('practice_results', ['error' => 'No se pudieron consultar los resultados: ' . $e->getMessage()]);
        }
    }

    public function practiceReview($id)
    {
        try {
            $results = $this->examService->getExamResults((int) $id, $this->getUserId());

            return view('practice_review', ['results' => $results, 'questions' => $results['questions_detail']]);
        } catch (Throwable $e) {
            return view('practice_review', ['error' => 'No se pudo cargar la revisión: ' . $e->getMessage()]);
        }
    }
}
