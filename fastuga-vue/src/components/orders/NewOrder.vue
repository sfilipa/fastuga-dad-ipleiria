<script setup>
    import { ref, computed, onMounted, inject, watch } from 'vue'
    import { useRouter } from 'vue-router'
    import { useOrderItemsStore } from '@/stores/orderItems.js'
    import axios from 'axios'

    const PAYMENT_URL = 'https://dad-202223-payments-api.vercel.app' 
    const store = useOrderItemsStore()
    const toast = inject("toast")
    const paymentType = ref("visa")
    const paymentReference = ref("")
    const referenceFocus = ref(null)
    const errors = ref(null)

    const deleteClick = (product) => {
        const idx = store.items.findIndex((element) => element === product)
        if (idx >= 0) {
            store.items.splice(idx, 1)
        }
    }

    const refocus = () => {
        paymentReference.value = ""
        referenceFocus.value.focus()
        errors.value = null
    }

    const confirmPayment = () => {

        if(paymentReference.value.length == 0){
            errors.value = {
            default: ["Empty Reference Value"]
            }
            return 
        }

        if(store.totalPrice <= 0){
            errors.value = {
                default: ["Price Must Be Higher Than Zero!"]
            }
            return
        }

        if(paymentType.value == 'visa'){
            if(!paymentReference.value.match('[1-9][0-9]{15}')){
                errors.value = {
                visa: ["Invalid Visa Reference"]
                }
                return
            }
        }else if(paymentType.value == 'mbway'){
            if(!paymentReference.value.match('[1-9][0-9]{8}')){
                errors.value = {
                mbway: ["Invalid Phone Number"]
                }
                return
            }
        }else if(paymentType.value == 'paypal'){
            if(!paymentReference.value.match('[a-zA-Z0-9.+_]+@[a-zA-Z0-9.+_]+.[a-zA-Z]')){
                errors.value = {
                paypal: ["Invalid Email Format"]
                }
                return
            }
        }else{
            errors.value = {
                default: ["Payment Type Not Supported"]
            }
            return
        }

        const requestBody = {
                'type': paymentType.value,
                'reference': paymentReference.value,
                'value': store.totalPrice
        }
        
        axios.post(`${PAYMENT_URL}/api/payments`, requestBody)
            .then((response) => {
                //if successful, create order in DB with correct status (check if has a hot dish or not)
                console.log(response)
            })
            .catch((error) => {
                if (error.response.status == 422) {
                toast.error('Order was not created due to validation errors!')
                } else {
                toast.error('Order was not created due to unknown server error!')
                }
            })
    }

</script>

<template>
    <div v-if="store.items.length == 0">
        <h2>No items to order</h2>
        <p>Please add some items from the menu first.</p>
    </div>
    <div v-else>
        <h2>Create a new order</h2>
        <table class="table">
            <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Type</th>
                <th>Price</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="product in store.items" 
                :key="product.id">
                <td>
                    <img :src='"http://localhost:8081/storage/products/" + product.photo_url' />
                </td>
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
                <li><input @change="refocus" type="radio" name="payment" v-model="paymentType" value="visa" id="visa">Visa</li>
                <li><input @change="refocus" type="radio" name="payment" v-model="paymentType" value="mbway" id="mbway">MbWay</li>
                <li><input @change="refocus" type="radio" name="payment" v-model="paymentType" value="paypal" id="paypal">PayPal</li>
            </ul>
        </div>
        <div v-if="paymentType=='visa'">
            <div>
                <label>Visa Card ID:</label>
                <input ref="referenceFocus" class="form-control" type="text" v-model="paymentReference"/>
                <field-error-message :errors="errors" fieldName="visa"></field-error-message>
            </div>
        </div>
        <div v-else-if="paymentType=='mbway'">
            <div>
                <label>Phone Number:</label>
                <input ref="referenceFocus" class="form-control" type="text" v-model="paymentReference"/>
                <field-error-message :errors="errors" fieldName="mbway"></field-error-message>
            </div>
        </div>
        <div v-else>
            <div>
                <label>Email:</label>
                <input ref="referenceFocus" class="form-control" type="text" v-model="paymentReference"/>
                <field-error-message :errors="errors" fieldName="paypal"></field-error-message>
            </div>
        </div>
        <field-error-message :errors="errors" fieldName="default"></field-error-message>
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
        <br>
        <button
            type="button"
            class="btn btn-success hvr-grow"
            style="float: right;"
            @click="confirmPayment"> Confirm Payment</button>
    </div>
</template>

<style>
.payment-box{
    margin: auto;
    height: 110px;
    width: 35%;
    position: relative;
    border: 1px solid rgb(12, 12, 12);
}

input[type="radio"]{
    margin-right: 10px;
    cursor: pointer;
}

img, svg {
    vertical-align: middle;
    width: 65px;
}

</style>