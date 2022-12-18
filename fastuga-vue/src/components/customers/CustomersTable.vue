<script setup>
import { ref, computed, onMounted, inject } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast")

const props = defineProps({
	customers: Object,
	default: () => [],
	searchByType: String,
	searchByName: String,
});
const emit = defineEmits(["show", "edit", "delete", "block", "unblock"]);

const showClick = (customer) => {
	emit("show", customer);
};

const editClick = (customer) => {
	emit("edit", customer);
};

// Delete
const deleteConfirmationDialog = ref(null)
const customerToDelete = ref(null);
const customerToDeleteDescription = computed(() => {
	return customerToDelete.value ? `${customerToDelete.value.name}` : ""
}) 
const deleteClick = (customer) => {
	customerToDelete.value = customer;
	if (deleteConfirmationDialog.value !== null) {
		deleteConfirmationDialog.value.show();
	}
};
const dialogConfirmDelete = () => {
	emit("delete", customerToDelete.value);
	toast.info("Customer " + customerToDeleteDescription.value.name + " was deleted")
}

// Block and Unblock
const blockClick = (customer) => {
	emit("block", customer);
};
const unblockClick = (customer) => {
	emit("unblock", customer);
};
</script>

<template>
	<ConfirmationDialog
		ref="deleteConfirmationDialog"
		confirmationBtn="Delete Customer"
		:msg="`Do you really want to delete: ${customerToDeleteDescription}`"
		@confirmed="dialogConfirmDelete"
		></ConfirmationDialog>
	<table class="table">
		<thead>
			<tr>
				<th>Photo</th>
				<th>Name</th>
				<th>Email</th>
				<th>Blocked</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<tr
				v-for="customer in props.customers"
				:key="customer">
				<td>
					<img
						v-if="customer.user_id.photo_url" :src="'http://localhost:8081/storage/fotos/' + customer.user_id.photo_url"
						class="rounded-circle z-depth-0 avatar-img"
						alt="avatar image" />
					<img 
						v-else :src="'https://via.placeholder.com/150'"
						class="rounded-circle z-depth-0 avatar-img"
						alt="avatar image" />
				</td>
				<td>{{ customer.user_id.name }}</td>
				<td>{{ customer.user_id.email }}</td>

				<td v-if="customer.user_id.blocked === 0">No</td>
				<td v-if="customer.user_id.blocked === 1">Yes</td>
				<td class="text-end">
					<div class="d-flex justify-content-around">
						<button class="btn btn-xs btn-info" @click="showClick(customer)">
							<i class="bi bi-info-square-fill"></i> About
						</button>
						<button
							v-if="customer.user_id.blocked === 0"
							class="btn btn-xs btn-warning"
							@click="blockClick(customer.user_id)">
							<i class="bi bi-x-octagon-fill"></i> Block
						</button>
						<button
							v-if="customer.user_id.blocked === 1"
							class="btn btn-xs btn-warning"
							@click="unblockClick(customer.user_id)">
							<i class="bi bi-x-octagon-fill"></i> Unblock
						</button>
						<button class="btn btn-xs btn-danger" @click="deleteClick(customer.user_id)">
							<i class="bi bi-trash-fill"></i> Delete
						</button>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</template>

<style></style>
