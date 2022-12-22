<script setup>
import { ref, inject } from "vue";

import axios from "axios";
import { useRouter } from "vue-router";
import { useUserStore } from "../../stores/user.js";

const router = useRouter();
const toast = inject("toast");

const errors = ref({
  name: null,
  email: null,
  password: null,
  passwordConfirmation: null,
  name: null,
  nif: null,
  phone: null,
  payment_type: null,
  payment_reference: null,
});

const nameInput = ref("");
const emailInput = ref("");
const passwordInput = ref("");
const confirmationPasswordInput = ref("");

const phoneInput = ref("");
const nifInput = ref("");
const default_payment_typeInput = ref("visa");
const default_payment_referenceInput = ref("");

const registerBool = ref(false);

const emit = defineEmits(["register"]);

const register = async () => {
  registerBool.value = true;
  const userV = userValidations();
  const customerV =  customerValidations();
  const paymentV = paymentReferenceValidations();
  if (userV == -1 || customerV == -1 || paymentV == -1) {
    registerBool.value = false;
    console.log(errors)
    return;
  }
  let formData = new FormData();

  formData.append("name", nameInput.value);
  formData.append("email", emailInput.value);
  formData.append("password", passwordInput.value);
  formData.append("blocked", 0);
  formData.append("type", "C");
  formData.append("_method", "POST");
  formData.append("phone", phoneInput.value);
  formData.append("nif", nifInput.value);
  formData.append("default_payment_type", default_payment_typeInput.value);
  formData.append(
    "default_payment_reference",
    default_payment_referenceInput.value
  );
  formData.append("points", 0);

  await axios
    .post(`http://localhost:8081/api/register`, formData)
    .then((response) => {
      registerBool.value = false;
      toast.success("Register Successful.");
      emit("register");
      router.push({ name: "Login" });
    })
    .catch((error) => {
      registerBool.value = false;
      const errorSplit = error.response.data.split(".");
      toast.error("Register Failed - " + errorSplit[0] + ".");
    });
};

const userValidations = () => {
  let invalid = 0;
  
  // Name
  if (nameInput.value == "") {
    errors.value.name =  ["Name field cannot be empty!"];
    invalid = -1;
  }

  // Email
  var pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
  if (emailInput.value == "") {
    errors.value.email = ["Email field cannot be empty!"];
    invalid = -1;
  }
  else if (!emailInput.value.match(pattern)) {
    errors.value.email = ["Invalid Email Format!"];
    invalid = -1;
  }

  // Password
  if (passwordInput.value == "") {
    errors.value.password = ["Password field cannot be empty!"];
    invalid = -1;
  }
  else if (!passwordInput.value.match("[0-9a-zA-Z]{8}")) {
    errors.value.password = ["Invalid Password Format!"];
    invalid = -1;
  }

  // Confirmation password
  if (confirmationPasswordInput.value == "") {
    errors.value.confirmationPassword = ["Password Confirmation field cannot be empty!"];
    invalid = -1;
  }
  else if (!confirmationPasswordInput.value.match("[0-9a-zA-Z]{8}")) {
    errors.value.confirmationPassword = ["Invalid Confirmation Password Format!"];
    invalid = -1;
  }
  else if (confirmationPasswordInput.value != passwordInput.value) {
    errors.value.confirmationPassword = ["Password and Confirmation Password don't match!"];
    invalid = -1;
  }

  return invalid;
};

const customerValidations = () => {
  let invalid = 0;
  if (nifInput.value == "") {
    errors.value.nif = ["NIF field cannot be empty!"];
    invalid = -1;
  }
  var pattern = /^[1-9][0-9]{8}$/;
  if (!nifInput.value.match(pattern)) {
    errors.value.nif = ["Invalid NIF Format"];
    invalid = -1;
  }
  if (phoneInput.value == "") {
    errors.value.phone = ["Phone field cannot be empty!"];
    invalid = -1;
  }
  var pattern = /^[1-9][0-9]{8}$/;
  if (!phoneInput.value.match(pattern)) {
    errors.value.phone = ["Invalid Phone Format"];
    invalid = -1;
  }
  return invalid;
};

const paymentReferenceValidations = () => {
  let invalid = 0;
  if (default_payment_typeInput.value == "visa") {
    if (default_payment_referenceInput.value == "") {
      errors.value.payment_reference = ["Default Payment Reference field cannot be empty!"];
      invalid = -1;
    } else if (!default_payment_referenceInput.value.match("[1-9][0-9]{15}")) {
      errors.value.payment_reference = ["Invalid Visa Reference"];
      invalid = -1;
    }
  } else if (default_payment_typeInput.value == "mbway") {
    var pattern = /^[1-9][0-9]{8}$/;
    if (default_payment_referenceInput.value == "") {
      errors.value.payment_reference = ["Default Payment Reference field cannot be empty!"];
      invalid = -1;
    } else if (!default_payment_referenceInput.value.match(pattern)) {
      errors.value.payment_reference = ["Invalid Phone Number"];
      invalid = -1;
    }
  } else if (default_payment_typeInput.value == "paypal") {
    var pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    if (default_payment_referenceInput.value == "") {
      errors.value.payment_reference = ["Default Payment Reference field cannot be empty!"];
      invalid = -1;
    } else if (!default_payment_referenceInput.value.match(pattern)) {
      errors.value.payment_reference = ["Invalid Phone Format"];
      invalid = -1;
    }
  } else {
    errors.value.payment_type = ["Payment Type Not Supported"];
    invalid = -1;
    toast.error("Order was not created due to validation errors!");
  }
  return invalid
};
</script>

