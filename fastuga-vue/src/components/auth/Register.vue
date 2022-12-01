<script setup>
import { ref, inject } from 'vue'

import axios from 'axios'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user.js'

const router = useRouter()
const toast = inject('toast')

const userStore = useUserStore()

const credentials = ref({
  email: '',
  password: '',
  confirmationPassword: '',
  name: '',
  phone: '',
  nif: '',
  default_payment_type: ref("visa"),
  default_payment_reference: '',
  type: 'C',
  blocked: 0,
  points: 0
})


const emit = defineEmits(['register'])

const register = async () => {
  if (credentials.value.password == credentials.value.confirmationPassword) {
    await axios.post(`http://localhost:8081/api/register`, credentials.value)
      .then((response) => {
        toast.success('Register Successful.')
        emit('register')
        router.push({ name: "Login" })
      })
      .catch((error) => {
        const errorSplit = error.response.data.split('.')
        toast.error("Register Failed - " + errorSplit[0] + ".")
      });
  } else {
    toast.error('Password and Confirmation Password should match!')
  }
}

</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="register">
    <h3 class="mt-4">Register</h3>
    <hr>
    <div class="row">
      <div class="col-50">
        <label>Name:</label>
        <input type="text" class="form-control" id="inputName" placeholder="Enter Name" required
          v-model="credentials.name">
      </div>
      <div class="col-50">
        <label>Email:</label>
        <input type="text" class="form-control" id="inputEmail" placeholder="Enter Email" required
          v-model="credentials.email">
      </div>
    </div>
    <div class="row">
      <div class="col-50">
        <label>NIF:</label>
        <input type="text" class="form-control" id="inputNif" placeholder="Enter NIF" required
          v-model="credentials.nif">
      </div>
      <div class="col-50">
        <label>Phone Number:</label>
        <input type="text" class="form-control" id="inputPhoneNumber" placeholder="Enter Phone Number" required
          v-model="credentials.phone">
      </div>
    </div>
    <div class="row">
      <div class="col-50">
        <label>Password:</label>
        <input type="password" class="form-control" id="inputPassword" placeholder="Enter Password" required
          v-model="credentials.password">
      </div>
      <div class="col-50">
        <label>Confirmation Password:</label>
        <input type="password" class="form-control" id="inputPasswordConfirmation"
          placeholder="Enter Confirmation Password" required v-model="credentials.confirmationPassword">
      </div>
    </div>
    <div class="row">
      <div class="col-50">
        <label>Default Payment Type:</label>
        <select class="form-select" id="selectType" v-model="credentials.default_payment_type">
          <option value="visa">Visa</option>
          <option value="mbway">MBWay</option>
          <option value="paypal">PayPal</option>
        </select>
      </div>
      <div class="col-50">
        <label>Default Payment Reference:</label>
        <div v-if="credentials.default_payment_type == 'visa'">
          <input type="text" class="form-control" id="inputVisaReference"
            placeholder="Enter Visa Card ID Payment Reference" required v-model="credentials.default_payment_reference">
        </div>
        <div v-else-if="credentials.default_payment_type == 'mbway'">
          <input type="text" class="form-control" id="inputNumberReference"
            placeholder="Enter Phone Number Payment Reference" required v-model="credentials.default_payment_reference">
        </div>
        <div v-else>
          <input type="text" class="form-control" id="inputEmailReference" placeholder="Enter Email Payment Reference"
            required v-model="credentials.default_payment_reference">
        </div>
      </div>
    </div>

    <div class="mb-3 d-flex justify-content-center">
      <button type="button" class="btn btn-success px-5" @click="register">Register</button>
    </div>
  </form>

  <div class="mb-3 d-flex justify-content-center">
    <p>
      <RouterLink to="/login"> Already have an account? </RouterLink>
    </p>
  </div>
</template>

<style scoped>
@media (max-width: 800px) {
  .row {
    flex-direction: column-reverse;
  }

  .col-25 {
    margin-bottom: 20px;
  }
}

input[type=text] {
  width: 100%;
  margin-bottom: 20px;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 3px;
}

input[type=password] {
  width: 100%;
  margin-bottom: 20px;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 3px;
}

.btn:hover {
  background-color: #0b450f;
}

select {
  width: 100%;
  margin-bottom: 20px;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 3px;
}

body {
  font-family: Arial;
  font-size: 17px;
  padding: 8px;
}

* {
  box-sizing: border-box;
}

.row {
  display: -ms-flexbox;
  /* IE10 */
  display: flex;
  -ms-flex-wrap: wrap;
  /* IE10 */
  flex-wrap: wrap;
  margin: 0 -16px;
}

.col-25 {
  -ms-flex: 25%;
  /* IE10 */
  flex: 25%;
}

.col-50 {
  -ms-flex: 50%;
  /* IE10 */
  flex: 50%;
}

.col-75 {
  -ms-flex: 75%;
  /* IE10 */
  flex: 75%;
}

.col-25,
.col-50,
.col-75 {
  padding: 0 16px;
}
</style>
