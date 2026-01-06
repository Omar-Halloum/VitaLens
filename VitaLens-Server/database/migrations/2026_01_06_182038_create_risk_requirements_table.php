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
        Schema::create('risk_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_type_id')->constrained('risk_types')->onDelete('cascade');
            $table->foreignId('feature_definition_id')->constrained('feature_definitions')->onDelete('cascade');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_requirements');
    }
};
