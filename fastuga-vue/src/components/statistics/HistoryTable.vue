<script setup>
import { ref, computed, onMounted, inject } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast")

const props = defineProps({
	orders: Array,
	default: () => [],
	filterByPaymentType: String,
	date: Date
});

const emit = defineEmits(["show"]);


const showClick = (order) => {
	emit("show", order);
};

</script>

<template>
	<table class="table">
		<thead>
			<tr>
				<th>Date</th>
				<th>Payment Type</th>
				<th>Payment Reference</th>
				<th>Total Paid</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="order in props.orders
			.filter((order) => (props.filterByPaymentType === 'A' ? true : order.payment_type === props.filterByPaymentType))
			.filter((order) => (!props.date ? true : order.date === props.date))" :key="order.id">
				<td>{{ order.date }}</td>
				<td>{{ order.payment_type }}</td>
				<td>{{ order.payment_reference }}</td>
				<td>{{ order.total_paid }} €</td>
				<td class="text-end"><button class="btn btn-xs btn-light" @click="showClick(order)">
						<i class="bi bi-xs bi-search"></i>
					</button>
				</td>
			</tr>
		</tbody>
	</table>
</template>

<style>

</style>
