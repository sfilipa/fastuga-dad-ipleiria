<script setup>
  import { ref, computed, onMounted, inject } from 'vue'
  import { useRouter } from 'vue-router'
  import OrdersTable from "./OrdersTable.vue"

  const axios = inject('axios')
  const router = useRouter()

  const LoadOrders = () => {
    axios.get(`/orders`)
      .then((response) => {
        response.data.data.forEach(element => {
          orders.value.push(element)
        });
        // orders.value = response.data.data
      })
      .catch((error) => {
        console.log(error)
      })
  }

  const createOrder = () => {

  }

  const searchByTicketNumber = () => {

  }

  const editOrder = (order) => {

  }

  const deleteOrder = (order) => {

  }

  const orders = ref([])

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
  <div
    class="mb-3 d-flex justify-content-between flex-wrap"
  >
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectType"
        class="form-label"
      >Filter by Status:</label>
      <select
        class="form-select"
        id="selectType"
        v-model="filterByType"
      >
        <option value="-1">Any</option>
        <option value="0">Preparing</option>
        <option value="1">Ready</option>
        <option value="2">Delivered</option>
        <option value="3">Cancelled</option>
      </select>
    </div>

    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectType"
        class="form-label"
      >Search for Ticket Number:</label>

      <form id="tickerNumberFilter">
        <input name="ticketnumber" v-model="ticketNumber">
        <button @click="searchByTicketNumber">Search</button>
      </form>
    </div>

    <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-success px-4"
        @click="createOrder"
      ><i class="bi bi-xs bi-plus-circle"></i>&nbsp; Create Order</button>
    </div>
  </div> 
  <orders-table
    :orders="orders"
    @edit="editOrder"
    @delete="deleteOrder">
  </orders-table>

  <order-item
    v-for="item in orders"
    :key="item.id"
    :item="item"
    >
    </order-item>
</template>

