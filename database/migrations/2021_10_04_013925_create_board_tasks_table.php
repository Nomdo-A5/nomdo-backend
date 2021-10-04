<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('boards')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('tasks')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['board_id', 'task_id']);
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
        Schema::dropIfExists('board_tasks');
    }
}
