<script setup>
  import { ref, computed, onMounted, inject, toDisplayString } from 'vue'
  import {useRouter} from 'vue-router'
  import ProductTable from "./ProductTable.vue"

  const axios = inject('axios')
  const router = useRouter()
  const toast = inject("toast")

  const products = ref([])
  const productTypes = ref([])
  const filterByType = ref('any')
  const filterByPrice = ref(15)
  const orderConfirmationDialog = ref(null)

  const orderItems = ref([])

  const addProduct = () => {
    
  }

  const addProductToOrder = (product) => {
    orderItems.value.push(product)
  }

  const makeOrder = () => {
    orderConfirmationDialog.value.show()
    console.log(orderItems.value) 
  } 

  const dialogConfirmOrder = () => {
    toast.info(orderItems.value)
  }

  const LoadProducts = () => {
    axios.get(`/products`)
      .then((response) => {
        products.value = response.data
      })
      .catch((error) => {
        console.log(error)
      })
  }

  const LoadProductTypes = () => {
    axios.get(`/products/types`)
      .then((response) => {
        productTypes.value = response.data
        console.log(productTypes);
      })
      .catch((error) => {
        console.log(error)
      })
  }
  
  const props = defineProps({
    menuTitle: {
      type: String,
      default: 'Menu'
    }
  })

  onMounted (() => {
    LoadProducts()
    LoadProductTypes()
  })
</script>

<template>
  <confirmation-dialog
    ref="orderConfirmationDialog"
    confirmationBtn="Delete product"
    :msg="`Do you really want to delete the product ${orderItems.value}?`"
    @confirmed="dialogConfirmOrder"
  >
  </confirmation-dialog>

  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">{{ menuTitle }}</h3>
    </div>
    <button
        type="button"
        class="btn btn-success hvr-grow"
        @click="makeOrder"
      ><i class="bi bi-xs bi-plus-circle"></i>&nbsp; Make Order</button>
  </div>

  <hr>
  <div
    class="mb-3 d-flex justify-content-between flex-wrap"
  >
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectType"
        class="form-label"
      >Filter by type:</label>
      <select
        class="form-select"
        id="selectType"
        v-model="filterByType"
      >
        <option  @click="filterByType='any'" value="any">any</option>
        <option  v-for="filterByType in productTypes" :value="filterByType">
          {{filterByType}}
          </option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectPrice"
        class="form-label"
      >Filter by price:</label>
      <input v-model="filterByPrice" class="form-control" type="number" min="0" step="0.01"/>
    </div>
    <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-success px-4 btn-addproduct"
        @click="addProduct"
      ><i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Product</button>
    </div>
  </div> 
  <product-table
    :products="products"
    @edit="editProduct"
    @deleted="deletedProduct"
    @add="addProductToOrder"
    :filterByType="filterByType"
    :filterByPrice="filterByPrice"
  ></product-table>
</template>


<style scoped>
.filter-div {
  min-width: 12rem;
}
.total-filtro {
  margin-top: 0.35rem;
}
.btn-addproduct {
  margin-top: 1.85rem;
  background-color: #a52222;
}

.btn-addproduct:hover {
  margin-top: 1.85rem;
  background-color: #821c1c;
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
.hvr-grow:hover, .hvr-grow:focus, .hvr-grow:active {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}

</style>
