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
  axiosLaravel.patch(`/orders/${orderObj.id}/R`)
      .then(() => {
        toast.success("Order is now ready!")
        LoadOrders()
      })
      .catch((error) => {
        console.log(error)
      })
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