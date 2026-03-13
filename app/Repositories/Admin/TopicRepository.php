<?php

namespace App\Repositories\Admin;

use App\Models\Topic;
use Illuminate\Support\Facades\DB;

class TopicRepository
{

    public function getAllWithSubject($subjectId = null, $perPage = 10, $search = null)
    {
        $query = Topic::with(['subject.translations']);

        if ($search) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        return $query->paginate($perPage)->withQueryString();
    }

    public function getTopic($id)
    {
        return Topic::with('translations')->findOrFail($id);
    }

    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            $topic = Topic::create([
                'subject_id' => $data['subject_id'],
            ]);

            foreach ($data['translations'] as $languageId => $translationData) {
                $name = $translationData['name'];

                $translation = $topic->translations()->where('language_id', $languageId)->first();

                if ($translation) {
                    $translation->update(['name' => $name]);
                } else {
                    $topic->translations()->create([
                        'language_id' => $languageId,
                        'name' => $name,
                    ]);
                }
            }
            return $topic;
        });
    }

    public function update($data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $topic = Topic::findOrFail($id);
            $topic->update([
                'subject_id' => $data['subject_id'],
            ]);

            foreach ($data['translations'] as $languageId => $translationData) {
                $name = $translationData['name'];

                $translation = $topic->translations()->where('language_id', $languageId)->first();

                if ($translation) {
                    $translation->update(['name' => $name]);
                } else {
                    $topic->translations()->create([
                        'language_id' => $languageId,
                        'name' => $name,
                    ]);
                }
            }

            return $topic;
        });
    }
}
