<script setup>
  import { ref, computed, onMounted, inject, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import { useOrderItemsStore } from '@/stores/orderItems.js'
  import axios from 'axios'
  
  const store = useOrderItemsStore()
  const paymentType = ref("visa")

  const deleteClick = (product) => {
    const idx = store.items.findIndex((element) => element === product)
    if (idx >= 0) {
        store.items.splice(idx, 1)
    }
  }

  const confirmPayment = () => {

  }

</script>

<template>
    <div v-if="store.items.length == 0">
        <h2>No items to order</h2>
        <p>Please add some items from the <router-link :to="{ name: 'Menu' }">menu</router-link> first.</p>
    </div>
    <div v-else>
        <h2>Create a new order</h2>
        <table class="table">
            <thead>
            <tr>
                <!-- <th>Image</th> -->
                <th>Name</th>
                <th>Type</th>
                <th>Price</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="product in store.items" 
                :key="product.id">
                <!-- <td>
                    <img :src='"http://localhost:8081/storage/products/"+product.photo_url' />
                </td> -->
                <td>
                    {{ product.name }}
                </td>
                <td>{{ product.type }}</td>
                <td>{{ product.price }}€</td>
                <td>
                <div class="d-flex justify-content-end">
                    <button
                    class="btn btn-xs btn-light"
                    @click="deleteClick(product)"
                    >
                    <i class="bi bi-xs bi-x-square-fill"></i>
                    </button>
                </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="text-center">
            <b>Total Price:</b> {{ store.totalPrice }} €
        </div>
        <br>
        <hr/>
        <h2 class="text-center">Select Payment Type:</h2>
        <div class="paymentChoice">
            <ul>
                <li><input type="radio" name="payment" v-model="paymentType" value="visa" id="visa">Visa</li>
                <li><input type="radio" name="payment" v-model="paymentType" value="mbway" id="mbway">MbWay</li>
                <li><input type="radio" name="payment" v-model="paymentType" value="paypal" id="paypal">PayPal</li>
            </ul>
            <!-- <div>
                <input type="radio" name="payment" v-model="paymentType" value="visa" id="visa">
                <label class="radio-inline" for="visa">Visa</label>
            </div>
            <div>
                <input type="radio" name="payment" v-model="paymentType" value="mbway" id="mbway">
                <label class="radio-inline" for="mbway">MbWay</label>
            </div>
            <div>
                <input type="radio" name="payment" v-model="paymentType" value="paypal" id="paypal">
                <label class="radio-inline" for="paypal">PayPal</label>
            </div> -->
        </div>
        <div v-if="paymentType=='visa'">
            <div>
                <label>Visa Card ID:</label>
                <input class="form-control" type="text" />
            </div>
        </div>
        <div v-else-if="paymentType=='mbway'">
            <div>
                <label>Phone Number:</label>
                <input class="form-control" type="text" />
            </div>
        </div>
        <div v-else>
            <div>
                <label>Email:</label>
                <input class="form-control" type="text" />
            </div>
        </div>
        <br>
        <button
            type="button"
            class="btn btn-success hvr-grow"
            style="float: right;"
            @click="confirmPayment"> Confirm Payment</button>
    </div>
</template>

<style>
.paymentChoice {
  width: 100%;
  font-size: 125%
}
.paymentChoice ul{
  width: 100%;
  text-align: center;
}
.paymentChoice ul li{
  margin: 10px 10px 0px 0px;
  display: inline-block;
}
input[type="radio"]{
  margin-right: 10px;
  cursor: pointer;
}

</style>