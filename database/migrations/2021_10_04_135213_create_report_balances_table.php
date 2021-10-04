<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('balance_id')->constrained('balances')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['report_id','balance_id']);
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
        Schema::dropIfExists('report_balances');
    }
}
