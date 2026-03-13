<?php

namespace App\Repositories\Admin;

use App\Models\Faculty;
use Illuminate\Support\Facades\DB;

class FacultyRepository
{
    public function getAll()
    {
        return \App\Models\Faculty::with('translations')->paginate(10);
    }

    public function create($data)
    {
        return DB::transaction(function () use ($data) {
            $faculty = Faculty::create([]);

            foreach ($data['name'] as $languageId => $name) {
                $faculty->translations()->create([
                    'language_id' => $languageId,
                    'name' => $name,
                ]);
            }

            return $faculty;
        });
    }

    public function getFaculty()
    {
        return Faculty::with('translations')->findOrFail(request()->route('faculty'));
    }

    public function update($data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $faculty = Faculty::findOrFail($id);

            foreach ($data['translations'] as $languageId => $translationData) {
                $name = $translationData['name'];

                $translation = $faculty->translations()->where('language_id', $languageId)->first();

                if ($translation) {
                    $translation->update(['name' => $name]);
                } else {
                    $faculty->translations()->create([
                        'language_id' => $languageId,
                        'name' => $name,
                    ]);
                }
            }

            return $faculty;
        });
    }
}
