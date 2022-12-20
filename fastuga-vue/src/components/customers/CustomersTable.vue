<script setup>
import { ref, computed, onUpdated, inject, toRaw } from "vue";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";

const serverBaseUrl = inject("serverBaseUrl");
const toast = inject("toast");

const props = defineProps({
  customers: Object,
  default: () => [],
});
const emit = defineEmits(["delete", "block", "unblock"]);

const customerToDeleteDescription = computed(() => {
  return customerToDelete.value ? `${customerToDelete.value.name}` : "";
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
  console.log(toRaw(customer));
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
  console.log(deleteConfirmationDialog.value);
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

  if (
    deleteConfirmationDialog.value == null &&
    customerInfoDialog.value == null
  ) {
    deleteDialog.value = null;
  }
});

// Block and Unblock
const blockClick = (customer) => {
  console.log(customer);
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
          <span class="confirmation-label">Name: </span
          ><span>{{ customerInfo.name }}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Email: </span
          ><span>{{ customerInfo.email }}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">NIF: </span
          ><span>{{ customerInfo.nif }}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Phone: </span
          ><span>{{ customerInfo.phone }}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Points: </span
          ><span>{{ customerInfo.points }}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Default Payment Reference: </span
          ><span>{{ customerInfo.default_payment_reference }}</span>
        </div>
        <div class="confirmation-row">
          <span class="confirmation-label">Default Payment Type: </span
          ><span>{{ customerInfo.default_payment_type }}</span>
        </div>
      </div>
    </ConfirmationDialog>
  </div>

  <table class="table fastuga-font">
    <thead>
      <tr>
        <th>Photo</th>
        <th>Name</th>
        <th>Email</th>
        <th style="text-align: center">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="customer in props.customers" :key="customer">
        <td>
          <img
            v-if="customer.photo_url"
            :src="`${serverBaseUrl}/storage/fotos/${customer.photo_url}`"
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
        <td>{{ customer.name }}</td>
        <td>{{ customer.email }}</td>

        <td class="text-end">
          <div class="d-flex justify-content-around customers-buttons fastuga-font">
            <button
              class="btn customers-button btn-info"
              @click="
                showDeleteDialog(false);
                showCustomerInfo(customer);
              "
            >
              <span><i class="bi bi-info-circle"></i></span>
              <span> Info </span>
            </button>
            <button
              v-if="customer.blocked === 0"
              class="btn customers-button button-block"
              @click="blockClick(customer)"
            >
              <span><i class="bi bi-slash-circle"></i></span>
              <span> Block </span>
            </button>
            <button
              v-else
              class="btn customers-button button-unblock"
              @click="unblockClick(customer)"
            >
              <span><i class="bi bi-slash-circle-fill"></i></span>
              <span>Unblock</span>
            </button>
            <button
              class="btn customers-button btn-delete"
              @click="
                showDeleteDialog(true);
                deleteClick(customer);
              "
            >
              <span><i class="bi bi-trash3"></i></span>
              <span> Delete </span>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
.customers-button{
  width: 30%;
}

.customers-buttons{
  display: flex;
  flex-direction: row;
  align-items: center;
}

.btn-delete:hover,
.btn-delete:active {
  border: 2.5px solid #c53b3b !important;
  color: #c53b3b !important;
  background-color: rgb(242, 241, 241) !important;
  display: flex;
  flex-direction: row;
  align-items: center;
}

.btn-delete {
  border: 2.5px solid #ff5b5b;
  color: #ff5b5b;
  display: flex;
  flex-direction: row;
  align-items: center;
}

.btn-info:hover,
.btn-info:active {
  border: 2.5px solid #4d3838 !important;
  color: #4d3838 !important;
  background-color: rgb(242, 241, 241)  !important;
  display: flex;
  flex-direction: row;
  align-items: center;
}

.btn-info {
  border: 2.5px solid #5e4444;
  color: #5e4444;
  background-color: white;
  display: flex;
  flex-direction: row;
  align-items: center;
}

.button-unblock:hover, .button-unblock:active{
  background-color: #ff8300 !important;
  color: white !important;
  display: flex;
  flex-direction: row;
  align-items: center;
}

.button-unblock {
  background-color: #ffa71dd6;
  color: white;
  display: flex;
  flex-direction: row;
  align-items: center;
}

.button-block:hover, .button-block:active {
  border: 2.5px solid #ff8300 !important;
  color: #ff8300 !important;
  background-color: rgb(242, 241, 241)  !important;
  height: fit-content;
  display: flex;
  flex-direction: row;
  align-items: center;
}

.button-block {
  border: 2.5px solid #ffa71dd6;
  color: #ffa71dd6;
  display: flex;
  flex-direction: row;
  align-items: center;
}

.confirmation-label {
  width: 50%;
  font-weight: bold;
}

.confirmation-row {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.confirmation-container {
  display: flex;
  flex-direction: column;
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
