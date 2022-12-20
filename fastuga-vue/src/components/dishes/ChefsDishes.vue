<script setup>
import {onMounted, inject, ref} from "vue"
import axios from 'axios'
import {useUserStore} from "@/stores/user";
const axiosLaravel = inject('axios')
const toast = inject("toast")
const serverBaseUrl = inject("serverBaseUrl")
const products = ref([])
const user = useUserStore()
const noResults = ref(false)

const ticketNumberFilter = ref(null)
const orderLocalNumberFilter = ref(null)

const LoadHotDishes = () => {
  axiosLaravel.get(`/order-items/hotdishes/${user.userId}`)
      .then((response)=>{
        products.value = response.data
        noResults.value = products.value.length === 0
      })
      .catch((error)=>{
        console.log(error)
        toast.error(error.response.data)
      })
}

const changeStatus = (productInOrder) => {
  const productItemObject = Object.assign({}, productInOrder)
  console.log(productItemObject)
  axiosLaravel.patch(`/order-items/${productInOrder.id}`, {
    userId : user.userId
  })
      .then((response) => {
        console.log(response.data)
        if(response.data.data.status === 'R'){
          toast.success(`Dish ${productInOrder.product_id.name} with number ${productInOrder.order_id.ticket_number}-${productInOrder.order_local_number} is now ready!`)
        }else{
          toast.success(`Dish ${productInOrder.product_id.name} with number ${productInOrder.order_id.ticket_number}-${productInOrder.order_local_number} is in preparation!`)
        }
        LoadHotDishes()
      })
      .catch((error) => {
        toast.error(error.response.data)
        console.log(error)
      })
}

onMounted(()=>{
  LoadHotDishes()
})
</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4"> Hot Dishes </h3>
    </div>
  </div>
  <hr>

  <div class="grid-container">
    <div class="fastuga-font grid-item-filter">
      <label>Ticket Number:</label>
      <input v-model.lazy="ticketNumberFilter" type="number" min="0" max="99" name="ticketnumber" class="form-control"/>
    </div>
    <div class="fastuga-font grid-item-filter">
      <label>Order Local Number:</label>
      <input v-model.lazy="orderLocalNumberFilter" type="number" min="0" max="99" name="orderlocalnumber" class="form-control"/>
    </div>
  </div>

  <div class="grid-container">
    <div v-if="noResults">
        <p style="text-align: center"><b>No hot dishes to show!</b></p>
    </div>
    <div v-if="!noResults && products.length === 0">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div v-else class="grid-item hvr-grow" v-for="product in products.filter((p) => (ticketNumberFilter == null || ticketNumberFilter === '' ? true : p.order_id.ticket_number === ticketNumberFilter))
                                                                     .filter((p) => (orderLocalNumberFilter == null || orderLocalNumberFilter === '' ? true : p.order_local_number === orderLocalNumberFilter))" :key="product.id">
        <div class="product-header fastuga-colored-font">
          <img class="product-image" :src="`${serverBaseUrl}/storage/products/${product.product_id.photo_url}`"/>
          <div class="product-title-and-subtitle">
            <div class="product-name"> {{ product.product_id.name }} </div>
            <div class="product-subtitle">{{ "Dish Identifier:  " + product.order_id.ticket_number + " - " + product.order_local_number }}</div>
          </div>
        </div>
      <div class="product-body fastuga-colored-font">
        <div class="product-description">{{ "Chef: " + (product.preparation_by == null ? "none" : product.preparation_by?.name) }}</div>
      </div>
        <hr class="fastuga-colored-font" />
        <div class="product-footer fastuga-colored-font">
            <button
                class="btn btn-xs btn-light hvr-grow"
                @click="changeStatus(product)"
                v-if="product.status === 'W' || product.preparation_by?.id === user.userId"
            >
              <i class="bi-check-circle-fill"></i>
            </button>
          <div class="product-price">{{(product.status === 'W' ? "Waiting" : "Preparing")}}</div>
        </div>
      </div>

    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Maven+Pro&display=swap");

input[type="number"] {
  width: 35%;
}

.fastuga-font {
  font-size: 15px;
  font-family: "Maven Pro", sans-serif;
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
  display: block;
  margin-top: 10px;
  overflow: auto;
  height: 45px;
  text-align: center;
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
  column-gap: 20px;
  padding: 10px;
  width: auto;
  margin: auto;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.grid-item {
  padding: 20px;
  font-size: 30px;
  text-align: center;
  height: 82%;
  margin: 29px;
  border-radius: 2%;
  background-image: linear-gradient(to top left, #ff8300, #ffa71dd6);
  box-shadow: 0 10px 16px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

.grid-item-filter{
  justify-content: center;
}

td {
  vertical-align: middle;
}

textarea {
  width: 100%;
  height: 100%;
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