<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->float('price',36);
            $table->float('sale_price',36)->nullable();
            $table->string('location');
            $table->mediumText('google_address');
            $table->mediumText('postal_address');
            $table->decimal('longitude',10,7);
            $table->decimal('latitude',10,7);
            $table->boolean('active')->default(false);
            $table->uuid('guid')->unique();
            $table->timestamps();
        });

        Schema::table('products', function(Blueprint $table){
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
