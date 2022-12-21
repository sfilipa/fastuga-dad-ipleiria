<script setup>
import { ref, computed, onMounted, inject } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";
import moment from 'moment'
const toast = inject("toast")

const serverBaseUrl = inject("serverBaseUrl")
const props = defineProps({
	dishPrepared: Array,
	default: () => [],
  filterByName: String,
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
				<th>Photo Url</th>
				<th>Date</th>
				<th>Name</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="dish in props.dishPrepared
			.filter((dish) => (props.filterByName === null ? true: dish.name.toLowerCase().includes(props.filterByName.toLowerCase())))
			.filter((dish) => (!props.date ? true : moment(String(dish.created_at)).format('MM/DD/YYYY') === moment(String(props.date)).format('MM/DD/YYYY')))" :key="dish.id">
				<td style="text-align: center;">
					<img :src='`${serverBaseUrl}/storage/products/${dish.photo_url}`' />
				</td>
				<td>{{ moment(String(dish.created_at)).format('MM/DD/YYYY hh:mm') }}</td>
				<td>{{ dish.name }}</td>
				<td>{{ dish.description }}</td>
			</tr>
		</tbody>
	</table>
</template>

<style>
img {
  height: 100px;
}
</style>
