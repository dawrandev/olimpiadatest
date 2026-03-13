<?php

namespace App\Repositories\Admin;

use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectRepository
{
    public function __construct()
    {
        //
    }

    public function getAll($perPage = 10, $search = null)
    {
        $query = Subject::with(['translations' => function ($q) {
            $q->where('language_id', currentLanguageId());
        }]);

        if ($search) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            $subject = Subject::create([]);

            foreach ($data['name'] as $languageId => $name) {
                $subject->translations()->create([
                    'language_id' => $languageId,
                    'name' => $name,
                ]);
            }

            return $subject;
        });
    }

    public function update($data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $subject = Subject::findOrFail($id);

            foreach ($data['translations'] as $languageId => $translationData) {
                $name = $translationData['name'];

                $translation = $subject->translations()->where('language_id', $languageId)->first();

                if ($translation) {
                    $translation->update(['name' => $name]);
                } else {
                    $subject->translations()->create([
                        'language_id' => $languageId,
                        'name' => $name,
                    ]);
                }
            }
            return $subject;
        });
    }
}
