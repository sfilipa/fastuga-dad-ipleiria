<script setup>
  import { ref, computed, onMounted, inject } from 'vue'
  import {useRouter} from 'vue-router'
  import ProductTable from "./ProductTable.vue"

  const axios = inject('axios')
  const router = useRouter()
  const products = ref([])
  const productTypes = ref([])
  const filterByType = ref('any')
  const filterByPrice = ref(15)

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

</style>
