<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => '西怜奈', 'email' => 'reina.n@coachtech.com'],
            ['name' => '山田太郎', 'email' => 'taro.y@coachtech.com'],
            ['name' => '増田一世', 'email' => 'issei.m@coachtech.com'],
            ['name' => '山本敬吉', 'email' => 'keikichi.y@coachtech.com'],
            ['name' => '秋田朋美', 'email' => 'tomomi.a@coachtech.com'],
            ['name' => '中西敦夫', 'email' => 'norio.n@coachtech.com'],
            ['name' => '松尾小朱衣', 'email' => 'k0901k.1110@gmail.com'],
        ];

        foreach ($users as $user) {
            $password = ($user['email'] === 'taro.y@coachtech.com') ? 'special_password' : 'password';

            User::firstOrCreate([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($password),
            ]);
        }
    }
}
