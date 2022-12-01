<script setup>

import { ref, watch, computed, inject } from "vue";

import avatarNoneUrl from '@/assets/avatar-none.png'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const toast = inject('toast')

const credentials = ref({
    email: '',
    password: '',
    name: '',
    type: ref("EC"),
    blocked: 0,
    photo_url: ''
})

const cancel = () => {
    router.push({ name: "Employees" })
}

const emit = defineEmits(['addEmployee'])

const addEmployee = async () => {
    await axios.post(`http://localhost:8081/api/users`, credentials.value)
        .then((response) => {
            toast.info("Employee '" + response.data.data.name + "' was created")
            emit('addEmployee')
            router.push({ name: "Employees" })
        })
        .catch((error) => {
            if (error.response.data.errors.name != undefined) {
                toast.error(error.response.data.errors.name)
            } else {
                if (error.response.data.errors.email != undefined) {
                    toast.error(error.response.data.errors.email)
                } else {
                    if (error.response.data.errors.password != undefined) {
                        toast.error(error.response.data.errors.password)
                    } else {
                        toast.error('Add Employee Failed!')
                    }
                }
                console.log(error.response.data)
                console.log(credentials.photo_url)
            }
        });
}

const updatePhoto = (e)=>{
    if (!e.target.files.length){ 
        return;
    }

    credentials.photo_url = e.target.files[0].name;
  }

</script>

<template>
    <form class="row g-3 needs-validation" novalidate @submit.prevent="addEmployee">
        <h3 class="mt-4">Add New Employee</h3>
        <hr>
        <div class="row">
            <div class="col-50">
                <label>Name:</label>
                <input type="text" class="form-control" id="inputName" placeholder="Enter Name" required
                    v-model="credentials.name">
            </div>
            <div class="col-50">
                <label>Email:</label>
                <input type="text" class="form-control" id="inputEmail" placeholder="Enter Email" required
                    v-model="credentials.email">
            </div>
        </div>
        <div class="row">
            <div class="col-50">
                <label>Password:</label>
                <input type="password" class="form-control" id="inputPassword" placeholder="Enter Password" required
                    v-model="credentials.password">
            </div>
            <div class="col-50">
                <label>Employee Type:</label>
                <select class="form-select" id="selectType" v-model="credentials.type">
                    <option value="EC">Chef</option>
                    <option value="ED">Delivery</option>
                    <option value="EM">Manager</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-50">
                <label>Photo: </label>
                <input type="file" class="form-control" id="photo_url" name="photo_url"
                    accept="image/png, image/jpeg, image/jpg" @change="updatePhoto">
            </div>
        </div>
        <div class="mb-3 d-flex justify-content-center">
            <button type="button" class="btn btn-success px-5" @click="addEmployee">Save</button>
            <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
        </div>
    </form>

</template>

<style scoped>
@media (max-width: 800px) {
    .row {
        flex-direction: column-reverse;
    }

    .col-25 {
        margin-bottom: 20px;
    }
}

input[type=text] {
    width: 100%;
    margin-bottom: 20px;
    padding: 12px;
}

input[type=password] {
    width: 100%;
    margin-bottom: 20px;
    padding: 12px;
}

.btn-success:hover {
    background-color: #0b450f;
}

select {
    width: 100%;
    margin-bottom: 20px;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 3px;
}

body {
    font-family: Arial;
    font-size: 17px;
    padding: 8px;
}

* {
    box-sizing: border-box;
}

.row {
    display: -ms-flexbox;
    /* IE10 */
    display: flex;
    -ms-flex-wrap: wrap;
    /* IE10 */
    flex-wrap: wrap;
    margin: 0 -16px;
}

.col-25 {
    -ms-flex: 25%;
    /* IE10 */
    flex: 25%;
}

.col-50 {
    -ms-flex: 50%;
    /* IE10 */
    flex: 50%;
}

.col-75 {
    -ms-flex: 75%;
    /* IE10 */
    flex: 75%;
}

.col-25,
.col-50,
.col-75 {
    padding: 0 16px;
}
</style>
