<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Models\Student;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            // FacultySeeder::class,
            // GroupSeeder::class,
            // SubjectSeeder::class,
            // TopicSeeder::class,
            UserSeeder::class,
            // StudentSeeder::class,
        ]);
    }
}
