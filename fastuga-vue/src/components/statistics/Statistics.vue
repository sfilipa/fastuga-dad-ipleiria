<script setup>
import axios from 'axios'
import { ref, computed, onMounted, inject, watch, reactive, toRef } from "vue";
import BarChart from './BarChart.vue'
import HistoryTable from './HistoryTable.vue'
import OrderItemsHistoryTable from './OrderItemsHistoryTable.vue'

import { useUserStore } from "../../stores/user.js"

const orders = ref([]);
const topProducts = ref([]);
const orderItems = ref([null]);
const date = ref(undefined);
const filterByPaymentType = ref("A");
const filterByDate = ref(undefined);


const props = defineProps({
    renderKey: ref(0),
});

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
const LoadTopProducts = () => {
    axios
        .get(`http://localhost:8081/api/products/top`)
        .then((response) => {
            topProducts.value = response.data;
                //const element = array[index];
                console.log(topProducts.value[1][0]);
            
        })
        .catch((error) => {
            console.log(error);
        });
};
const showOrder = (order) => {
    axios
        .get(`http://localhost:8081/api/orders/order/orderItems/${order.id}`)
        .then((response) => {
            orderItems.value = response.data;
            console.log(orderItems.value);

        })
        .catch((error) => {
            console.log(error);
        });
};

const data = reactive({
    user: null,
    totals: null,
    checkins: null
})
const state = reactive({
    loading: true
})
const charts = reactive({
    doughnutConfig: null,
    barConfig: null
})


const userStore = useUserStore()

onMounted(async () => {
    if (userStore.user.type == 'EM') {
        LoadTopProducts();
        // load data from store and api
        /*data.user = await userStore.fetchUser()
        const user_resp = await axios.get(...)
        data.totals = user_resp.data.totals
        data.checkins = user_resp.data.check_ins
        state.loading = false*/

        // create line chart
      /*  var dates = topProducts
        var ratings = [5, 5, 3, 2, 4]
        /*var length = 10
        for (var i = 0; i < length; i++) {
            dates.push(data.checkins[i].date)
            ratings.push(data.checkins[i].rating)
        }*/

        /*console.log(dates) // [ "2022-09-04T00:00:00", "2022-09-04T00:00:00", "2022-09-04T00:00:00", "2022-09-04T00:00:00", "2022-09-05T00:00:00" ]
        console.log(ratings) // [ 5, 5, 3, 2, 4 ]

        charts.barConfig = {
            data: {
                labels: dates,
                datasets: [{
                    label: 'Ratings by date',
                    data: ratings,
                    backgroundColor: '#f87979'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        }*/
    } else {
        LoadOrders();
    }
})
</script>
<template>
    <div :key="props.renderKey">
        <div class="d-flex justify-content-between">
            <div class="mx-2">
                <h3 class="mt-4">Statistics</h3>
            </div>
        </div>
        <hr />
        <div v-if="userStore.user.type == 'EM'">
            <div class="flex-container">
                <div>
                    <h5 class="center">Top Products</h5>
                    <bar-chart v-if="charts.barConfig" :chart-options="charts.barConfig.options"
                        :chart-data="charts.barConfig.data" :width="400" :height="300" />
                </div>
            </div>
        </div>
        <div v-if="userStore.user.type == 'C'">
            <div v-if="(orderItems.value == null)">
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
                <order-items-history-table :orders="orders" :filterByPaymentType="filterByPaymentType"
                    :date="filterByDate" @show="showOrder">
                </order-items-history-table>
            </div>
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