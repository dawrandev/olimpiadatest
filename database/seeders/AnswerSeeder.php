<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $answers = [
            [
                'question_id' => 1,
                'is_correct' => false
            ],
            [
                'question_id' => 1,
                'is_correct' => false
            ],
            [
                'question_id' => 1,
                'is_correct' => false
            ],
            [
                'question_id' => 1,
                'is_correct' => true
            ],
            [
                'question_id' => 2,
                'is_correct' => false
            ],
            [
                'question_id' => 2,
                'is_correct' => false
            ],
            [
                'question_id' => 2,
                'is_correct' => false
            ],
            [
                'question_id' => 2,
                'is_correct' => true
            ],
            [
                'question_id' => 2,
                'is_correct' => false
            ],
            [
                'question_id' => 3,
                'is_correct' => false
            ],
            [
                'question_id' => 3,
                'is_correct' => false
            ],
            [
                'question_id' => 3,
                'is_correct' => false
            ],
            [
                'question_id' => 3,
                'is_correct' => true
            ]
        ];

        foreach ($answers as $answer) {
            Answer::create($answer);
        }
    }
}
