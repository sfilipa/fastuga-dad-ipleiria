<script setup>
import {ref, watch, computed, inject, onMounted} from "vue";
import avatarNoneUrl from '@/assets/avatar-none.png'
import {useUserStore} from "../../stores/user.js"

const axiosLaravel = inject('axios')
const userStore = useUserStore()

const customer = ref(null)

const serverBaseUrl = inject("serverBaseUrl");

const toast = inject('toast')

const emit = defineEmits(["save", "cancel"]);

const editRow = ref(false);

const errors = ref({
  name: null,
  email: null,
  nif: null,
  phone: null,
  payment_type: null,
  payment_reference: null,
});
const nameInput = ref(userStore.user.name)
const emailInput = ref(userStore.user.email)
const photoInput = ref(userStore.user.photo_url)

const phoneInput = ref(0)
const nifInput = ref(0)
const default_payment_typeInput = ref(null)
const default_payment_referenceInput = ref(null)

const userV = ref(0)
const customerV = ref(0)
const paymentV = ref(0)

const loadCustomer = () => {
  if (userStore.user.type = 'C') {
    phoneInput.value = userStore.customer.phone
    nifInput.value = userStore.customer.nif
    default_payment_typeInput.value = userStore.customer.default_payment_type.toLowerCase()
    default_payment_referenceInput.value = userStore.customer.default_payment_reference
  }
}
const profileBool = ref(false);
const newPhoto = ref(null)

const save = async () => {
  profileBool.value = true;
  userV.value = userValidations();

  if (userStore.user.type == 'C') {
    customerV.value = customerValidations();
    paymentV.value = paymentReferenceValidations();
  }

  if (userV.value == -1 || customerV.value == -1 || paymentV.value == -1) {
    profileBool.value = false;
    console.log(errors)
    return;
  }

  let formData = new FormData();

  formData.append('name', nameInput.value);
  formData.append('email', emailInput.value);
  formData.append('blocked', userStore.user.blocked);
  formData.append('type', userStore.user.type);
  formData.append('photo_url', photoInput.value);
  formData.append('_method', 'PUT');
  if (userStore.user.type == 'C') {
    const customerV = customerValidations();
    const paymentV = paymentReferenceValidations();

    formData.append('phone', phoneInput.value);
    formData.append('nif', nifInput.value);
    formData.append('default_payment_type', default_payment_typeInput.value);
    formData.append('default_payment_reference', default_payment_referenceInput.value);
    formData.append('user_id', userStore.user.id);
    formData.append('points', userStore.customer.points);
  }
  await axiosLaravel.post(`/users/${userStore.user.id}`,
      formData)
      .then((response) => {
        profileBool.value = false;
        userStore.loadUser()
        editRow.value = false;
        toast.success("Profile updated")
      })
      .catch((error) => {
        registerBool.value = false;
        console.log(error)
        if (error.response.data.message) {
          toast.error('Edit Profile Failed! - ' + error.response.data.message)
        } else {
          toast.error('Edit Profile Failed!')
        }
      });
}

