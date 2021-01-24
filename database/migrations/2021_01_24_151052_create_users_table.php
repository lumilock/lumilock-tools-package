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
            $table->uuid('id')->primary();
            $table->string('login',150)->unique();
            $table->string('first_name',50)->default('');
            $table->string('last_name',50)->default('');
            $table->string('email',191)->unique()->nullable();
            $table->string('picture',255)->default('');
            $table->boolean('active')->default(true);
            $table->json('tokens');
            $table->json('roles')->nullable();
            $table->timestamps();
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
