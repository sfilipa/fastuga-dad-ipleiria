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
  const errors = ref(null)
  const passwords = ref({
        current_password: '',
        password: '',
        confirm_password: ''
    })

  const emit = defineEmits(['changedPassword'])

  const changePassword = async () => {
    await axiosLaravel
        .patch(`/users/${userStore.userId}/password`, passwords.value)
        .then((response) => {
          console.log(response)
          toast.info("Password updated successfully");
          router.back()
        })
        .catch((error) => {
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
    <h3 class="mt-5 mb-3">Change Password</h3>
    <hr>
    <div class="mb-2">
      <div class="col-md-offset-5 col-md-4 center">
        <label>Current Password:</label>
        <input type="password" class="form-control" id="inputCurrentPassword" placeholder="Enter Current Password" required
               v-model="passwords.current_password">
        <field-error-message :errors="errors" fieldName="current_password"></field-error-message>
      </div>
    </div>
      <div class="mb-2">
        <div class="col-md-offset-5 col-md-4 center">
          <label>New Password:</label>
          <input type="password" class="form-control" id="inputNewPassword" placeholder="Enter New Password" required
                 v-model="passwords.password">
          <field-error-message :errors="errors" fieldName="password"></field-error-message>
        </div>
      </div>
    <div class="mb-2">
      <div class="col-md-offset-5 col-md-4 center">
        <label>Password Confirmation:</label>
        <input type="password" class="form-control" id="inputPasswordConfirm" placeholder="Enter Password Confirmation" required
               v-model="passwords.confirm_password">
        <field-error-message :errors="errors" fieldName="confirm_password"></field-error-message>
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-center">
      <button type="button" class="btn btn-success px-5" @click="changePassword">Change Password</button>
    </div>
  </form>
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

input[type=password] {
  width: 100%;
  margin-bottom: 10px;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 3px;
}
</style>

