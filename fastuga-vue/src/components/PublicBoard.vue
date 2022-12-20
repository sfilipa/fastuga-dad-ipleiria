<script setup>
import { ref, onMounted, inject } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from '../stores/user.js'

const axios = inject("axios");
const router = useRouter();
const userStore = useUserStore();

const ordersReady = ref(null);
const ordersPreparing = ref(null);

const LoadOrdersReady = () => {
  axios
    .get(`/orders/status/R`)
    .then((response) => {
      ordersReady.value = response.data;
    })
    .catch((error) => {
      console.log(error);
    });
};

const LoadOrdersPreparing = () => {
  axios
    .get(`/orders/status/P`)
    .then((response) => {
      ordersPreparing.value = response.data;
    })
    .catch((error) => {
      console.log(error);
    });
};

const groupItems = (orderItems) => {
  var counts = orderItems.reduce((newArray, array) => {
    if (array.product != null) {
      var name = array.product.name;

      if (!newArray.hasOwnProperty(name)) {
        newArray[name] = 0;
      }

      newArray[name]++;
    }
    return newArray;
  }, {});

  var countsExtended = Object.keys(counts).map((productName) => {
    return {
      name: productName,
      count: counts[productName],
      price: (
        orderItems.find((order) =>
          order.product != null ? order.product.name == productName : false
        ).price * counts[productName]
      ).toFixed(2),
    };
  });

  return countsExtended;
};

onMounted(() => {
  LoadOrdersPreparing();
  LoadOrdersReady();
});
</script>

<template>
  <div
    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom fastuga-font"
  >
    <h1 class="h2">Public Board</h1>
  </div>

  <div v-if="userStore.user != null && userStore.user.type == 'EM'" style="margin-bottom: 5%">
    <h4 style="text-align: center">Orders Ready</h4>
    <div
      v-if="ordersReady == null"
      class="d-flex justify-content-center spinner-font"
    >
      <div class="spinner-border" role="status">
        <span class="sr-only"></span>
      </div>
    </div>
    <div
      v-else
      v-for="order in ordersReady"
      class="order-ready"
      :groupedItems="order.order_items"
    >
      <hr />
      <div class="middle">
        <p>
          <i class="bi bi-ticket-perforated ticket-bi"></i>
          Ticket Number - {{ order.ticket_number }}
        </p>
        <hr class="underline" />
      </div>
      <ul>
        <li v-for="orderItem in groupItems(order.order_items)">
          <div>
            {{ orderItem.count }} x {{ orderItem.name
            }}<span style="float: right">{{ orderItem.price }}€</span>
          </div>
        </li>
        <li>
          <div>
            Points used:
            <span style="float: right">{{ order.points_used_to_pay }} pts</span>
          </div>
          <hr style="margin: revert" />
        </li>

        <li>
          <div>
            Total Paid:
            <span style="float: right">{{ order.total_paid }}€</span>
          </div>
        </li>
        <li>
          <div>
            Points Gained:
            <span style="float: right">{{ order.points_gained }} pts</span>
          </div>
        </li>
      </ul>
    </div>
  </div>

  <div v-else class="grid-container">
    <div class="grid-item">
      <div class="orders-preparing">
        <p class="fastuga-font orders-title">Preparing...</p>
      </div>
      <div class="orders-body">
        <div class="orders-container fastuga-font">
          <div v-if="ordersPreparing == null" class="orders-loading">
            <p>loading..</p>
            <img src="@/assets/loadingCook.jpg" class="orders-loading-image" />
          </div>
          <div v-else-if="ordersPreparing.length == 0">
            Without orders
          </div>
          <span v-else v-for="order in ordersPreparing" class="order-item">
            0{{ order.ticket_number }}
          </span>
        </div>
      </div>
    </div>
    <div class="grid-item">
      <div class="orders-ready">
        <p class="fastuga-font orders-title">Ready to Deliver</p>
      </div>
      <div class="orders-body">
        <div class="orders-container fastuga-font">
          <div v-if="ordersReady == null" class="orders-loading">
            <p>loading..</p>
            <img src="@/assets/loadingCook.jpg" class="orders-loading-image" />
          </div>
          <span v-else-if="ordersReady.length == 0"> Without orders </span>
          <span v-else v-for="order in ordersReady" class="order-item">
            0{{ order.ticket_number }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ticket-bi {
  font-size: 1rem;
}

.orders-loading-image {
  display: inline;
  width: inherit;
  position: relative;
  top: -19px;
}

.orders-loading {
  height: 100%;
  width: -moz-available;
  text-align: center;
}

.orders-ready {
  background-image: linear-gradient(to top left, #70e248, #cbff6f);
}

.orders-preparing {
  background-image: linear-gradient(to top left, #ff8300, #ffa71dd6);
}

.order-item {
  text-align: flex-start;
  border-radius: 2%;
}

.orders-container {
  display: flex;
  padding: 10px;
  width: auto;
  height: inherit;
  margin: auto;
  flex-direction: column;
  flex-wrap: wrap;
}

.orders-title {
  padding: 20px;
}

.orders-body {
  padding: 20px;
  height: inherit;
  padding-top: unset;
}

.grid-item {
  font-size: 30px;
  text-align: flex-start;
  margin: 29px;
  border-radius: 2%;
  box-shadow: 0 10px 16px 0 rgba(139, 136, 136, 0.2), 0 6px 20px 0 rgba(128, 128, 128, 0.19);
  max-height: 420px;
}

.grid-container {
  display: grid;
  padding: 10px;
  width: auto;
  margin: auto;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  height: inherit;
  margin: 0 8%;
}

ul {
  /* columns: 2;
    -webkit-columns: 2;
    -moz-columns: 2; */
  list-style-type: none;
  width: 60%;
  margin: auto;
}

.middle {
  text-align: center;
}

.underline {
  width: 50%;
  margin: auto;
}
</style>
