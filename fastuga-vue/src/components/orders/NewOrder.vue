<script setup>
import { ref, computed, onMounted, inject, watch, reactive, toRaw } from "vue";
import { useRouter } from "vue-router";
import { useOrderItemsStore } from "@/stores/orderItems.js";
import { useUserStore } from "@/stores/user.js";
import axios from "axios";
import ConfirmationDialog from "../global/ConfirmationDialog.vue";
import router from "@/router";

const axiosLaravel = inject("axios");
const serverBaseUrl = inject("serverBaseUrl");
const PAYMENT_URL = "https://dad-202223-payments-api.vercel.app";
const store = useOrderItemsStore();

//IMPORTANTE - SÓ DEIXAR FAZER COMPRAS A QUEM SEJA CUSTOMER/UTILIZADOR ANONIMO! (fazer middleware)

const user = useUserStore();
const pointsToUse = ref(0);
const pointsAvailableToUse = ref([]);
const customer = ref(null);
const toast = inject("toast");
const paymentType = ref("VISA");
const paymentReference = ref("");
const referenceFocus = ref(null);
const errors = ref(null);
const orderCompletedDialog = ref(false);
const ticketNumber = ref(0);

const deleteAllClick = (product) => {
  const idx = store.items.findIndex((element) => element.name === product.name);
  if (idx >= 0) {
    store.items.splice(idx, product.count);
  }
};

const deleteClick = (product) => {
  const idx = store.items.findIndex((element) => element.name === product.name);
  if (idx >= 0) {
    store.items.splice(idx, 1);
  }
};

const addClick = (product) => {
  const productToAdd = store.items.find(
    (element) => element.name === product.name
  );

  store.items.push(productToAdd);
};

const changePaymentType = (paymentString) => {
  paymentType.value = paymentString;
  refocus();
};

const refocus = () => {
  referenceFocus.value.focus();
  errors.value = null;
  paymentReference.value = "";
  if (customer.value != null) {
    if (paymentType.value == customer.value.default_payment_type) {
      paymentReference.value = customer.value.default_payment_reference;
    }
  }
};

const checkCurrentOrdersLimit = () => {
  axiosLaravel.get('/orders/active')
      .then((response)=>{
        const currentOrdersCount = response.data
        if(currentOrdersCount > 99){
          return false
        }
      })
      .catch((error)=>{
        console.log(error)
      })
  return true
}

const confirmPayment = () => {
  if(!checkCurrentOrdersLimit()){
    toast.error("Can't place a new order - we're currently at full capacity (max. 99 orders)")
  }

  const paymentBody = {
    ticket_number: 1,
    status: "P",
    customer_id: customer.value != null ? customer.value.id : null,
    total_price: store.totalPrice,
    total_paid: finalPrice.value,
    total_paid_with_points: store.totalPrice - finalPrice.value,
    points_gained: user.userId != -1 ? calculatePointsGained() : 0,
    points_used_to_pay: pointsToUse.value,
    payment_type: finalPrice.value == 0 ? null : paymentType.value, //It's zero when the customer paid for the whole order with just their points
    payment_reference: finalPrice.value == 0 ? null : paymentReference.value,
    date: getTimestamp(),
    delivered_by: null,
    order_items: store.items,
  };

  if (finalPrice.value == 0) {
    axiosLaravel
      .post("/orders", paymentBody)
      .then((response) => {
        ticketNumber.value = response.data.data.ticket_number;
        orderCompletedDialog.value.show();
      })
      .catch((error) => {
        toast.error(
          "Order was not created due to " + error.response.data.message
        );
      });
    return;
  }

  if (paymentReference.value.length == 0) {
    errors.value = {
      default: ["Empty Reference Value"],
    };
    toast.error("Order was not created due to validation errors!");
    return;
  }

  if (store.totalPrice < 0) {
    errors.value = {
      default: ["Price Must Be Higher Than Zero!"],
    };
    toast.error("Order was not created due to validation errors!");
    return;
  }

  if (paymentReferenceValidations() == -1) {
    return;
  }

  const requestBody = {
    type: paymentType.value.toLowerCase(),
    reference: paymentReference.value,
    value: finalPrice.value,
  };

  axios
    .post(`${PAYMENT_URL}/api/payments`, requestBody)
    .then(() => {
      axiosLaravel
        .post("/orders", paymentBody)
        .then((response) => {
          ticketNumber.value = response.data.data.ticket_number;
          orderCompletedDialog.value.show();
        })
        .catch((error) => {
          toast.error(
            "Order was not created due to " + error.response.data.message
          );
        });
    })
    .catch((error) => {
      if (error.response.status == 422) {
        toast.error(
          "Order was not created due to validation errors - " +
            error.response.data.message
        );
      } else {
        toast.error("Order was not created due to unknown server error!");
      }
    });
};

