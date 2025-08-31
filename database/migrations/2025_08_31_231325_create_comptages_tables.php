<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up(): void
    {
        // Table pour stocker les noms des caisses
        Schema::create('caisses', function (Blueprint $table) {
            $table->id();
            $table->string('nom_caisse');
            $table->timestamps(); // Ajoute created_at et updated_at
        });

        // Table pour les terminaux de paiement, liée aux caisses
        Schema::create('terminaux_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('nom_terminal');
            $table->foreignId('caisse_associee')->constrained('caisses')->onDelete('cascade');
            $table->timestamps();
        });

        // Table principale pour chaque session de comptage
        Schema::create('comptages', function (Blueprint $table) {
            $table->id();
            $table->string('nom_comptage');
            $table->text('explication')->nullable();
            $table->timestamp('date_comptage')->useCurrent();
            $table->timestamps();
        });

        // Table pour les détails financiers de chaque caisse pour un comptage donné
        Schema::create('comptage_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comptage_id')->constrained('comptages')->onDelete('cascade');
            $table->foreignId('caisse_id')->constrained('caisses')->onDelete('cascade');
            $table->decimal('fond_de_caisse', 10, 2)->default(0.00);
            $table->decimal('ventes', 10, 2)->default(0.00);
            $table->decimal('retrocession', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // Table pour le décompte des pièces et billets
        Schema::create('comptage_denominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comptage_detail_id')->constrained('comptage_details')->onDelete('cascade');
            $table->string('denomination_nom');
            $table->integer('quantite')->default(0);
            $table->timestamps();
        });

        // Table pour les suggestions de retraits lors de la clôture
        Schema::create('comptage_retraits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comptage_detail_id')->constrained('comptage_details')->onDelete('cascade');
            $table->string('denomination_nom');
            $table->integer('quantite_retiree');
            $table->timestamps();
        });
        
        // Table pour les montants des chèques
        Schema::create('comptage_cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comptage_detail_id')->constrained('comptage_details')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->timestamps();
        });

        // Table pour le journal des relevés TPE
        Schema::create('comptage_cb_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comptage_detail_id')->constrained('comptage_details')->onDelete('cascade');
            $table->foreignId('terminal_id')->constrained('terminaux_paiement')->onDelete('cascade');
            $table->decimal('montant_releve', 10, 2);
            $table->dateTime('heure_releve');
            $table->timestamps();
        });

        // Table pour les administrateurs de l'application
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->timestamps();
        });

        // Table pour gérer l'état de clôture en temps réel
        Schema::create('cloture_status', function (Blueprint $table) {
            $table->foreignId('caisse_id')->primary()->constrained('caisses')->onDelete('cascade');
            $table->enum('status', ['open', 'locked', 'closed'])->default('open');
            $table->string('locked_by_ws_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        // L'ordre est l'inverse de la création pour respecter les contraintes de clés étrangères
        Schema::dropIfExists('cloture_status');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('comptage_cb_log');
        Schema::dropIfExists('comptage_cheques');
        Schema::dropIfExists('comptage_retraits');
        Schema::dropIfExists('comptage_denominations');
        Schema::dropIfExists('comptage_details');
        Schema::dropIfExists('comptages');
        Schema::dropIfExists('terminaux_paiement');
        Schema::dropIfExists('caisses');
    }
};
