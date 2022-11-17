<script setup>
import { ref, computed, onMounted, inject } from 'vue'

const props = defineProps({
    orders: Array
})
//const emit = defineEmits([])


</script>

<template>
<table class="table">
    <thead>
      <tr>
        <th>Ticket Number</th>
        <th>Status</th>
        <th>Date</th>
        <th>Total Price</th>
        <th v-if="showEditButton || showDeleteButton || showAddButton"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="order in props.orders" :key="order.id">
        <td>
            {{order.name}}
        </td>
        <td>
          <span >{{ order.description }}</span>
        </td>
        <td>{{ order.type }}</td>
        <td>{{ order.price }}€</td>
        <td
          class="text-end"
          v-if="showEditButton || showDeleteButton || showAddButton"
        >
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="addClick(order)"
              v-if="showAddButton"
            >
              <i class="bi bi-xs bi-cart-check"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="editClick(order)"
              v-if="showEditButton"
            >
              <i class="bi bi-xs bi-pencil"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(order)"
              v-if="showDeleteButton"
            >
              <i class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>
      </tr>
      <tr >
        <td>#001</td>
        <td>Delivered</td>
        <td>16/11/2022</td>
        <td>3€</td>
        
        <td
          class="text-end"
          v-if="showEditButton || showDeleteButton || showAddButton"
        >
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="addClick(product)"
              v-if="showAddButton"
            >
              <i class="bi bi-xs bi-cart-check"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="editClick(product)"
              v-if="showEditButton"
            >
              <i class="bi bi-xs bi-pencil"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(product)"
              v-if="showDeleteButton"
            >
              <i class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style>
</style>