const paymentReferenceValidations = () => {
  if (paymentType.value == "VISA") {
    let pattern = /^[1-9][0-9]{15}$/;
    if (!paymentReference.value.match(pattern)) {
      errors.value = {
        visa: ["Invalid Visa Reference"],
      };
      toast.error("Order was not created due to validation errors!");
      return -1;
    }
  } else if (paymentType.value == "MBWAY") {
    let pattern = /^[1-9][0-9]{8}$/;
    if (!paymentReference.value.match(pattern)) {
      errors.value = {
        mbway: ["Invalid Phone Number"],
      };
      toast.error("Order was not created due to validation errors!");
      return -1;
    }
  } else if (paymentType.value == "PAYPAL") {
    let pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/; ///^([a-zA-Z0-9.+_])+@([a-zA-Z0-9.+_])+.([a-zA-Z])+$/
    if (!paymentReference.value.match(pattern)) {
      errors.value = {
        paypal: ["Invalid Email Format"],
      };
      toast.error("Order was not created due to validation errors!");
      return -1;
    }
  } else {
    errors.value = {
      default: ["Payment Type Not Supported"],
    };
    toast.error("Order was not created due to validation errors!");
    return -1;
  }
};

const getTimestamp = () => {
  const date = new Date();
  return `${date.getFullYear()}-${
    date.getMonth() + 1
  }-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
};

const LoadCustomerInfo = () => {
  axiosLaravel
    .get(`/customers/user/${user.userId}`)
    .then((response) => {
      customer.value = response.data.data;
      paymentReference.value = customer.value.default_payment_reference;
      paymentType.value = customer.value.default_payment_type;
      calculateAvailablePointsOptions();
    })
    .catch((error) => {
      console.log(error);
    });
};

const calculateAvailablePointsOptions = () => {
  let total = Math.trunc(customer.value.points);
  //Desired transformation - Example 1: 23 -> 20, Example 2: 46 -> 40
  while (total % 10 != 0) {
    total--;
  }
  let arrayElement = 10;
  while (arrayElement <= total) {
    pointsAvailableToUse.value.push(arrayElement);
    arrayElement += 10;
  }
};

const calculatePointsGained = () => {
  let total = Math.trunc(finalPrice.value);
  while (total % 10 != 0) {
    total--;
  }
  if (total == 0) {
    return 0;
  }
  return total / 10;
};

onMounted(() => {
  pointsAvailableToUse.value = [0];
  if (store.items.length != 0 && user.userId != -1) {
    LoadCustomerInfo();
  }
});

const finalPrice = computed(() => {
  if (customer) {
    var total = store.totalPrice - transformatePointsToEuros(pointsToUse.value);
    if (total < 0) {
      return 0;
    }
    return Math.round(total * 100) / 100;
  }
  return store.totalPrice;
});

const transformatePointsToEuros = (points) => {
  return Math.trunc(points / 10) * 5;
};

const dialogConfirm = () => {
  user.loadMyCurrentOrders();
  store.resetOrderItems();
  //   router.push('/publicBoard')
};

const groupItems = (orderItems) => {
  const arrayOrderItems = toRaw(orderItems);
  var counts = arrayOrderItems.reduce((newArray, product) => {
    if (product != null) {
      var name = product.name;

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
      price: arrayOrderItems.find((product) =>
        product != null ? product.name == productName : false
      ).price,
      photo_url: arrayOrderItems.find((product) =>
        product != null ? product.name == productName : false
      ).photo_url,
    };
  });

  return countsExtended;
};
</script>

<template>
  <ConfirmationDialog
    ref="orderCompletedDialog"
    confirmationBtn="Confirm"
    :msg="`You order has been successfully placed with the ticket number: ${ticketNumber}`"
    @confirmed="dialogConfirm"
  >
  </ConfirmationDialog>

  <!-- <div class="container"> -->
  <div class="d-flex justify-content-between fastuga-font menu-header">
    <div class="mx-2">
      <h3 class="mt-4">My Order</h3>
    </div>
  </div>
  <hr />
  <div class="container fastuga-font">
    <div v-if="store.items.length == 0" class="my-order-empty">
      <h3><b>Without items to order</b></h3>
      <br />
      <p class="order-font">
        Please add some items from the
        <router-link :to="{ name: 'Menu' }"
          >Menu <i class="bi bi-cup-straw"></i></router-link
        >first.
      </p>
      <img src="@/assets/emptyOrder.jpg" class="new-order-img" />
    </div>
    <div v-else>
      <div class="order-list">
        <div v-for="product in groupItems(store.items)" class="order-list-item">
          <div>
            <img
              :src="`${serverBaseUrl}/storage/products/${product.photo_url}`"
            />
          </div>

          <div class="order-list-body">
            <div class="order-list-row" style="margin-top: 2%">
              <span class="order-product-name">{{ product.name }}</span>
              <div class="order-right">
                <button
                  class="btn order-remove-button"
                  @click="deleteAllClick(product)"
                >
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>

            <div class="order-list-row" style="margin-bottom: 2%">
              <span>{{ product.price }}€</span>

              <div class="order-right">
                <div class="order-list-qtd-buttons">
                  <button class="btn order-list-qtd-button">
                    <i
                      class="bi bi-dash btn-qtd-text"
                      @click="deleteClick(product)"
                    ></i>
                  </button>
                  <span class="order-list-qtd">{{ product.count }}</span>
                  <button
                    class="btn order-list-qtd-button"
                    @click="addClick(product)"
                  >
                    <i class="bi bi-plus btn-qtd-text"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center total-price">
        <b>Total Price:</b> {{ store.totalPrice }} €
      </div>
      <br />
      <hr class="hr-center" />

      <div class="order-payment">
        <h2 class="text-payment">Payment</h2>
        <hr />
        <div class="payment-choice">
          <div class="align-payment-choices">
            <div
              class="payment-item"
              :class="{ 'payment-item-active': paymentType == 'VISA' }"
              @click="changePaymentType('VISA')"
            >
              <div
                :class="[paymentType == 'VISA' ? 'dot-active' : 'dot']"
              ></div>
              <span>Visa</span>
            </div>
            <div
              class="payment-item"
              :class="{ 'payment-item-active': paymentType == 'MBWAY' }"
              @click="changePaymentType('MBWAY')"
            >
              <div
                :class="[paymentType == 'MBWAY' ? 'dot-active' : 'dot']"
              ></div>
              <span>MB Way</span>
            </div>
            <div
              class="payment-item"
              :class="{ 'payment-item-active': paymentType == 'PAYPAL' }"
              @click="changePaymentType('PAYPAL')"
            >
              <div
                :class="[paymentType == 'PAYPAL' ? 'dot-active' : 'dot']"
              ></div>
              <span>Paypal</span>
            </div>
          </div>
        </div>
        <div v-if="paymentType == 'VISA'" class="payment-input">
          <label class="payment-input-label">Visa Card ID:</label>
          <input
            @keydown="errors = null"
            ref="referenceFocus"
            type="text"
            v-model="paymentReference"
            class="form-control"
          />
        </div>
        <div class="payment-error-message">
          <field-error-message
            :errors="errors"
            fieldName="visa"
            style="font-size: 16px"
          ></field-error-message>
        </div>
        <div v-if="paymentType == 'MBWAY'" class="payment-input">
          <label class="payment-input-label">Phone Number:</label>
          <input
            @keydown="errors = null"
            ref="referenceFocus"
            type="text"
            v-model="paymentReference"
            class="form-control"
          />
        </div>
        <div class="payment-error-message">
          <field-error-message
            :errors="errors"
            fieldName="mbway"
            style="font-size: 16px"
          ></field-error-message>
        </div>
        <div v-if="paymentType == 'PAYPAL'" class="payment-input">
          <label class="payment-input-label">Email:</label>
          <input
            @keydown="errors = null"
            ref="referenceFocus"
            type="text"
            v-model="paymentReference"
            class="form-control"
          />
        </div>
        <div class="payment-error-message">
          <field-error-message
            :errors="errors"
            fieldName="paypal"
            style="font-size: 16px"
          ></field-error-message>
        </div>
        <div class="payment-error-message">
          <field-error-message
            :errors="errors"
            fieldName="default"
            style="font-size: 16px"
          ></field-error-message>
        </div>
        <!-- <div class="payment-box text-center"> 
            <div >
                <input ref="referenceFocus" @change="refocus" type="radio" name="payment" v-model="paymentType" value="visa" id="visa">
                <label for="visa">Visa</label>
            
                <input ref="referenceFocus" @change="refocus" type="radio" name="payment" v-model="paymentType" value="mbway" id="mbway">
                <label for="mbway">MBWay</label>
            
                <input ref="referenceFocus" @change="refocus" type="radio" name="payment" v-model="paymentType" value="paypal" id="paypal">
                <label for="paypal">PayPal</label>
            </div>
            
             <div class="btn-group payment-choice">
                <input ref="referenceFocus" @change="refocus" type="radio" class="btn-check" name="payment" v-model="paymentType" value="visa" id="visa">
                <label class="btn btn-secondary" for="visa">Visa</label>
            
                <input ref="referenceFocus" @change="refocus" type="radio" class="btn-check" name="payment" v-model="paymentType" value="mbway" id="mbway">
                <label class="btn btn-secondary" for="mbway">MBWay</label>
            
                <input ref="referenceFocus" @change="refocus" type="radio" class="btn-check" name="payment" v-model="paymentType" value="paypal" id="paypal">
                <label class="btn btn-secondary" for="paypal">PayPal</label>
            </div> 
            
                <div v-if="paymentType=='visa'">
                    <input v-model="paymentReference" class="input-group input-group-sm" type="text" placeholder="Insert Visa Card ID"/>
                </div>
                <div v-else-if="paymentType=='mbway'">
                    <input v-model="paymentReference" class="input-group input-group-sm" type="text" placeholder="Insert Phone Number"/>
                </div>
                <div v-else>
                    <input v-model="paymentReference" class="input-group input-group-sm" type="text" placeholder="Insert Email Address"/>
                </div>
            
        </div> -->
        <br />
        <div class="payment-details">
          <div v-if="customer">
            <div class="payment-details-row">
              <div class="payment-details-label">Available Points:</div>
              <div class="payment-details-detail">{{ customer.points }}</div>
            </div>

            <div class="payment-details-row">
              <div class="payment-details-label" for="points">
                Points To Use:
              </div>
              <select
                class="form-select payment-details-detail"
                id="selectType"
                v-model="pointsToUse"
              >
                <option
                  v-for="points in pointsAvailableToUse"
                  :value="points"
                  :disabled="
                    transformatePointsToEuros(points) - store.totalPrice > 5
                  "
                >
                  {{ points }}
                </option>
              </select>
              <field-error-message
                :errors="errors"
                fieldName="pointsToUse"
              ></field-error-message>
            </div>
          </div>
          <div v-else class="payment-details-row">
            <span class="payment-details-label"> Available Points:</span>
            <span class="payment-details-detail"
              >Not Available - Must Login First</span
            >
          </div>

          <div class="payment-details-row">
            <span class="payment-details-label">Total Discount:</span>
            <span class="payment-details-detail">
              {{
                store.totalPrice == finalPrice
                  ? 0
                  : Math.round((store.totalPrice - finalPrice) * 100) / 100
              }}
              €
            </span>
          </div>
          <div class="payment-details-row">
            <span class="payment-details-label">Total Price To Pay:</span>
            <span class="payment-details-detail"> {{ finalPrice }} €</span>
          </div>
        </div>

        <div class="confirm-payment-div">
          <button
            type="button"
            class="btn hvr-grow confirm-payment"
            @click="confirmPayment"
          >
            Confirm Payment
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

.align-payment-choices{
  margin: auto;
  flex-direction: row;
  display: flex;
  height: 3.5rem;
}

.confirm-payment:hover,
.confirm-payment:active {
  background-color: #ff8300;
  border-color: #ff8300;
  color: white;
}

.confirm-payment {
  height: 3rem;
  align-self: center;
  background-color: #ffa71dd6;
  border-color: #ffa71dd6;
  color: white;
  font-weight: bolder;
  margin-left: auto;
  margin-right: 10%;
  height: 4rem;
  width: 15rem;
}

.confirm-payment-div {
  display: flex;
  align-items: center;
}

.payment-details-detail {
  width: -moz-available;
  text-align: start;
}

.payment-details-label {
  width: 30%;
  font-weight: bold;
}

.payment-details-row {
  display: flex;
  flex-direction: row;
  align-items: center;
  margin-bottom: 2%;
}

.payment-details {
  display: flex;
  flex-direction: column;
  width: 80%;
  margin: auto;
  font-size: 18px;
}

.dot-active {
  height: 16px;
  width: 16px;
  border-radius: 50%;
  margin-right: 15%;
  background-color: white;
  /* border: 3.5px solid #ffa71dd6; */
  /* border: 3.5px solid white;
  background-color: #c47801d6; */
}

.dot {
  height: 16px;
  width: 16px;
  border-radius: 50%;
  margin-right: 15%;
  /* background-color: #ffa71dd6; */
  border: 3.5px solid #ffa71dd6;
  background-color: white;
}

.payment-input-label {
  width: 30%;
  font-size: 18px;
  font-weight: bold;
}

.payment-error-message {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 43%;
  margin: auto;
}

.payment-input {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 80%;
  margin: auto;
  margin-top: 4%;
}

.payment-item-active:hover {
  background-color: #ff8300 !important;
}

.payment-item-active {
  background-color: #ffa71dd6;
  color: white !important;
}

.payment-item:hover {
  background-color: whitesmoke;
}

.payment-item {
  border: 3px solid #ffa71dd6;
  cursor: pointer;
  width: 10rem;
  margin-right: 4%;
  padding: 1.5%;
  border-color: #ffa71dd6;
  border-radius: 10px;
  text-align: center;

  color: #ffa71dd6;
  font-weight: bold;
  display: flex;
  align-items: center;
}

.payment-choice {
  display: flex;
  flex-direction: row;
  align-items: center;
  margin-top: 3%;
  margin-left: 9%;
}

.text-payment {
  margin-top: 3%;
}

.hr-center {
  background: #362222;
  height: 6px;
  opacity: initial;
}

.total-price {
  margin-top: 4%;
  font-size: 19px;
}

.order-product-name {
  width: -moz-available;
  font-size: 17px;
  margin-top: 1%;
}

.order-list-qtd {
  margin: 0 15px;
  font-size: 15px;
}

.btn-qtd-text {
  color: white;
  vertical-align: middle;
  font-weight: bold;
  margin: 0 2px;
}

.order-list-qtd-button:active {
  background-color: #ff9c34;
}

.order-list-qtd-button:hover {
  background-color: #ff9c34;
}

.order-list-qtd-button {
  background-color: #ffa71dd6;
  height: 2rem;
  padding: 0px;
  width: 34px;
}

.order-list-qtd-buttons {
  float: right;
  display: flex;
  flex-direction: row;
  width: auto;
  margin-right: 2%;
  align-items: center;
}

.order-remove-button {
  display: block;
  margin-left: auto;
  font-size: 1rem !important;
  top: -15px;
  position: relative;
  right: -5px;
  color: #bababa;
}

.order-right {
  width: -moz-available;
}

.order-list-row {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.order-list-body {
  display: flex;
  flex-direction: column;
  width: inherit;
  margin-left: 15px;
}

.order-list-item {
  width: -moz-available;
  align-items: center;
  display: flex;
  flex-direction: row;
  margin: 1% 20%;
  border-radius: 2%;
  /* background-image: linear-gradient(to top left, #ff8300, #ffa71dd6);
  box-shadow: 0 10px 16px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); */
  /* background-image: linear-gradient(to top left, #fffbf8, #ffffffd6); */
  box-shadow: 0 10px 16px 0 rgba(200, 200, 200, 0.2),
    0 6px 20px 0 rgba(196, 196, 196, 0.19);
  padding: 10px;
  /* background-color: #fff6e9; */
  border-radius: 10px;
}

.order-payment {
  max-height: 570px;
  min-height: 570px;
}

.order-list {
  max-height: 390px;
  min-height: 390px;
  overflow: auto;
}

.new-order-img {
  width: 35%;
  margin-top: 5%;
  border-radius: 50%;
}

.order-font {
  font-size: 20px;
}

.my-order-empty {
  text-align: center;
}

.payment-box {
  margin: auto;
  height: 110px;
  width: 35%;
  position: relative;
  border: 1px solid rgb(12, 12, 12);
}

.menu-header {
  align-items: end;
}

.container {
  display: flex;
  flex-direction: column;
}

a {
  color: #725151;
}

input[type="radio"] {
  margin-right: 10px;
  cursor: pointer;
}
img,
svg {
  vertical-align: middle;
  width: 80px;
}
</style>
