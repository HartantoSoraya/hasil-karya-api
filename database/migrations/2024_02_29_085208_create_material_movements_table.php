<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('material_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code');
            $table->uuid('driver_id');
            $table->foreign('driver_id')->references('id')->on('drivers');
            $table->uuid('truck_id');
            $table->foreign('truck_id')->references('id')->on('trucks');
            $table->uuid('station_id');
            $table->foreign('station_id')->references('id')->on('stations');
            $table->uuid('checker_id');
            $table->foreign('checker_id')->references('id')->on('checkers');
            $table->datetime('date');
            $table->decimal('truck_capacity', 30, 8)->default(0);
            $table->decimal('observation_ratio', 30, 8)->default(0);
            $table->decimal('solid_ratio', 30, 8)->default(0);
            $table->decimal('solid_volume_estimate', 30, 8)->default(0);
            $table->text('remarks')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_movements');
    }
};
