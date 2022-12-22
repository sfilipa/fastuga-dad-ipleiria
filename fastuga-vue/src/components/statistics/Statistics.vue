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
const orders = ref(null)

//bar charts - managers
const topProducts = ref([])
const topProductsTotal = ref(null)
const worstProducts = ref([])
const worstProductsTotal = ref(null)
const months = ref([])
const ordersByMonth = ref(null)
const gainedByMonth = ref(null)

//Delivery
const ordersDelivered = ref(null)
const showingBarStatistic = ref(false)
const totalOrdersDelivered = ref(null)

//Chef
const totalDishes = ref(null)
const dishesName = ref([])

//filters
const filterByPaymentType = ref("A")
const filterByDate = ref("")


const lastPage = ref(20)
const currentPage = ref(1)

//Statistic - customer
const LoadOrders = (pageNumber) => {
  currentPage.value = pageNumber
  let URL = `/orders/customer/${userStore.user.id}?page=${pageNumber}`;
  if (filterByPaymentType.value != "A") {
    URL += `&type=${filterByPaymentType.value}`
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


//Statistics - Delivery

const LoadOrdersDelivered = (pageNumber) => {
  currentPage.value = pageNumber
  let URL = `/orders/delivered/${userStore.user.id}?page=${pageNumber}`;

  if (filterByPaymentType.value != "A") {
    URL += `&type=${filterByPaymentType.value}`
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


async function LoadTotalOrdersDelivered() {
  try {
    const response = await axiosLaravel.get(`/orders/${userStore.userId}/totaldelivered/bymonth`)
    totalOrdersDelivered.value = Object.values(response.data)
    months.value = Object.keys(response.data)

  } catch (error) {

    console.log(error)
    throw error
  }
}

//Statistics - Chef

async function LoadOrdersPrepared() {
  try {
    const response = await axiosLaravel.get(`/order-items/prepared/${userStore.userId}`)
    console.log(Object.values(response.data))
    totalDishes.value = Object.keys(response.data)
    dishesName.value = Object.values(response.data)

  } catch (error) {

    console.log(error)
    throw error
  }
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

const chartTotalPreparedDishes = reactive({
  doughnutConfig: null,
  barConfig: null
})

const chartTotalOrdersDeliveredByMonth = reactive({
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
          backgroundColor: '#ffa71dd6'
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
          backgroundColor: '#ffa71dd6',
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
          backgroundColor: '#ffa71dd6',
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
          data: gainedByMonth.value,
          backgroundColor: '#ffa71dd6',
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
    LoadOrdersDelivered();
    await LoadTotalOrdersDelivered();
    chartTotalOrdersDeliveredByMonth.barConfig = {
      data: {
        labels: months.value,
        datasets: [{
          label: 'Total Orders Delivered',
          data: totalOrdersDelivered.value,
          backgroundColor: '#ffa71dd6',
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
  } else {
    await LoadOrdersPrepared()
    chartTotalPreparedDishes.barConfig = {
      data: {
        labels: dishesName.value,
        datasets: [{
          label: 'Total Dishes Prepared',
          data: totalDishes.value,
          backgroundColor: '#ffa71dd6',
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
    <div>
      <h5 class="center">Best-selling Products</h5>
      <div v-if="topProducts == null">
        <div class="d-flex justify-content-center spinner-font">
          <div class="spinner-border" role="status">
            <span class="sr-only"></span>
          </div>
        </div>
      </div>
      <div v-else-if="topProducts.length === 0">
        <p style="text-align: center"><b>No statistics to show!</b></p>
      </div>
      <div v-else>
        <bar-chart v-if="chartTopProducts.barConfig"
                   :chart-options="chartTopProducts.barConfig.options"
                   :chart-data="chartTopProducts.barConfig.data" :height="345"/>
      </div>
    </div>
    <br>
    <br>

    <div>
      <h5 class="center">Least Sold Products</h5>
      <div v-if="worstProducts == null">
        <div class="d-flex justify-content-center spinner-font">
          <div class="spinner-border" role="status">
            <span class="sr-only"></span>
          </div>
        </div>
      </div>
      <div v-else-if="worstProducts.length === 0">
        <p style="text-align: center"><b>No statistics to show!</b></p>
      </div>
      <div v-else>
        <bar-chart v-if="chartWorstProducts.barConfig"
                   :chart-options="chartWorstProducts.barConfig.options"
                   :chart-data="chartWorstProducts.barConfig.data" :height="300"/>
      </div>
    </div>
    <br>
    <br>
    <div>
      <h5 class="center">Total Orders By Month</h5>
      <div v-if="ordersByMonth == null">
        <div class="d-flex justify-content-center spinner-font">
          <div class="spinner-border" role="status">
            <span class="sr-only"></span>
          </div>
        </div>
      </div>
      <div v-else-if="ordersByMonth.length === 0">
        <p style="text-align: center"><b>No statistics to show!</b></p>
      </div>
      <div v-else>
        <bar-chart v-if="chartOrdersByMonth.barConfig"
                   :chart-options="chartOrdersByMonth.barConfig.options"
                   :chart-data="chartOrdersByMonth.barConfig.data" :height="275"/>
      </div>
    </div>
    <br>
    <br>
    <div>
      <h5 class="center">Total Gained By Month (€)</h5>
      <div v-if="gainedByMonth == null">
        <div class="d-flex justify-content-center spinner-font">
          <div class="spinner-border" role="status">
            <span class="sr-only"></span>
          </div>
        </div>
      </div>
      <div v-else-if="gainedByMonth.length === 0">
        <p style="text-align: center"><b>No statistics to show!</b></p>
      </div>
      <div v-else>
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
        <select class="form-select"
                id="selectType"
                v-model="filterByPaymentType"
                @change="LoadOrders(1)">
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
          <input
              v-model="filterByDate"
              @change="LoadOrders(1)"
              type="date"
              name="date"
              class="form-control"/>
        </div>
      </div>
    </div>
    <div v-if="orders == null">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div v-else-if="orders.length === 0">
      <p style="text-align: center"><b>No orders to show!</b></p>
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
        <select class="form-select"
                id="selectType"
                v-model="filterByPaymentType"
                @change="LoadOrdersDelivered(1)">
          <option value="A">Any</option>
          <option value="VISA">VISA</option>
          <option value="PAYPAL">PAYPAL</option>
          <option value="MBWAY">MBWAY</option>
        </select>
      </div>

      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectType" class="form-label">Filter by Date:</label>
        <i class="glyphicon glyphicon-user"></i>
        <input v-model="filterByDate"
               @change="LoadOrdersDelivered(1)"
               type="date" name="date" class="form-control"/>
      </div>
      <button
          type="button"
          class="btn btn-success hvr-grow make-order"
          @click="showingBarStatistic = !showingBarStatistic"
      >
        <i class="bi bi-check2-circle menu-bi"></i>{{ showingBarStatistic ? 'Show History' : 'Show Graph Bar' }}
      </button>
    </div>


    <div v-if="ordersDelivered == null">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div v-else-if="ordersDelivered.length === 0">
      <p style="text-align: center"><b>No orders delivered to show!</b></p>
    </div>
    <div v-else>
      <div v-if="showingBarStatistic">

        <div>
          <div v-if="totalOrdersDelivered.length === 0">
            <p style="text-align: center"><b>No statistics to show!</b></p>
          </div>
          <div v-else>
            <h5 class="center">Total Orders Delivered</h5>
            <bar-chart v-if="chartTotalOrdersDeliveredByMonth.barConfig"
                       :chart-options="chartTotalOrdersDeliveredByMonth.barConfig.options"
                       :chart-data="chartTotalOrdersDeliveredByMonth.barConfig.data" :height="275"/>
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
            :click-handler="LoadOrdersDelivered"
        >
        </paginate>
      </div>
    </div>
  </div>
  <div v-if="userStore.user.type == 'EC'">
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">Prepared Dishes</h3>
      </div>
    </div>
    <hr/>
    <div v-if="totalDishes == null">
      <div class="d-flex justify-content-center spinner-font">
        <div class="spinner-border" role="status">
          <span class="sr-only"></span>
        </div>
      </div>
    </div>
    <div v-else-if="totalDishes.length === 0">
      <p style="text-align: center"><b>No statistics to show!</b></p>
    </div>
    <div v-else>
      <h5 class="center">Total Dishes Prepared</h5>
      <bar-chart v-if="chartTotalPreparedDishes.barConfig"
                 :chart-options="chartTotalPreparedDishes.barConfig.options"
                 :chart-data="chartTotalPreparedDishes.barConfig.data" :height="275"/>
    </div>
  </div>
</template>

<style scoped>
.menu-bi {
  font-size: 1rem;
}

.make-order:hover,
.btn:first-child:active {
  background-color: #ff8300;
}


.make-order {
  height: fit-content;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
}

.filter-div {
  min-width: 12rem;
}

.center {
  display: block;
  margin-right: auto;
  margin-left: auto;
}

.hvr-grow {
  margin-top: 1.90rem;
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

.hvr-grow:hover,
.hvr-grow:focus,
.hvr-grow:active {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}
</style>