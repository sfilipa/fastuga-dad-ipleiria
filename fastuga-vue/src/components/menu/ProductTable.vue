<script setup>
import { ref, watch, computed, inject, onUpdated } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const axios = inject("axios");
const toast = inject("toast");
const serverBaseUrl = inject("serverBaseUrl");

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
  productTypes: {
    type: Array,
    default: () => [],
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
  showAddButton: {
    type: Boolean,
    default: true,
  },
  filterByType: String,
  filterByPrice: Number,
});

const emit = defineEmits(["add", "edit", "deleted"]);

const editingProducts = ref(props.products);
const productToDelete = ref(null);
const deleteConfirmationDialog = ref(null);

const addDialog = ref(null);
const editRow = ref(false);
const currentImage = ref(null);

const editingProduct = ref(null);

// Web Sockets
const socket = inject("socket")

const newProduct = () => {
  return {
    id: null,
    name: "",
    description: "",
    price: "",
    type: "",
    photo_url: "",
  };
};

let oldProduct = newProduct();

const showAddDialog = (bool) => {
  addDialog.value = bool;
};

const addItemsToMenuDialog = ref(null);
const productToAddOrder = ref(null);
const quantityToAddOrder = ref(1);

const productToAddOrderName = computed(() => {
  return productToAddOrder.value ? productToAddOrder.value.name : "";
});

const productToDeleteDescription = computed(() => {
  return productToDelete.value
    ? `#${productToDelete.value.id} (${productToDelete.value.description})`
    : "";
});

const updatePhoto = (e) => {
  if (!e.target.files.length) {
    return;
  }

  editingProduct.value.photo_url = e.target.files[0];
  currentImage.value = URL.createObjectURL(e.target.files[0]);
  console.log(currentImage.value);
};

watch(
  () => props.products,
  () => props.productsTypes,
  (newProducts) => {
    editingProducts.value = newProducts;
  }
);

const setRowActive = (product) => {
  editingProduct.value = product;
  editRow.value = true;
};

const disableRowActive = () => {
  editRow.value = false;
  editingProduct.value = null;
  currentImage.value = null;
};

const restoreProduct = () => {
  editingProduct.value.description = oldProduct.description;
  editingProduct.value.type = oldProduct.type;
  editingProduct.value.name = oldProduct.name;
  editingProduct.value.price = oldProduct.price;
  editingProduct.value.photo_url = oldProduct.photo_url;
};

const saveOldProduct = (product) => {
  oldProduct.id = product.id;
  oldProduct.name = product.name;
  oldProduct.description = product.description;
  oldProduct.type = product.type;
  oldProduct.price = product.price;
  oldProduct.photo_url = product.photo_url;
};

const editClick = (product) => {
  saveOldProduct(product);
  setRowActive(product);
  console.log("Old Product: ");
  console.log(oldProduct);
  console.log("Editing Product: ");
  console.log(editingProduct.value);
};

const doneClick = () => {
  emit("edit", editingProduct.value);
  disableRowActive();
};

const cancelClick = () => {
  console.log("Cancel old: ");
  console.log(oldProduct);
  console.log("Cancel editing: ");
  console.log(editingProduct.value);
  restoreProduct();
  disableRowActive();
};

const dialogConfirmAdd = () => {
  emit("add", productToAddOrder.value, quantityToAddOrder.value);
  quantityToAddOrder.value = 1;
  productToAddOrder.value = null;
};

const addClick = (product) => {
  productToAddOrder.value = product;
  if (addItemsToMenuDialog.value !== null && addDialog.value !== null) {
    addItemsToMenuDialog.value.show();
  }
};

const dialogConfirmedDelete = () => {
  axios
    .delete("products/" + productToDelete.value.id)
    .then((response) => {
      addDialog.value = null;
      emit("deleted", response.data.data);

      // Send message to web socket
      socket.emit('deleteProduct', productToDelete.value)

      toast.info(
        "Product " + productToDeleteDescription.value + " was deleted"
      );
    })
    .catch((error) => {
      console.log(error);
    });
};

const deleteClick = (product) => {
  productToDelete.value = product;
  if (deleteConfirmationDialog.value !== null && addDialog.value !== null) {
    deleteConfirmationDialog.value.show();
  }
};

