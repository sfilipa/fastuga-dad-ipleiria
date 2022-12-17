<script setup>
import { ref, inject } from 'vue'
import { useRouter } from 'vue-router'

import { useUserStore } from '../../stores/user.js'

const router = useRouter()
const axios = inject('axios')
const toast = inject('toast')

const userStore = useUserStore()

const credentials = ref({
  username: '',
  password: ''
})

const emit = defineEmits(['login'])

const login = async () => {
  if (await userStore.login(credentials.value)) {
    toast.success('User ' + userStore.user.name + ' has entered the application.')
    socket.emit('userLoggedIn', userStore.user)
    await userStore.loadUser()
    emit('login')
    router.push({name: "PublicBoard"})
  } else {
    userStore.clearUser()
    credentials.value.password = ''
    toast.error('User credentials are invalid!')
  }
}
</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="login">
    <h3 class="mt-4">Login</h3>
    <hr>
    <div class="mb-2">
      <div class="col-md-offset-5 col-md-4 center">
        <label>Email:</label>
        <input type="text" class="form-control" id="inputEmail" placeholder="Enter Email" required
          v-model="credentials.username">
      </div>
    </div>
    <div class="mb-2">
      <div class="col-md-offset-5 col-md-4 center">
        <label>Password:</label>
        <input type="password" class="form-control" id="inputPassword" placeholder="Enter Password" required
          v-model="credentials.password">
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-center">
      <button type="button" class="btn btn-success px-5" @click="login">Login</button>
    </div>
  </form>

  <div class="mb-3 d-flex justify-content-center">
    <p>
      <RouterLink to="/register"> Create a new account </RouterLink>
    </p>
  </div>
</template>

<style scoped>
.center {
  display: block;
  margin-right: auto;
  margin-left: auto;
}


.btn:hover {
  background-color: #0b450f;
}

input[type=text] {
  width: 100%;
  margin-bottom: 0px;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 3px;
}

input[type=password] {
  width: 100%;
  margin-bottom: 10px;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 3px;
}
</style>