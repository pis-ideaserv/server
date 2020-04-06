<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
// use App\Models\User;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id')->unique();
            $table->string('username');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('company');
            $table->boolean('activated')->default(true);
            $table->integer('level')->length(1)->default(1);
            $table->string('password');
            $table->timestamps();
        });


        DB::table('user')->insert([
            "username"      => "admin",
            "first_name"    => "Admin",
            "last_name"     => "Admin",
            "email"         =>  "admin@gmail.com",
            "company"       =>  "Ideaserv Systems Inc.",
            "activated"     =>  1,
            "level"         =>  1,
            "password"      =>  bcrypt('password'),
            "created_at"    =>  new DateTime(),
            "updated_at"    =>  new DateTime(),
        ]);

        // $user = new User();
        // $user->username     =   'admin';
        // $user->first_name   =   'Admin';
        // $user->last_name    =   'Admin';
        // $user->email        =   'admin@gmail.com';
        // $user->company      =   'Ideaserv Systems Inc.';
        // $user->activated    =   true;
        // $user->level        =   1;
        // $user->password     =   bcrypt('password');
        // $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
