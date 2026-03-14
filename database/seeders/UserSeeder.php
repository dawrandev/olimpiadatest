<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // DB facade'ni qo'shish kerak
use App\Models\User; // Modelni import qilish yaxshi praktika

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Jadvalni tozalash
        // Diqqat: Foreign key bo'lsa xato bermasligi uchun cheklovni vaqtincha o'chiramiz
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $users = [
            [
                'login' => 'admin',
                'password' => Hash::make('@admin2333'),
                'role' => 'admin'
            ],
        ];

        foreach ($users as $user) {
            User::create($user); // Endi updateOrCreate shart emas, chunki jadval bo'sh
        }
    }
}
