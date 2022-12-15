<script setup>
  import { ref, computed, onMounted, inject, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import OrdersTable from "./OrdersTable.vue"
  import axios from 'axios'

  const REFUND_URL = 'https://dad-202223-payments-api.vercel.app' 
  const axiosLaravel = inject('axios')
  const router = useRouter()
  const toast = inject("toast")

  const orders = ref([])
  const orderDate = ref(undefined)
  const ticketNumber = ref(undefined)
  const filterByType = ref('A')

  const LoadOrders = () => {
    axiosLaravel.get(`/orders`)
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
    const orderObj = Object.assign({}, order)
    console.log(orderObj)
    axiosLaravel.put(`/orders/${orderObj.id}/cancel`, orderObj)
      .then((response)=>{
        console.log(response.data)
        
        if(orderObj.payment_type != null){
          const requestBody = {
            'type': orderObj.payment_type.toLowerCase(),
            'reference': orderObj.payment_reference,
            'value': Number.parseInt(orderObj.total_paid)
          }
          axios.post(`${REFUND_URL}/api/refunds`, requestBody)
            .then((response) => {
                console.log(response)
            })
            .catch((error) => {
              console.log(error)
                if (error.response.status == 422) {
                  toast.error('Refund was not created due to validation errors - ' + error.response.data.message)
                } else {
                  toast.error('Refund created due to unknown server error!')
                }
            })
        }

        toast.success("Order was successfully cancelled!")
        LoadOrders()
      })
      .catch((error)=>{
          toast.error(error)
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
      <label for="selectType" class="form-label">Filter by Date:</label>
      <form id="costumerFilter">
        <input v-model="orderDate" type="date" name="orderDate" class="form-control">
      </form>
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


  </div> 
  <orders-table
    :orders="orders"
    :filterByType="filterByType"
    :ticketNumber="ticketNumber"
    :orderDate="(new Date(orderDate))"
    @show="showOrder"
    @edit="editOrder"
    @delete="deleteOrder">
  </orders-table>
</template>

