<script setup>
import { ref, computed, onMounted, inject } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast")

const serverBaseUrl = inject("serverBaseUrl")
const props = defineProps({
	dishPrepared: Array,
	default: () => [],
	name: String,
	date: Date
});

const emit = defineEmits(["show"]);

const getDate = (date)=>{
	const formated = new Date(date)
	return `${formated.getFullYear()}-${formated.getMonth()}-${formated.getDay()}`
}

const showClick = (order) => {
	emit("show", order);
};

</script>

<template>
	<table class="table">
		<thead>
			<tr>
				<th>Photo Url</th>
				<th>Date</th>
				<th>Name</th>
				<th>Description</th>
				<th>Total Paid</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="dish in props.dishPrepared
			.filter((dish) => (!props.name ? true : dish.name === props.name))
			.filter((dish) => (!props.date ? true : dish.created_at === props.date))" :key="dish.id">
				<td style="text-align: center;">
					<img :src='`${serverBaseUrl}/storage/products/${dish.photo_url}`' />
				</td>
				<td>{{ getDate(dish.created_at) }}</td>
				<td>{{ dish.name }}</td>
				<td>{{ dish.description }}</td>
			</tr>
		</tbody>
	</table>
</template>

<style>

</style>
