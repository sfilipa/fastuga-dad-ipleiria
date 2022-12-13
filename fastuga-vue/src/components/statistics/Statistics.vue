<script setup>
import axios from 'axios'
import { ref, computed, onMounted, inject, watch, reactive, toRef } from "vue";
import BarChartTopProducts from './BarChartTopProducts.vue'
import BarChartWorstProducts from './BarChartWorstProducts.vue'
import HistoryTable from './HistoryTable.vue'
import OrderItemsHistoryTable from './OrderItemsHistoryTable.vue'
import BarChartOrdersByMonth from './BarChartOrdersByMonth.vue'

import { useUserStore } from "../../stores/user.js"

//Customers
const orders = ref([]);
const orderItems = ref([null]);

//bar charts - managers
const topProducts = ref([]);
const topProductsTotal = ref([]);
const worstProducts = ref([]);
const worstProductsTotal = ref([]);
const ordersByMonth = ref([]);
const ordersByMonthTotal = ref([]);

//filters
const filterByPaymentType = ref("A");
const filterByDate = ref(undefined);
let isActive = ref(false);

const LoadOrders = () => {
    axios
        .get(`http://localhost:8081/api/orders/customer/${userStore.user.id}`)
        .then((response) => {
            orders.value = response.data;
        })
        .catch((error) => {
            console.log(error);
        });
};

async function loadTopProducts() {
    try {
        const response = await axios.get(`http://localhost:8081/api/products/top`)
        topProducts.value = response.data;

       // console.log(topProducts.value[0]);
    } catch (error) {

        console.log(error);
        throw error
    }
}

async function loadTopProductsTotal() {
    try {
        const response = await axios.get(`http://localhost:8081/api/products/top/total`)
        topProductsTotal.value = response.data;

        //console.log(topProductsTotal.value[0]);
    } catch (error) {

        console.log(error);
        throw error
    }
}

async function loadWorstProducts() {
    try {
        const response = await axios.get(`http://localhost:8081/api/products/worst`)
        worstProducts.value = response.data;

       // console.log(worstProducts.value[0]);
    } catch (error) {

        console.log(error);
        throw error
    }
}

async function loadWorstProductsTotal() {
    try {
        const response = await axios.get(`http://localhost:8081/api/products/worst/total`)
        worstProductsTotal.value = response.data;

       // console.log(worstProductsTotal.value[0]);
    } catch (error) {

        console.log(error);
        throw error
    }
}

async function loadOrdersByMonth() {
    try {
        const response = await axios.get(`http://localhost:8081/api/orders/bymonth`)
        ordersByMonth.value = response.data;

        console.log(ordersByMonth.value);
    } catch (error) {

        console.log(error);
        throw error
    }
}

async function loadOrdersByMonthTotal() {
    try {
        const response = await axios.get(`http://localhost:8081/api/orders/bymonth/total`)
        ordersByMonthTotal.value = response.data;

        console.log(ordersByMonthTotal.value);
    } catch (error) {

        console.log(error);
        throw error
    }
}

const showOrder = (order) => {
    axios
        .get(`http://localhost:8081/api/orders/order/orderItems/${order.id}`)
        .then((response) => {
            orderItems.value = response.data;

            isActive = true;

            console.log(isActive);
        })
        .catch((error) => {
            console.log(error);
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

const userStore = useUserStore()

onMounted(async () => {
    if (userStore.user.type == 'EM') {
        await loadTopProducts();
        await loadTopProductsTotal();
        
        await loadWorstProducts();
        await loadWorstProductsTotal();
        
        await loadOrdersByMonth();
        await loadOrdersByMonthTotal();

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
                labels: ordersByMonth.value,
                datasets: [{
                    label: 'Total Orders',
                    data: ordersByMonthTotal.value,
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
    } else {
        LoadOrders();
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
        <hr />
        <div class="flex-container">
            <div>
                <h5 class="center">Top Products</h5>
                <bar-chart-top-products v-if="chartTopProducts.barConfig"
                    :chart-options="chartTopProducts.barConfig.options" :chart-data="chartTopProducts.barConfig.data"
                    :width="400" :height="345" />

            </div>
            <div>
                <h5 class="center">Worst Products</h5>
                <bar-chart-worst-products v-if="chartWorstProducts.barConfig"
                    :chart-options="chartWorstProducts.barConfig.options" :chart-data="chartWorstProducts.barConfig.data"
                    :width="400" :height="300" />
            </div>
            <div>
                <h5 class="center">Orders By Month</h5>
                <bar-chart-orders-by-month v-if="chartOrdersByMonth.barConfig"
                    :chart-options="chartOrdersByMonth.barConfig.options" :chart-data="chartOrdersByMonth.barConfig.data"
                    :width="400" :height="300" />
            </div>
        </div>
    </div>
    <div v-if="userStore.user.type == 'C'">
        <div v-if="!isActive">
            <div class="d-flex justify-content-between">
                <div class="mx-2">
                    <h3 class="mt-4">Orders History</h3>
                </div>
            </div>
            <hr />
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
                        <input v-model="filterByDate" type="date" name="date" class="form-control" />
                    </div>
                </div>
            </div>
            <history-table :orders="orders" :filterByPaymentType="filterByPaymentType" :date="filterByDate"
                @show="showOrder">
            </history-table>
        </div>
        <div v-else>
            <div class="d-flex justify-content-between">
                <div class="mx-2">
                    <h3 class="mt-4">Order Items</h3>
                </div>
            </div>
            <hr />
            <order-items-history-table :orderItems="orderItems">
            </order-items-history-table>

        </div>
    </div>
</template>

<style scoped>
.filter-div {
    min-width: 12rem;
}

.flex-container {
    display: flex;
    background-color: #f1f1f1;
}

.flex-container>div {
    margin: 30px;
    background-color: #f1f1f1;
    color: black;
    text-align: center;
    line-height: 75px;
    font-size: 30px;

}

.center {
    display: block;
    margin-right: auto;
    margin-left: auto;
}
</style>