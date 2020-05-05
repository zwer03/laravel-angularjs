<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        $password = Hash::make('its0lutions');

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@easy.com.ph',
            'username' => 'admin',
            'password' => $password
        ]);

        $password = Hash::make('infotech');

        User::create([
            'name' => 'Information Technology',
            'email' => 'infotech@easy.com.ph',
            'username' => 'infotech',
            'password' => $password
        ]);
    }
}
