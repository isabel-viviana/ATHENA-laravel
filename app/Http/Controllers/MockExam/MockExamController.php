<?php

namespace App\Http\Controllers\MockExam;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\ExamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MockExamController extends Controller
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

    public function mockConfig()
    {
        return view('mock_config');
    }

    public function mockQuick()
    {
        try {
            $result = $this->examService->configureExam($this->getUserId(), [
                'exam_type' => 'quick',
                'difficulty' => 'medium',
                'subject_ids' => [],
            ]);

            return view('mock_quick', ['attempt' => $result['attempt'], 'config' => $result['config']]);
        } catch (Throwable $e) {
            return view('mock_quick', ['error' => 'No se pudo iniciar el simulacro rápido: ' . $e->getMessage()]);
        }
    }

    public function mockExam($id)
    {
        try {
            $questions = $this->examService->getQuestions((int) $id, $this->getUserId());

            return view('mock_exam', ['attemptId' => (int) $id, 'questions' => $questions]);
        } catch (Throwable $e) {
            return view('mock_exam', ['attemptId' => (int) $id, 'error' => 'No se pudieron obtener las preguntas: ' . $e->getMessage()]);
        }
    }

    public function mockHistory()
    {
        try {
            $attempts = ExamAttempt::where('user_id', $this->getUserId())
                ->where('status', 'completed')
                ->orderByDesc('started_at')
                ->get();

            return view('mock_history', ['attempts' => $attempts]);
        } catch (Throwable $e) {
            return view('mock_history', ['attempts' => collect(), 'error' => 'No se pudo consultar el historial: ' . $e->getMessage()]);
        }
    }

    public function mockResults($id)
    {
        try {
            $results = $this->examService->getExamResults((int) $id, $this->getUserId());

            return view('mock_results', ['results' => $results]);
        } catch (Throwable $e) {
            return view('mock_results', ['error' => 'No se pudieron consultar los resultados: ' . $e->getMessage()]);
        }
    }

    public function mockReview($id)
    {
        try {
            $results = $this->examService->getExamResults((int) $id, $this->getUserId());

            return view('mock_review', ['results' => $results, 'questions' => $results['questions_detail']]);
        } catch (Throwable $e) {
            return view('mock_review', ['error' => 'No se pudo cargar la revisión: ' . $e->getMessage()]);
        }
    }
}
