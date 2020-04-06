<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
// use \App\Models\User;
// use DB;

class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for($i = 0; $i<20;$i++){

            DB::table('user')->insert([
                "username"      => $faker->userName,
                "first_name"    => $faker->firstName(),
                "last_name"     => $faker->lastName,
                "email"         => $faker->email,
                "company"       => $faker->company,
                "activated"     => $faker->boolean,
                "level"         => rand(1,3),
                "password"      => bcrypt('password'),
                "created_at"    => date('Y-m-d H:i:s'),
                "updated_at"    => date('Y-m-d H:i:s'),
            ]);


            // $user = new User();
            // $user->username     =   $faker->userName;
            // $user->first_name   =   $faker->firstName();
            // $user->last_name    =   $faker->lastName;
            // $user->email        =   $faker->email;
            // $user->company      =   $faker->company;
            // $user->activated    =   $faker->boolean;
            // $user->level        =   rand(1,3);
            // $user->password     =   bcrypt('password');
            // $user->save();
        }
    }
}
