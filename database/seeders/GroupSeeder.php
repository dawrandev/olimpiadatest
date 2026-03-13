<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'faculty_id' => 1,
                'name' => 308
            ],
        ];

        foreach ($groups as $group) {
            Group::create($group);
        }
    }
}
