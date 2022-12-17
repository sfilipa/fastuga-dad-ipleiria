<script setup>
import { RouterLink } from "vue-router"

</script>

<template>
  <div class="about about-body fastuga-font">
    <h1><b>Welcome to Fastuga</b></h1>
    <br>
    <p class="fastuga-font home-font">
      Check our <router-link :to="{ name: 'Menu' }">Menu <i class="bi bi-cup-straw"></i></router-link>
       or the <router-link :to="{ name: 'PublicBoard' }">PublicBoard <i class="bi bi-card-checklist"></i></router-link>!
    </p>
    <img src="@/assets/backgroundHomePage.jpg" class="home-view">
  </div>
</template>

<style scoped>
  .home-view{
    width: 72%;
    margin-top: 5%;
  }

  
  a{
    color: #725151;
  }

  .home-font{
    font-size: 20px;
  }

  .about-body{
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 2%;
  }
  
</style>
