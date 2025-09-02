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
        Schema::create('denomination_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counting_session_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ex: "Billet 20€", "Pièce 2€"
            $table->decimal('value', 8, 2); // La valeur d'une seule unité (ex: 20.00, 2.00)
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denomination_counts');
    }
};
