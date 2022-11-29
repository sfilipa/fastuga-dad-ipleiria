<script setup>
  import { ref, computed, onMounted, inject, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import axiosImported from 'axios'

  const axios = inject('axios')
  const toast = inject('toast')

  const router = useRouter()
  const newProduct = ref('')

  const emit = defineEmits(["addProduct"])

  const nameInput = ref(null)
  const typeInput = ref(null)
  const descriptionInput = ref(null)
  const priceInput = ref(null)
  const photoInput = ref(null)

  const productTypes = ref([])
  
  const LoadProductTypes = () => {
    axios.get(`/products/types`)
      .then((response) => {
        productTypes.value = response.data
      })
      .catch((error) => {
        console.log(error)
      })
  }

  const updatePhoto = (e)=>{
    if (!e.target.files.length){ 
        return;
    }

    photoInput.value = e.target.files[0];
  }

  const addProduct = async () => {
    let formData = new FormData();

    formData.append('name', nameInput.value);
    formData.append('type', typeInput.value);
    formData.append('description', descriptionInput.value);
    formData.append('price', priceInput.value);
    formData.append('photo_url', photoInput.value);

    await axiosImported.post(`http://localhost:8081/api/products`, formData)
        .then((response)=>{
            toast.info("Product '" + response.data.data.name + "' was created")
            emit('addProduct')
            router.push('/menu')
        })
        .catch((error)=>{
            console.log(error)
        });
  }

  onMounted (() => {
    LoadProductTypes()
  })

</script>

<template>
    
    <h2>Create a new product</h2>
    <div>
        <form action="#" class="form-inline row d-flex" enctype="multipart/form-data">
            <div class="flex-grow-2 d-flex">
                <label>Name: </label>
                <input type="text" name="name" v-model="nameInput">
            </div>
            <div class="flex-grow-2 d-flex">
                <label>Type: </label>
                <select name="type" id="type" v-model="typeInput">
                    <option v-for="productType in productTypes" :value="productType">{{productType.charAt(0).toUpperCase()}}{{productType.slice(1)}}</option>
                </select>
            </div>
            
            <div class="flex-grow-1 d-flex-direction-col">
                <p><label>Description: </label></p>
                <textarea id="description" name="description" rows="4" cols="50" v-model="descriptionInput"></textarea><br>
            </div>
            <div class="flex-grow-1 d-flex">
                <label>Price: </label>
                <input type="number" name="price" min="0" max="99" step="0.1" v-model="priceInput">
            </div>
            <div class="flex-grow-1 d-flex">
                <label>Photo: </label>
                <input type="file" id="photo_url" name="photo_url" accept="image/png, image/jpeg, image/jpg" @change="updatePhoto">
            </div>
             <div>
                <button type="submit" class="btn btn-info" @click.prevent="addProduct">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true">
                        Add Product
                    </span>
                </button>
            </div>
        </form>
    </div>
   
</template>

<style scoped>
    label {
        margin-right: 10px;
    }

    .d-flex, .d-flex-direction-col{
        margin-bottom: 10px;
    }

</style>