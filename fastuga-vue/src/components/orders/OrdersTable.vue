<script setup>
import { ref, computed, onMounted, inject } from 'vue'


const props = defineProps({
    orders: Array,
    default: () => [],
    filterByType: String,
    ticketNumber: Number,
    orderDate: Date,
    parent: String
})
const emit = defineEmits(['show','delete'])

const showClick = (order) => {
  emit('show', order)
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
      <tr v-for="order in props.orders.filter(order => !props.filterByType ? true : order.status === props.filterByType)
                                      .filter(order => !props.ticketNumber ? true : order.ticket_number === props.ticketNumber)
                                      .filter(order => !props.orderDate || isNaN(props.orderDate.getTime()) ? true : order.date === `${props.orderDate.getFullYear()}-${props.orderDate.getMonth() + 1}-${props.orderDate.getDate()}`)" :key="order.id">
        <td> {{ order.id }} </td>
        <td> {{ order.ticket_number }} </td>

        <td v-if="order.status == 'P'">Preparing</td>
        <td v-else-if="order.status == 'R'">Ready</td>
        <td v-else-if="order.status == 'D'">Delivered</td>
        <td v-else-if="order.status == 'C'">Cancelled</td>

        <td>{{ order.date }}</td>
        <td>{{ order.total_price }}€</td>
        <td class="text-end">
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="showClick(order)"
              >
              <i :class="{ 'bi bi-xs bi-search': props.parent=='all_orders',
                                              'bi bi-check-circle-fill': props.parent=='chefs_orders'}"></i>
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

<style>
</style>