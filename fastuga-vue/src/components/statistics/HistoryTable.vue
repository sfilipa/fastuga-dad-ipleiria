<script setup>
import axios from 'axios'
import {ref, computed, onMounted, inject} from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast")

const orderItems = ref([])
let indexesAdded = []

const table_ref = ref(null)

const serverBaseUrl = inject("serverBaseUrl")
const props = defineProps({
  orders: Array,
  default: () => [],
  filterByPaymentType: String,
  date: Date
});

const emit = defineEmits(["show"]);

const showOrder = (order, indexRow) => {
  orderItems.value = order.order_items

  indexRow = indexRow + 1;
  if (indexesAdded.includes(order.id)) {
    props.orders.splice(indexRow, orderItems.value.length)

    let key = indexesAdded.indexOf(order.id)
    indexesAdded.splice(key, 1)
  } else {
    indexesAdded.push(order.id)
    orderItems.value.forEach((value, index) => {
      props.orders.splice(indexRow, 0, value)
      indexRow++
    })
  }
};

const showClick = (order) => {
  emit("show", order);
};

</script>

<template>
  <table class="table" ref="table_ref">
    <thead>
    <tr>
      <th>Ticket Number</th>
      <th>Date</th>
      <th>Payment Type</th>
      <th>Payment Reference</th>
      <th>Points Used</th>
      <th>Total Paid</th>
      <th>Points Gained</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(order, index) in props.orders
			.filter((order) => (props.filterByPaymentType === 'A' ? true : order.payment_type === props.filterByPaymentType))
			.filter((order) => (!props.date ? true : order.date === props.date))" :key="order.id">
      <td v-if="order.ticket_number === undefined" class="products">
      </td>
      <td v-else>
        {{ order.ticket_number }}
      </td>
      <td v-if="order.date === undefined" style="text-align: center;" class="products">
        <img class="picture" :src='`${serverBaseUrl}/storage/products/${order.product.photo_url}`'/>
      </td>
      <td v-else>
        {{ order.date }}
      </td>
      <td v-if="order.payment_type === undefined" class="products">
        {{ order.product.name }}
      </td>
      <td v-else>
        {{ order.payment_type }}
      </td>
      <td v-if="order.payment_reference === undefined" class="products">
        {{ order.product.type }}
      </td>
      <td v-else>
        {{ order.payment_reference }}
      </td>
      <td v-if="order.points_used_to_pay === undefined" class="products">
        {{ order.product.price + ' €' }}
      </td>
      <td v-else>
        {{ order.points_used_to_pay + ' points' }}
      </td>
      <td v-if="order.total_paid === undefined" class="products">
      </td>
      <td v-else>
        {{ order.total_paid + ' €' }}
      </td>
      <td v-if="order.points_gained === undefined" class="products">
      </td>
      <td v-else>
        {{ order.points_gained + ' points' }}
      </td>
      <td v-if="order.points_gained === undefined" class="products">

      </td>
      <td v-else class="text-end">
        <button class="btn btn-xs btn-light" @click="showOrder(order, index)">
          <i class="bi bi-xs bi-search"></i>
        </button>
      </td>
    </tr>

    <!-- <tr v-show="showDetails" v-for="item in orderItems">
      <td style="text-align: center;">
        <img :src='`${serverBaseUrl}/storage/products/${item.photo_url}`' />
      </td>
      <td>{{ item.name }}</td>
      <td>{{ item.description }}</td>
    </tr> -->

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
