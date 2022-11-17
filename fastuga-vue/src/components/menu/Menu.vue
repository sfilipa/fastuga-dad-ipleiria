<script setup>
  import { ref, computed, onMounted, inject } from 'vue'
  import {useRouter} from 'vue-router'
  import ProductTable from "./ProductTable.vue"

  const axios = inject('axios')
  const router = useRouter()
  const products = ref([])

  const LoadProducts = () => {
    axios.get(`/products`)
      .then((response) => {
        console.log(response)
        products.value = response.data
        console.log(products)
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
  })
</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">{{ menuTitle }}</h3>
    </div>
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
        <option value="-1">Any</option>
        <option value="0">Hot dish</option>
        <option value="1">Cold dish</option>
        <option value="2">Drink</option>
        <option value="3">Dessert</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectPrice"
        class="form-label"
      >Filter by price:</label>
      <select
        class="form-select"
        id="selectPrice"
        v-model="filterByPrice"
      >
        <option value="-1">Any</option>
        
      </select>
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
}
</style>
