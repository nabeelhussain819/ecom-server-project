<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingSizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_sizes', function (Blueprint $table) {
            $table->id();
            $table->text("name")->nullable();
            $table->text("size")->nullable();
            $table->text("description")->nullable();

        });
        Schema::table('products', function (Blueprint $table) {

            $table->foreign('shipping_size_id')->references('id')->on('shipping_sizes')->cascadeOnDelete();
        });

        DB::table('shipping_sizes')->insert([
            ["id" => 1, "name" => 'X-small', "description" => '80z(1/2lb)'],
            ["id" => 2, "name" => 'Small', "description" => '80z(1/2lb)'],
            ["id" => 3, "name" => 'Medium', "description" => '80z(1/2lb)'],
            ["id" => 4, "name" => 'Large', "description" => '80z(1/2lb)'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_sizes');
    }
}