<template>
  <form class="needs-validation" novalidate @submit.prevent="register">
    <div class="mx-2 fastuga-font">
      <h3 class="mt-4">Register</h3>
    </div>
    <hr />
    <div class="register-body">
      <div class="register-field">
        <label class="register-label">Name:</label>
        <input
          type="text"
          class="form-control"
          id="inputName"
          placeholder="Enter Name"
          required
          v-model="nameInput"
          @focus="errors != null && errors.name != null ? errors.name = null : null"
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="name"
        class="register-field register-error"
      ></field-error-message>
      <div class="register-field">
        <label class="register-label">Email:</label>
        <input
          type="text"
          class="form-control"
          id="inputEmail"
          placeholder="Enter Email"
          required
          v-model="emailInput"
          @focus="errors != null && errors.email != null ? errors.email = null : null"
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="email"
        class="register-field register-error"
      ></field-error-message>
      <div class="register-field">
        <label class="register-label">Password:</label>
        <input
          type="password"
          class="form-control"
          id="inputPassword"
          placeholder="Enter Password"
          required
          v-model="passwordInput"
          @focus="errors != null && errors.password != null ? errors.password = null : null"
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="password"
        class="register-field register-error"
      ></field-error-message>
      <div class="register-field">
        <label class="register-label">Confirmation Password:</label>
        <input
          type="password"
          class="form-control"
          id="inputPasswordConfirmation"
          placeholder="Enter Confirmation Password"
          required
          v-model="confirmationPasswordInput"
          @focus="errors != null && errors.confirmationPassword != null ? errors.confirmationPassword = null : null"
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="confirmationPassword"
        class="register-field register-error"
      ></field-error-message>
      <div class="register-field">
        <label class="register-label">NIF:</label>
        <input
          type="text"
          class="form-control"
          id="inputNif"
          placeholder="Enter NIF"
          required
          v-model="nifInput"
          @focus="errors != null && errors.nif != null ? errors.nif = null : null"
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="nif"
        class="register-field register-error"
      ></field-error-message>
      <div class="register-field">
        <label class="register-label">Phone Number:</label>
        <input
          type="text"
          class="form-control"
          id="inputPhoneNumber"
          placeholder="Enter Phone Number"
          required
          v-model="phoneInput"
          @focus="errors != null && errors.phone != null ? errors.phone = null : null"
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="phone"
        class="register-field register-error"
      ></field-error-message>
      <div class="register-field">
        <label class="register-label">Default Payment Type:</label>
        <select
          class="form-select"
          id="selectType"
          v-model="default_payment_typeInput"
          @focus="errors != null && errors.payment_type != null ? errors.payment_type = null : null"
        >
          <option value="visa">Visa</option>
          <option value="mbway">MBWay</option>
          <option value="paypal">PayPal</option>
        </select>
      </div>
      <field-error-message
        :errors="errors"
        fieldName="payment_type"
        class="register-field register-error"
      ></field-error-message>
      <div class="register-field">
        <label class="register-label">Default Payment Reference:</label>
        <div v-if="default_payment_typeInput == 'visa'" style="width: inherit">
          <input
            type="text"
            class="form-control"
            id="inputVisaReference"
            placeholder="Enter Visa Card ID Payment Reference"
            required
            v-model="default_payment_referenceInput"
            @focus="errors != null && errors.payment_reference != null ? errors.payment_reference = null : null"
          />
        </div>
        <div
          v-else-if="default_payment_typeInput == 'mbway'"
          style="width: inherit"
        >
          <input
            type="text"
            class="form-control"
            id="inputNumberReference"
            placeholder="Enter Phone Number Payment Reference"
            required
            v-model="default_payment_referenceInput"
            @focus="errors != null && errors.payment_reference != null ? errors.payment_reference = null : null"
          />
        </div>
        <div v-else style="width: inherit">
          <input
            type="text"
            class="form-control"
            id="inputEmailReference"
            placeholder="Enter Email Payment Reference"
            required
            v-model="default_payment_referenceInput"
            @focus="errors != null && errors.payment_reference != null ? errors.payment_reference = null : null"
          />
        </div>
      </div>
      <field-error-message
        :errors="errors"
        fieldName="payment_reference"
        class="register-field register-error"
      ></field-error-message>
      <div class="mb-3 d-flex justify-content-center">
        <button
          type="button"
          class="btn btn-register px-5"
          @click="register"
          :disabled="registerBool"
        >
          Register
        </button>
      </div>
    </div>
  </form>

  <div class="mb-3 d-flex justify-content-center">
    <p>
      <RouterLink to="/login"> Already have an account?</RouterLink>
    </p>
  </div>
</template>

<style scoped>

.register-error{
  margin-left: 60%;
  position: relative;
  top: -15px;
  margin-bottom: 0px !important;
}

.btn-register:hover,
.btn-register:active {
  background-color: #ff8300 !important;
  color: white;
}

.btn-register {
  height: 3rem;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.register-label {
  width: 40%;
}

.register-field {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 100%;
  padding: 10px;
}

.register-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 60%;
  margin: auto;
}

a {
  color: #725151;
}
</style>
