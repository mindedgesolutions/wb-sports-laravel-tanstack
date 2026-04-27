<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::create([
            'name' => 'Sudeshna Deb',
            'email' => 'ssp.yss-wb@bangla.gov.in',
            'password' => bcrypt('Ssp!2025'),
            'organisation' => 'sports',
        ])->assignRole('admin');

        UserDetail::create([
            'user_id' => $user1->id,
            'slug' => Str::slug($user1->name),
        ]);

        // $user2 = User::create([
        //     'name' => 'Nelson Arafat Ali',
        //     'email' => 'nelson@test.com',
        //     'password' => bcrypt('password'),
        //     'organisation' => 'services',
        // ])->assignRole('admin');

        // UserDetail::create([
        //     'user_id' => $user2->id,
        //     'slug' => Str::slug($user2->name),
        // ]);

        // $user3 = User::create([
        //     'name' => 'Souvik Nag (Sports)',
        //     'email' => 'souvik_sports@test.com',
        //     'password' => bcrypt('password'),
        //     'organisation' => 'sports',
        // ])->assignRole('admin');

        // UserDetail::create([
        //     'user_id' => $user3->id,
        //     'slug' => Str::slug($user3->name),
        // ]);
    }
}
