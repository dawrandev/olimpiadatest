<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TopicRequest;
use App\Http\Requests\Admin\TopicUpdateRequest;
use App\Services\Admin\TopicService;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function __construct(protected TopicService $topicService)
    {
        // 
    }

    public function index(Request $request)
    {
        $topics = $this->topicService->getTopics($request->subject_id, 10, $request->search);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.admin.topics.topics_table  ', compact('topics'))->render()
            ]);
        }

        return view('pages.admin.topics.index', compact('topics'));
    }

    public function create()
    {
        return view('pages.admin.topics.create');
    }

    public function store(TopicRequest $request)
    {
        $this->topicService->createTopic($request->validated());

        return redirect()->route('admin.topics.index')->with('success', __('Topic created successfully'));
    }

    public function edit(string $id)
    {
        $topic = $this->topicService->findById($id);

        return view('pages.admin.topics.edit', compact('topic'));
    }

    public function update(TopicUpdateRequest $request, string $id)
    {
        $this->topicService->updateTopic($request->validated(), $id);

        return redirect()->route('admin.topics.index')->with('success', __('Topic updated successfully'));
    }

    public function destroy(string $id)
    {
        $topic = $this->topicService->findById($id);

        $topic->delete();

        return redirect()->route('admin.topics.index')->with('success', __('Topic deleted successfully'));
    }
}
