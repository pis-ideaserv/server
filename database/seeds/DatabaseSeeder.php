<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(userSeeder::class);
        $this->call(supplierSeeder::class);
        $this->call(category::class);
        $this->call(productMasterList::class);
        $this->call(productSeeder::class);
    }
}
