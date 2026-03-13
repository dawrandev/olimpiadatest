<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\FacultyTranslation;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run()
    {
        $faculties = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
        ];

        foreach ($faculties as $faculty) {
            Faculty::create($faculty);
        }

        $facultyTranslations = [
            ['faculty_id' => 1, 'language_id' => 1, 'name' => 'Dawalaw isi'],
            ['faculty_id' => 1, 'language_id' => 2, 'name' => 'Davolash ishi'],
            ['faculty_id' => 1, 'language_id' => 3, 'name' => 'Лечебное дело'],

            ['faculty_id' => 2, 'language_id' => 1, 'name' => 'Pediatriya'],
            ['faculty_id' => 2, 'language_id' => 2, 'name' => 'Pediatriya'],
            ['faculty_id' => 2, 'language_id' => 3, 'name' => 'Педиатрия'],

            ['faculty_id' => 3, 'language_id' => 1, 'name' => 'Stomatologiya'],
            ['faculty_id' => 3, 'language_id' => 2, 'name' => 'Stomatologiya'],
            ['faculty_id' => 3, 'language_id' => 3, 'name' => 'Стоматология'],

            ['faculty_id' => 4, 'language_id' => 1, 'name' => 'Farmatsiya'],
            ['faculty_id' => 4, 'language_id' => 2, 'name' => 'Farmatsiya'],
            ['faculty_id' => 4, 'language_id' => 3, 'name' => 'Фармация'],
        ];

        foreach ($facultyTranslations as $translation) {
            FacultyTranslation::create($translation);
        }
    }
}
