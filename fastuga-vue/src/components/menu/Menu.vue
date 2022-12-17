<script setup>
import { ref, onMounted, inject } from "vue";
import ProductTable from "./ProductTable.vue";
import { useOrderItemsStore } from "@/stores/orderItems.js";

const axios = inject("axios");
const toast = inject("toast");

const products = ref(null);
const productTypes = ref([]);
const filterByType = ref("any");
const filterByPrice = ref(15);
const filterByName = ref(null);

const store = useOrderItemsStore();

// Web Socket
const socket = inject("socket");

const editProduct = async (product) => {
  let formData = new FormData();

  formData.append("name", product.name);
  formData.append("type", product.type);
  formData.append("description", product.description);
  formData.append("price", product.price);
  formData.append("photo_url", product.photo_url);

  await axios
    .put(`/products/${product.id}`, formData)
    .then((response) => {
      LoadProducts();

      // Send message to web socket
      socket.emit("updateProduct", response.data.data);

      toast.info("Product '" + response.data.data.name + "' was updated");
    })
    .catch((error) => {
      console.log(error);
    });
};

const addProductToOrder = (product, quantity) => {
  for (let index = 0; index < quantity; index++) {
    store.addItem(product);
  }
};

const makeOrder = () => {
  console.log(store.items);
};

const LoadProducts = () => {
  axios
    .get(`/products`)
    .then((response) => {
      products.value = response.data.data;
    })
    .catch((error) => {
      console.log(error);
    });
};

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

const deletedProduct = () => {
  LoadProducts();
};

const props = defineProps({
  menuTitle: {
    type: String,
    default: "Menu",
  },
});

onMounted(() => {
  LoadProducts();
  LoadProductTypes();
});

//==================================================
// Web Sockets
//==================================================

// Listen for the 'message' event from the server and log the data
// received from the server to the users.

// waits for the created product message
socket.on("newProduct", (product) => {
  toast.success(
    `New Product: ${product.name} was created with the price ${product.price} €`
  );
  LoadProducts();
});
// waits for the updated product message
socket.on("updateProduct", (product) => {
  toast.success(`Product: ${product.name} was updated`);
  LoadProducts();
});
// TODO: Emit for the delete
// waits for the deleted product message
socket.on("deleteProduct", (product) => {
  toast.success(`Product: ${product.name} was deleted`);
  LoadProducts();
});
</script>

<template>
  <div class="d-flex justify-content-between fastuga-font menu-header">
    <div class="mx-2">
      <h3 class="mt-4">{{ menuTitle }}</h3>
    </div>
    <router-link
      class="mx-4 link-secondary fastuga-font"
      :to="{ name: 'AddProduct' }"
      aria-label="Add Product"
    >
      <button
          type="button"
          class="btn btn-success add-product"
        >
          <i class="bi bi-plus-lg menu-bi"></i> Add Product
        </button>
    </router-link>
  </div>

  <hr />
  <!-- <div class="filter-div fastuga-font">
    <label for="searchName" class="form-label">Search:</label>
    <input v-model="filterByName" class="form-control" type="text" />
  </div> -->
  <div class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 filter-div fastuga-font">
      <label for="selectType" class="form-label">Filter by type:</label>
      <select class="form-select" id="selectType" v-model="filterByType">
        <option @click="filterByType = 'any'" value="any">Any</option>
        <option v-for="filterByType in productTypes" :value="filterByType">
          {{ filterByType.charAt(0).toUpperCase() }}{{ filterByType.slice(1) }}
        </option>
      </select>
    </div>
    <div class="mx-2 mt-2 filter-div fastuga-font">
      <label for="selectPrice" class="form-label">Filter by price:</label>
      <input
        v-model="filterByPrice"
        class="form-control"
        type="number"
        min="0"
        max="99"
        step="0.1"
      />
    </div>
    <div
      class="mx-2 mt-2 flex-grow-1 input-group rounded filter-div fastuga-font filter-name"
    >
      <input
        v-model="filterByName"
        type="search"
        class="form-control rounded"
        placeholder="Search by Name"
        aria-label="Search"
        aria-describedby="search-addon"
      />
      <i class="bi bi-search name-search"></i>
    </div>
    <div class="mx-2 mt-2 input-group rounded fastuga-font filter-name">
      <router-link
        class="link-secondary fastuga-font"
        :to="{ name: 'NewOrder' }"
        aria-label="Make a new order"
      >
        <button
          type="button"
          class="btn btn-success hvr-grow make-order"
          @click="makeOrder"
        >
          <i class="bi bi-check2-circle menu-bi"></i> Make Order
        </button>
        <!-- <i class="bi bi-xs bi-plus-circle">Make Order</i> -->
      </router-link>
    </div>
    <div class="mx-2 mt-2"></div>
  </div>
  <div>
    <product-table
      :products="products"
      :productTypes="productTypes"
      @edit="editProduct"
      @deleted="deletedProduct"
      @add="addProductToOrder"
      :filterByType="filterByType"
      :filterByPrice="filterByPrice"
      :filterByName="filterByName"
    ></product-table>
  </div>
</template>

<style scoped>

.menu-header{
  align-items: end;
}

.menu-bi{
  font-size: 1rem;
}

option:hover {
  background-color: #ffa71dd6 !important;
}

.add-product:hover, .make-order:hover, .btn:first-child:active{
  background-color: #ff8300;
}


.add-product{
  height: 3rem;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.make-order {
  height: fit-content;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.name-search {
  margin-left: inherit !important;
}

.filter-name {
  width: auto;
  height: fit-content;
  align-self: end;
}
.filter-div {
  min-width: 12rem;
}

.hvr-grow {
  margin-top: 1.85rem;
  display: inline-block;
  vertical-align: middle;
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-property: transform;
  transition-property: transform;
}
.hvr-grow:hover,
.hvr-grow:focus,
.hvr-grow:active {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}
</style>
