<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('balance_id')->constrained('balances')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['task_id','balance_id']);
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
        Schema::dropIfExists('task_balances');
    }
}
