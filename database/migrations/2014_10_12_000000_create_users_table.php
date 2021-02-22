<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('username')->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('othernames');
            $table->string('mobile');
            $table->string('gender');
            $table->string('station')->nullable();
            $table->string('role');
            $table->decimal('accountbalance', 10, 2);
            $table->string('rep')->nullable();
            $table->integer('status')->default(0);
            $table->integer('last_login');
            $table->string('created_by');
            $table->integer('login_status')->default(0);
            $table->timestamps();

            $table->softDeletes();
        });
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
