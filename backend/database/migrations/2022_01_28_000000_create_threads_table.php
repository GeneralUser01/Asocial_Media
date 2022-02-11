<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Set it up so that a user can have multiple roles. For more info see:
        // https://stackoverflow.com/questions/37093523/laravel-how-to-check-if-user-is-admin
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->text('scrambled_body');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // $table->binary('image')->nullable();
            $table->string('image_mime_type')->nullable();
            $table->integer('likes')->nullable();
            $table->integer('dislikes')->nullable();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE posts ADD image MEDIUMBLOB");

        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->text('scrambled_content');
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('likes')->nullable();
            $table->integer('dislikes')->nullable();
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
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user_roles');

        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_comments');
    }
}
