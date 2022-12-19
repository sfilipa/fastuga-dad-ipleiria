<script setup>
import { ref, watch, computed, inject, onUpdated } from "vue";
import { useUserStore } from "../../stores/user.js";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const serverBaseUrl = inject("serverBaseUrl");
const userStore = useUserStore();

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
  disableEditButtons: {
    type: Boolean,
    default: false,
  },
  errors: Object,
  filterByType: String,
  filterByPrice: Number,
  filterByName: String,
});

const emit = defineEmits(["add", "edit", "delete", "cancel"]);

const editingProducts = ref(props.products);
const productToDelete = ref(null);
const deleteConfirmationDialog = ref(null);

const addDialog = ref(null);
const editRow = ref(false);
const currentImage = ref(null);

const editingProduct = ref(null);

const addItemsToMenuDialog = ref(null);
const productToAddOrder = ref(null);
const quantityToAddOrder = ref(1);

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

  // Save uploaded image
  const uploadedImage = e.target.files[0];

  // Create temporary Url
  currentImage.value = URL.createObjectURL(uploadedImage);

  // Save image in base64
  const reader = new FileReader();
  reader.readAsDataURL(uploadedImage);
  reader.onload = (event) => {
    editingProduct.value.photo_url = event.target.result;
  };
};

watch(
  () => props.products,
  () => props.productsTypes,
  (newProducts) => {
    editingProducts.value = newProducts;
  }
);

watch(
  () => props.disableEditButtons,
  () => {
    if (!props.disableEditButtons && props.errors == null) {
      disableRowActive();
    }
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
};

const doneClick = () => {
  emit("edit", editingProduct.value);
};

const cancelClick = () => {
  emit("cancel");
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
  emit("delete", productToDelete.value);
  addDialog.value = null;
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
      confirmationBtn="Add Item(s)"
      :msg="``"
      @confirmed="dialogConfirmAdd"
    >
      <div class="confirmation-middle">
        <span>{{ productToAddOrderName }}:</span
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
        <div class="spinner-border" role="status">
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
        )
        .filter((product) =>
          props.filterByName == null || (editRow && product == editingProduct)
            ? true
            : product.name.toLowerCase().match(props.filterByName)
        )"
      :key="product.id"
    >
      <div class="product-header fastuga-colored-font">
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
            class="input-photo fastuga-colored-font"
          />
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
              class="form-control product-input product-name"
            />
            <field-error-message
              class="error-field-align"
              :errors="props.errors"
              fieldName="name"
            ></field-error-message>
          </div>

          <!-- Product Name -->
          <div v-else class="product-name">
            {{ product.name }}
          </div>

          <div class="product-subtitle">
            <!-- Editiing Row Type -->
            <div v-if="editRow && product == editingProduct" class="mb-3">
              <select
                class="form-select product-input fastuga-colored-font"
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
              <field-error-message
                class=""
                :errors="props.errors"
                fieldName="type"
              ></field-error-message>
            </div>

            <!-- Product Type -->
            <div v-else>
              {{ product.type }}
            </div>
          </div>
        </div>
      </div>
      <div class="product-body fastuga-colored-font">
        <!-- Editiing Row Description -->
        <div
          v-if="editRow && product == editingProduct"
          style="width: -moz-available"
        >
          <textarea
            id="inputDescription"
            rows="2"
            v-model="editingProduct.description"
            class="form-control product-input fastuga-colored-font"
          ></textarea>
          <field-error-message
            class="error-field-align"
            :errors="props.errors"
            fieldName="description"
          ></field-error-message>
        </div>

        <!-- Product Description -->
        <div v-else class="product-description">
          <span v-if="product.description.length > 110">
            {{ product.description.substring(0, 110 - 3) }}
            <abbr :title="product.description">...</abbr>
          </span>
          <span v-else>
            {{ product.description }}
          </span>
        </div>
      </div>
      <hr class="fastuga-colored-font" />
      <div class="product-footer fastuga-colored-font">
        <!-- Product Add to Order Button -->
        <div v-if="product != editingProduct">
          <div v-if="userStore.user == null || userStore.user.type == 'C'">
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
          </div>

          <div
            v-if="userStore.user != null && userStore.user.type == 'EM'"
            class="manager-buttons"
          >
            <!-- Product Edit Button -->
            <button
              class="btn btn-xs btn-light"
              @click="editClick(product)"
              v-if="showEditButton"
              :disabled="editRow"
            >
              <i class="bi bi-xs bi-pencil"></i>
            </button>

            <!-- Product Delete Button -->
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
        </div>

        <!-- Editiing Row Buttons -->
        <div v-else-if="editRow" class="product-edit">
          <button
            class="btn btn-xs btn-light"
            @click="doneClick(product)"
            :disabled="props.disableEditButtons"
          >
            <i class="bi bi-xs bi-check-lg"></i>
          </button>

          <button
            class="btn btn-xs btn-light"
            @click="cancelClick"
            :disabled="props.disableEditButtons"
          >
            <i class="bi bi-xs bi-x-lg"></i>
          </button>
        </div>

        <!-- Editiing Row Price -->
        <div v-if="editRow && product == editingProduct" class="mb-3">
          <div>
            <input
              type="number"
              class="form-control product-input product-price"
              id="inputPrice"
              min="0.1"
              max="99.99"
              step="0.1"
              v-model="editingProduct.price"
            />
            <field-error-message
              class="price-error-field"
              :errors="props.errors"
              fieldName="price"
            ></field-error-message>
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

.error-field-align {
  text-align: initial;
}

.price-error-field {
  text-align: end;
}

.manager-buttons {
  display: inline;
}

.confirmation-middle {
  text-align: center;
}

.product-edit {
  display: flex;
}

.product-input {
  background-color: #e68310 !important;
  border-radius: 7%;
  color: white !important;
}

.confirmation-dialog-input {
  margin-left: 4% !important;
  display: inline !important;
  width: 20% !important;
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
  text-align: start;
  word-break: break-all;
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
  background-image: linear-gradient(to top left, #ff8300, #ffa71dd6);
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
  width: 50%;
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

</style>
