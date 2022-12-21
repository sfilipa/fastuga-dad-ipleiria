<script setup>
import { ref, onMounted, inject} from "vue";
import { useRouter } from "vue-router";
import axiosImported from "axios";

const axios = inject("axios");
const toast = inject("toast");
const serverBaseUrl = inject("serverBaseUrl");

const router = useRouter();

const nameInput = ref(null);
const typeInput = ref(null);
const descriptionInput = ref(null);
const priceInput = ref(null);
const photoInput = ref(null);

const addProductBool = ref(false);

const errors = ref(null);

const productTypes = ref([]);

// Web Sockets
const socket = inject("socket");

const LoadProductTypes = () => {
  axios
    .get(`/products/types`)
    .then((response) => {
      productTypes.value = response.data;
    })
    .catch((error) => {
      console.log(error);
    });
};

const updatePhoto = (e) => {
  if (!e.target.files.length) {
    return;
  }

  // Save uploaded image
  const uploadedImage = e.target.files[0];

  // Save image in base64
  const reader = new FileReader();
  reader.readAsDataURL(uploadedImage);
  reader.onload = (event) => {
    photoInput.value = event.target.result;
  };
};

const addProduct = async () => {
  addProductBool.value = true;

  let product = {
    name: nameInput.value,
    type: typeInput.value,
    description: descriptionInput.value,
    price: priceInput.value != null && priceInput.value == "" ? "non numeric" : priceInput.value,
    photo_url: photoInput.value,
  };

  await axiosImported
    .post(`${serverBaseUrl}/api/products`, product)
    .then((response) => {
      router.push("/menu");
      // Send message to web socket
      socket.emit("newProduct", response.data.data);

      toast.info("Product '" + response.data.data.name + "' was created");
    })
    .catch((error) => {
      addProductBool.value = false;
      errors.value = error.response.data.errors;
    });
};

onMounted(() => {
  LoadProductTypes();
});
</script>

<template>
  <div class="container fastuga-font">
    <div class="add-product-title">
      <h2>Add a new product to the menu</h2>
      <hr />
    </div>
    <div class="add-product-body">
      <div class="add-product-field">
        <!-- Product Name -->
        <label class="add-product-label">Name: </label>
        <input
          type="text"
          name="name"
          v-model="nameInput"
          class="form-control"
          required
          @focus="errors != null && errors.name != null ? errors.name = null : null"
        />
      </div>
      <field-error-message class="add-product-field add-product-error" :errors="errors" fieldName="name"></field-error-message>
      <div class="add-product-field">
        <label class="add-product-label">Type: </label>
        <select name="type" id="type" v-model="typeInput" class="form-select" @focus="errors != null && errors.type != null ? errors.type = null : null">
          <option v-for="productType in productTypes" :value="productType">
            {{ productType.charAt(0).toUpperCase() }}{{ productType.slice(1) }}
          </option>
        </select>
      </div>
      <field-error-message class="add-product-field add-product-error" :errors="errors" fieldName="type"></field-error-message>
      <div class="add-product-field">
        <label class="add-product-label">Description: </label>
        <textarea
          id="description"
          name="description"
          rows="4"
          cols="50"
          v-model="descriptionInput"
          class="form-control"
          required
          @focus="errors != null &&  errors.description != null ? errors.description = null : null"
        ></textarea
        ><br />
      </div>
      <field-error-message class="add-product-field add-product-error" :errors="errors" fieldName="description"></field-error-message>
      <div class="add-product-field">
        <label class="add-product-label">Price: </label>
        <input
          type="number"
          name="price"
          min="0.1"
          max="99.99"
          step="0.1"
          v-model="priceInput"
          class="form-control"
          required
          @focus="errors != null && errors.price != null ? errors.price = null : null"
        />
      </div>
      <field-error-message class="add-product-field add-product-error" :errors="errors" fieldName="price"></field-error-message>
      <div class="add-product-field">
        <label class="add-product-label">Photo: </label>
        <input
          type="file"
          id="photo_url"
          name="photo_url"
          accept="image/png, image/jpeg, image/jpg"
          @change="updatePhoto"
          class="form-control"
          @focus="errors != null && errors.photo_url != null ? errors.photo_url = null : null"
        />
      </div>
      <field-error-message class="add-product-field add-product-error" :errors="errors" fieldName="photo_url"></field-error-message>
      
    </div>
    <div class="mb-3 d-flex justify-content-end product-add-buttons">
      <div class="product-add-buttons-div">
        <router-link
          class="link-secondary fastuga-font "
          :to="{ name: 'Menu' }"
          aria-label="Cancel"
        >
          <button type="button" class="btn product-cancel-button px-5">
            Cancel
          </button>
        </router-link>
        
        <button
          type="submit"
          class="btn add-product"
          @click.prevent="addProduct"
          :disabled="addProductBool"
        >
          <span aria-hidden="true"> Add Product </span>
        </button>
      </div>
      </div>
  </div>
</template>

<style scoped>

.product-cancel-button:hover, .product-cancel-button:active{
  background-color: #4d3838 !important;
  color: white !important;
}

.product-cancel-button{
  height: 3rem;
  margin-right: 60px;
  background-color: #5e4444;
  color: white;
  display: inline-block;
}

.product-add-buttons-div {
  margin: auto;
}

.product-add-buttons {
  display: flex;
  flex-direction: row;
  align-items: center;
}


.add-product-error{
  margin-left: 30%;
  position: relative;
  top: -15px;
  margin-bottom: 0px !important;
}

hr {
  background: #362222;
  height: 6px;
  opacity: initial;
}

.add-product:hover,
.add-product:first-child:active {
  background-color: #ff8300;
  color: white;
}

.add-product {
  height: 3rem;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
  width: 146px;
}

.add-product-button-field {
  display: flex !important;
  flex-direction: column !important;
  margin-top: 1%;
}

.add-product-label {
  width: 20%;
}

.add-product-field {
  display: flex;
  flex-direction: row;
  width: 80%;
  align-items: center;
  margin-bottom: 2%;
}

.add-product-body {
  width: auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 5%;
}

.add-product-title {
  text-align: center;
}

.container {
  display: flex;
  flex-direction: column;
  padding: 20px;
}

label {
  margin-right: 10px;
}

.d-flex,
.d-flex-direction-col {
  margin-bottom: 10px;
}
</style>
