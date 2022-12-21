<script setup>
import { ref, computed, onMounted, inject, watch } from "vue";
import { useRouter } from "vue-router";
import EmployeesTable from "./EmployeesTable.vue";
import Paginate from "vuejs-paginate-next";
import { useUserStore } from "../../stores/user.js";

const axios = inject("axios");
const router = useRouter();
const socket = inject("socket");
const toast = inject("toast");


const lastPage = ref(15);
const currentPage = ref(1);


const userStore = useUserStore();
const employees = ref([]);
const filterByType = ref("A");
const filterByName = ref("");

const LoadEmployees = (pageNumber) => {

  currentPage.value = pageNumber;
  let URL = "/users/employees?page=" + pageNumber;

  if (filterByName.value.length != 0) {
    URL += `&name=${filterByName.value}`;
  }
  if (filterByType.value.length != 0 && filterByType.value != 'A') {
    URL += `&type=${filterByType.value}`;
  }

  axios
    .get(URL)
    .then((response) => {
      lastPage.value = response.data.last_page;
      employees.value = response.data.data;
    })
    .catch((error) => {
      console.log(error);
    });
};

const blockEmployee = async (employee) => {
  const employeeObj = Object.assign({}, employee);
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
    if (err.response != null && err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  LoadEmployees(1);
};

const unblockEmployee = async (employee) => {
  const employeeObj = Object.assign({}, employee);
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
    if (err.response != null && err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  LoadEmployees(1);
};

const deleteEmployee = async (employee) => {
  const obj = Object.assign({}, employee);
  try {
    const { data } = await axios({
      method: "delete",
      url: `/users/${obj.id}`,
    });
    
    const users = new Object();
    users.user = obj;
    users.manager = userStore.user.name;
    socket.emit("userDeleted", users);
    toast.warning(`You have deleted ${obj.name}!`);  
  } catch (err) {
    if (err.response != null && err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  LoadEmployees(1);
};

const clear = ()=>{
  filterByType.value = "A";
  filterByName.value = "";
  LoadEmployees(1);
}

// User Deleted
socket.on("update", () => {
  LoadEmployees(1);
});

const addEmployee = () => {
  router.push({ name: "AddEmployee" });
};

onMounted(() => {
  LoadEmployees(1);
});
</script>

<template>
  <div class="d-flex justify-content-between employees-header fastuga-font">
    <div class="mx-2">
      <h3 class="mt-4">Employees</h3>
    </div>
    <div class="mx-2 mt-2 ">
      <button
        type="button"
        class="btn btn-primary px-4 employees-add-button"
        @click="addEmployee"
      >
        <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Employee
      </button>
    </div>
  </div>

  <hr />
  <div class="mb-3 d-flex justify-content-between flex-wrap search-bar fastuga-font">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectType" class="form-label"
        >Filter by Type:</label
      >
      <select class="form-select" id="selectType" v-model.lazy="filterByType" @change="LoadEmployees(1)">
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
          v-model.lazy="filterByName"
          type="search"
          class="form-control rounded"
          placeholder="Search by Name"
          aria-label="Search"
          aria-describedby="search-addon"
          @change="LoadEmployees(1)"
        />
      </div>
    </div>

    <div class="mx-2 mt-2" style="align-self: flex-end;">
      <button type="button" class="btn px-4 btn-search" @click="LoadEmployees(1)">
        <i class="bi bi-xs bi-search"></i> Search
      </button>
    </div>
    <div class="mx-2 mt-2" style="align-self: flex-end;">
      <button type="button" class="btn px-4 btn-clear" @click="clear">
        Clear
      </button>
    </div>
  </div>
  <employees-table
    :employees="employees"
    :filterByType="filterByType"
    :filterByName="filterByName"
    @delete="deleteEmployee"
    @block="blockEmployee"
    @unblock="unblockEmployee"
  >
  </employees-table>
  <div v-if="employees.length != 0 && lastPage > 1" style="display: flex">
    <paginate
      :page-count="lastPage"
      :prev-text="'Previous'"
      :next-text="'Next'"
      :click-handler="LoadEmployees"
      class="pagination"
    >
    </paginate>
  </div>
</template>

<style scoped>

.btn-clear:hover,
.btn-clear:active {
  background-color: #4d3838;
  border-color: #4d3838;
  color: white;
}

.btn-clear {
  background-color: #5e4444;
  border-color: #5e4444;
  color: white;
}

.employees-add-button:hover,
.employees-add-button:active {
  background-color: #ff8300;
  color: white !important;
}

.employees-add-button {
  display: block;
  margin-left: auto;
  height: 3rem;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.btn-search:hover,
.btn-search:active {
  background-color: #ff8300;
  color: white !important;
}

.btn-search {
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.employees-header {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.search-bar {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.filter-div {
  min-width: 12rem;
}

.btn-addtask {
  margin-top: 1.85rem;
}
</style>
