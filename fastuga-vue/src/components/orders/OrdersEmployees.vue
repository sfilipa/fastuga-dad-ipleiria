<script setup>
import {inject, onMounted, ref, VueElement} from "vue";
import OrdersTable from "./OrdersTable.vue"
import {useUserStore} from "@/stores/user";
import axios from "axios";

const axiosLaravel = inject('axios')
const orders = ref([])
const toast = inject("toast")
const socket = inject("socket")
const lastPage = ref(1)
const componentName = "delivery_orders"
const noResults = ref(false)
const user = useUserStore()
const ticketNumber = ref(0)
let customer = ref([]);

const LoadOrders = (pageNumber) => {
  let URL = "/orders/delivery?page="+pageNumber

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

const changeOrderStatus = (order) => {
  const orderObj = Object.assign({}, order)
  if(!checkOrderItemsAreReady(order)){
    toast.error(`Couldn't get order number ${orderObj.ticket_number} ready - there are still items to prepare!`)
    return
  }
  if(orderObj.status === 'P'){
    axiosLaravel.patch(`/orders/${orderObj.id}/R`, {
      userId: user.userId
    })
      .then(() => {
        if (orderObj.customer_id != null) {
          axiosLaravel.get(`/customers/${orderObj.customer_id}`)
            .then(response => {
              customer = response.data;
              orderObj['customerUserID'] = customer.data.user_id.id;
              toast.warning(`Order number ${orderObj.ticket_number} is now ready to be delivered!`)
              socket.emit("orderReadyToDeliver", orderObj);
              LoadOrders(1)
            })
          return
        }
        toast.warning(`Order number ${orderObj.ticket_number} is now ready to be delivered!`)
        socket.emit("orderReadyToDeliver", orderObj);
        LoadOrders(1)
      })
      .catch((error) => {
        console.log(error)
        toast.error(error.response.data)
      })
  }else{
    axiosLaravel.patch(`/orders/${orderObj.id}/D`, {
      userId: user.userId
    })
      .then(() => {
        socket.emit("orderDelivered", orderObj.ticket_number);
        toast.success(`Order number ${orderObj.ticket_number} was delivered!`)
        LoadOrders(1)
      })
      .catch((error) => {
        console.log(error)
        toast.error(error.response.data)
      })
  }
}

const checkOrderItemsAreReady = (order) => {
  for(const item of order.order_items){
    if(item.status != 'R'){
        return false
      }
  }
  return true
}

onMounted (() => {
  LoadOrders()
})

//==================================================
// Web Sockets
//==================================================

// Listen for the 'message' event from the server and log the data
// received from the server to the users.

// Order Placed
socket.on("orderPlaced", (ticket) => {
  toast.info("New Order! Ticket Number: " + ticket);
  LoadOrders(1);
});

// Order Ready to Deliver
socket.on("orderReadyToDeliver", (ticket) => {
  toast.warning("Order is ready to be delivered! Ticket Number: " + ticket);
  LoadOrders(1);
});

// Order Delivered
socket.on("orderDelivered", (ticket) => {
  toast.success("Order was delivered! Ticket Number: " + ticket);
  LoadOrders(1);
});
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
  <div>
    <orders-table
        :orders="orders"
        :parent="componentName"
        @changeOrderStatus="changeOrderStatus">
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