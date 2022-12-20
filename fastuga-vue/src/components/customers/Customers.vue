<script setup>
import { ref, computed, onMounted, inject, watch, toRaw } from "vue";
import { useRouter } from "vue-router";
import CustomersTable from "./CustomersTable.vue";

const axios = inject("axios");
const router = useRouter();

let customers = ref({});
const searchByEmail = ref(null);
const searchByNif = ref(null);

const lastPage = ref(15)
const currentPage = ref(1)
const load = (pageNumber) => {
  currentPage.value = pageNumber
  let URL = "/customers?page=" + pageNumber;
  axios
    .get(URL)
    .then((response) => {
      customers.value = response.data;
      lastPage.value = Object.keys(response.data).length
    })
    .catch((error) => {
      console.log(error);
    });
};

const show = (customer) => {
  Object.assign({}, customer);
};

const block = async (customer) => {
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
        blocked: 1,
        custom: obj.custom,
      },
    });

  } catch (err) {
    if (err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  load();
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
  } catch (err) {
    if (err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  load();
};

const search = () => {
  if (searchByEmail.value != null) {
    axios
      .get(`/customers/byEmail/${searchByEmail.value}`)
      .then((response) => {
        customers.value = response;
      })
      .catch((error) => {
        console.log(error);
      });
  } else if (searchByNif.value != null) {
    axios
      .get(`/customers/byNif/${searchByNif.value}`)
      .then((response) => {
        customers.value = response;
      })
      .catch((error) => {
        console.log(error);
      });
  }
};

const deleteFromDatabase = async (customer) => {
  console.log("Delete");
  const obj = Object.assign({}, customer);
  try {
    const { data } = await axios({
      method: "delete",
      url: `/customers/${obj.id}`,
    });
    console.log(data);
  } catch (err) {
    if (err.response.status === 404) {
      console.log("Resource could not be found!");
    } else {
      console.log(err.message);
    }
  }
  load();
};

const saveToDatabase = () => {
  router.push({ name: "Register" });
};

function clear() {
  searchByEmail.value = null;
  searchByNif.value = null;
}

onMounted(() => {
  load();
});
</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">Customers</h3>
    </div>
    <div class="mx-2">
      <button
        type="button"
        class="btn btn-primary px-4 btn-addtask"
        @click="saveToDatabase"
      >
        <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Customer
      </button>
    </div>
  </div>
  <hr />
  <div class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <div class="inner-addon left-addon">
        <label for="searchbar" class="form-label">Search by Email:</label>
        <i class="glyphicon glyphicon-user"></i>
        <input
          v-model="searchByEmail"
          :disabled="searchByNif"
          type="search"
          class="form-control rounded"
          placeholder="Email"
          aria-label="Search"
          aria-describedby="search-addon"
        />
      </div>
    </div>

    <div class="mx-1 mt-5">
      <div class="inner-addon left-addon">
        <label for="searchbar" class="form-label">or</label>
      </div>
    </div>

    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <div class="inner-addon left-addon">
        <label for="searchbar" class="form-label">Search by NIF:</label>
        <i class="glyphicon glyphicon-user"></i>
        <input
          v-model="searchByNif"
          :disabled="searchByEmail"
          type="number"
          class="form-control rounded"
          placeholder="NIF"
          aria-label="Search"
          aria-describedby="search-addon"
        />
      </div>
    </div>
    <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-primary px-4 btn-addtask"
        @click="search"
      >
        <i class="bi bi-xs bi-search-heart-fill"></i>&nbsp; Search
      </button>
    </div>
    <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-secondary px-4 btn-addtask"
        @click="clear"
      >
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
  <paginate
    :page-count="lastPage"
    :prev-text="'Previous'"
    :next-text="'Next'"
    :click-handler="load"
  >
  </paginate>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}

.btn-addtask {
  margin-top: 1.85rem;
}
</style>
