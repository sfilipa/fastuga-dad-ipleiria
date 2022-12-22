<script setup>
import {inject, ref} from 'vue'
import {useUserStore} from "@/stores/user";
import {useRouter} from "vue-router";

const axiosLaravel = inject('axios')
const toast = inject('toast')
const router = useRouter()
const userStore = useUserStore()
const serverBaseUrl = inject("serverBaseUrl");

const axios = inject("axios");
const errors = ref({
  current_password: '',
  password: '',
  confirm_password: ''
})
const passwords = ref({
  current_password: '',
  password: '',
  confirm_password: ''
})

const passwordBool = ref(false);

const emit = defineEmits(['changedPassword'])

const changePassword = async () => {
  passwordBool.value = true;
  await axiosLaravel
      .patch(`/users/${userStore.userId}/password`, passwords.value)
      .then((response) => {
        passwordBool.value = false;
        toast.info("Password updated successfully");
        router.back()
      })
      .catch((error) => {
        passwordBool.value = false;
        console.log(error)
        errors.value = error.response.data.errors;
      });
}

</script>

<template>
  <form
      class="row g-3 needs-validation"
      novalidate
      @submit.prevent="changePassword"
  >
    <div class="mx-2 fastuga-font">
      <h3 class="mt-5 mb-3">Change Password</h3>
    </div>
    <hr>
    <div class="password-body">
      <div class="password-field">
        <label class="password-label">Current Password:</label>
        <input type="password" class="form-control" id="inputCurrentPassword" placeholder="Enter Current Password"
               required
               v-model="passwords.current_password"
               @focus="errors != null && errors.current_password != null ? errors.current_password = null : null"/>
      </div>
      <field-error-message :errors="errors" fieldName="current_password" class="password-field password-error"></field-error-message>
      <div class="password-field">
        <label class="password-label">New Password:</label>
        <input type="password" class="form-control" id="inputNewPassword" placeholder="Enter New Password" required
               v-model="passwords.password"
               @focus="errors != null && errors.password != null ? errors.password = null : null">
      </div>
      <field-error-message :errors="errors" fieldName="password" class="password-field password-error"></field-error-message>
      <div class="password-field">
          <label class="password-label">Password Confirmation:</label>
          <input type="password" class="form-control" id="inputPasswordConfirm"
                 placeholder="Enter Password Confirmation" required
                 v-model="passwords.confirm_password"
                 @focus="errors != null && errors.confirm_password != null ? errors.confirm_password = null : null">
        </div>
      <field-error-message :errors="errors" fieldName="confirm_password" class="password-field password-error"></field-error-message>
      <div class="mb-3 d-flex justify-content-center">
        <button type="button" class="btn btn-password px-5" @click="changePassword" :disabled="passwordBool">Change Password</button>
      </div>
    </div>
  </form>
</template>

<style scoped>

.btn-password:hover,
.btn-password:active {
  background-color: #ff8300 !important;
  color: white;
}

.btn-password {
  height: 3rem;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.password-label {
  width: 20%;
}

.password-field {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 100%;
  padding: 20px;
}

.password-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 50%;
  margin: auto;
}

a {
  color: #725151;
}

.password-error{
  margin-left: 40%;
  position: relative;
  top: -15px;
  margin-bottom: 0px !important;
  padding: 0px;
}
</style>

