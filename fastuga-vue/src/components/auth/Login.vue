<script setup>
import { ref, inject } from "vue";
import { useRouter } from "vue-router";

import { useUserStore } from "../../stores/user.js";

const router = useRouter();
const axios = inject("axios");
const toast = inject("toast");
const socket = inject("socket");

const userStore = useUserStore();

const credentials = ref({
  username: "",
  password: "",
});

const loginBool = ref(false);

const emit = defineEmits(["login"]);

const login = async () => {
  loginBool.value = true;
  if (await userStore.login(credentials.value)) {
    loginBool.value = false;
    toast.success(
      "User " + userStore.user.name + " has entered the application."
    );
    await userStore.loadUser();
    emit("login");
    socket.emit("loggedIn", userStore.user);
    router.push("/");
  } else {
    loginBool.value = false;
    userStore.clearUser();
    credentials.value.password = "";
    toast.error("User credentials are invalid!");
  }
};
</script>

<template>
  <div>
    <form class="row g-3 needs-validation" novalidate @submit.prevent="login">
      <div class="mx-2 fastuga-font">
        <h3 class="mt-4">Login</h3>
      </div>
      <hr />
      <div class="login-body">
        <div class="login-field">
          <label class="login-label">Email:</label>
          <input
            type="text"
            class="form-control"
            id="inputEmail"
            placeholder="Enter Email"
            required
            v-model="credentials.username"
          />
        </div>
        <div class="login-field">
          <label class="login-label">Password:</label>
          <input
            type="password"
            class="form-control"
            id="inputPassword"
            placeholder="Enter Password"
            required
            v-model="credentials.password"
          />
        </div>
        <div class="mb-3 d-flex justify-content-center">
          <button type="button" class="btn btn-login px-5" @click="login" :disabled="loginBool">
            Login
          </button>
        </div>
      </div>
    </form>

    <div class="mb-3 d-flex justify-content-center">
      <p>
        <RouterLink to="/register"> Create a new account </RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>

.btn-login:hover,
.btn-login:active {
  background-color: #ff8300 !important;
  color: white;
}

.btn-login{
  height: 3rem;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.login-label {
  width: 20%;
}

.login-field {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 100%;
  padding: 20px;
}

.login-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 50%;
  margin: auto;
}

a{
    color: #725151;
  }

</style>
