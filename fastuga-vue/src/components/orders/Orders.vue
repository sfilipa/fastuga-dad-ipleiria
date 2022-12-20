<script setup>
  import { ref, onMounted, inject } from 'vue'
  import { useRouter } from 'vue-router'
  import OrdersTable from "./OrdersTable.vue"
  import Paginate from "vuejs-paginate-next"
  import axios from 'axios'

  const REFUND_URL = 'https://dad-202223-payments-api.vercel.app' 
  const axiosLaravel = inject('axios')
  const router = useRouter()
  const toast = inject("toast")
  const componentName = "all_orders"

  const orders = ref([])
  const orderDate = ref("")
  const ticketNumber = ref(0)
  const filterByType = ref('')
  const lastPage = ref(1)
  const noResults = ref(false)

  const LoadOrders = (pageNumber) => {
    let URL = "/orders?page="+pageNumber

    if(filterByType.value.length != 0){
      URL += `&status=${filterByType.value}`
    }
    if(ticketNumber.value > 0){
      URL += `&ticket=${ticketNumber.value}`
    }
    if(orderDate.value != ''){
      URL += `&date=${orderDate.value}`
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

  const deleteOrder = (order) => {
    const orderObj = Object.assign({}, order)
    axiosLaravel.put(`/orders/${orderObj.id}/cancel`, orderObj)
      .then((response)=>{
        console.log(response.data)
        if(orderObj.payment_type != null){
          const requestBody = {
            'type': orderObj.payment_type.toLowerCase(),
            'reference': orderObj.payment_reference,
            'value': Number.parseInt(orderObj.total_paid)
          }
          console.log(requestBody)
          axios.post(`${REFUND_URL}/api/refunds`, requestBody)
            .then((response) => {
                console.log(response)
            })
            .catch((error) => {
              //coloquei só para o erro 422 pois há valores gerados de pagamentos no seed na BD que nao sao válidos - confirmar
                if (error.response.status != 422) {
                  toast.error('Refund was not created due to validation errors - ' + error.response.data.message)
                }
            })
        }

        toast.success("Order was successfully cancelled!")
        LoadOrders(1)
      })
      .catch((error)=>{
          toast.error(error)
      })
  }

  onMounted (() => {
    LoadOrders(1)
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
        @change="LoadOrders(1)"
      >
        <option value="">Any</option>
        <option value="P">Preparing</option>
        <option value="R">Ready</option>
        <option value="D">Delivered</option>
        <option value="C">Cancelled</option>
      </select>
    </div>

    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectType" class="form-label">Filter by Date:</label>
      <form id="costumerFilter">
        <input v-model="orderDate" @change="LoadOrders(1)" type="date" name="orderDate" class="form-control">
      </form>
    </div>

    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <div class="inner-addon left-addon">
        <label for="selectType" class="form-label">Search for Ticket Number:</label>
        <i class="glyphicon glyphicon-user"></i>
        <input v-model.lazy="ticketNumber" @change="LoadOrders(1)" type="number" min="0" max="99" name="ticketnumber" class="form-control"/>
      </div>
    </div>

  </div>
  <div v-if="noResults">
    <p style="text-align: center"><b> No orders match current filters! </b></p>
  </div>
  <div v-else>
    <orders-table
        :orders="orders"
        :parent="componentName"
        @delete="deleteOrder">
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

