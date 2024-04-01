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
        Schema::create('material_movement_error_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->text('code')->nullable();
            $table->text('driver_id')->nullable();
            $table->text('truck_id')->nullable();
            $table->text('station_id')->nullable();
            $table->text('checker_id')->nullable();
            $table->text('date')->nullable();
            $table->text('truck_capacity')->nullable();
            $table->text('observation_ratio')->nullable();
            $table->text('solid_ratio')->nullable();
            $table->text('remarks')->nullable();
            $table->text('error_log')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('material_movement_error_logs');
    }
};
