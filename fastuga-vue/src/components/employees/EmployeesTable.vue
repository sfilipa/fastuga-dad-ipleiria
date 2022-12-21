<script setup>
import { ref, computed, onMounted, inject } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const serverBaseUrl = inject("serverBaseUrl");
const toast = inject("toast");

const props = defineProps({
  employees: Array,
  default: () => [],
  filterByType: String,
  filterByName: String,
});
const emit = defineEmits(["delete", "block", "unblock"]);

// Delete
const deleteConfirmationDialog = ref(null);
const employeeToDelete = ref(null);
const employeeToDeleteDescription = computed(() => {
  return employeeToDelete.value ? `${employeeToDelete.value.name}` : "";
});
const deleteClick = (employee) => {
  employeeToDelete.value = employee;
  if (deleteConfirmationDialog.value !== null) {
    deleteConfirmationDialog.value.show();
  }
};
const dialogConfirmDelete = () => {
  emit("delete", employeeToDelete.value);
  toast.info("Employee " + employeeToDeleteDescription.value + " was deleted");
};

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
        <th style="text-align: center">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="employee in props.employees
          .filter((employee) =>
            props.filterByType === 'A'
              ? true
              : employee.type === props.filterByType
          )
          // Filter by name
          .filter((employee) =>
            props.filterByName === null
              ? true
              : employee.name
                  .toLowerCase()
                  .includes(props.filterByName.toLowerCase())
          )"
        :key="employee.id"
      >
        <td>
          <img
            v-if="employee.photo_url"
            :src="`${serverBaseUrl}/storage/fotos/${employee.photo_url}`"
            class="rounded-circle z-depth-0 avatar-img table-img"
            alt="avatar image"
          />
          <img
            v-else
            :src="'https://via.placeholder.com/150'"
            class="rounded-circle z-depth-0 avatar-img table-img"
            alt="avatar image"
          />
        </td>
        <td>{{ employee.name }}</td>
        <td>{{ employee.email }}</td>

        <td v-if="employee.type === 'EC'">Chef</td>
        <td v-if="employee.type === 'ED'">Delivery</td>
        <td v-if="employee.type === 'EM'">Manager</td>

        <td class="text-end">
          <div class="d-flex justify-content-around employees-buttons">
            <button
              v-if="employee.blocked === 0"
              class="btn btn-xs employees-button button-block"
              @click="blockEmployeeClick(employee)"
            >
              <i class="bi bi-slash-circle"></i> Block
            </button>
            <button
              v-if="employee.blocked === 1"
              class="btn btn-xs employees-button button-unblock"
              @click="unblockEmployeeClick(employee)"
            >
              <i class="bi bi-slash-circle-fill"></i> Unblock
            </button>
            <button
              class="btn btn-xs employees-button btn-delete-css"
              @click="deleteClick(employee)"
            >
              <i class="bi bi-trash3"></i> Delete
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
.employees-button {
  width: 40%;
}

.employees-buttons {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.table-img {
  margin: auto;
}

.bi {
  display: flex;
}

td {
  vertical-align: middle;
}
</style>
