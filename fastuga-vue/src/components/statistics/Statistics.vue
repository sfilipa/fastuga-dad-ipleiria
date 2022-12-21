<script setup>
import {ref, computed, onMounted, inject, watch, reactive, toRef} from "vue";
import HistoryTable from './HistoryTable.vue'
import BarChart from './BarChart.vue'
import HistoryTableOrdersDelivered from './HistoryTableOrdersDelivered.vue'
import HistoryTableDishPrepared from './HistoryTableDishPrepared.vue'
import Paginate from "vuejs-paginate-next"

import {useUserStore} from "../../stores/user.js"

const axiosLaravel = inject('axios')

let laravelData = ref()

//Customers
const orders = ref([])

//bar charts - managers
const topProducts = ref([])
const topProductsTotal = ref([])
const worstProducts = ref([])
const worstProductsTotal = ref([])
const months = ref([])
const ordersByMonth = ref([])
const gainedByMonth = ref([])

//Driver
const ordersDelivered = ref([])

//Chef
const dishPrepared = ref([])

//filters
const filterByPaymentType = ref("A")
const filterByDate = ref("")
const filterByName = ref("")


const lastPage = ref(20)
const currentPage = ref(1)

//Statistic - customer
const LoadOrders = (pageNumber) => {
  currentPage.value = pageNumber
  let URL = `/orders/customer/${userStore.user.id}?page=${pageNumber}`;
  if (filterByPaymentType.value != "A") {
    URL += `&type=${filterByDate.value}`
  }
  if (filterByDate.value != "") {
    URL += `&date=${filterByDate.value}`
  }

  axiosLaravel
      .get(URL)
      .then((response) => {
        lastPage.value = response.data.last_page
        orders.value = response.data.data
      })
      .catch((error) => {
        console.log(error)
      });
};


//Statistic - Manager

async function loadTopProducts() {
  try {
    const response = await axiosLaravel.get(`/products/top`)
    topProducts.value = Object.values(response.data)
    topProductsTotal.value = Object.keys(response.data)

  } catch (error) {

    console.log(error)
    throw error
  }
}

async function loadWorstProducts() {
  try {
    const response = await axiosLaravel.get(`/products/worst`)
    worstProducts.value = Object.values(response.data)
    worstProductsTotal.value = Object.keys(response.data)

  } catch (error) {

    console.log(error)
    throw error
  }
}


async function loadOrdersByMonth() {
  try {
    const response = await axiosLaravel.get(`/orders/totalOrders/bymonth`)
    ordersByMonth.value = Object.values(response.data)
    months.value = Object.keys(response.data)

  } catch (error) {

    console.log(error)
    throw error
  }
}

async function loadTotalGainedByMonth() {
  try {
    const response = await axiosLaravel.get(`/orders/totalGained/bymonth`)
    gainedByMonth.value = Object.values(response.data)
    months.value = Object.keys(response.data)

  } catch (error) {

    console.log(error)
    throw error
  }
}


//Statistics - Driver

const LoadOrdersDriverDelivered = (pageNumber) => {
  currentPage.value = pageNumber
  let URL = `/orders/delivered/${userStore.user.id}?page=${pageNumber}`;

  if (filterByPaymentType.value != "A") {
    URL += `&type=${filterByDate.value}`
  }
  if (filterByDate.value != "") {
    URL += `&date=${filterByDate.value}`
  }

  axiosLaravel
      .get(URL)
      .then((response) => {
        lastPage.value = response.data.last_page
        ordersDelivered.value = response.data.data
      })
      .catch((error) => {
        console.log(error)
      });
};

//Statistics - Chef

const LoadOrdersPrepared = (pageNumber) => {
  currentPage.value = pageNumber
  let URL = `/order-items/prepared/${userStore.user.id}?page=${pageNumber}`;

  if (filterByDate.value != "") {
    URL += `&date=${filterByDate.value}`
  }
  if (filterByName.value != "") {
    URL += `&name=${filterByName.value}`
  }

  axiosLaravel
      .get(URL)
      .then((response) => {
console.log(response)
        lastPage.value = response.data.last_page
        dishPrepared.value = response.data.data;
      })
      .catch((error) => {
        console.log(error)
      });
};


const data = reactive({
  checkins: null
})

const chartTopProducts = reactive({
  doughnutConfig: null,
  barConfig: null
})

const chartWorstProducts = reactive({
  doughnutConfig: null,
  barConfig: null
})

const chartOrdersByMonth = reactive({
  doughnutConfig: null,
  barConfig: null
})

const chartTotalGainedByMonth = reactive({
  doughnutConfig: null,
  barConfig: null
})

const userStore = useUserStore()

