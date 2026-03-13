<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Topic;
use App\Models\TopicTranslation;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            [
                'subject_id' => 1,
                'translations' => [
                    ['language_id' => 1, 'name' => 'Skelet sistemasi'],
                    ['language_id' => 2, 'name' => 'Skelet sistemasi'],
                    ['language_id' => 3, 'name' => 'Скелетная система'],
                ],
            ],
            [
                'subject_id' => 2,
                'translations' => [
                    ['language_id' => 1, 'name' => 'qan aylanıw sisteması'],
                    ['language_id' => 2, 'name' => 'Qon aylanish tizimi'],
                    ['language_id' => 3, 'name' => 'Кровеносная система'],
                ],
            ],
            [
                'subject_id' => 3,
                'translations' => [
                    ['language_id' => 1, 'name' => 'Beloklardıń dúzilisi hám funktsiyaları'],
                    ['language_id' => 2, 'name' => 'Oqsillarning tuzilishi va vazifalari'],
                    ['language_id' => 3, 'name' => 'Структура и функции белков'],
                ],
            ],
        ];

        foreach ($topics as $topicData) {
            $topic = Topic::create([
                'subject_id' => $topicData['subject_id'],
            ]);

            foreach ($topicData['translations'] as $translation) {
                TopicTranslation::create([
                    'topic_id'    => $topic->id,
                    'language_id' => $translation['language_id'],
                    'name'        => $translation['name'],
                ]);
            }
        }
    }
}
