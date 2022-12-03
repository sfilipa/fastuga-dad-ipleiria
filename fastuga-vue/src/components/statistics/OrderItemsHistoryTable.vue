<script setup>
import { ref, computed, onMounted, inject } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast")

const serverBaseUrl = inject("serverBaseUrl")
const props = defineProps({
	orderItems: Array,
	default: () => []
});

</script>

<template>
	<table class="table">
		<thead>
			<tr>
				<th>Name</th>
				<th>Type</th>
				<th>Description</th>
				<th>Photo Url</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="orderItem in props.orderItems">
				<td style="text-align: center;">
					<img :src='`${serverBaseUrl}/storage/products/${orderItem.photo_url}`' />
				</td>
				<td>{{ orderItem.name }}</td>
				<td>{{ orderItem.type }}</td>
				<td>{{ orderItem.description }}</td>
			</tr>
		</tbody>
	</table>
</template>

<style>

</style>
