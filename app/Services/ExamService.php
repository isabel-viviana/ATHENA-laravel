<?php

namespace App\Services;

use App\Models\AttemptAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamConfig;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\UserTopicPerformance;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ExamService
{
    /**
     * Configura y crea un nuevo simulacro e intento inicial.
     */
    public function configureExam(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {
            $examType = $data['exam_type'] ?? 'quick';
            $difficulty = $data['difficulty'] ?? 'medium';
            $subjectIds = $data['subject_ids'] ?? [];

            // 1. Crear configuración del examen
            $config = ExamConfig::create([
                'user_id' => $userId,
                'exam_type' => $examType,
                'difficulty' => $difficulty,
            ]);

            if (!empty($subjectIds)) {
                $config->subjects()->sync($subjectIds);
            } else {
                // Si no especificó materias, incluir todas las materias disponibles
                $allSubjectIds = Subject::pluck('id')->toArray();
                $config->subjects()->sync($allSubjectIds);
            }

            // 2. Crear intento de simulacro en estado 'in_progress'
            $attempt = ExamAttempt::create([
                'user_id' => $userId,
                'exam_config_id' => $config->id,
                'mode' => $examType,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            return [
                'config' => $config->load('subjects'),
                'attempt' => $attempt,
            ];
        });
    }

    /**
     * Obtiene las preguntas correspondientes a la configuración de un simulacro.
     * Importante: NO incluye 'is_correct' ni la 'explanation' para mantener la integridad del examen.
     */
    public function getQuestions(int $attemptId, int $userId): array
    {
        $attempt = ExamAttempt::where('id', $attemptId)
            ->where('user_id', $userId)
            ->where('status', 'in_progress')
            ->with(['examConfig.subjects'])
            ->firstOrFail();

        $subjectIds = $attempt->examConfig->subjects->pluck('id')->toArray();
        $difficulty = $attempt->examConfig->difficulty;

        // Determinar cantidad de preguntas según modo de examen
        $limit = match ($attempt->mode) {
            'quick' => 5,
            'full' => 20,
            'practice' => 10,
            default => 10,
        };

        // Buscar preguntas filtrando por materias y dificultad
        $query = Question::whereIn('subject_id', $subjectIds)
            ->with(['subject:id,name,slug', 'topic:id,name,slug', 'options']);

        if ($difficulty) {
            $query->where('difficulty', $difficulty);
        }

        $questions = $query->inRandomOrder()->limit($limit)->get();

        // Si no hay suficientes preguntas con la dificultad exacta, traer de cualquier dificultad
        if ($questions->count() < $limit) {
            $questions = Question::whereIn('subject_id', $subjectIds)
                ->with(['subject:id,name,slug', 'topic:id,name,slug', 'options'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        // Formatear ocultando 'is_correct' y 'explanation'
        $formattedQuestions = $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'statement' => $q->statement,
                'difficulty' => $q->difficulty,
                'subject' => [
                    'id' => $q->subject->id ?? null,
                    'name' => $q->subject->name ?? 'Materia',
                    'slug' => $q->subject->slug ?? '',
                ],
                'topic' => [
                    'id' => $q->topic->id ?? null,
                    'name' => $q->topic->name ?? 'Tema',
                    'slug' => $q->topic->slug ?? '',
                ],
                'options' => $q->options->map(fn($opt) => [
                    'id' => $opt->id,
                    'option_letter' => $opt->option_letter,
                    'option_text' => $opt->option_text,
                ]),
            ];
        });

        return [
            'attempt_id' => $attempt->id,
            'status' => $attempt->status,
            'total_questions' => $formattedQuestions->count(),
            'questions' => $formattedQuestions,
        ];
    }

    /**
     * Registra o actualiza una respuesta a una pregunta del simulacro.
     */
    public function submitAnswer(int $attemptId, int $userId, array $data): array
    {
        $attempt = ExamAttempt::where('id', $attemptId)
            ->where('user_id', $userId)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $questionId = $data['question_id'];
        $selectedOptionId = $data['selected_option_id'] ?? null;
        $timeSpentSeconds = $data['time_spent_seconds'] ?? ($data['time'] ?? 0);

        // Verificar que la pregunta exista
        $question = Question::findOrFail($questionId);

        // Verificar que la opción pertenezca a la pregunta si se proporcionó una opción
        $isCorrect = false;
        if ($selectedOptionId) {
            $option = QuestionOption::where('id', $selectedOptionId)
                ->where('question_id', $questionId)
                ->firstOrFail();
            $isCorrect = (bool) $option->is_correct;
        }

        // Guardar o actualizar la respuesta
        $answer = AttemptAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ],
            [
                'selected_option_id' => $selectedOptionId,
                'is_correct' => $isCorrect,
                'time_spent_seconds' => $timeSpentSeconds,
            ]
        );

        return [
            'answer_id' => $answer->id,
            'question_id' => $answer->question_id,
            'recorded' => true,
        ];
    }

    /**
     * Finaliza el simulacro, calcula las métricas finales y actualiza el rendimiento académico (user_topic_performance).
     */
    public function finishExam(int $attemptId, int $userId): array
    {
        return DB::transaction(function () use ($attemptId, $userId) {
            $attempt = ExamAttempt::where('id', $attemptId)
                ->where('user_id', $userId)
                ->where('status', 'in_progress')
                ->with(['examConfig.subjects'])
                ->firstOrFail();

            $subjectIds = $attempt->examConfig->subjects->pluck('id')->toArray();
            $difficulty = $attempt->examConfig->difficulty;

            // Determinar total de preguntas asignadas
            $limit = match ($attempt->mode) {
                'quick' => 5,
                'full' => 20,
                'practice' => 10,
                default => 10,
            };

            $assignedQuestions = Question::whereIn('subject_id', $subjectIds)
                ->when($difficulty, fn($q) => $q->where('difficulty', $difficulty))
                ->limit($limit)
                ->get();

            if ($assignedQuestions->count() < $limit) {
                $assignedQuestions = Question::whereIn('subject_id', $subjectIds)->limit($limit)->get();
            }

            $totalQuestions = $assignedQuestions->count();
            $answers = AttemptAnswer::where('exam_attempt_id', $attempt->id)->get()->keyBy('question_id');

            $correctCount = 0;
            $incorrectCount = 0;
            $omittedCount = 0;
            $totalTimeSpent = 0;

            // Agrupar conteos por tema para actualizar user_topic_performance
            $topicStats = [];

            foreach ($assignedQuestions as $q) {
                $topicId = $q->topic_id;
                if (!isset($topicStats[$topicId])) {
                    $topicStats[$topicId] = ['correct' => 0, 'incorrect' => 0, 'omitted' => 0];
                }

                if (isset($answers[$q->id])) {
                    $ans = $answers[$q->id];
                    $totalTimeSpent += $ans->time_spent_seconds;

                    if ($ans->selected_option_id === null) {
                        $omittedCount++;
                        $topicStats[$topicId]['omitted']++;
                    } elseif ($ans->is_correct) {
                        $correctCount++;
                        $topicStats[$topicId]['correct']++;
                    } else {
                        $incorrectCount++;
                        $topicStats[$topicId]['incorrect']++;
                    }
                } else {
                    $omittedCount++;
                    $topicStats[$topicId]['omitted']++;
                }
            }

            $durationSeconds = now()->diffInSeconds($attempt->started_at);
            if ($durationSeconds < $totalTimeSpent) {
                $durationSeconds = $totalTimeSpent;
            }

            $scorePercentage = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;

            // Actualizar el intento
            $attempt->update([
                'status' => 'completed',
                'finished_at' => now(),
                'duration_seconds' => $durationSeconds,
                'score' => $scorePercentage,
                'correct_answers' => $correctCount,
                'incorrect_answers' => $incorrectCount,
                'omitted_answers' => $omittedCount,
            ]);

            // Actualizar rendimiento por tema en user_topic_performance
            foreach ($topicStats as $topicId => $stats) {
                $performance = UserTopicPerformance::firstOrNew([
                    'user_id' => $userId,
                    'topic_id' => $topicId,
                ]);

                $performance->correct_count += $stats['correct'];
                $performance->incorrect_count += $stats['incorrect'];
                $performance->omitted_count += $stats['omitted'];

                $totalTopicAns = $performance->correct_count + $performance->incorrect_count;
                $masteryScore = $totalTopicAns > 0 ? round($performance->correct_count / $totalTopicAns, 2) : 0.00;

                $performance->mastery_score = $masteryScore;
                $performance->save();
            }

            return [
                'attempt_id' => $attempt->id,
                'status' => 'completed',
                'score_percentage' => $scorePercentage,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctCount,
                'incorrect_answers' => $incorrectCount,
                'omitted_answers' => $omittedCount,
                'duration_seconds' => $durationSeconds,
            ];
        });
    }

    /**
     * Obtiene el reporte de resultados completo de un simulacro finalizado.
     */
    public function getExamResults(int $attemptId, int $userId): array
    {
        $attempt = ExamAttempt::where('id', $attemptId)
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->with([
                'examConfig.subjects',
                'answers.question.subject',
                'answers.question.topic',
                'answers.question.options',
                'answers.selectedOption',
            ])
            ->firstOrFail();

        $answers = $attempt->answers;

        // Rendimiento por materia
        $subjectBreakdown = [];
        // Rendimiento por tema
        $topicBreakdown = [];

        foreach ($answers as $ans) {
            $subjectName = $ans->question->subject->name ?? 'Materia';
            $subjectId = $ans->question->subject_id;
            $topicName = $ans->question->topic->name ?? 'Tema';
            $topicId = $ans->question->topic_id;

            if (!isset($subjectBreakdown[$subjectId])) {
                $subjectBreakdown[$subjectId] = [
                    'subject_id' => $subjectId,
                    'subject_name' => $subjectName,
                    'total' => 0,
                    'correct' => 0,
                    'incorrect' => 0,
                    'omitted' => 0,
                ];
            }

            if (!isset($topicBreakdown[$topicId])) {
                $topicBreakdown[$topicId] = [
                    'topic_id' => $topicId,
                    'topic_name' => $topicName,
                    'subject_name' => $subjectName,
                    'total' => 0,
                    'correct' => 0,
                    'incorrect' => 0,
                    'omitted' => 0,
                ];
            }

            $subjectBreakdown[$subjectId]['total']++;
            $topicBreakdown[$topicId]['total']++;

            if ($ans->selected_option_id === null) {
                $subjectBreakdown[$subjectId]['omitted']++;
                $topicBreakdown[$topicId]['omitted']++;
            } elseif ($ans->is_correct) {
                $subjectBreakdown[$subjectId]['correct']++;
                $topicBreakdown[$topicId]['correct']++;
            } else {
                $subjectBreakdown[$subjectId]['incorrect']++;
                $topicBreakdown[$topicId]['incorrect']++;
            }
        }

        // Calcular porcentajes
        $formattedSubjects = array_values(array_map(function ($s) {
            $s['score_percentage'] = $s['total'] > 0 ? round(($s['correct'] / $s['total']) * 100) : 0;
            return $s;
        }, $subjectBreakdown));

        $formattedTopics = array_values(array_map(function ($t) {
            $t['score_percentage'] = $t['total'] > 0 ? round(($t['correct'] / $t['total']) * 100) : 0;
            return $t;
        }, $topicBreakdown));

        // Detalle de preguntas con respuestas reveladas y explicaciones
        $questionsDetail = $answers->map(function ($ans) {
            $q = $ans->question;
            $correctOption = $q->options->where('is_correct', true)->first();

            return [
                'question_id' => $q->id,
                'statement' => $q->statement,
                'explanation' => $q->explanation,
                'difficulty' => $q->difficulty,
                'subject_name' => $q->subject->name ?? '',
                'topic_name' => $q->topic->name ?? '',
                'user_selected_option' => $ans->selectedOption ? [
                    'id' => $ans->selectedOption->id,
                    'option_letter' => $ans->selectedOption->option_letter,
                    'option_text' => $ans->selectedOption->option_text,
                ] : null,
                'correct_option' => $correctOption ? [
                    'id' => $correctOption->id,
                    'option_letter' => $correctOption->option_letter,
                    'option_text' => $correctOption->option_text,
                ] : null,
                'is_correct' => $ans->is_correct,
                'time_spent_seconds' => $ans->time_spent_seconds,
            ];
        });

        return [
            'attempt_id' => $attempt->id,
            'exam_type' => $attempt->mode,
            'status' => $attempt->status,
            'started_at' => $attempt->started_at->toDateTimeString(),
            'finished_at' => $attempt->finished_at ? $attempt->finished_at->toDateTimeString() : null,
            'duration_seconds' => $attempt->duration_seconds,
            'score_percentage' => $attempt->score,
            'total_questions' => $attempt->correct_answers + $attempt->incorrect_answers + $attempt->omitted_answers,
            'correct_answers' => $attempt->correct_answers,
            'incorrect_answers' => $attempt->incorrect_answers,
            'omitted_answers' => $attempt->omitted_answers,
            'subject_performance' => $formattedSubjects,
            'topic_performance' => $formattedTopics,
            'questions_detail' => $questionsDetail,
        ];
    }
}
