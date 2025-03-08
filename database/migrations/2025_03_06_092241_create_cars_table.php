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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable()->unique();
            $table->string('title')->nullable();
            $table->integer('price')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->integer('regdate')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('postcode')->nullable();
            $table->integer('distance')->nullable();
            $table->string('fuel')->nullable();
            $table->string('gearbox')->nullable();
            $table->integer('doors')->nullable();
            $table->integer('seats')->nullable();
            $table->integer('critair')->nullable();
            $table->integer('issuance_date')->nullable();
            $table->string('vehicle_damage')->nullable();
            $table->string('custom_ref')->nullable();
            $table->string('spare_parts_availability')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('car_contract')->nullable();
            $table->string('vehicle_euro_emissions_standard')->nullable();
            $table->integer('vehicle_technical_inspection_a')->nullable();
            $table->string('vehicle_upholstery')->nullable();
            $table->string('vehicle_specifications')->nullable();
            $table->string('vehicle_interior_specs')->nullable();
            $table->string('vehicule_color')->nullable();
            $table->integer('horsepower')->nullable();
            $table->integer('horse_power_din')->nullable();
            $table->string('vehicle_vsp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
