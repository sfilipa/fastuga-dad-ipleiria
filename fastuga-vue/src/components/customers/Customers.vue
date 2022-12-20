<script setup>
import { ref, computed, onMounted, inject, watch, toRaw } from "vue";
import { useRouter } from "vue-router";
import CustomersTable from "./CustomersTable.vue";
import Paginate from "vuejs-paginate-next";
import { useUserStore } from "../../stores/user.js";

const axios = inject("axios");
const toast = inject("toast");
const router = useRouter();
const socket = inject("socket");

const userStore = useUserStore();
let customers = ref({});
const searchByEmail = ref(null);
const searchByNif = ref(null);

const lastPage = ref(15);
const currentPage = ref(1);

const filterByName = ref("");
const filterByEmail = ref("");
const filterByNif = ref("");

const loadUsers = (pageNumber) => {
  currentPage.value = pageNumber;
  let URL = "/customers?page=" + pageNumber;

  if (filterByName.value.length != 0) {
    console.log(filterByName.value);
    URL += `&name=${filterByName.value}`;
    console.log(URL);
  }
  if (filterByEmail.value.length != 0) {
    URL += `&email=${filterByEmail.value}`;
  }
  if (filterByNif.value.length != 0) {
    URL += `&nif=${filterByNif.value}`;
  }

  axios
    .get(URL)
    .then((response) => {
      console.log(response);
      customers.value = response.data.data;
      lastPage.value = response.data.last_page;
    })
    .catch((error) => {
      console.log(error);
    });
};

const show = (customer) => {
  Object.assign({}, customer);
};

const block = async (customer) => {
  console.log(customer);
  const obj = Object.assign({}, customer);
  console.log(obj);
  try {
    const { data } = await axios({
      method: "put",
      url: `/users/blockUnblock/${obj.user_id}`,
      data: {
        name: obj.name,
        email: obj.email,
        type: obj.type,
        photo_url: obj.photo_url,
        blocked: 1,
        custom: obj.custom,
      },
    });

	console.log(data)

    const users = new Object();
    users.user = obj.user_id;
    users.manager = userStore.user.name;
    socket.emit("userBlocked", users);
    toast.warning(`You have blocked ${obj.name}!`);
  } catch (err) {
    if (err.response != null && err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  loadUsers(1);
};

const unblock = async (customer) => {
  const obj = Object.assign({}, customer);
  try {
    const { data } = await axios({
      method: "put",
      url: `/users/blockUnblock/${obj.id}`,
      data: {
        name: obj.name,
        email: obj.email,
        type: obj.type,
        photo_url: obj.url,
        blocked: 0,
        custom: obj.custom,
      },
    });

    const users = new Object();
    users.user = obj;
    users.manager = userStore.user.name;
    socket.emit("userUnblocked", users);
    toast.warning(`You have unblocked ${obj.name}!`);
  } catch (err) {
	if (err.response != null && err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  loadUsers(1);
};

const deleteFromDatabase = async (customer) => {
  console.log("Delete");
  const obj = Object.assign({}, customer);
  try {
    const { data } = await axios({
      method: "delete",
      url: `/customers/${obj.id}`,
    });

    const users = new Object();
    users.user = obj;
    users.manager = userStore.user.name;
    socket.emit("userDeleted", users);
    toast.warning(`You have deleted ${obj.name}!`);
  } catch (err) {
    if (err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  loadUsers(1);
};

function clear() {
  filterByName.value = "";
  filterByEmail.value = "";
  filterByNif.value = "";
  loadUsers(1);
}

onMounted(() => {
  loadUsers(1);
});

// User Deleted
socket.on("update", () => {
  loadUsers(1);
});
</script>

<template>
  <div class="d-flex justify-content-between customers-header fastuga-font">
    <div class="mx-2">
      <h3 class="mt-4">Customers</h3>
    </div>
  </div>
  <hr />
  <div
    class="mb-3 d-flex justify-content-between flex-wrap search-bar fastuga-font"
  >
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <div class="inner-addon left-addon">
        <input
          v-model="filterByName"
          type="search"
          class="form-control rounded"
          placeholder="Search by Name"
          aria-label="Search"
          aria-describedby="search-addon"
          @change="loadUsers(1)"
        />
      </div>
    </div>

    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <div class="inner-addon left-addon">
        <input
          v-model="filterByEmail"
          type="search"
          class="form-control rounded"
          placeholder="Search by Email"
          aria-label="Search"
          aria-describedby="search-addon"
          @change="loadUsers(1)"
        />
      </div>
    </div>

    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <div class="inner-addon left-addon">
        <input
          v-model="filterByNif"
          type="search"
          class="form-control rounded"
          placeholder="Search by NIF"
          aria-label="Search"
          aria-describedby="search-addon"
          @change="loadUsers(1)"
        />
      </div>
    </div>
    <div class="mx-2 mt-2">
      <button type="button" class="btn px-4 btn-search" @click="loadUsers(1)">
        <i class="bi bi-xs bi-search"></i> Search
      </button>
    </div>
    <div class="mx-2 mt-2">
      <button type="button" class="btn px-4 btn-clear" @click="clear">
        Clear
      </button>
    </div>
  </div>
  <CustomersTable
    :customers="customers"
    @show="show"
    @delete="deleteFromDatabase"
    @block="block"
    @unblock="unblock"
  >
  </CustomersTable>
  <div v-if="customers.length != 0 && lastPage > 1" style="display: flex">
    <paginate
      :page-count="lastPage"
      :prev-text="'Previous'"
      :next-text="'Next'"
      :click-handler="loadUsers"
      class="pagination"
    >
    </paginate>
  </div>
</template>

<style>
.pagination .page-item.active a:hover,
.pagination .page-item.active a:active {
  background-color: #ff8300 !important;
  color: white !important;
  cursor: pointer;
}

.pagination .page-item.active a {
  background-color: #ffa71dd6 !important;
  border-color: #ffa71dd6 !important;
  color: white !important;
  cursor: pointer !important;
}

.pagination .page-item a.page-link:focus {
  box-shadow: 2px 2px #ffd07b !important;
}

.pagination .page-item a:hover {
  color: #ff8300 !important;
  cursor: pointer;
}

.pagination .page-item a {
  color: #ffa71dd6 !important;
  cursor: pointer;
}

.pagination {
  margin: auto;
}
</style>

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

.customers-add-button:hover,
.customers-add-button:active {
  background-color: #ff8300;
  color: white !important;
}

.customers-add-button {
  display: block;
  margin-left: auto;
  height: 3rem;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.customers-add-button-div {
  display: block;
  margin-left: auto;
  margin-top: 1%;
}

.customers-header {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.filter-div {
  min-width: 12rem;
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

.search-bar {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.pagination {
  display: inline-flex;
}
</style>
