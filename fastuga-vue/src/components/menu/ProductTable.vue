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
  filterByType: String,
  filterByPrice: Number
})

const emit = defineEmits(["add", "edit", "deleted"])

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
        <th></th>
        <th>Name</th>
        <th>Description</th>
        <th>Type</th>
        <th>Price</th>
        <th v-if="showEditButton || showDeleteButton || showAddButton"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="product in props.products
                            .filter(product => props.filterByType==='any' ? true : product.type===props.filterByType)
                            .filter(product => props.filterByPrice==null ? true : product.price<=props.filterByPrice)" 
        :key="product.id">
        <td>
            <img :src='"http://localhost:8081/storage/products/"+product.photo_url' />
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
              class="btn btn-xs btn-light hvr-grow"
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

img, svg {
  vertical-align: middle;
  width: 55px;
}

.hvr-grow {
  display: inline-block;
  vertical-align: middle;
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-property: transform;
  transition-property: transform;
}
.hvr-grow:hover, .hvr-grow:focus, .hvr-grow:active {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}


</style>
