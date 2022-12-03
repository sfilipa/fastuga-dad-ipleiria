<script setup>
import { ref, watch, computed, inject, onUpdated} from "vue"
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const axios = inject("axios")
const toast = inject("toast")
const serverBaseUrl = inject("serverBaseUrl")

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
  filterByPrice: Number
})

const emit = defineEmits(["add", "edit", "deleted"])

const editingProducts = ref(props.products)
const productToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const addDialog = ref(null)
const editRow = ref(false)
const currentImage = ref(null)

const editingProduct = ref(null)

const newProduct = () => {
      return {
        id: null,
        name: '',  
        description: '',
        price: '',
        type: '',
        photo_url: '',
      }
  }

let oldProduct = newProduct();

const showAddDialog = (bool)=>{
  addDialog.value = bool
}

const addItemsToMenuDialog = ref(null)
const productToAddOrder = ref(null)
const quantityToAddOrder = ref(1)

const productToAddOrderName = computed(() => {
  return productToAddOrder.value ? productToAddOrder.value.name : ""
})

const productToDeleteDescription = computed(() => {
  return productToDelete.value
    ? `#${productToDelete.value.id} (${productToDelete.value.description})`
    : ""
})

const updatePhoto = (e)=>{
    if (!e.target.files.length){ 
        return;
    }

    editingProduct.value.photo_url = e.target.files[0];
    currentImage.value = URL.createObjectURL(e.target.files[0]);
    console.log(currentImage.value)
  }

watch(
  () => props.products,
  () => props.productsTypes,
  (newProducts) => {
    editingProducts.value = newProducts
  }
)

const setRowActive = (product) => {
  editingProduct.value = product;
  editRow.value = true;
}

const disableRowActive = () => {
  editRow.value = false;
  editingProduct.value = null;
  currentImage.value = null;
}

const restoreProduct = () => {
  editingProduct.value.description = oldProduct.description;
  editingProduct.value.type = oldProduct.type;
  editingProduct.value.name = oldProduct.name;
  editingProduct.value.price = oldProduct.price;
  editingProduct.value.photo_url = oldProduct.photo_url;
}

const saveOldProduct = (product) => {
  oldProduct.id = product.id;
  oldProduct.name = product.name;
  oldProduct.description = product.description;
  oldProduct.type = product.type;
  oldProduct.price = product.price;
  oldProduct.photo_url = product.photo_url;
}

const editClick = (product) => {
  saveOldProduct(product)
  setRowActive(product)
  console.log("Old Product: ")
  console.log(oldProduct)
  console.log("Editing Product: ")
  console.log(editingProduct.value)
}

const doneClick = () => {
  emit("edit", editingProduct.value)
  disableRowActive()
}

const cancelClick = () => {
  console.log("Cancel old: ")
  console.log(oldProduct)
  console.log("Cancel editing: ")
  console.log(editingProduct.value)
  restoreProduct()
  disableRowActive()
}

const dialogConfirmAdd = () => {
  emit("add", productToAddOrder.value, quantityToAddOrder.value)
  quantityToAddOrder.value = 1
  productToAddOrder.value = null
}

const addClick = (product) => {
  productToAddOrder.value = product
  if(addItemsToMenuDialog.value !== null && addDialog.value !== null){
    addItemsToMenuDialog.value.show()
  }
}

const dialogConfirmedDelete = () => {
  axios
    .delete("products/" + productToDelete.value.id)
    .then((response) => {
      addDialog.value = null;
      emit("deleted", response.data.data)
      toast.info("Product " + productToDeleteDescription.value + " was deleted")

    })
    .catch((error) => {
      console.log(error)
    })
}

const deleteClick = (product) => {
  productToDelete.value = product
  if(deleteConfirmationDialog.value !== null && addDialog.value !== null){
    deleteConfirmationDialog.value.show()
  }
}

onUpdated(()=>{
  if(addDialog.value === false){
   deleteConfirmationDialog.value != null ? deleteConfirmationDialog.value.show() : addDialog.value = null
  }
  if(addDialog.value === true){
    addItemsToMenuDialog.value != null ? addItemsToMenuDialog.value.show() : addDialog.value = null
  }
})

</script>

