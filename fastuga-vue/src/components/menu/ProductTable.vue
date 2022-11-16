<script setup>
import { ref, watch, watchEffect, computed, inject } from "vue"


const axios = inject("axios")
const toast = inject("toast")

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
  showAddButton: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(["edit", "deleted"])

const editingProducts = ref(props.products)
const productToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const productToDeleteDescription = computed(() => {
  return productToDelete.value
    ? `#${productToDelete.value.id} (${productToDelete.value.description})`
    : ""
})

watch(
  () => props.products,
  (newProducts) => {
    editingProducts.value = newProducts
  }
)

// Alternative to previous watch
// watchEffect(() => {
//   editingTasks.value = props.tasks
// })
const editClick = (product) => {
  emit("edit", product)
}

const addClick = (product) => {
    emit("add", product)
}

const dialogConfirmedDelete = () => {
  axios
    .delete("product/" + productToDelete.value.id)
    .then((response) => {
      emit("deleted", response.data.data)
      toast.info("Product " + productToDeleteDescription.value + " was deleted")
    })
    .catch((error) => {
      console.log(error)
    })
}

const deleteClick = (product) => {
    productToDelete.value = product
  deleteConfirmationDialog.value.show()
}
</script>

<template>
  <confirmation-dialog
    ref="deleteConfirmationDialog"
    confirmationBtn="Delete product"
    :msg="`Do you really want to delete the product ${productToDeleteDescription}?`"
    @confirmed="dialogConfirmedDelete"
  >
  </confirmation-dialog>

  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Description</th>
        <th>Type</th>
        <th>Price</th>
        <th v-if="showEditButton || showDeleteButton || showAddButton"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="product in props.products" :key="product.id">
        <td>
            <img src="{{product.photo_url}}"/>
        </td>
        <td>
            {{product.name}}
        </td>
        <td>
          <span >{{ product.description }}</span>
        </td>
        <td>{{ product.type }}</td>
        <td>{{ product.price }}€</td>
        <td
          class="text-end"
          v-if="showEditButton || showDeleteButton || showAddButton"
        >
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="addClick(product)"
              v-if="showAddButton"
            >
              <i class="bi bi-xs bi-cart-check"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="editClick(product)"
              v-if="showEditButton"
            >
              <i class="bi bi-xs bi-pencil"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(product)"
              v-if="showDeleteButton"
            >
              <i class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>
      </tr>
      <tr >
        <td>
            <img src=""/>
            aa
        </td>
        <td>name</td>
        <td>
          <span >teste</span>
        </td>
        <td>t1</td>
        <td>3€</td>
        
        <td
          class="text-end"
          v-if="showEditButton || showDeleteButton || showAddButton"
        >
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="addClick(product)"
              v-if="showAddButton"
            >
              <i class="bi bi-xs bi-cart-check"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="editClick(product)"
              v-if="showEditButton"
            >
              <i class="bi bi-xs bi-pencil"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(product)"
              v-if="showDeleteButton"
            >
              <i class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>

button {
  margin-left: 3px;
  margin-right: 3px;
}
</style>
