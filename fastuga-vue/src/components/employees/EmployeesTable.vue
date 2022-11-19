<script setup>
import { ref, computed, onMounted, inject } from 'vue'


const props = defineProps({
    employees: Array,
    default: () => [],
    filterByType: String,
    name: String
})
const emit = defineEmits(['show','edit','delete'])

const showClick = (employee) => {
    emit('show', employee)
}

const editClick = (employee) => {
  emit('edit', employee)
}

const deleteClick = (employee) => {
  emit('delete', employee)
}
</script>

<template>
<table class="table">
    <thead>
      <tr>
        <th>Photo</th>
        <th>Id</th>
        <th>Name</th>
        <th>Email</th>
        <th>Type</th>
        <th>Blocked</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="employee in props.employees.filter(employee => props.filterByType === 'A' ? true : employee.type === props.filterByType)
                                      .filter(employee => !props.name ? true : employee.name === props.name)" :key="employee.id">
        <td>
            <img :src='"http://localhost:8081/storage/fotos/" + employee.photo_url' class="rounded-circle z-depth-0 avatar-img" alt="avatar image"/>
        </td>
        <td> {{ employee.id }} </td>
        <td> {{employee.name}} </td>
        <td> {{employee.email}} </td>

        <td v-if="employee.type == 'EC'">Chef</td> 
        <td v-if="employee.type == 'ED'">Delivery</td> 
        <td v-if="employee.type == 'EM'">Manager</td> 

        <td v-if="employee.blocked == 0">No</td>
        <td v-if="employee.blocked == 1">Yes</td>
        <td class="text-end">
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="showClick(employee)"
              >
              <i class="bi bi-xs bi-search"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(employee)"
              >
              <i class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style>
</style>