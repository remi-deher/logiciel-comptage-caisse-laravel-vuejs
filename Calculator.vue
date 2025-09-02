<template>
  <div class="calculator-container" v-if="isLoaded">
    <div class="tab-selector">
      <button
        v-for="caisse in caisses"
        :key="caisse.id"
        class="tab-link"
        :class="{ active: caisse.id === caisseActiveId }"
        @click="caisseActiveId = caisse.id"
      >
        {{ caisse.nom_caisse }}
      </button>
    </div>

    <div v-if="caisseActive" class="caisse-content">
        <div class="ecart-display" :class="ecartStatus.class">
            Écart Caisse Actuelle : <span class="ecart-value">{{ formatCurrency(ecartStatus.ecart) }}</span>
            <p class="ecart-explanation">{{ ecartStatus.message }}</p>
        </div>

        <div class="form-section">
            <h3>Informations Générales</h3>
            <div class="grid-3">
                <div class="form-group">
                    <label>Fond de Caisse</label>
                    <input type="number" v-model.number="caisseActive.fond_de_caisse" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Total Ventes du Jour (Théorique)</label>
                    <input type="number" v-model.number="caisseActive.ventes_theoriques" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Rétrocessions / Prélèvements</label>
                    <input type="number" v-model.number="caisseActive.retrocession" placeholder="0.00">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Détail des Espèces (Total: {{ formatCurrency(totalEspeces) }})</h3>
            <h4>Billets</h4>
            <div class="grid">
                <div v-for="billet in denominations.billets" :key="billet.nom" class="form-group">
                    <label>{{ billet.valeur }} {{ currency.symbol }}</label>
                    <input type="number" v-model.number="caisseActive.especes[billet.nom]" min="0">
                    <span class="total-line">{{ formatCurrency(caisseActive.especes[billet.nom] * billet.valeur) }}</span>
                </div>
            </div>
            <h4>Pièces</h4>
            <div class="grid">
                 <div v-for="piece in denominations.pieces" :key="piece.nom" class="form-group">
                    <label>{{ piece.valeur >= 1 ? `${piece.valeur} ${currency.symbol}` : `${piece.valeur * 100} cts` }}</label>
                    <input type="number" v-model.number="caisseActive.especes[piece.nom]" min="0">
                    <span class="total-line">{{ formatCurrency(caisseActive.especes[piece.nom] * piece.valeur) }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div v-else>
        <p>Chargement des données du calculateur...</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      isLoaded: false,
      caisses: [],
      terminauxParCaisse: {},
      denominations: { billets: [], pieces: [] },
      currency: { symbol: '€' },
      
      caisseActiveId: null,
      
      // L'état du comptage actuel
      comptage: {
        // La clé sera l'ID de la caisse
        // ex: 1: { fond_de_caisse: 0, ... }
      }
    };
  },

  computed: {
    // Retourne l'objet de la caisse active
    caisseActive() {
        if (!this.caisseActiveId) return null;
        return this.comptage[this.caisseActiveId];
    },

    // Calcule le total des espèces pour la caisse active
    totalEspeces() {
        if (!this.caisseActive) return 0;
        const allDenominations = [...this.denominations.billets, ...this.denominations.pieces];
        return allDenominations.reduce((total, denom) => {
            const quantite = this.caisseActive.especes[denom.nom] || 0;
            return total + (quantite * denom.valeur);
        }, 0);
    },

    // Calcule l'écart et le message associé
    ecartStatus() {
        if (!this.caisseActive) return { ecart: 0, message: '', class: '' };

        const recetteTheorique = (this.caisseActive.ventes_theoriques || 0) + (this.caisseActive.retrocession || 0);
        const recetteReelle = this.totalEspeces - (this.caisseActive.fond_de_caisse || 0);
        const ecart = recetteReelle - recetteTheorique;
        
        if (Math.abs(ecart) < 0.01) {
            return { ecart, message: "L'écart est nul. La caisse est juste.", class: 'ecart-ok' };
        } else if (ecart > 0) {
            return { ecart, message: "Il y a un surplus dans la caisse.", class: 'ecart-positif' };
        } else {
            return { ecart, message: "Il manque de l'argent dans la caisse.", class: 'ecart-negatif' };
        }
    }
  },

  methods: {
    // Formate un nombre en devise
    formatCurrency(value) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: this.currency.code || 'EUR'
        }).format(value || 0);
    },

    // Initialise l'état du comptage
    initComptageState() {
        const comptageState = {};
        this.caisses.forEach(caisse => {
            
            // Préparer un objet vide pour les quantités de pièces/billets
            const especes = {};
            [...this.denominations.billets, ...this.denominations.pieces].forEach(denom => {
                especes[denom.nom] = 0;
            });

            comptageState[caisse.id] = {
                fond_de_caisse: 0,
                ventes_theoriques: 0,
                retrocession: 0,
                especes: especes,
                cheques: [],
                cb_logs: {}
            };
        });
        this.comptage = comptageState;
    },

    // Charge les données initiales depuis le backend Laravel
    async loadInitialData() {
        try {
            const response = await axios.get('/api/calculateur/init');
            const data = response.data;
            
            this.caisses = data.caisses;
            this.terminauxParCaisse = data.terminauxParCaisse;
            this.denominations = data.denominations;
            this.currency = data.currency;

            if (this.caisses.length > 0) {
                this.caisseActiveId = this.caisses[0].id;
            }

            this.initComptageState();
            this.isLoaded = true;

        } catch (error) {
            console.error("Erreur lors du chargement des données initiales:", error);
            // Gérer l'erreur, par exemple afficher un message à l'utilisateur
        }
    }
  },

  // Cette fonction est appelée automatiquement quand le composant est créé
  mounted() {
    this.loadInitialData();
  }
}
</script>

<style scoped>
/* Styles spécifiques au calculateur */
.calculator-container { padding: 2rem; max-width: 1200px; margin: auto; }
.tab-selector { display: flex; border-bottom: 2px solid #eee; margin-bottom: 1rem; }
.tab-link { padding: 0.8rem 1.5rem; border: none; background: none; cursor: pointer; font-size: 1.1rem; color: #777; border-bottom: 3px solid transparent; }
.tab-link.active { color: #3498db; border-bottom-color: #3498db; }

.ecart-display { padding: 1rem; border-radius: 8px; text-align: center; margin-bottom: 2rem; border: 1px solid #ddd; }
.ecart-value { font-size: 1.5rem; font-weight: bold; }
.ecart-explanation { font-style: italic; color: #666; }
.ecart-ok { background-color: #e8f5e9; color: #2e7d32; }
.ecart-positif { background-color: #fff3e0; color: #ef6c00; }
.ecart-negatif { background-color: #ffebee; color: #c62828; }

.form-section { margin-bottom: 2.5rem; }
.form-section h3, .form-section h4 { border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1rem; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1rem; }
.grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }

.form-group label { font-weight: bold; margin-bottom: 0.5rem; display: block; }
.form-group input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; text-align: right; }
.total-line { text-align: right; margin-top: 0.3rem; color: #888; font-size: 0.9em; }
</style>
