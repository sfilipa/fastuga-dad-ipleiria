<script setup>
import {ref, watch, computed, inject, onMounted} from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'
import {useUserStore} from "../../stores/user.js"
import axios from "axios";

const userStore = useUserStore()

const customer = ref(null)

const serverBaseUrl = inject("serverBaseUrl");

const toast = inject('toast')

const emit = defineEmits(["save", "cancel"]);

const editRow = ref(false);

const errors = ref(null)
const nameInput = ref(userStore.user.name)
const emailInput = ref(userStore.user.email)
const photoInput = ref(userStore.user.photo_url)

const phoneInput = ref(0)
const nifInput = ref(0)
const default_payment_typeInput = ref(null)
const default_payment_referenceInput = ref(null)

const loadCustomer = () => {
  if (userStore.user.type = 'C') {
    phoneInput.value = userStore.customer.phone
    nifInput.value = userStore.customer.nif
    default_payment_typeInput.value = userStore.customer.default_payment_type.toLowerCase()
    default_payment_referenceInput.value = userStore.customer.default_payment_reference
  }
}
const newPhoto = ref(null)

const save = async () => {
  if (updateUserValidations() == -1) {
    return
  }

  let formData = new FormData();

  formData.append('name', nameInput.value);
  formData.append('email', emailInput.value);
  formData.append('blocked', userStore.user.blocked);
  formData.append('type', userStore.user.type);
  formData.append('photo_url', photoInput.value);
  formData.append('_method', 'PUT');
  if (userStore.user.type == 'C') {

    if (updateCustomerValidations() == -1) {
      return
    }
    if (paymentReferenceValidations() == -1) {
      return
    }
    formData.append('phone', phoneInput.value);
    formData.append('nif', nifInput.value);
    formData.append('default_payment_type', default_payment_typeInput.value);
    formData.append('default_payment_reference', default_payment_referenceInput.value);
    formData.append('user_id', userStore.user.id);
    formData.append('points', userStore.customer.points);
  }
  await axios.post(`${serverBaseUrl}/api/users/${userStore.user.id}`,
      formData)
      .then((response) => {
        userStore.loadUser()
        editRow.value = false;
        toast.success("Profile updated")
      })
      .catch((error) => {
        if(error.response.data){
          toast.error(error.response.data.message)
        }else{
          toast.error('Edit Profile Failed!')
        }
      });
}

const updateUserValidations = () => {
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
  if (!emailInput.value.match('[a-zA-Z0-9.+_]+@[a-zA-Z0-9.+_]+.[a-zA-Z]')) {
    errors.value = {
      email: ["Invalid Email Format!"]
    }
    return -1
  }
  return 0
}


