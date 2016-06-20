<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'ChiliPizza',
            'email' => 'ChiliPizzaMap@gmail.com',
            'password' => bcrypt('mnogomnogo'),
            'is_admin'  => true
        ]);
    }
}
