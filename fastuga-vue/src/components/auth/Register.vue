<script setup>
import {ref, inject} from 'vue'

import axios from 'axios'
import {useRouter} from 'vue-router'
import {useUserStore} from '../../stores/user.js'

const router = useRouter()
const toast = inject('toast')

const errors = ref(null)

const nameInput = ref('')
const emailInput = ref('')
const passwordInput = ref('')
const confirmationPasswordInput = ref('')

const phoneInput = ref('')
const nifInput = ref('')
const default_payment_typeInput = ref('visa')
const default_payment_referenceInput = ref('')

const emit = defineEmits(['register'])

const register = async () => {
  if (userValidations() == -1) {
    return
  }
  if (customerValidations() == -1) {
    return
  }
  if (paymentReferenceValidations() == -1) {
    return
  }
  let formData = new FormData();

  formData.append('name', nameInput.value);
  formData.append('email', emailInput.value);
  formData.append('password', passwordInput.value);
  formData.append('blocked', 0);
  formData.append('type', 'C');
  formData.append('_method', 'POST');
  formData.append('phone', phoneInput.value);
  formData.append('nif', nifInput.value);
  formData.append('default_payment_type', default_payment_typeInput.value);
  formData.append('default_payment_reference', default_payment_referenceInput.value);
  formData.append('points', 0);

  await axios.post(`http://localhost:8081/api/register`, formData)
      .then((response) => {
        toast.success('Register Successful.')
        emit('register')
        router.push({name: "Login"})
      })
      .catch((error) => {
        const errorSplit = error.response.data.split('.')
        toast.error("Register Failed - " + errorSplit[0] + ".")
      });
}

const userValidations = () => {
  if (nameInput.value == '') {
    errors.value = {
      name: ["Name field cannot be empty!"]
    }
    return -1
  }
  if (emailInput.value == '') {
    errors.value = {
      email: ["Email field cannot be empty!"]
    }
    return -1
  }
  var pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/
  if (!emailInput.value.match(pattern)) {
    errors.value = {
      email: ["Invalid Email Format!"]
    }
    return -1
  }
  if (passwordInput.value == '') {
    errors.value = {
      password: ["Password field cannot be empty!"]
    }
    return -1
  }
  if (!passwordInput.value.match('[0-9a-zA-Z]{8}')) {
    errors.value = {
      password: ["Invalid Password Format!"]
    }
    return -1
  }
  if (confirmationPasswordInput.value == '') {
    errors.value = {
      confirmationPassword: ["Password Confirmation field cannot be empty!"]
    }
    return -1
  }
  if (!confirmationPasswordInput.value.match('[0-9a-zA-Z]{8}')) {
    errors.value = {
      confirmationPassword: ["Invalid Confirmation Password Format!"]
    }
    return -1
  }
  if (confirmationPasswordInput.value != passwordInput.value) {
    errors.value = {
      confirmationPassword: ["Password and Confirmation Password don't match!"]
    }
    return -1
  }
}

const customerValidations = () => {
  if (nifInput.value == '') {
    errors.value = {
      nif: ["NIF field cannot be empty!"]
    }
    return -1
  }
  var pattern = /^[1-9][0-9]{8}$/
  if (!nifInput.value.match(patterm)) {
    errors.value = {
      nif: ["Invalid NIF Format"]
    }
    return -1
  }
  if (phoneInput.value == '') {
    errors.value = {
      phone: ["Phone field cannot be empty!"]
    }
    return -1
  }
  var pattern = /^[1-9][0-9]{8}$/
  if (!phoneInput.value.match(pattern)) {
    errors.value = {
      phone: ["Invalid Phone Format"]
    }
    return -1
  }
}