onUpdated(() => {
  if (addDialog.value === false) {
    deleteConfirmationDialog.value != null
      ? deleteConfirmationDialog.value.show()
      : (addDialog.value = null);
  }
  if (addDialog.value === true) {
    addItemsToMenuDialog.value != null
      ? addItemsToMenuDialog.value.show()
      : (addDialog.value = null);
  }
});
</script>

<template>
  <!-- Add Product to Order Dialog Confirmation -->
  <div v-if="addDialog && !editRow">
    <ConfirmationDialog
      ref="addItemsToMenuDialog"
      confirmationBtn="Add Items"
      :msg="``"
      @confirmed="dialogConfirmAdd"
    >
      <div>
        <span>Product: {{ productToAddOrderName }}</span
        ><input
          v-model="quantityToAddOrder"
          class="form-control confirmation-dialog-input"
          type="number"
          min="1"
        />
      </div>
    </ConfirmationDialog>
  </div>

  <!-- Delete Product Dialog Confirmation -->
  <div v-if="!addDialog && !editRow">
    <ConfirmationDialog
      ref="deleteConfirmationDialog"
      confirmationBtn="Delete product"
      :msg="`Do you really want to delete the product ${productToDeleteDescription}?`"
      @confirmed="dialogConfirmedDelete"
    >
    </ConfirmationDialog>
  </div>

  <div class="grid-container">
    <div v-if="props.products == null">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status" style="margin: 2%">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div
      class="grid-item hvr-grow"
      v-else
      v-for="product in props.products
        .filter((product) =>
          props.filterByType === 'any' || (editRow && product == editingProduct)
            ? true
            : product.type === props.filterByType
        )
        .filter((product) =>
          props.filterByPrice == null || (editRow && product == editingProduct)
            ? true
            : product.price <= props.filterByPrice
        )"
      :key="product.id"
    >
      <div class="product-header product-font">
        <!-- Editing Row Photo -->
        <div v-if="editRow && product == editingProduct" class="mb-2">
          <!-- Uploaded Photo -->
          <div v-if="currentImage != null">
            <img :src="`${currentImage}`" class="product-image" />
          </div>
          <!-- Original Photo -->
          <div v-else>
            <img
              :src="`${serverBaseUrl}/storage/products/${product.photo_url}`"
              class="product-image"
            />
          </div>

          <input
            type="file"
            id="inputPhoto"
            accept="image/png, image/jpeg, image/jpg"
            @change="updatePhoto"
            class="input-photo product-font"
          />
          <!-- <field-error-message :errors="errors" fieldName="photo"></field-error-message> -->
        </div>

        <!-- Product Photo -->
        <img
          v-else
          class="product-image"
          :src="`${serverBaseUrl}/storage/products/${product.photo_url}`"
        />

        <div class="product-title-and-subtitle">
          <!-- Editiing Row Name -->
          <div v-if="editRow && product == editingProduct" class="mb-2">
            <input
              type="text"
              id="inputName"
              required
              v-model="editingProduct.name"
              class="product-input product-name"
            />
            <!-- <field-error-message :errors="errors" fieldName="name"></field-error-message> -->
          </div>

          <!-- Product Name -->
          <div v-else class="product-name">
            {{ product.name }}
          </div>

          <div class="product-subtitle">
            <!-- Editiing Row Type -->
            <div v-if="editRow && product == editingProduct" class="mb-3">
              <select
                class="form-select product-input product-font"
                id="inputType"
                v-model="editingProduct.type"
              >
                <option
                  v-for="prdtype in props.productTypes"
                  :value="prdtype"
                  :selected="product.type == prdtype"
                >
                  {{ prdtype }}
                </option>
              </select>
            </div>

            <!-- Product Type -->
            <div v-else>
              {{ product.type }}
            </div>
          </div>
        </div>
      </div>
      <div class="product-body product-font">
        <!-- Editiing Row Description -->
        <div v-if="editRow && product == editingProduct" style="width: -moz-available;">
          <textarea
            id="inputDescription"
            rows="2"
            v-model="editingProduct.description"
            class="product-input product-font"
          ></textarea>
          <!-- <field-error-message :errors="errors" fieldName="description"></field-error-message> -->
        </div>

        <!-- Product Description -->
        <div v-else class="product-description">
          {{ product.description }}
        </div>
      </div>
      <hr class="product-font" />
      <div class="product-footer product-font">
        <div v-if="product != editingProduct">
          <button
            class="btn btn-xs btn-light hvr-grow"
            @click="
              showAddDialog(true);
              addClick(product);
            "
            v-if="showAddButton"
            :disabled="editRow"
          >
            <i class="bi bi-xs bi-cart-check"></i>
          </button>

          <button
            class="btn btn-xs btn-light"
            @click="editClick(product)"
            v-if="showEditButton"
            :disabled="editRow"
          >
            <i class="bi bi-xs bi-pencil"></i>
          </button>

          <button
            class="btn btn-xs btn-light"
            @click="
              showAddDialog(false);
              deleteClick(product);
            "
            v-if="showDeleteButton"
            :disabled="editRow"
          >
            <i class="bi bi-xs bi-x-square-fill"></i>
          </button>
        </div>
        <!-- Editiing Row Buttons -->
        <div v-else-if="editRow" class="product-edit">
          <button class="btn btn-xs btn-light" @click="doneClick(product)">
            <i class="bi bi-xs bi-check-lg"></i>
          </button>

          <button class="btn btn-xs btn-light" @click="cancelClick">
            <i class="bi bi-xs bi-x-lg"></i>
          </button>
        </div>

        <!-- Editiing Row Price -->
        <div v-if="editRow && product == editingProduct" class="mb-3">
          <div >
            <input
              type="number"
              class="product-input product-price"
              id="inputPrice"
              min="0"
              max="99"
              step="0.1"
              v-model="editingProduct.price"
            />
            <!-- <field-error-message
                  :errors="errors"
                  fieldName="price"
                ></field-error-message> -->
          </div>
        </div>

        <!-- Product Price -->
        <div v-else class="product-price">{{ product.price }}€</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Maven+Pro&display=swap");

