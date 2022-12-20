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
  let indexOriginal = indexRow

  axios
      .get(`http://localhost:8081/api/orders/${order.id}/products`)
      .then((response) => {
        //console.log(showDetails.value)
        orderItems.value = response.data

        indexRow = indexRow + 1;
        console.log(order.id)
        if (indexesAdded.includes(order.id)) {
          props.orders.splice(indexRow, orderItems.value.length)

          console.log('ARRAY ANTES '+indexesAdded)
          let key = indexesAdded.indexOf(order.id)
          indexesAdded.splice(key, 1)

          console.log('ARRAY ANTES '+indexesAdded)
          console.log('entrou no if')
        } else {
          indexesAdded.push(order.id)
          orderItems.value.forEach((value, index) => {

            console.log('entrou no else')
            props.orders.splice(indexRow, 0, value)
            indexRow++
          })
        }

      })
      .catch((error) => {
        console.log(error);
      });
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
      <td v-if="order.name != undefined" class="products">
      </td>
      <td v-else>
        {{ order.ticket_number }}
      </td>
      <td v-if="order.name != undefined" style="text-align: center;" class="products">
        <img :src='`${serverBaseUrl}/storage/products/${order.photo_url}`' />
      </td>
      <td v-else>
        {{ order.date }}
      </td>
      <td v-if="order.name != undefined" class="products">
        {{ order.name }}
      </td>
      <td v-else>
        {{ order.payment_type }}
      </td>
      <td v-if="order.name != undefined" class="products">
        {{ order.type }}
      </td>
      <td v-else>
        {{ order.payment_reference }}
      </td>
      <td v-if="order.name != undefined" class="products">
        {{ order.price + ' €'}}
      </td>
      <td v-else>
        {{ order.points_used_to_pay + ' points'}}
      </td>
      <td v-if="order.name != undefined" class="products">
      </td>
      <td v-else>
        {{ order.total_paid + ' €'}}
      </td>
      <td v-if="order.name != undefined" class="products">
      </td>
      <td v-else>
        {{ order.points_gained + ' points'}}
      </td>
      <td v-if="order.name != undefined" class="products">

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
.products{
  background-color: #f1f1f1;
}
</style>
