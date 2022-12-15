<script setup>
import axios from 'axios'
import { ref, computed, onMounted, inject } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast")

const orderItems = ref([])
let showDetails = ref(false)

const table_ref = ref(null)

const serverBaseUrl = inject("serverBaseUrl")
const props = defineProps({
	orders: Array,
	default: () => [],
	filterByPaymentType: String,
	date: Date
});

const emit = defineEmits(["show"]);

const showOrder = (order, index) => {
	console.log(table_ref.value)
	axios
		.get(`http://localhost:8081/api/orders/${order.id}/orderItems`)
		.then((response) => {
			console.log(showDetails.value)
			orderItems.value = response.data
			showDetails.value = true
			console.log(orderItems.value)

			for(elem in orderItems.value){
				const row = table_ref.insertRow(index)
				const cell = row.insertCell(0)
				
				index++
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
				<td>{{ order.ticket_number }}</td>
				<td>{{ order.date }}</td>
				<td>{{ order.payment_type }}</td>
				<td>{{ order.payment_reference }}</td>
				<td>{{ order.points_used_to_pay }} points</td>
				<td>{{ order.total_paid }} €</td>
				<td>{{ order.points_gained }} points</td>
				<td class="text-end"><button class="btn btn-xs btn-light" @click="showOrder(order, index)">
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

<style>

</style>
