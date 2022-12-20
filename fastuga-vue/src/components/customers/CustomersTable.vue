<script setup>
import { ref, computed, onUpdated, inject, toRaw } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const toast = inject("toast");

const props = defineProps({
  customers: Object,
  default: () => [],
});
const emit = defineEmits(["show", "delete", "block", "unblock"]);

const showClick = (customer) => {
  emit("show", customer);
};

const customerToDeleteDescription = computed(() => {
  return customerToDelete.value ? `${customerToDelete.value.user_id.name}` : "";
});

const deleteDialog = ref(null);

const showDeleteDialog = (bool) => {
  deleteDialog.value = bool;
};

// Show Customer Info
const customerInfoDialog = ref(null);
let customerInfo = ref(null);

const showCustomerInfo = (customer) => {
  customerInfo.value = toRaw(customer);
  console.log(toRaw(customer))
  if (customerInfoDialog.value !== null && deleteDialog.value !== null) {
    customerInfoDialog.value.show();
  }
};

const closeInfo = () => {
  deleteDialog.value = null;
};

// Delete
const deleteConfirmationDialog = ref(null);
const customerToDelete = ref(null);

const deleteClick = (customer) => {
  customerToDelete.value = customer;
  console.log( deleteConfirmationDialog.value)
  if (deleteConfirmationDialog.value !== null && deleteDialog.value !== null) {
    deleteConfirmationDialog.value.show();
  }
};
const dialogConfirmDelete = () => {
  emit("delete", customerToDelete.value);
  deleteDialog.value = null;
  toast.info("Customer " + customerToDeleteDescription.value + " was deleted");
};

onUpdated(() => {
  if (deleteDialog.value === true) {
    deleteConfirmationDialog.value != null
      ? deleteConfirmationDialog.value.show()
      : (deleteDialog.value = null);
  }

  if (deleteDialog.value === false) {
    customerInfoDialog.value != null
      ? customerInfoDialog.value.show()
      : (deleteDialog.value = null);
  }

  if(deleteConfirmationDialog.value == null || customerInfoDialog.value == null){
    deleteDialog.value = null;
  }
});

// Block and Unblock
const blockClick = (customer) => {
  emit("block", customer);
};

const unblockClick = (customer) => {
  emit("unblock", customer);
};
</script>

<template>
  <div v-if="deleteDialog">
    <ConfirmationDialog
      ref="deleteConfirmationDialog"
      confirmationBtn="Delete Customer"
      :msg="`Do you really want to delete: ${customerToDeleteDescription}`"
      @confirmed="dialogConfirmDelete"
    >
    </ConfirmationDialog>
  </div>

  <div v-if="deleteDialog != null && !deleteDialog">
    <ConfirmationDialog
      ref="customerInfoDialog"
      confirmationBtn="Close Info"
      :msg="``"
      :title="`Customer Information`"
      @confirmed="closeInfo"
    >
    <div class="confirmation-container fastuga-font">
        <div class="confirmation-row">
          <span class="confirmation-label">Name: </span><span>{{customerInfo.user_id.name}}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Email: </span><span>{{customerInfo.user_id.email}}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">NIF: </span><span>{{customerInfo.nif}}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Phone: </span><span>{{customerInfo.phone}}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Points: </span><span>{{customerInfo.points}}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Default Payment Reference: </span><span>{{customerInfo.default_payment_reference}}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Default Payment Type: </span><span>{{customerInfo.default_payment_type}}</span>
        </div>
      </div>
    </ConfirmationDialog>
  </div>

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
      <tr v-for="customer in props.customers" :key="customer">
        <td>
          <img
            v-if="customer.user_id.photo_url"
            :src="
              'http://localhost:8081/storage/fotos/' +
              customer.user_id.photo_url
            "
            class="rounded-circle z-depth-0 avatar-img"
            alt="avatar image"
          />
          <img
            v-else
            :src="'https://via.placeholder.com/150'"
            class="rounded-circle z-depth-0 avatar-img"
            alt="avatar image"
          />
        </td>
        <td>{{ customer.user_id.name }}</td>
        <td>{{ customer.user_id.email }}</td>

        <td v-if="customer.user_id.blocked === 0">No</td>
        <td v-if="customer.user_id.blocked === 1">Yes</td>
        <td class="text-end">
          <div class="d-flex justify-content-around">
            <button
              class="btn btn-xs btn-info"
              @click="
                showDeleteDialog(false);
                showCustomerInfo(customer);
              "
            >
              <i class="bi bi-info-square-fill"></i> About
            </button>
            <button
              v-if="customer.user_id.blocked === 0"
              class="btn btn-xs btn-warning"
              @click="blockClick(customer.user_id)"
            >
              <i class="bi bi-x-octagon-fill"></i> Block
            </button>
            <button
              v-if="customer.user_id.blocked === 1"
              class="btn btn-xs btn-warning"
              @click="unblockClick(customer.user_id)"
            >
              <i class="bi bi-x-octagon-fill"></i> Unblock
            </button>
            <button
              class="btn btn-xs btn-danger"
              @click="
                showDeleteDialog(true);
                deleteClick(customer);
              "
            >
              <i class="bi bi-trash-fill"></i> Delete
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>

.confirmation-label{
  width: 50%;
  font-weight: bold;
}

.confirmation-row{
  display: flex;
  flex-direction: row;
  align-items: center;
}

.confirmation-container{
  display: flex;
  flex-direction: column;

}
</style>