<template>
    <!-- Add Product to Order Dialog Confirmation -->
    <div v-if="(addDialog && !editRow)">
      <ConfirmationDialog
        ref="addItemsToMenuDialog"
        confirmationBtn="Add Items"
        :msg="``"
        @confirmed="dialogConfirmAdd"
      >
      <div>
        <span>Product: {{productToAddOrderName}}</span><input v-model="quantityToAddOrder" class="form-control" type="number" min="1"/>
      </div>
      </ConfirmationDialog>
    </div>
    
    <!-- Delete Product Dialog Confirmation -->
    <div v-if="(!addDialog && !editRow)">
      <ConfirmationDialog
        ref="deleteConfirmationDialog"
        confirmationBtn="Delete product"
        :msg="`Do you really want to delete the product ${productToDeleteDescription}?`"
        @confirmed="dialogConfirmedDelete"
      >
      </ConfirmationDialog>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th></th>
          <th>Name</th>
          <th>Description</th>
          <th>Type</th>
          <th>Price</th>
          <th v-if="showEditButton || showDeleteButton || showAddButton"></th>
        </tr>
      </thead>    
      <tbody >
        <!-- Spinner While Waiting for Products -->
        <tr v-if="(props.products==null)">
          <td colspan="6">
            <div class="d-flex justify-content-center">
              <div class="spinner-border" role="status" style="margin: 2%;">
                <span class="sr-only"></span>
              </div>
          </div>
          </td>
        </tr>
        
        <tr v-else v-for="product in props.products
                              .filter(product => props.filterByType==='any' || (editRow  && product==editingProduct) ? true : product.type===props.filterByType)
                              .filter(product => props.filterByPrice==null || (editRow  && product==editingProduct) ? true : product.price<=props.filterByPrice)" 
          :key="product.id" >

          <td class="align-middle-td">
            <!-- Editiing Row Photo -->
            <div v-if="(editRow && product==editingProduct)" class="mb-2">

              <!-- Uploaded Photo -->
              <div v-if="(currentImage!=null)" >
                <img  :src='`${currentImage}`' width="65px"/>
              </div>
              <!-- Original Photo -->
              <div v-else >
                <img :src='`${serverBaseUrl}/storage/products/${product.photo_url}`' width="65px"/>
              </div>

              <input 
                type="file" 
                id="inputPhoto" 
                accept="image/png, image/jpeg, image/jpg"
                @change="updatePhoto"
                class="input-photo"
                />
              <!-- <field-error-message :errors="errors" fieldName="photo"></field-error-message> -->
            </div>

            <!-- Product Photo -->
            <div v-else>
              <img :src='`${serverBaseUrl}/storage/products/${product.photo_url}`' width="65px"/>
            </div>
          </td>
          
          <td>
            <!-- Editiing Row Name -->
            <div v-if="(editRow && product==editingProduct)" class="mb-2">
              <input
                type="text"
                id="inputName"
                required
                v-model="editingProduct.name"
              />
              <!-- <field-error-message :errors="errors" fieldName="name"></field-error-message> -->
            </div>

            <!-- Product Name -->
            <div v-else>
              {{product.name}}
            </div>
          </td>

          <td>
            <!-- Editiing Row Description -->
            <div v-if="(editRow && product==editingProduct)" class="mb-3">
              <textarea
                id="inputDescription"
                rows="2"
                style="width: -moz-available;"
                v-model="editingProduct.description"
              ></textarea>
              <!-- <field-error-message :errors="errors" fieldName="description"></field-error-message> -->
            </div>

            <!-- Product Description -->
            <div v-else>
              {{product.description}}
            </div>
          </td>

          <td >
            <!-- Editiing Row Type -->
            <div v-if="(editRow && product==editingProduct)" class="mb-3">
              <select
                class="form-select"
                id="inputType"
                v-model="editingProduct.type"
              >
                <option v-for="prdtype in props.productTypes" :value="prdtype" :selected="(product.type==prdtype)" >
                  {{prdtype}}
                </option>
              </select>
            </div>

            <!-- Product Type -->
            <div v-else>
              {{product.type}}
            </div>
          </td>

          <td >
            <!-- Editiing Row Price -->
            <div v-if="(editRow && product==editingProduct)" class="mb-3">
              <div class="col-sm-10">
                <input
                  type="number"
                  class="form-control"
                  id="inputPrice"
                  min="0" max="99" step="0.1"
                  v-model="editingProduct.price"
                />
                <!-- <field-error-message
                  :errors="errors"
                  fieldName="price"
                ></field-error-message> -->
              </div>
            </div>

            <!-- Product Price -->
            <div v-else>
              {{ product.price }}€
            </div>
          </td>

          <td
            class="text-end"
            v-if="showEditButton || showDeleteButton || showAddButton"
          >
            <div class="d-flex justify-content-end">
              <!-- Add to Order, Edit and Delete Buttons -->
              <div v-if="(product!=editingProduct)" class="buttons-product">
                <button
                  class="btn btn-xs btn-light hvr-grow"
                  @click="showAddDialog(true); addClick(product)"
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
                  @click="showAddDialog(false);  deleteClick(product)"
                  v-if="showDeleteButton"
                  :disabled="editRow"
                >
                  <i class="bi bi-xs bi-x-square-fill"></i>
                </button>
              </div>

              <!-- Editiing Row Buttons -->
              <div v-else-if="editRow" class=" buttons-product">
                <button
                  class="btn btn-xs btn-light"
                  @click="doneClick(product)"
                >
                  <i class="bi bi-xs bi-check-lg"></i>
                </button>

                <button
                  class="btn btn-xs btn-light"
                  @click="cancelClick"
                >
                  <i class="bi bi-xs bi-x-lg"></i>
                </button>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
</template>

<style scoped>

.input-photo{
  width: 74px;
}

.buttons-product{
  flex-direction: row;
  display: inline-flex;
}

td{
  vertical-align: middle;
}

.align-middle-td{ 
  text-align: center;
}

select{
  width: auto;
}

input[type=text]{
  width: 160px;
}

input[type=number]{
  width: 80px;
}

button {
  margin-left: 3px;
  margin-right: 3px;
}

img, svg {
  vertical-align: middle;
  height: 68px;
  width: auto;
}

.hvr-grow {
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
.hvr-grow:hover, .hvr-grow:focus, .hvr-grow:active {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}


</style>