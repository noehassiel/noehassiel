<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => "Webmaster",
            'email' => "webmaster@noehassiel.com",
            'password' => bcrypt('noe12345'),
        ]);
        $user->assignRole('webmaster');

        $user = User::create([
            'name' => "Cliente",
            'email' => "client@noehassiel.com",
            'password' => bcrypt('noe12345'),
        ]);
        $user->assignRole('client');
    }
}
