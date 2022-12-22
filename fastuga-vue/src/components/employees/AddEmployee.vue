<script setup>
import { ref, inject } from "vue";
import avatarNoneUrl from "@/assets/avatar-none.png";

import { useRouter } from "vue-router";

const router = useRouter();
const toast = inject("toast");
const axios = inject("axios");

const newPhoto = ref(null);
const emit = defineEmits(["addEmployee"]);

const errors = ref(null);

const nameInput = ref("");
const typeInput = ref("EC");
const emailInput = ref("");
const passwordInput = ref("");
const blockedInput = ref(0);
const photoInput = ref("");

const addEmployeeBool = ref(false);

const updatePhoto = (e) => {
  if (!e.target.files.length) {
    return;
  }

  // Save uploaded image
  const uploadedImage = e.target.files[0];

  // Create temporary Url
  newPhoto.value = URL.createObjectURL(uploadedImage);

  // Save image in base64
  const reader = new FileReader();
  reader.readAsDataURL(uploadedImage);
  reader.onload = (event) => {
    photoInput.value = event.target.result;
  };
};

const addEmployee = async () => {
  addEmployeeBool.value = true;
  let formData = new FormData();

  formData.append("name", nameInput.value);
  formData.append("type", typeInput.value);
  formData.append("email", emailInput.value);
  formData.append("password", passwordInput.value);
  formData.append("blocked", blockedInput.value);
  formData.append("photo_url", photoInput.value);

  await axios
    .post(`/users`, formData)
    .then((response) => {
      toast.info("Employee '" + response.data.data.name + "' was created");
      emit("addEmployee");
      router.push({ name: "Employees" });
    })
    .catch((error) => {
      console.log(error);
      errors.value = error.response.data.errors;
      addEmployeeBool.value = false;
    });
};
</script>

<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    @submit.prevent="addEmployee"
  >
    <div class="add-employee-title">
      <h3 class="mt-4">Add a New Employee</h3>
      <hr />
    </div>
    <div class="employee-add-container">
      <div class="employee-add-field">
        <label class="employee-add-label">Name:</label>
        <input
          type="text"
          class="form-control"
          id="inputName"
          placeholder="Enter Name"
          required
          v-model="nameInput"
          @focus="
            errors != null && errors.name != null ? (errors.name = null) : null
          "
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="name"
        class="employee-add-field add-employee-error"
      ></field-error-message>

      <div class="employee-add-field">
        <label class="employee-add-label">Email:</label>
        <input
          type="text"
          class="form-control"
          id="inputEmail"
          placeholder="Enter Email"
          required
          v-model="emailInput"
          @focus="
            errors != null && errors.email != null
              ? (errors.email = null)
              : null
          "
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="email"
        class="employee-add-field add-employee-error"
      ></field-error-message>

      <div class="employee-add-field">
        <label class="employee-add-label">Password:</label>
        <input
          type="password"
          class="form-control"
          id="inputPassword"
          placeholder="Enter Password"
          required
          v-model="passwordInput"
          @focus="
            errors != null && errors.password != null
              ? (errors.password = null)
              : null
          "
        />
      </div>
      <field-error-message
        :errors="errors"
        fieldName="password"
        class="employee-add-field add-employee-error"
      ></field-error-message>

      <div class="employee-add-field">
        <label class="employee-add-label">Employee Type:</label>
        <select
          class="form-select"
          id="selectType"
          v-model="typeInput"
          @focus="
            errors != null && errors.type != null ? (errors.type = null) : null
          "
        >
          <option value="EC">Chef</option>
          <option value="ED">Delivery</option>
          <option value="EM">Manager</option>
        </select>
      </div>
      <field-error-message
        :errors="errors"
        fieldName="type"
        class="employee-add-field add-employee-error"
      ></field-error-message>

      <div class="employee-add-field">
        <label class="employee-add-label">Photo: </label>
        <input
          type="file"
          id="photo_url"
          name="photo_url"
          accept="image/png, image/jpeg, image/jpg"
          @change="updatePhoto"
          class="form-control"
          @focus="
            errors != null && errors.photo_url != null
              ? (errors.photo_url = null)
              : null
          "
        />
        <field-error-message
          :errors="errors"
          fieldName="photo_url"
          class="employee-add-field add-employee-error"
        ></field-error-message>
      </div>

      <div class="mb-3 d-flex justify-content-end employee-add-buttons">
        <div class="employee-add-buttons-div">
          <router-link
            class="link-secondary fastuga-font"
            :to="{ name: 'Employees' }"
            aria-label="Cancel"
          >
            <button type="button" class="btn employee-cancel-button px-5">
              Cancel
            </button>
          </router-link>

          <button
            type="button"
            class="btn employee-add-button px-5"
            @click="addEmployee"
            :disabled="addEmployeeBool"
          >
            <span> Add </span>
          </button>
        </div>
      </div>
    </div>
  </form>
</template>

<style scoped>
.add-employee-error {
  margin-left: 30%;
  position: relative;
  top: -15px;
  margin-bottom: 0px !important;
}

.employee-cancel-button:hover,
.employee-cancel-button:active {
  background-color: #4d3838 !important;
  color: white !important;
}

.employee-cancel-button {
  height: 3rem;
  margin-right: 60px;
  background-color: #5e4444;
  color: white;
  display: inline-block;
}

.employee-add-button:hover,
.employee-add-button:first-child:active {
  background-color: #ff8300;
  color: white;
}

.employee-add-button {
  height: 3rem;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
  width: 146px;
}

.employee-add-buttons-div {
  margin: auto;
}

.employee-add-buttons {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.employee-add-label {
  width: 20%;
}

.employee-add-field {
  display: flex;
  flex-direction: row;
  width: 80%;
  align-items: center;
  margin-bottom: 2%;
}

.employee-add-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 5%;
}

hr {
  background: #362222;
  height: 6px;
  opacity: initial;
}

.add-employee-title {
  text-align: center;
}
</style>
