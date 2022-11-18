<script setup>
import { ref, computed, onMounted, inject, watch } from 'vue'
import { useRouter } from 'vue-router'
import EmployeesTable from "./EmployeesTable.vue"

const axios = inject('axios')
const router = useRouter()

const employees = ref([])
const name = ref(undefined)
const filterByType = ref('A')
const filterByName = ref(undefined)

const LoadEmployees = () => {
    axios.get(`/users/employees`)
        .then((response) => {
            employees.value = response.data
        })
        .catch((error) => {
            console.log(error)
        })
}

const showEmployee = (employee) => {
    console.log("Show")
    const employeeObj = Object.assign({}, employee)
    console.log(employeeObj)
}

const addEmployee = () => {
    router.push({ name: 'NewEmployee' })
}

const editEmployee = (employee) => {
    console.log("Edit")
    const employeeObj = Object.assign({}, employee)
    console.log(employeeObj)
}

const deleteEmployee = (employee) => {
    console.log("Delete")
    const employeeObj = Object.assign({}, employee)
    console.log(employeeObj)
}

onMounted(() => {
    LoadEmployees()
})
</script>

<template>
    <div class="d-flex justify-content-between">
        <div class="mx-2">
            <h3 class="mt-4"> Employees </h3>
        </div>
    </div>
    <hr>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
        <div class="mx-2 mt-2 flex-grow-1 filter-div">
            <label for="selectType" class="form-label">Filter by Employee Type:</label>
            <select class="form-select" id="selectType" v-model="filterByType">
                <option value="A">Any</option>
                <option value="EC">Chef</option>
                <option value="ED">Delivery</option>
                <option value="EM">Manager</option>
            </select>
        </div>

        <div class="mx-2 mt-2 flex-grow-1 filter-div">
            <div class="inner-addon left-addon">
                <label for="searchbar" class="form-label">Search for Name:</label>
                <i class="glyphicon glyphicon-user"></i>
                <input v-model="filterByName" type="text" name="name" class="form-control" />
            </div>
        </div>

        <div class="mx-2 mt-2">
            <button type="button" class="btn btn-success px-4 btn-addtask" @click="addEmployee"><i
                    class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Employee</button>
        </div>

    </div>
    <employees-table :employees="employees" :filterByType="filterByType" :name="filterByName" @show="showEmployee"
        @edit="editEmployee" @delete="deleteEmployee">
    </employees-table>
</template>


<style scoped>
.filter-div {
    min-width: 12rem;
}

.btn-addtask {
    margin-top: 1.85rem;
}
</style>
