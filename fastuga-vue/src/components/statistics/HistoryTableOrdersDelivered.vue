<script setup>
import {ref, computed, onMounted, inject} from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast")

const orderItems = ref([])
let indexesAdded = []

const serverBaseUrl = inject("serverBaseUrl")

const props = defineProps({
  ordersDelivered: Array,
  default: () => []
});

const emit = defineEmits(["show"]);

const showOrder = (order, indexRow) => {
  orderItems.value = order.order_items

  indexRow = indexRow + 1;
  if (indexesAdded.includes(order.id)) {
    props.ordersDelivered.splice(indexRow, orderItems.value.length)

    let key = indexesAdded.indexOf(order.id)
    indexesAdded.splice(key, 1)
  } else {
    indexesAdded.push(order.id)
    orderItems.value.forEach((value, index) => {
      props.ordersDelivered.splice(indexRow, 0, value)
      indexRow++
    })
  }
};

</script>

<template>
  <table class="table">
    <thead>
    <tr>
      <th>ID</th>
      <th>Ticket Number</th>
      <th>Date</th>
      <th>Payment Type</th>
      <th>Payment Reference</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(order, index) in props.ordersDelivered" :key="order.id">
      <td v-if="order.ticket_number === undefined && order.product != null" class="products">
      </td>
      <td v-else>
        {{ order.id }}
      </td>
      <td v-if="order.ticket_number === undefined && order.product != null" class="products">
        <img class="picture" :src='`${serverBaseUrl}/storage/products/${order.product.photo_url}`'/>
      </td>
      <td v-else>
        {{ order.ticket_number }}
      </td>
      <td v-if="order.date === undefined && order.product != null"  style="text-align: center;" class="products">
        {{ order.product.name }}
      </td>
      <td v-else>
        {{ order.date }}
      </td>
      <td v-if="order.payment_type === undefined && order.product != null" class="products">
        {{ order.product.type }}
      </td>
      <td v-else>
        {{ order.payment_type }}
      </td>
      <td v-if="order.payment_reference === undefined && order.product != null" class="products">
      </td>
      <td v-else>
        {{ order.payment_reference }}
      </td>
      <td v-if="order.points_gained === undefined" class="products">
      </td>
      <td v-else class="text-end">
        <button class="btn btn-xs btn-light" @click="showOrder(order, index)">
          <i class="bi bi-xs bi-search"></i>
        </button>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<style scoped>
.products {
  background-color: #f1f1f1;
}

img {
  height: 100px;
}

</style>