const updateCustomerValidations = () => {
  if (phoneInput.value == '') {
    errors.value = {
      phone: ["Phone field cannot be empty!"]
    }
    return -1
  }
  if (!phoneInput.value.match('[1-9][0-9]{8}')) {
    errors.value = {
      phone: ["Invalid Phone Format"]
    }
    return -1
  }
  if (nifInput.value == '') {
    errors.value = {
      nif: ["NIF field cannot be empty!"]
    }
    return -1
  }
  if (!nifInput.value.match('[1-9][0-9]{8}')) {
    errors.value = {
      nif: ["Invalid NIF Format"]
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
    if (!default_payment_referenceInput.value.match('[1-9][0-9]{8}')) {
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
    if (!default_payment_referenceInput.value.match('[a-zA-Z0-9.+_]+@[a-zA-Z0-9.+_]+.[a-zA-Z]')) {
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

const cancel = () => {
  editRow.value = false;
}

const editProfile = () => {
  if (userStore.user.type == 'C') {
    loadCustomer()
  }
  editRow.value = true;
};


const updatePhoto = (e) => {
  if (!e.target.files.length) {
    return;
  }

  photoInput.value = e.target.files[0];
  newPhoto.value = URL.createObjectURL(e.target.files[0]);
}

</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
    <h3 class="mt-5 mb-3">{{ userStore.user.name }}</h3>
    <hr/>

    <div class="d-flex flex-wrap justify-content-between">

      <div class="w-75 pe-4">
        <div class="row">
          <div class="col-50">
            <b>Name:</b>
            <!-- Editing Row Name -->
            <div v-if="editRow" class="mb-2">
              <input type="text" class="form-control" id="inputName" placeholder="Enter Name" required
                     v-model="nameInput">
              <field-error-message :errors="errors" fieldName="name"></field-error-message>
            </div>
            <div v-else>
              <p> {{ userStore.user.name }}</p>
            </div>
          </div>

          <div class="col-50">
            <b>Email:</b>
            <!-- Editing Row Email -->
            <div v-if="editRow" class="mb-2">
              <input type="text" class="form-control" id="inputEmail" placeholder="Enter Email" required
                     v-model="emailInput">
              <field-error-message :errors="errors" fieldName="email"></field-error-message>
            </div>
            <div v-else>
              <p>{{ userStore.user.email }}</p>
            </div>
          </div>
        </div>

        <div v-if="userStore.user.type == 'C'">
          <div class="row">
            <div class="col-50">
              <b>Phone:</b>
              <!-- Editing Row Phone -->
              <div v-if="editRow" class="mb-2">
                <input type="text" class="form-control" id="inputName" placeholder="Enter Phone" required
                       v-model="phoneInput">
                <field-error-message :errors="errors" fieldName="phone"></field-error-message>
              </div>
              <div v-else>
                <p>{{ userStore.customer.phone }}</p>
              </div>
            </div>

            <div class="col-50">
              <b>NIF:</b>
              <!-- Editing Row Nif -->
              <div v-if="editRow" class="mb-2">
                <input type="text" class="form-control" id="inputEmail" placeholder="Enter NIF" required
                       v-model="nifInput">
                <field-error-message :errors="errors" fieldName="nif"></field-error-message>
              </div>
              <div v-else>
                <p> {{ userStore.customer.nif }}</p>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-50">
              <b>Default Payment Type:</b>
              <!-- Editing Row Default Payment Type -->
              <div v-if="editRow" class="mb-2">
                <select class="form-select" id="selectType" v-model="default_payment_typeInput">
                  <option value="visa">Visa</option>
                  <option value="mbway">MBWay</option>
                  <option value="paypal">PayPal</option>
                </select>
              </div>
              <div v-else>
                <p>{{ userStore.customer.default_payment_type }}</p>
              </div>
            </div>
            <div class="col-50">
              <b>Default Payment Reference:</b>
              <!-- Editing Row Default Payment Type -->
              <div v-if="editRow" class="mb-2">
                <div v-if="default_payment_typeInput == 'visa'">
                  <input type="text" class="form-control" id="inputVisaReference"
                         placeholder="Enter Visa Card ID Payment Reference" required
                         v-model="default_payment_referenceInput">
                  <field-error-message :errors="errors" fieldName="visa"></field-error-message>
                </div>
                <div v-else-if="default_payment_typeInput == 'mbway'">
                  <input type="text" class="form-control" id="inputNumberReference"
                         placeholder="Enter Phone Number Payment Reference" required
                         v-model="default_payment_referenceInput">
                  <field-error-message :errors="errors" fieldName="mbway"></field-error-message>
                </div>
                <div v-else>
                  <input type="text" class="form-control" id="inputEmailReference"
                         placeholder="Enter Email Payment Reference"
                         required v-model="default_payment_referenceInput">
                  <field-error-message :errors="errors" fieldName="paypal"></field-error-message>
                </div>
              </div>
              <div v-else>
                <p> {{ userStore.customer.default_payment_reference }}</p>
              </div>
            </div>
            <h5>Points:</h5>
            <p>{{ userStore.customer.points }}</p>
          </div>
        </div>
      </div>
      <div class="w-25">
        <div class="mb-3">
          <b class="form-label">Photo:</b>

          <div v-if="editRow" class="mb-2">
            <div class="form-control text-center">

              <div v-if="newPhoto != null" class="mb-2">
                <img :src="newPhoto" class="w-100"/>
              </div>
              <div v-else>
                <img :src="userStore.userPhotoUrl" class="w-100"/>
              </div>
              <input type="file" class="form-control" id="photo_url" name="photo_url"
                     accept="image/png, image/jpeg, image/jpg" @change="updatePhoto">
            </div>
          </div>
          <div v-else>
            <div class="form-control text-center">
              <img
                  :src="userStore.userPhotoUrl" class="w-100"/>
            </div>
          </div>
        </div>

        <div v-if="!editRow" class="mb-2">
          <button type="button" class="btn btn-primary px-5" @click="editProfile">Edit Profile</button>
        </div>
      </div>
    </div>
    <div v-if="editRow" class="mb-2">
      <div class="mb-3 d-flex justify-content-end">
        <button type="button" class="btn btn-primary px-5" @click="save">Save</button>
        <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
      </div>
    </div>
  </form>
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
}

input[type=password] {
  width: 100%;
  margin-bottom: 20px;
  padding: 12px;
}

.btn-success:hover {
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
