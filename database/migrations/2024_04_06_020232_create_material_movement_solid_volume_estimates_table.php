<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('material_movement_solid_volume_estimates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code');
            $table->datetime('date');
            $table->uuid('station_id');
            $table->foreign('station_id')->references('id')->on('stations');
            $table->decimal('solid_volume_estimate', 30, 8)->default(0);
            $table->text('remarks')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('material_movement_solid_volume_estimates');
    }
};