onMounted(async () => {
  if (userStore.user.type == 'EM') {
    await loadTopProducts()

    await loadWorstProducts()

    await loadOrdersByMonth()

    await loadTotalGainedByMonth()

    chartTopProducts.barConfig = {
      data: {
        labels: topProducts.value,
        datasets: [{
          label: 'Total Orders',
          data: topProductsTotal.value,
          backgroundColor: '#27995a'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true
          }
        }
      }
    }

    chartWorstProducts.barConfig = {
      data: {
        labels: worstProducts.value,
        datasets: [{
          label: 'Total Orders',
          data: worstProductsTotal.value,
          backgroundColor: '#27995a',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true
          }
        }
      }
    }

    chartOrdersByMonth.barConfig = {
      data: {
        labels: months.value,
        datasets: [{
          label: 'Total Orders',
          data: ordersByMonth.value,
          backgroundColor: '#27995a',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true
          }
        }
      }
    }
    chartTotalGainedByMonth.barConfig = {
      data: {
        labels: months.value,
        datasets: [{
          label: 'Total Gained',
          data: gainedByMonth.value ,
          backgroundColor: '#27995a',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true
          }
        }
      }
    }
  } else if (userStore.user.type == 'C') {
    LoadOrders();
  } else if (userStore.user.type == 'ED') {
    LoadOrdersDriverDelivered();
  } else {
    LoadOrdersPrepared(currentPage.value);
  }
})
</script>
<template>
  <div v-if="userStore.user.type == 'EM'">
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">Statistics</h3>
      </div>
    </div>
    <hr/>

    <div v-if="topProductsTotal.length == 0">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div v-else>
      <div>
        <h5 class="center">Best-selling Products</h5>
        <bar-chart v-if="chartTopProducts.barConfig"
                                :chart-options="chartTopProducts.barConfig.options"
                                :chart-data="chartTopProducts.barConfig.data" :height="345"/>

      </div>
      <br>
      <br>
      <div>
        <h5 class="center">Least Sold Products</h5>
        <bar-chart v-if="chartWorstProducts.barConfig"
                                  :chart-options="chartWorstProducts.barConfig.options"
                                  :chart-data="chartWorstProducts.barConfig.data" :height="300"/>
      </div>
      <br>
      <br>
      <div>
        <h5 class="center">Total Orders By Month</h5>
        <bar-chart v-if="chartOrdersByMonth.barConfig"
                                   :chart-options="chartOrdersByMonth.barConfig.options"
                                   :chart-data="chartOrdersByMonth.barConfig.data" :height="275"/>
      </div>
      <br>
      <br>
      <div>
        <h5 class="center">Total Gained By Month (€)</h5>
        <bar-chart v-if="chartTotalGainedByMonth.barConfig"
                                   :chart-options="chartTotalGainedByMonth.barConfig.options"
                                   :chart-data="chartTotalGainedByMonth.barConfig.data" :height="275"/>
      </div>
    </div>
  </div>
  <div v-if="userStore.user.type == 'C'">
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">Orders History</h3>
      </div>
    </div>
    <hr/>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectType" class="form-label">Filter by Payment Type:</label>
        <select class="form-select" id="selectType" v-model="filterByPaymentType">
          <option value="A">Any</option>
          <option value="VISA">VISA</option>
          <option value="PAYPAL">PAYPAL</option>
          <option value="MBWAY">MBWAY</option>
        </select>
      </div>

      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <div class="inner-addon left-addon">
          <label for="searchbar" class="form-label">Filter by Payment Date:</label>
          <i class="glyphicon glyphicon-user"></i>
          <input v-model="filterByDate" type="date" name="date" class="form-control"/>
        </div>
      </div>
    </div>
    <div v-if="orders.length == 0">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div v-else>
      <history-table :orders="orders" :filterByPaymentType="filterByPaymentType" :date="filterByDate">
      </history-table>
      <paginate
          :page-count="lastPage"
          :prev-text="'Previous'"
          :next-text="'Next'"
          :click-handler="LoadOrders"
      >
      </paginate>
    </div>
  </div>
  <div v-if="userStore.user.type == 'ED'">
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">Orders Delivered History</h3>
      </div>
    </div>
    <hr/>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectType" class="form-label">Filter by Payment Type:</label>
        <select class="form-select" id="selectType" v-model="filterByPaymentType">
          <option value="A">Any</option>
          <option value="VISA">VISA</option>
          <option value="PAYPAL">PAYPAL</option>
          <option value="MBWAY">MBWAY</option>
        </select>
      </div>

      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <div class="inner-addon left-addon">
          <label for="searchbar" class="form-label">Filter by Payment Date:</label>
          <i class="glyphicon glyphicon-user"></i>
          <input v-model="filterByDate" type="date" name="date" class="form-control"/>
        </div>
      </div>
    </div>

    <div v-if="ordersDelivered.length == 0">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div v-else>
      <history-table-orders-delivered :ordersDelivered="ordersDelivered" :filterByPaymentType="filterByPaymentType"
                                      :date="filterByDate">
      </history-table-orders-delivered>

      <paginate
          :page-count="lastPage"
          :prev-text="'Previous'"
          :next-text="'Next'"
          :click-handler="LoadOrdersDriverDelivered"
      >
      </paginate>
    </div>
  </div>
  <div v-if="userStore.user.type == 'EC'">
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">Prepared Dishes History</h3>
      </div>
    </div>
    <hr/>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <div class="inner-addon left-addon">
          <label for="searchbar" class="form-label">Search for Name:</label>
          <i class="glyphicon glyphicon-user"></i>
          <input v-model="filterByName" type="text" name="name" class="form-control"/>
        </div>
      </div>

      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <div class="inner-addon left-addon">
          <label for="searchbar" class="form-label">Filter by Date:</label>
          <i class="glyphicon glyphicon-user"></i>
          <input v-model="filterByDate" type="date" name="date" class="form-control"/>
        </div>
      </div>
    </div>
    <div v-if="dishPrepared.length == 0">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div v-else>
      <history-table-dish-prepared :dishPrepared="dishPrepared" :filterByName="filterByName" :date="filterByDate">
      </history-table-dish-prepared>

      <paginate
          :page-count="lastPage"
          :prev-text="'Previous'"
          :next-text="'Next'"
          :click-handler="LoadOrdersPrepared"
      >
      </paginate>
    </div>
  </div>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}

.center {
  display: block;
  margin-right: auto;
  margin-left: auto;
}

</style>