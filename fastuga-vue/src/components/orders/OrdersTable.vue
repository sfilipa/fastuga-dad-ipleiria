<script setup>
import { ref, inject } from 'vue'

const serverBaseUrl = inject("serverBaseUrl")
const axiosLaravel = inject('axios')
const orderItems = ref([])
let indexesAdded = []

const props = defineProps({
    orders: Array,
    default: () => [],
    filterByType: String,
    ticketNumber: Number,
    orderDate: Date,
    parent: String
})
const emit = defineEmits(['delete'])

const showItems = (order, indexRow) => {
  axiosLaravel.get(`/orders/${order.id}/orderItems`)
      .then((response) => {
        orderItems.value = response.data.data
        indexRow = indexRow + 1;
        if (indexesAdded.includes(order.id)) {
          props.orders.splice(indexRow, orderItems.value.length) //Delete order items from table

          let key = indexesAdded.indexOf(order.id)
          indexesAdded.splice(key, 1)

        } else {
          indexesAdded.push(order.id)
          orderItems.value.forEach((value, index) => {
            props.orders.splice(indexRow, 0, value) //Insert order items into table
            indexRow++
          })
        }
      })
      .catch((error) => {
        console.log(error)
      })
}

const deleteClick = (order) => {
  emit('delete', order)
}
</script>

<template>
<table class="table">
    <thead>
      <tr>
        <th>Id</th>
        <th>Ticket Number</th>
        <th>Status</th>
        <th>Date</th>
        <th>Total Price</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(order, index) in props.orders">
        <td v-if="order.ticket_number === undefined" class="products">
          <img :src='`${serverBaseUrl}/storage/products/${order.product_id.photo_url}`' />
        </td>
        <td v-else> {{ order.id }} </td>
        <td v-if="order.ticket_number === undefined" class="products">
          {{ order.product_id.name }}
        </td>
        <td v-else> {{ order.ticket_number }} </td>


        <td v-if="order.status == 'P'" :class="{ 'products' : order.ticket_number === undefined}">Preparing</td>
        <td v-else-if="order.status == 'R'" :class="{ 'products' : order.ticket_number === undefined}">Ready</td>
        <td v-else-if="order.status == 'D'">Delivered</td>
        <td v-else-if="order.status == 'C'">Cancelled</td>
        <td v-else-if="order.status == 'W'" :class="{ 'products' : order.ticket_number === undefined}">Waiting</td>

        <td v-if="order.ticket_number === undefined" class="products">
          {{ order.product_id.type}}
        </td>
        <td v-else>{{ order.date }}</td>

        <td v-if="order.ticket_number === undefined" class="products">
          {{ order.product_id.price }}€
        </td>
        <td v-else>{{ order.total_price }}€</td>
        <td class="text-end" :class="{'products': order.ticket_number === undefined}">
          <div class="d-flex justify-content-end" v-if="order.ticket_number !== undefined">

            <button
              class="btn btn-xs btn-light"
              @click="showItems(order, index)"
              >
              <i class="bi bi-xs bi-check-circle"></i>
            </button>

            <div v-if="order.status == 'P' || order.status == 'R'">
              <button
                class="btn btn-xs btn-light"
                @click="deleteClick(order)"
                v-if="props.parent == 'all_orders'"
                >
                <i class="bi bi-xs bi-x-square-fill"></i>
              </button>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
.products{
  background-color: #f1f1f1;
}
img,
svg {
  vertical-align: middle;
  height: 50px;
  width: auto;
}
</style>