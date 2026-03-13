<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionMatchingPair;
use App\Models\Subject;
use App\Repositories\Admin\QuestionRepository;
use App\Services\Admin\QuestionImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    protected $importService;
    protected $repository;

    public function __construct(
        QuestionImportService $importService,
        QuestionRepository $repository
    ) {
        $this->importService = $importService;
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        if (!$request->has('subject_id') || empty($request->get('subject_id'))) {
            $subjects = Subject::with(['translations' => function ($query) use ($request) {
                if ($request->has('language_id') && !empty($request->get('language_id'))) {
                    $query->where('language_id', $request->get('language_id'));
                }
            }])
                ->withCount('questions')
                ->get();

            return view('pages.admin.questions.index', [
                'subjects' => $subjects,
                'questions' => null,
                'showSubjects' => true
            ]);
        }

        $filters = [
            'language_id' => $request->get('language_id'),
            'subject_id'  => $request->get('subject_id'),
            'topic_id'    => $request->get('topic_id'),
            'search'      => $request->get('search'),
        ];

        $questions = $this->repository->getPaginated(40, $filters);

        if ($request->ajax()) {
            return view('partials.admin.questions.question_list', compact('questions'))->render();
        }

        return view('pages.admin.questions.index', [
            'questions' => $questions,
            'subjects' => null,
            'showSubjects' => false
        ]);
    }

    public function getSubjectsByLanguage(Request $request)
    {
        $languageId = $request->get('language_id');

        $subjects = Subject::with(['translations' => function ($query) use ($languageId) {
            if ($languageId) {
                $query->where('language_id', $languageId);
            }
        }])
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->translations->first()->name ?? 'No name',
                    'questions_count' => $subject->questions()->count()
                ];
            });

        return response()->json($subjects);
    }

    public function create()
    {
        return view('pages.admin.questions.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => 'required|exists:languages,id',
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'required|exists:topics,id',
            'file' => 'required|file|mimes:docx,doc|max:3072',
        ], [
            'language_id.required' => __('Language is required'),
            'language_id.exists' => __('Invalid language selected'),
            'subject_id.required' => __('Subject is required'),
            'subject_id.exists' => __('Invalid subject selected'),
            'topic_id.required' => __('Topic is required'),
            'topic_id.exists' => __('Invalid topic selected'),
            'file.required' => __('File upload is required'),
            'file.mimes' => __('Only .docx files are accepted'),
            'file.max' => __('File size must not exceed 10MB'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('Validation error'),
                'errors' => $validator->errors()
            ], 422);
        }

        $tempPath = null;

        try {
            $file = $request->file('file');

            $tempPath = $file->store('temp', 'local');
            $fullPath = storage_path('app/' . $tempPath);

            if (!file_exists($fullPath)) {
                throw new \Exception(__('Error saving file'));
            }

            $result = $this->importService->importFromDocx($fullPath, [
                'language_id' => $request->language_id,
                'subject_id' => $request->subject_id,
                'topic_id' => $request->topic_id,
            ]);

            if ($tempPath) {
                Storage::disk('local')->delete($tempPath);
            }

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('Questions uploaded successfully'),
                    'uploaded_count' => $result['uploaded_count'],
                    'error_count' => $result['error_count'],
                    'errors' => $result['errors'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Error uploading file'),
                    'uploaded_count' => $result['uploaded_count'],
                    'error_count' => $result['error_count'],
                    'errors' => $result['errors'],
                ], 422);
            }
        } catch (\Exception $e) {
            if ($tempPath && Storage::disk('local')->exists($tempPath)) {
                Storage::disk('local')->delete($tempPath);
            }

            return response()->json([
                'success' => false,
                'message' => __('Server error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $question = $this->repository->findWithRelations($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => __('Question not found')
                ], 404);
            }

            $formattedQuestion = [
                'id' => $question->id,
                'type' => $question->type,
                'text' => $question->text,
                'image' => $question->image ?? 'medicaltest.png',
                'language' => $question->language->name ?? null,
                'subject' => $question->subject->translations->first()?->name ?? 'N/A',
                'topic' => $question->topic->translations->first()?->name ?? 'N/A',
            ];

            switch ($question->type) {
                case Question::TYPE_SINGLE_CHOICE:
                    $formattedQuestion['answers'] = $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'text' => $answer->text,
                            'is_correct' => $answer->is_correct,
                        ];
                    })->values()->toArray();
                    break;

                case Question::TYPE_MATCHING:
                    $formattedQuestion['left_items_title'] = $question->left_items_title;
                    $formattedQuestion['right_items_title'] = $question->right_items_title;

                    $formattedQuestion['left_items'] = $question->matchingPairs()
                        ->where('side', 'left')
                        ->orderBy('order')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'key' => $item->key,
                                'text' => $item->text,
                            ];
                        })
                        ->toArray();

                    $formattedQuestion['right_items'] = $question->matchingPairs()
                        ->where('side', 'right')
                        ->orderBy('order')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'key' => $item->key,
                                'text' => $item->text,
                            ];
                        })
                        ->toArray();

                    $formattedQuestion['answer_variants'] = $question->answers->map(function ($answer) {
                        return [
                            'text' => $answer->text,
                            'is_correct' => $answer->is_correct,
                        ];
                    })->toArray();
                    break;

                case Question::TYPE_SEQUENCE:
                    $formattedQuestion['sequence_items'] = $question->answers()
                        ->orderBy('id')
                        ->get()
                        ->map(function ($answer, $index) {
                            return [
                                'order' => $answer->order,
                                'text' => $answer->text,
                                'display_number' => $index + 1,
                            ];
                        })
                        ->toArray();

                    $formattedQuestion['correct_sequence'] = $question->answers()
                        ->orderBy('id')
                        ->pluck('order')
                        ->toArray();
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $formattedQuestion
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $question = $this->repository->findWithRelations($id);

            if (!$question) {
                return back()->with('error', __('Question not found'));
            }

            $languages = $this->repository->getAllLanguages();

            return view('pages.admin.questions.edit', compact('question', 'languages'));
        } catch (Exception $e) {
            return back()->with('error', __('Error') . ': ' . $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $question = Question::findOrFail($id);

            $baseRules = [
                'text' => 'required|string|max:2000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'remove_current_image' => 'nullable|in:0,1',
            ];

            $typeSpecificRules = [];

            if ($question->type === 'single_choice') {
                $hasMultipleCorrect = $request->has('correct_answers') &&
                    is_array($request->input('correct_answers'));

                if ($hasMultipleCorrect) {
                    $typeSpecificRules = [
                        'answers' => 'required|array|min:2',
                        'answers.*.text' => 'required|string|max:1000',
                        'answers.*.id' => 'nullable|exists:answers,id',
                        'correct_answers' => 'required|array|min:1',
                        'correct_answers.*' => 'integer|min:0',
                    ];
                } else {
                    $typeSpecificRules = [
                        'answers' => 'required|array|min:2',
                        'answers.*.text' => 'required|string|max:1000',
                        'answers.*.id' => 'nullable|exists:answers,id',
                        'correct_answer' => 'required|integer|min:0',
                    ];
                }
            } elseif ($question->type === 'matching') {
                $typeSpecificRules = [
                    'left_items_title' => 'required|string|max:200',
                    'right_items_title' => 'required|string|max:200',
                    'left_items' => 'required|array|min:1',
                    'left_items.*.text' => 'required|string|max:1000',
                    'left_items.*.key' => 'required|string|max:10',
                    'left_items.*.id' => 'nullable|integer',
                    'right_items' => 'required|array|min:1',
                    'right_items.*.text' => 'required|string|max:1000',
                    'right_items.*.key' => 'required|string|max:10',
                    'right_items.*.id' => 'nullable|integer',
                    'answer_variants' => 'required|array|min:1',
                    'answer_variants.*.text' => 'required|string|max:200',
                    'answer_variants.*.id' => 'nullable|exists:answers,id',
                    'correct_variants' => 'required|integer|min:0',
                ];
            } elseif ($question->type === 'sequence') {
                $typeSpecificRules = [
                    'sequence_items' => 'required|array|min:2',
                    'sequence_items.*.text' => 'required|string|max:1000',
                    'sequence_items.*.order' => 'required|integer|min:1',
                    'sequence_items.*.id' => 'nullable|exists:answers,id',
                ];
            }

            $validated = $request->validate(array_merge($baseRules, $typeSpecificRules));

            DB::beginTransaction();

            $question->text = $validated['text'];

            if ($request->hasFile('image')) {
                if ($question->image && $question->image !== 'medicaltest.png') {
                    Storage::disk('public')->delete('questions/' . $question->image);
                }

                $imageName = time() . '_' . uniqid() . '.' . $request->file('image')->extension();
                $request->file('image')->storeAs('questions', $imageName, 'public');
                $question->image = $imageName;
            } elseif ($request->input('remove_current_image') == '1') {
                if ($question->image && $question->image !== 'medicaltest.png') {
                    Storage::disk('public')->delete('questions/' . $question->image);
                }
                $question->image = 'medicaltest.png';
            }

            $question->save();

            if ($question->type === 'single_choice') {
                $hasMultipleCorrect = $request->has('correct_answers') &&
                    is_array($request->input('correct_answers'));

                if ($hasMultipleCorrect) {
                    $this->updateMultipleChoice($question, $validated);
                } else {
                    $this->updateSingleChoice($question, $validated);
                }
            } elseif ($question->type === 'matching') {
                $this->updateMatching($question, $validated);
            } elseif ($question->type === 'sequence') {
                $this->updateSequence($question, $validated);
            }

            DB::commit();

            return redirect()
                ->route('admin.questions.index')
                ->with('success', __('Question updated successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', __('An error occurred while updating the question'))
                ->withInput();
        }
    }

    private function updateSingleChoice($question, $validated)
    {
        $incomingAnswerIds = collect($validated['answers'])
            ->pluck('id')
            ->filter()
            ->toArray();

        Answer::where('question_id', $question->id)
            ->whereNotIn('id', $incomingAnswerIds)
            ->delete();

        $correctAnswerIndex = (int) $validated['correct_answer'];

        foreach ($validated['answers'] as $index => $answerData) {
            $isCorrect = ($index == $correctAnswerIndex);

            if (!empty($answerData['id'])) {
                $answer = Answer::find($answerData['id']);
                if ($answer && $answer->question_id == $question->id) {
                    $answer->text = $answerData['text'];
                    $answer->is_correct = $isCorrect;
                    $answer->save();
                }
            } else {
                Answer::create([
                    'question_id' => $question->id,
                    'language_id' => $question->language_id,
                    'text' => $answerData['text'],
                    'is_correct' => $isCorrect,
                ]);
            }
        }
    }

    private function updateMultipleChoice($question, $validated)
    {
        $incomingAnswerIds = collect($validated['answers'])
            ->pluck('id')
            ->filter()
            ->toArray();

        Answer::where('question_id', $question->id)
            ->whereNotIn('id', $incomingAnswerIds)
            ->delete();

        $correctAnswers = $validated['correct_answers'] ?? [];

        foreach ($validated['answers'] as $index => $answerData) {
            $isCorrect = in_array($index, $correctAnswers);

            if (!empty($answerData['id'])) {
                $answer = Answer::find($answerData['id']);
                if ($answer && $answer->question_id == $question->id) {
                    $answer->text = $answerData['text'];
                    $answer->is_correct = $isCorrect;
                    $answer->save();
                }
            } else {
                Answer::create([
                    'question_id' => $question->id,
                    'language_id' => $question->language_id,
                    'text' => $answerData['text'],
                    'is_correct' => $isCorrect,
                ]);
            }
        }
    }

    private function updateMatching($question, $validated)
    {
        $question->left_items_title = $validated['left_items_title'];
        $question->right_items_title = $validated['right_items_title'];
        $question->save();

        $incomingLeftIds = collect($validated['left_items'])
            ->pluck('id')
            ->filter()
            ->toArray();

        QuestionMatchingPair::where('question_id', $question->id)
            ->where('side', 'left')
            ->whereNotIn('id', $incomingLeftIds)
            ->delete();

        foreach ($validated['left_items'] as $index => $itemData) {
            if (!empty($itemData['id'])) {
                $item = QuestionMatchingPair::find($itemData['id']);
                if ($item && $item->question_id == $question->id) {
                    $item->update([
                        'text' => $itemData['text'],
                        'key' => $itemData['key'],
                        'order' => $itemData['order'] ?? $index,
                    ]);
                }
            } else {
                QuestionMatchingPair::create([
                    'question_id' => $question->id,
                    'side' => 'left',
                    'text' => $itemData['text'],
                    'key' => $itemData['key'],
                    'order' => $itemData['order'] ?? $index,
                ]);
            }
        }

        $incomingRightIds = collect($validated['right_items'])
            ->pluck('id')
            ->filter()
            ->toArray();

        QuestionMatchingPair::where('question_id', $question->id)
            ->where('side', 'right')
            ->whereNotIn('id', $incomingRightIds)
            ->delete();

        foreach ($validated['right_items'] as $index => $itemData) {
            if (!empty($itemData['id'])) {
                $item = QuestionMatchingPair::find($itemData['id']);
                if ($item && $item->question_id == $question->id) {
                    $item->update([
                        'text' => $itemData['text'],
                        'key' => $itemData['key'],
                        'order' => $itemData['order'] ?? $index,
                    ]);
                }
            } else {
                QuestionMatchingPair::create([
                    'question_id' => $question->id,
                    'side' => 'right',
                    'text' => $itemData['text'],
                    'key' => $itemData['key'],
                    'order' => $itemData['order'] ?? $index,
                ]);
            }
        }

        $incomingVariantIds = collect($validated['answer_variants'])
            ->pluck('id')
            ->filter()
            ->toArray();

        Answer::where('question_id', $question->id)
            ->whereNotIn('id', $incomingVariantIds)
            ->delete();

        $correctVariantIndex = $validated['correct_variants'] ?? null;

        foreach ($validated['answer_variants'] as $index => $variantData) {
            $isCorrect = ($index == $correctVariantIndex);

            if (!empty($variantData['id'])) {
                $answer = Answer::find($variantData['id']);
                if ($answer && $answer->question_id == $question->id) {
                    $answer->text = $variantData['text'];
                    $answer->is_correct = $isCorrect;
                    $answer->save();
                }
            } else {
                Answer::create([
                    'question_id' => $question->id,
                    'language_id' => $question->language_id,
                    'text' => $variantData['text'],
                    'is_correct' => $isCorrect,
                ]);
            }
        }
    }

    private function updateSequence($question, $validated)
    {
        $incomingItemIds = collect($validated['sequence_items'])
            ->pluck('id')
            ->filter()
            ->toArray();

        Answer::where('question_id', $question->id)
            ->whereNotIn('id', $incomingItemIds)
            ->delete();

        foreach ($validated['sequence_items'] as $index => $itemData) {
            if (!empty($itemData['id'])) {
                $answer = Answer::find($itemData['id']);
                if ($answer && $answer->question_id == $question->id) {
                    $answer->text = $itemData['text'];
                    $answer->order = $itemData['order'];
                    $answer->is_correct = true;
                    $answer->save();
                }
            } else {
                Answer::create([
                    'question_id' => $question->id,
                    'language_id' => $question->language_id,
                    'text' => $itemData['text'],
                    'order' => $itemData['order'],
                    'is_correct' => true,
                ]);
            }
        }
    }

    public function destroy($id)
    {
        $deleted = $this->repository->delete($id);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => __('Question deleted successfully')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Question not found')
        ], 404);
    }

    public function deleteForm()
    {
        return view('pages.admin.questions.delete');
    }

    public function getQuestionsCount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'delete_type' => 'required|in:subject,topic',
                'language_id' => 'required|exists:languages,id',
                'subject_id' => 'required|exists:subjects,id',
                'topic_id' => 'required_if:delete_type,topic|nullable|exists:topics,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Validation error'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Question::where('language_id', $request->language_id)
                ->where('subject_id', $request->subject_id);

            if ($request->delete_type === 'topic' && $request->topic_id) {
                $query->where('topic_id', $request->topic_id);
            }

            $count = $query->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    public function massDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'delete_type' => 'required|in:subject,topic',
                'language_id' => 'required|exists:languages,id',
                'subject_id' => 'required|exists:subjects,id',
                'topic_id' => 'required_if:delete_type,topic|nullable|exists:topics,id',
            ]);

            if ($validator->fails()) {
                return back()->with('error', __('Validation error: ') . $validator->errors()->first());
            }

            DB::beginTransaction();

            $query = Question::where('language_id', $request->language_id)
                ->where('subject_id', $request->subject_id);

            if ($request->delete_type === 'topic' && $request->topic_id) {
                $query->where('topic_id', $request->topic_id);
            }

            $deletedCount = $query->delete();

            DB::commit();

            if ($deletedCount > 0) {
                $message = $request->delete_type === 'subject'
                    ? __(':count questions deleted from subject successfully', ['count' => $deletedCount])
                    : __(':count questions deleted from topic successfully', ['count' => $deletedCount]);

                return back()->with('success', $message);
            } else {
                return back()->with('warning', __('No questions found to delete'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', __('Error deleting questions') . ': ' . $e->getMessage());
        }
    }

    public function updateViaFile(Request $request)
    {
        try {
            $validated = $request->validate([
                'question_id' => 'required|exists:questions,id',
                'file' => 'required|file|mimes:docx|max:5120',
            ]);

            $questionId = $validated['question_id'];
            $file = $request->file('file');

            $oldQuestion = Question::with(['answers', 'matchingPairs'])->findOrFail($questionId);

            DB::beginTransaction();

            try {
                $tempPath = $file->store('temp', 'local');
                $fullPath = storage_path('app/' . $tempPath);

                $metadata = [
                    'language_id' => $oldQuestion->language_id,
                    'subject_id' => $oldQuestion->subject_id,
                    'topic_id' => $oldQuestion->topic_id,
                    'type' => $oldQuestion->type,
                ];

                $newQuestionData = $this->importService->parseSingleQuestionFromDocx($fullPath, $metadata);

                Storage::disk('local')->delete($tempPath);

                $this->deleteOldQuestionData($oldQuestion);
                $this->updateQuestionFromFile($oldQuestion, $newQuestionData);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => __('Question updated successfully'),
                    'question_id' => $questionId,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Validation error'),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function deleteOldQuestionData(Question $question)
    {
        Answer::where('question_id', $question->id)->delete();

        if ($question->type === 'matching') {
            QuestionMatchingPair::where('question_id', $question->id)->delete();
        }
    }

    private function updateQuestionFromFile(Question $question, array $newData)
    {
        $questionText = $newData['text'];
        $questionText = preg_replace('/<img[^>]*data-image="[^"]*"[^>]*>/i', '', $questionText);
        $question->text = trim($questionText);

        if (!empty($newData['image'])) {
            if ($question->image && $question->image !== 'medicaltest.png') {
                $oldImagePath = 'questions/' . $question->image;
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
            $question->image = $newData['image'];
        } elseif (empty($question->image)) {
            $question->image = 'medicaltest.png';
        }

        $question->save();

        switch ($question->type) {
            case 'single_choice':
                $this->insertAnswersFromFile($question->id, $question->language_id, $newData['answers']);
                break;

            case 'matching':
                $this->insertMatchingDataFromFile($question, $newData);
                break;

            case 'sequence':
                $this->insertSequenceAnswersFromFile($question->id, $question->language_id, $newData['answers']);
                break;
        }
    }

    private function insertAnswersFromFile($questionId, $languageId, array $answers)
    {
        foreach ($answers as $index => $answer) {
            $text = preg_replace('/<img[^>]*data-image="[^"]*"[^>]*>/i', '', $answer['text']);

            $answerData = [
                'question_id' => $questionId,
                'language_id' => $languageId,
                'text' => trim($text),
                'is_correct' => $answer['is_correct'],
            ];

            Answer::create($answerData);
        }
    }

    private function insertMatchingDataFromFile(Question $question, array $data)
    {
        $leftItems = $data['left_items'] ?? [];

        foreach ($leftItems as $index => $item) {
            $pairData = [
                'question_id' => $question->id,
                'side' => 'left',
                'key' => $item['key'],
                'text' => trim($item['text']),
                'order' => $index,
            ];

            QuestionMatchingPair::create($pairData);
        }

        $rightItems = $data['right_items'] ?? [];

        foreach ($rightItems as $index => $item) {
            $pairData = [
                'question_id' => $question->id,
                'side' => 'right',
                'key' => $item['key'],
                'text' => trim($item['text']),
                'order' => $index,
            ];

            QuestionMatchingPair::create($pairData);
        }

        $answers = $data['answers'] ?? [];

        foreach ($answers as $index => $variant) {
            $text = preg_replace('/<img[^>]*data-image="[^"]*"[^>]*>/i', '', $variant['text']);

            $answerData = [
                'question_id' => $question->id,
                'language_id' => $question->language_id,
                'text' => trim($text),
                'is_correct' => $variant['is_correct'],
            ];

            Answer::create($answerData);
        }

        $question->left_items_title = $data['left_items_title'] ?? 'Chap tomon';
        $question->right_items_title = $data['right_items_title'] ?? "O'ng tomon";
        $question->save();
    }

    private function insertSequenceAnswersFromFile($questionId, $languageId, array $answers)
    {
        foreach ($answers as $index => $answer) {
            $text = preg_replace('/<img[^>]*data-image="[^"]*"[^>]*>/i', '', $answer['text']);

            $answerData = [
                'question_id' => $questionId,
                'language_id' => $languageId,
                'text' => trim($text),
                'order' => $answer['order'],
                'is_correct' => true,
            ];

            Answer::create($answerData);
        }
    }
}
