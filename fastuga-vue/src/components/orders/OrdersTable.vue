<script setup>
import { ref, computed, onMounted, inject } from 'vue'


const props = defineProps({
    orders: Array,
    default: () => [],
    filterByType: String,
    ticketNumber: Number,
    orderDate: Date
})
const emit = defineEmits(['show','edit','delete'])

const showClick = (order) => {
    emit('show', order)
}

const editClick = (order) => {
  emit('edit', order)
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
      <tr v-for="order in props.orders.filter(order => props.filterByType === 'A' ? true : order.status === props.filterByType)
                                      .filter(order => !props.ticketNumber ? true : order.ticket_number === props.ticketNumber)
                                      .filter(order => isNaN(props.orderDate.getTime()) ? true : order.date === `${props.orderDate.getFullYear()}-${props.orderDate.getMonth() + 1}-${props.orderDate.getDate()}`)" :key="order.id">
        <td> {{ order.id }} </td>
        <td> {{order.ticket_number}} </td>

        <td v-if="order.status == 'P'">Preparing</td> <!-- <p class="text-primary">Preparing</p> -->
        <td v-else-if="order.status == 'R'">Ready</td> <!-- <p class="text-info">Ready</p> -->
        <td v-else-if="order.status == 'D'">Delivered</td> <!-- <p class="text-success">Delivered</p> -->
        <td v-else-if="order.status == 'C'">Cancelled</td> <!-- <p class="text-danger">Cancelled</p> -->

        <td>{{ order.date }}</td>
        <td>{{ order.total_price }}€</td>
        <td class="text-end">
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="showClick(order)"
              >
              <i class="bi bi-xs bi-search"></i>
            </button>

            <div v-if="order.status == 'P' || order.status == 'R'">
              <button
                class="btn btn-xs btn-light"
                @click="deleteClick(order)"
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

<style>
</style>