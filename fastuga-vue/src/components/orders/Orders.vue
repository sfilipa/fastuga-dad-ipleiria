<script setup>
  import { ref, computed, onMounted, inject, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import OrdersTable from "./OrdersTable.vue"

  const axios = inject('axios')
  const router = useRouter()

  const orders = ref([])
  const costumer_id = ref(undefined)
  const ticketNumber = ref(undefined)
  const filterByType = ref('A')

  const LoadOrders = () => {
    axios.get(`/orders`)
      .then((response) => {
         orders.value = response.data
      })
      .catch((error) => {
        console.log(error)
      })
  }

  // watch(filterByType, (newValue, oldValue) => {
  //   orders.value = []
  //   axios.get(`/orders/status`, {
  //     params: 'C'
  //   })
  //     .then((response) => {
  //        orders.value = response.data
  //     })
  //     .catch((error) => {
  //       console.log(error)
  //     })
  // })

  // const createOrder = () => {

  // }

  const showOrder = (order) => {
    console.log("Show")  
    const orderObj = Object.assign({}, order)
    console.log(orderObj)
  }

  const editOrder = (order) => {
    console.log("Edit")
    const orderObj = Object.assign({}, order)
    console.log(orderObj)
  }

  const deleteOrder = (order) => {
    console.log("Delete")
    const orderObj = Object.assign({}, order)
    console.log(orderObj)
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
  <div class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectType" class="form-label">Filter by Status:</label>
      <select
        class="form-select"
        id="selectType"
        v-model="filterByType"
      >
        <option value="A">Any</option>
        <option value="P">Preparing</option>
        <option value="R">Ready</option>
        <option value="D">Delivered</option>
        <option value="C">Cancelled</option>
      </select>
    </div>

    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <div class="inner-addon left-addon">
        <label for="selectType" class="form-label">Search for Ticket Number:</label>
        <i class="glyphicon glyphicon-user"></i>
        <input v-model="ticketNumber" type="number" name="ticketnumber" class="form-control"/>
        <!-- <form id="ticketNumberFilter"> -->
        <!-- <input name="ticketnumber" v-model="ticketNumber"> -->
        <!-- <button @click="searchByTicketNumber">Search</button> -->
      <!-- </form> -->
    </div>
    </div>

    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectType" class="form-label">Search for Costumer:</label>
      <form id="costumerFilter">
        <input v-model="costumer_id" type="number" name="costumer_id" class="form-control">
        <!-- <button @click="searchByCostumerId">Search</button> -->
      </form>
    </div>

    <!-- <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-success px-4"
        @click="createOrder"
      ><i class="bi bi-xs bi-plus-circle"></i>&nbsp; Create Order</button>
    </div> -->

  </div> 
  <orders-table
    :orders="orders"
    :filterByType="filterByType"
    :ticketNumber="ticketNumber"
    :costumerId="costumer_id"
    @show="showOrder"
    @edit="editOrder"
    @delete="deleteOrder">
  </orders-table>
</template>

