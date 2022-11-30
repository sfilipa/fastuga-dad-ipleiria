<script setup>
import { ref, computed, onMounted, inject } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast")

const props = defineProps({
	employees: Array,
	default: () => [],
	filterByType: String,
	name: String,
});
const emit = defineEmits(["show", "edit", "delete", "block", "unblock"]);

const showClick = (employee) => {
	emit("show", employee);
};

const editClick = (employee) => {
	emit("edit", employee);
};

// Delete
const deleteConfirmationDialog = ref(null)
const employeeToDelete = ref(null);
const employeeToDeleteDescription = computed(() => {
	return employeeToDelete.value ? `${employeeToDelete.value.name}` : ""
}) 
const deleteClick = (employee) => {
	employeeToDelete.value = employee;
	if (deleteConfirmationDialog.value !== null) {
		deleteConfirmationDialog.value.show();
	}
};
const dialogConfirmDelete = () => {
	emit("delete", employeeToDelete.value);
	toast.info("Employee " + employeeToDeleteDescription.value.name + " was deleted")
}

// Block and Unblock
const blockEmployeeClick = (employee) => {
	emit("block", employee);
};
const unblockEmployeeClick = (employee) => {
	emit("unblock", employee);
};

</script>

<template>
	<ConfirmationDialog
		ref="deleteConfirmationDialog"
		confirmationBtn="Delete Employee"
		:msg="`Do you really want to delete: ${employeeToDeleteDescription}`"
		@confirmed="dialogConfirmDelete"
		></ConfirmationDialog>
	<table class="table">
		<thead>
			<tr>
				<th>Photo</th>
				<th>Name</th>
				<th>Email</th>
				<th>Type</th>
				<th>Blocked</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<tr
				v-for="employee in props.employees
				.filter((employee) => (props.filterByType === 'A' ? true : employee.type === props.filterByType))
				.filter((employee) => (!props.name ? true : employee.name === props.name))"
				:key="employee.id">
				<td>
					<img
						:src="'http://localhost:8081/storage/fotos/' + employee.photo_url"
						class="rounded-circle z-depth-0 avatar-img"
						alt="avatar image" />
				</td>
				<td>{{ employee.name }}</td>
				<td>{{ employee.email }}</td>

				<td v-if="employee.type === 'EC'">Chef</td>
				<td v-if="employee.type === 'ED'">Delivery</td>
				<td v-if="employee.type === 'EM'">Manager</td>

				<td v-if="employee.blocked === 0">No</td>
				<td v-if="employee.blocked === 1">Yes</td>
				<td class="text-end">
					<div class="d-flex justify-content-around">
						<button class="btn btn-xs btn-info" @click="showClick(employee)">
							<i class="bi bi-info-square-fill"></i> About
						</button>
						<button
							v-if="employee.blocked === 0"
							class="btn btn-xs btn-warning"
							@click="blockEmployeeClick(employee)">
							<i class="bi bi-x-octagon-fill"></i> Block
						</button>
						<button
							v-if="employee.blocked === 1"
							class="btn btn-xs btn-warning"
							@click="unblockEmployeeClick(employee)">
							<i class="bi bi-x-octagon-fill"></i> Unblock
						</button>
						<button class="btn btn-xs btn-danger" @click="deleteClick(employee)">
							<i class="bi bi-trash-fill"></i> Delete
						</button>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</template>

<style></style>
