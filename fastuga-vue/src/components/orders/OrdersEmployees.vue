<script setup>
import {inject, onMounted, ref} from "vue";
import OrdersTable from "./OrdersTable.vue"

const axiosLaravel = inject('axios')
const orders = ref([])
const toast = inject("toast")
const lastPage = ref(1)
const componentName = "chefs_orders"
const noResults = ref(false)
const ticketNumber = ref(0)

const LoadOrders = (pageNumber) => {
  let URL = "/orders/status/R/paginate?page="+pageNumber

  if(ticketNumber.value > 0){
    URL += `&ticket=${ticketNumber.value}`
  }

  axiosLaravel.get(URL)
    .then((response) => {
      lastPage.value = response.data.last_page
      orders.value = response.data.data
      noResults.value = orders.value.length === 0
    })
    .catch((error) => {
      console.log(error)
    })
}

const getOrderReady = (order) => {
  const orderObj = Object.assign({}, order)
  if(!checkOrderItemsAreReady(order)){
    toast.error(`Couldn't get order number ${orderObj.ticket_number} ready - there are still items to prepare!`)
    return
  }
  console.log(orderObj)
  axiosLaravel.patch(`/orders/${orderObj.id}/D`)
      .then(() => {
        toast.success(`Order number ${orderObj.ticket_number} was delivered!`)
        LoadOrders(1)
      })
      .catch((error) => {
        console.log(error)
      })
}

const checkOrderItemsAreReady = (order) => {
  for(const item of order.order_items){
    console.log(item)
    if(item.status != 'R'){
        return false
      }
  }
  return true
}

onMounted (() => {
  LoadOrders()
})
</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4"> Orders </h3>
    </div>
  </div>
  <hr>
  <div class="grid-container">
    <div class="fastuga-font">
      <div class="grid-item">
        <label class="form-label">Search for Ticket Number:</label>
      </div>
      <div class="grid-item">
        <input v-model.lazy="ticketNumber" @change="LoadOrders(1)" type="number" min="0" max="99" name="ticketnumber" class="form-control"/>
      </div>
    </div>
  </div>
<!--  <div v-if="noResults && ticketNumber !== 0">-->
<!--    <p style="text-align: center"><b> There are no orders to deliver! </b></p>-->
<!--  </div>-->
<!--  <div v-else-if="ticketNumber === 0">-->
<!--    <p style="text-align: center"><b> No order match the ticket number inserted. </b></p>-->
<!--  </div>-->
  <div>
    <orders-table
        :orders="orders"
        :parent="componentName"
        @show="getOrderReady">
    </orders-table>
    <div v-if="orders.length != 0">
      <paginate
          :page-count="lastPage"
          :prev-text="'Previous'"
          :next-text="'Next'"
          :click-handler="LoadOrders"
      >
      </paginate>
    </div>
  </div>
</template>

<style scoped>

input[type="number"] {
  width: 30%;
}

.fastuga-font {
  font-size: 15px;
  font-family: "Maven Pro", sans-serif;
}

.grid-container{
  display: grid;
}

.grid-item{
  justify-content: center;
}

</style>