.product-edit {
  display: flex;
}

.product-input {
  background-color: #4e4646;
  border-radius: 7%;
  color: white;
}
.spinner-font {
  font-size: 10px;
  font-family: "Maven Pro", sans-serif;
}
.confirmation-dialog-input {
  margin-left: 20px;
  display: inline;
}

.product-button {
  display: inline;
  align-self: flex-start;
}

.product-footer {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
}

.product-body {
  display: flex;
  flex-direction: row;
  margin-bottom: 3%;
}

.product-price {
  margin-left: auto;
  align-self: center;
  font-size: 27px;
}

.product-subtitle {
  display: flex;
  flex-direction: row;
  width: -moz-available;
}

.product-title-and-subtitle {
  flex-direction: column;
  display: flex;
  align-items: baseline;
  margin-left: 5%;
  width: -moz-available;
  text-align: left;
}
.product-description {
  width: -moz-available;
  margin-top: 10px;
  overflow: auto;
  height: 72px;
  text-align: justify;
}

.product-name {
  font-size: 24px;
  font-weight: bold;
}
.product-image {
  border-radius: 50%;
  width: 65px;
  height: 65px;
  background-size: cover;
}
.product-header {
  display: flex;
  flex-direction: row;
  align-items: center;
  min-height: 94.5px;
}

.product-font {
  color: white;
  font-size: 15px;
  font-family: "Maven Pro", sans-serif;
}

.grid-container {
  display: grid;
  padding: 10px;
  width: auto;
  margin: auto;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.grid-item {
  padding: 20px;
  font-size: 30px;
  text-align: center;
  /* width: 325px; */
  height: 82%;
  margin: 29px;
  border-radius: 2%;
  background-image: linear-gradient(to top left, #4e4646, #7f7474);
  /* background-color: #6f6161; */
  box-shadow: 0 10px 16px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

.input-photo {
  width: 74px;
}

td {
  vertical-align: middle;
}

.align-middle-td {
  text-align: center;
}

select {
  width: auto;
}

textarea {
  width: 100%;
  height: 100%;
}

input[type="text"] {
  width: 100%;
}

input[type="number"] {
  width: 42%;
  margin-left: auto;
  display: flex;
}

button {
  margin-left: 3px;
  margin-right: 3px;
}

img,
svg {
  vertical-align: middle;
  height: 68px;
  width: auto;
}

.hvr-grow {
  display: inline-block;
  transform: perspective(1px) translateZ(0);
  transition-duration: 0.3s;
  transition-property: transform;
}
.hvr-grow:hover,
.hvr-grow:focus,
.hvr-grow:active {
  -webkit-transform: scale(1.025);
  transform: scale(1.025);
}

body {
  height: 100%;
}

/* @media (min-width: 880px) {
  .grid-container { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 900px) {
  .grid-container { grid-template-columns: repeat(3, 1fr); }
} */
</style>
