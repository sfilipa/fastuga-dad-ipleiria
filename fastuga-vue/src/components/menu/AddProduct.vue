<script setup>
import { ref, computed, onMounted, inject, watch } from "vue";
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

  photoInput.value = e.target.files[0];
};

const addProduct = async () => {
  let formData = new FormData();

  formData.append("name", nameInput.value);
  formData.append("type", typeInput.value);
  formData.append("description", descriptionInput.value);
  formData.append("price", priceInput.value);
  formData.append("photo_url", photoInput.value);

  await axiosImported
    .post(`${serverBaseUrl}/api/products`, formData)
    .then((response) => {
      router.push("/menu");

      // Send message to web socket
      socket.emit("newProduct", response.data.data);

      toast.info("Product '" + response.data.data.name + "' was created");
    })
    .catch((error) => {
      console.log(error);
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
        />
      </div>
      <div class="add-product-field">
        <label class="add-product-label">Type: </label>
        <select name="type" id="type" v-model="typeInput" class="form-select">
          <option v-for="productType in productTypes" :value="productType">
            {{ productType.charAt(0).toUpperCase() }}{{ productType.slice(1) }}
          </option>
        </select>
      </div>

      <div class="add-product-field">
        <label class="add-product-label">Description: </label>
        <textarea
          id="description"
          name="description"
          rows="4"
          cols="50"
          v-model="descriptionInput"
          class="form-control"
        ></textarea
        ><br />
      </div>

      <div class="add-product-field">
        <label class="add-product-label">Price: </label>
        <input
          type="number"
          name="price"
          min="0.1"
          max="99"
          step="0.1"
          v-model="priceInput"
          class="form-control"
        />
      </div>

      <div class="add-product-field">
        <label class="add-product-label">Photo: </label>
        <input
          type="file"
          id="photo_url"
          name="photo_url"
          accept="image/png, image/jpeg, image/jpg"
          @change="updatePhoto"
          class="form-control"
        />
      </div>

      <div class="add-product-field add-product-button-field">
        <button
          type="submit"
          class="btn add-product"
          @click.prevent="addProduct"
        >
          <span aria-hidden="true"> Add Product </span>
        </button>
      </div>

      <!-- <form
        action="#"
        class="form-inline row d-flex"
        enctype="multipart/form-data"
      >
        <div class="flex-grow-2 d-flex"></div>
        <div class="flex-grow-1 d-flex-direction-col"></div>

        <div class="flex-grow-1 d-flex"></div>
        <div class="flex-grow-1 d-flex">
          <label>Photo: </label>
        </div>

        <div></div>
      </form> -->
    </div>
  </div>
</template>

<style scoped>
hr {
  background: #362222;
  height: 6px;
  opacity: initial;
}

.add-product:hover,
.btn:first-child:active {
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
  width: 30%;
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
