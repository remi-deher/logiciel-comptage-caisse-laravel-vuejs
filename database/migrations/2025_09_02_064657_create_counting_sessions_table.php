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
        Schema::create('counting_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Caisse principale"
            $table->date('date');
            $table->enum('status', ['in_progress', 'completed', 'archived'])->default('in_progress');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counting_sessions');
    }
};