const userValidations = () => {
  let invalid = 0;

  // Name
  if (nameInput.value == "") {
    errors.value.name = ["Name field cannot be empty!"];
    invalid = -1;
  }

  // Email
  var pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
  if (emailInput.value == "") {
    errors.value.email = ["Email field cannot be empty!"];
    invalid = -1;
  } else if (!emailInput.value.match(pattern)) {
    errors.value.email = ["Invalid Email Format!"];
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
  // Save uploaded image
  const uploadedImage = e.target.files[0];

  // Create temporary Url
  newPhoto.value = URL.createObjectURL(uploadedImage);

  // Save image in base64
  const reader = new FileReader();
  reader.readAsDataURL(uploadedImage);
  reader.onload = (event) => {
    photoInput.value = event.target.result;
    console.log(photoInput.value)
  }
}

</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
    <div class="mx-2 fastuga-font">
      <h3 class="mt-4">{{ userStore.user.name }}</h3>
    </div>
    <hr/>

    <div class="w-75 pe-4">

      <div class="profile-body">
        <div class="profile-field">
          <label class="profile-label">Name:</label>
          <!-- Editing Row Name -->
          <div v-if="editRow">
            <input
                type="text"
                class="form-control"
                id="inputName"
                placeholder="Enter Name"
                required
                v-model="nameInput"
                @focus="errors != null && errors.name != null ? errors.name = null : null">
            <field-error-message
                :errors="errors"
                fieldName="name"
            ></field-error-message>
          </div>
          <div v-else>
            <span> {{ userStore.user.name }}</span>
          </div>
        </div>

        <div class="profile-field">
          <label class="profile-label">Email:</label>
          <!-- Editing Row Email -->
          <div v-if="editRow">
            <input
                type="text"
                class="form-control"
                id="inputEmail"
                placeholder="Enter Email"
                required
                v-model="emailInput"
                @focus="errors != null && errors.email != null ? errors.email = null : null">
            <field-error-message
                :errors="errors"
                fieldName="email"
            ></field-error-message>
          </div>
          <div v-else>
            <span>{{ userStore.user.email }}</span>
          </div>
        </div>

        <div v-if="userStore.user.type == 'C'" class="div-customer-details">
          <div class="profile-field">
            <label class="profile-label">Phone Number:</label>
            <!-- Editing Row Phone -->
            <div v-if="editRow">
              <input
                  type="text"
                  class="form-control"
                  id="inputName"
                  placeholder="Enter Phone"
                  required
                  v-model="phoneInput"
                  @focus="errors != null && errors.phone != null ? errors.phone = null : null">
              <field-error-message :errors="errors" fieldName="phone"></field-error-message>
            </div>
            <div v-else>
              <span>{{ userStore.customer.phone }}</span>
            </div>
          </div>

          <div class="profile-field">
            <label class="profile-label">NIF:</label>
            <!-- Editing Row Nif -->
            <div v-if="editRow">
              <input
                  type="text"
                  class="form-control"
                  id="inputEmail"
                  placeholder="Enter NIF"
                  required
                  v-model="nifInput"
                  @focus="errors != null && errors.nif != null ? errors.nif = null : null">
              <field-error-message :errors="errors" fieldName="nif"></field-error-message>
            </div>
            <div v-else>
              <span> {{ userStore.customer.nif }}</span>
            </div>
          </div>


          <div class="profile-field">
            <label class="profile-label">Default Payment Type:</label>
            <!-- Editing Row Default Payment Type -->
            <div v-if="editRow">
              <select class="form-select" id="selectType" v-model="default_payment_typeInput"
                      @focus="errors != null && errors.payment_type != null ? errors.payment_type = null : null">
                <option value="visa">Visa</option>
                <option value="mbway">MBWay</option>
                <option value="paypal">PayPal</option>
              </select>
            </div>
            <div v-else>
              <span>{{ userStore.customer.default_payment_type }}</span>
            </div>
            <field-error-message
                :errors="errors"
                fieldName="payment_type"
                class="register-field register-error"
            ></field-error-message>
          </div>
          <div class="profile-field">
            <label class="profile-label">Default Payment Reference:</label>
            <!-- Editing Row Default Payment Type -->
            <div v-if="editRow">
              <div v-if="default_payment_typeInput == 'visa'" style="width: inherit">
                <input type="text" class="form-control" id="inputVisaReference"
                       placeholder="Enter Visa Card ID Payment Reference" required
                       v-model="default_payment_referenceInput"
                       @focus="errors != null && errors.payment_reference != null ? errors.payment_reference = null : null">
              </div>
              <div v-else-if="default_payment_typeInput == 'mbway'" style="width: inherit">
                <input type="text" class="form-control" id="inputNumberReference"
                       placeholder="Enter Phone Number Payment Reference" required
                       v-model="default_payment_referenceInput"
                       @focus="errors != null && errors.payment_reference != null ? errors.payment_reference = null : null">
              </div>
              <div v-else style="width: inherit">
                <input type="text" class="form-control" id="inputEmailReference"
                       placeholder="Enter Email Payment Reference"
                       required v-model="default_payment_referenceInput"
                       @focus="errors != null && errors.payment_reference != null ? errors.payment_reference = null : null"
                />
              </div>
            </div>
            <div v-else>
              <span> {{ userStore.customer.default_payment_reference }}</span>
            </div>
          </div>
          <field-error-message
              :errors="errors"
              fieldName="payment_reference"
              class="register-field register-error"
          ></field-error-message>
          <div class="profile-field">
            <label class="profile-label">Points:</label>
            <div class="mb-2">
              <span>{{ userStore.customer.points }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="w-25">
      <div class="mb-3">
        <b class="form-label">Photo:</b>

        <div v-if="editRow">
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
    </div>

    <div v-if="!editRow">
      <div class="mb-3 d-flex justify-content-center">
        <button type="button" class="btn px-5 btn-profile" @click="editProfile">Edit Profile</button>
      </div>
    </div>
    <div v-if="editRow">
      <div class="mb-3 d-flex justify-content-center">
        <div class="mx-2 mt-2">
          <button type="button" class="btn px-5 btn-profile" @click="save" :disabled="profileBool">Save</button>
        </div>
        <div class="mx-2 mt-2">
          <button type="button" class="btn px-5 btn-cancel" @click="cancel">Cancel</button>
        </div>
      </div>
    </div>
  </form>
</template>

<style scoped>

.div-customer-details {
  width: 100%;
}

.profile-error {
  margin-left: 60%;
  position: relative;
  top: -15px;
  margin-bottom: 0px !important;
}

.btn-cancel:hover,
.btn-cancel:active {
  background-color: #4d3838;
  border-color: #4d3838;
  color: white;
}

.btn-cancel {
  height: 3rem;
  align-self: center;
  background-color: #5e4444;
  border-color: #5e4444;
  color: white;
  font-weight: bolder;
}

.btn-profile:hover,
.btn-profile:active {
  background-color: #ff8300 !important;
  color: white;
}

.btn-profile {
  height: 3rem;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.profile-label {
  width: 40%;
}

.profile-field {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 100%;
  padding: 10px;
}

.profile-body {
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
