<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item');
            $table->string('description')->nullable();
            $table->integer('quantity');
            $table->date('date_in');
            $table->string('purchased_by');
            $table->date('last_disbursed')->nullable();
            $table->integer('quantity_disbursed')->nullable();
            $table->date('date_disbursed')->nullable();
            $table->string('disbursed_to')->nullable();
            $table->string('purpose')->nullable();
            $table->integer('current_quantity');
            $table->string('created_by');
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
        Schema::dropIfExists('inventories');
    }
}