const paymentReferenceValidations = () => {
  if (default_payment_typeInput.value == 'visa') {
    if (!default_payment_referenceInput.value.match('[1-9][0-9]{15}')) {
      errors.value = {
        visa: ["Invalid Visa Reference"]
      }
      return -1
    }
    if (default_payment_referenceInput.value == '') {
      errors.value = {
        visa: ["Default Payment Reference field cannot be empty!"]
      }
      return -1
    }
  } else if (default_payment_typeInput.value == 'mbway') {
    var pattern = /^[1-9][0-9]{8}$/
    if (!default_payment_referenceInput.value.match(pattern)) {
      errors.value = {
        mbway: ["Invalid Phone Number"]
      }
      return -1
    }
    if (default_payment_referenceInput.value == '') {
      errors.value = {
        mbway: ["Default Payment Reference field cannot be empty!"]
      }
      return -1
    }
  } else if (default_payment_typeInput.value == 'paypal') {
    var pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/
    if (!default_payment_referenceInput.value.match(pattern)) {
      errors.value = {
        paypal: ["Invalid Phone Format"]
      }
      if (default_payment_referenceInput.value == '') {
        errors.value = {
          paypal: ["Default Payment Reference field cannot be empty!"]
        }
        return -1
      }
      return -1
    }
  } else {
    errors.value = {
      default: ["Payment Type Not Supported"]
    }
    toast.error('Order was not created due to validation errors!')
    return -1
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
               v-model="nameInput">
        <field-error-message :errors="errors" fieldName="name"></field-error-message>
      </div>
      <div class="col-50">
        <label>Email:</label>
        <input type="text" class="form-control" id="inputEmail" placeholder="Enter Email" required
               v-model="emailInput">
        <field-error-message :errors="errors" fieldName="email"></field-error-message>
      </div>
    </div>
    <div class="row">
      <div class="col-50">
        <label>Password:</label>
        <input type="password" class="form-control" id="inputPassword" placeholder="Enter Password" required
               v-model="passwordInput">
        <field-error-message :errors="errors" fieldName="password"></field-error-message>
      </div>
      <div class="col-50">
        <label>Confirmation Password:</label>
        <input type="password" class="form-control" id="inputPasswordConfirmation"
               placeholder="Enter Confirmation Password" required v-model="confirmationPasswordInput">
        <field-error-message :errors="errors" fieldName="confirmationPassword"></field-error-message>
      </div>
    </div>
    <div class="row">
      <div class="col-50">
        <label>NIF:</label>
        <input type="text" class="form-control" id="inputNif" placeholder="Enter NIF" required
               v-model="nifInput">
        <field-error-message :errors="errors" fieldName="nif"></field-error-message>
      </div>
      <div class="col-50">
        <label>Phone Number:</label>
        <input type="text" class="form-control" id="inputPhoneNumber" placeholder="Enter Phone Number" required
               v-model="phoneInput">
        <field-error-message :errors="errors" fieldName="phone"></field-error-message>
      </div>
    </div>
    <div class="row">
      <div class="col-50">
        <label>Default Payment Type:</label>
        <select class="form-select" id="selectType" v-model="default_payment_typeInput">
          <option value="visa">Visa</option>
          <option value="mbway">MBWay</option>
          <option value="paypal">PayPal</option>
        </select>
      </div>
      <div class="col-50">
        <label>Default Payment Reference:</label>
        <div v-if="default_payment_typeInput == 'visa'">
          <input type="text" class="form-control" id="inputVisaReference"
                 placeholder="Enter Visa Card ID Payment Reference" required v-model="default_payment_referenceInput">
          <field-error-message :errors="errors" fieldName="visa"></field-error-message>
        </div>
        <div v-else-if="default_payment_typeInput == 'mbway'">
          <input type="text" class="form-control" id="inputNumberReference"
                 placeholder="Enter Phone Number Payment Reference" required v-model="default_payment_referenceInput">
          <field-error-message :errors="errors" fieldName="mbway"></field-error-message>
        </div>
        <div v-else>
          <input type="text" class="form-control" id="inputEmailReference" placeholder="Enter Email Payment Reference"
                 required v-model="default_payment_referenceInput">
          <field-error-message :errors="errors" fieldName="paypal"></field-error-message>
        </div>
      </div>
    </div>

    <div class="mb-3 d-flex justify-content-center">
      <button type="button" class="btn btn-success px-5" @click="register">Register</button>
    </div>
  </form>

  <div class="mb-3 d-flex justify-content-center">
    <p>
      <RouterLink to="/login"> Already have an account?</RouterLink>
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
