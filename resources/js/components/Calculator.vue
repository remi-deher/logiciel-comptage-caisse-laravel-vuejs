<template>
  <div class="container">
    <h1>Calculateur de Caisse</h1>
    
    <p>Mon nouveau calculateur en Vue.js !</p>

  </div>
</template>

<script>
export default {
  data() {
    return {
      // Les données de notre application iront ici
      caisses: [],
      terminaux: [],
    };
  },
  methods: {
    // Les méthodes pour charger/sauvegarder les données iront ici
  },
  mounted() {
    console.log('Le composant Calculateur est monté !');
  }
}
</script>

<style scoped>
/* Les styles spécifiques à ce composant iront ici */
.container {
  padding: 2rem;
}
</style>
