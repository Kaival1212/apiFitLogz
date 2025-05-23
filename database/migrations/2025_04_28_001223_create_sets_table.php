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
        Schema::create('sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 5, 2);
            $table->unsignedSmallInteger('reps');
            $table->enum('intensity', ['Easy', 'Moderate', 'Hard', 'Failure']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sets');
    }
};
