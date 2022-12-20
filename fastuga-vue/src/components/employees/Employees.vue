<script setup>
import { ref, computed, onMounted, inject, watch } from "vue";
import { useRouter } from "vue-router";
import EmployeesTable from "./EmployeesTable.vue";
import { useUserStore } from "../../stores/user.js";

const axios = inject("axios");
const router = useRouter();
const socket = inject("socket");
const toast = inject("toast");

const userStore = useUserStore();
const employees = ref([]);
const name = ref(undefined);
const filterByType = ref("A");
const filterByName = ref(null);

const LoadEmployees = () => {
  axios
    .get(`/users/employees`)
    .then((response) => {
      employees.value = response.data;
    })
    .catch((error) => {
      console.log(error);
    });
};

const showEmployee = (employee) => {
  console.log("Show");
  const employeeObj = Object.assign({}, employee);
  console.log(employeeObj);
};

const editEmployee = (employee) => {
  console.log("Edit");
  const employeeObj = Object.assign({}, employee);
  console.log(employeeObj);
};

const blockEmployee = async (employee) => {
  console.log("Blocked");
  const employeeObj = Object.assign({}, employee);
  console.log(employeeObj);
  try {
    const { data } = await axios({
      method: "put",
      url: `/users/${employeeObj.id}`,
      data: {
        name: employeeObj.name,
        email: employeeObj.email,
        type: employeeObj.type,
        photo_url: employeeObj.url,
        blocked: 1,
        custom: employeeObj.custom,
      },
    });

    const users = new Object();
    users.user = employeeObj;
    users.manager = userStore.user.name;
    socket.emit("userBlocked", users);
    toast.warning(`You have blocked ${employeeObj.name}!`);
  } catch (err) {
    if (err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  LoadEmployees();
};

const unblockEmployee = async (employee) => {
  console.log("Unblocked");
  const employeeObj = Object.assign({}, employee);
  console.log(employeeObj);
  try {
    const { data } = await axios({
      method: "put",
      url: `/users/${employeeObj.id}`,
      data: {
        name: employeeObj.name,
        email: employeeObj.email,
        type: employeeObj.type,
        photo_url: employeeObj.url,
        blocked: 0,
        custom: employeeObj.custom,
      },
    });

    const users = new Object();
    users.user = employeeObj;
    users.manager = userStore.user.name;
    socket.emit("userUnblocked", users);
    toast.warning(`You have unblocked ${employeeObj.name}!`);
  } catch (err) {
    if (err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  LoadEmployees();
};

const deleteEmployee = async (employee) => {
  console.log("Delete");
  const employeeObj = Object.assign({}, employee);
  console.log(employeeObj);
  try {
    const { data } = await axios({
      method: "delete",
      url: `/users/${employeeObj.id}`,
    });
    console.log(data);
  } catch (err) {
    if (err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  LoadEmployees();
};

const addEmployee = () => {
  router.push({ name: "AddEmployee" });
};

onMounted(() => {
  LoadEmployees();
});
</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">Employees</h3>
    </div>
  </div>
  <hr />
  <div class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectType" class="form-label"
        >Filter by Employee Type:</label
      >
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
        <input
          v-model="filterByName"
          type="search"
          class="form-control rounded"
          placeholder="Search by Name"
          aria-label="Search"
          aria-describedby="search-addon"
        />
      </div>
    </div>

    <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-primary px-4 btn-addtask"
        @click="addEmployee"
      >
        <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Employee
      </button>
    </div>
  </div>
  <employees-table
    :employees="employees"
    :filterByType="filterByType"
    :filterByName="filterByName"
    @show="showEmployee"
    @edit="editEmployee"
    @delete="deleteEmployee"
    @block="blockEmployee"
    @unblock="unblockEmployee"
  >
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
