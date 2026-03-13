<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\SubjectTranslation;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'translations' => [
                    ['language_id' => 1, 'name' => 'Anatomiya'],
                    ['language_id' => 2, 'name' => 'Anatomiya'],
                    ['language_id' => 3, 'name' => 'Анатомия'],
                ],
            ],
            [
                'translations' => [
                    ['language_id' => 1, 'name' => 'Fiziologiya'],
                    ['language_id' => 2, 'name' => 'Fiziologiya'],
                    ['language_id' => 3, 'name' => 'Физиология'],
                ],
            ],
            [
                'translations' => [
                    ['language_id' => 1, 'name' => 'Bioximiya'],
                    ['language_id' => 2, 'name' => 'Biokimyo'],
                    ['language_id' => 3, 'name' => 'Биохимия'],
                ],
            ],
        ];

        foreach ($subjects as $subjectData) {
            $subject = Subject::create();

            foreach ($subjectData['translations'] as $translation) {
                SubjectTranslation::create([
                    'subject_id'  => $subject->id,
                    'language_id' => $translation['language_id'],
                    'name'        => $translation['name'],
                ]);
            }
        }
    }
}
