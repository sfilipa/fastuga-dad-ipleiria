<script setup>
  import { ref } from 'vue'

  const credentials = ref({
        email: '',
        password: ''
    })

  const emit = defineEmits(['login'])

  const login = () => {
      // FALTA FAZER O LOGIN
      if(credentials.email != "" && credentials.password != "") {
      }else{
        console.log("odeio isto")
      }
      //emit('login')
  }
</script>

<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    @submit.prevent="login"
  >
    <h3 class="mt-4">Login</h3>
    <hr>
    <div class="mb-2">
      <div class="col-md-offset-5 col-md-4" id="center" >
        <input
          type="text"
          class="form-control"
          id="inputEmail" placeholder="Enter Email"
          required
          v-model="credentials.email"
        >
      </div> 
      </div>
    <div class="mb-2">
      <div class="col-md-offset-5 col-md-4"  id="center">
        <input
          type="password"
          class="form-control"
          id="inputPassword"
          placeholder="Enter Password"
          required
          v-model="credentials.password"
        >
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-center">
      <p v-if="validated">Hey You got the error</p>
      <button
        type="button"
        class="btn btn-success px-5"
        @click="login"
      >Login</button>
    </div>
  </form>
  
      <div class="mb-3 d-flex justify-content-center">
        <p>
        <RouterLink to="/register"> Create a new account </RouterLink>
      </p>
    </div>
</template>

<style scoped>
#center {
  display: block;
  margin-right: auto;
  margin-left: auto;
}
</style>