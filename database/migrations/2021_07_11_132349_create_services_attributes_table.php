<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_attributes', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('service_id')->index();
            $table->unsignedBigInteger('attribute_id')->index();
            $table->jsonb('value');
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('attribute_id')->references('id')->on('attributes')->restrictOnDelete()->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services_attributes');
    }
}
