<script setup>
import {inject, onMounted, ref} from "vue";
import OrdersTable from "./OrdersTable.vue"

const axiosLaravel = inject('axios')
const orders = ref([])
const toast = inject("toast")
const componentName = "chefs_orders"

const LoadOrders = () => {
  axiosLaravel.get(`/orders/status/P`)
    .then((response) => {
      orders.value = response.data
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
  axiosLaravel.patch(`/orders/${orderObj.id}/R`)
      .then(() => {
        toast.success(`Order number ${orderObj.ticket_number} is now ready!`)
        LoadOrders()
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
  <orders-table
      :orders="orders"
      :parent="componentName"
      @show="getOrderReady">
  </orders-table>
</template>