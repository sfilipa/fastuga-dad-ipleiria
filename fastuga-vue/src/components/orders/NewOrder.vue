<script setup>
    import { ref, computed, onMounted, inject, watch, reactive } from 'vue'
    import { useRouter } from 'vue-router'
    import { useOrderItemsStore } from '@/stores/orderItems.js'
    import { useUserStore } from '@/stores/user.js'
    import axios from 'axios'
    import ConfirmationDialog from "../global/ConfirmationDialog.vue"

    const axiosLaravel = inject('axios')
    const PAYMENT_URL = 'https://dad-202223-payments-api.vercel.app' 
    const store = useOrderItemsStore()

    //IMPORTANTE - SÓ DEIXAR FAZER COMPRAS A QUEM SEJA CUSTOMER/UTILIZADOR ANONIMO! (fazer middleware)

    const user = useUserStore()
    const pointsToUse = ref(0)
    const pointsAvailableToUse = ref([])
    const customer = ref(null)
    const toast = inject("toast")
    const paymentType = ref("VISA")
    const paymentReference = ref("")
    const referenceFocus = ref(null)
    const errors = ref(null)
    const orderCompletedDialog = ref(false)
    const ticketNumber = ref(0)

    const deleteClick = (product) => {
        const idx = store.items.findIndex((element) => element === product)
        if (idx >= 0) {
            store.items.splice(idx, 1)
        }
    }

    const refocus = () => {
        referenceFocus.value.focus()
        errors.value = null
        paymentReference.value = ""
        if(customer.value != null){
            if(paymentType.value == customer.value.default_payment_type){
                paymentReference.value = customer.value.default_payment_reference
            }
        }
    }

    const confirmPayment = () => {
        const paymentBody = {
            'ticket_number': 1,
            'status': checkOrderStatus(),
            'customer_id': customer.value != null ? customer.value.id : null,
            'total_price': store.totalPrice,
            'total_paid': finalPrice.value,
            'total_paid_with_points': store.totalPrice - finalPrice.value,
            'points_gained': user.userId != -1 ? calculatePointsGained() : 0,
            'points_used_to_pay': pointsToUse.value,
            'payment_type': finalPrice.value == 0 ? null : paymentType.value, //It's zero when the customer paid for the whole order with just their points
            'payment_reference': finalPrice.value == 0 ? null : paymentReference.value,
            'date': getTimestamp(),
            'delivered_by':null,
            'order_items': store.items
        }

        if(finalPrice.value == 0){
            axiosLaravel.post('/orders', paymentBody)
                .then((response)=>{
                    console.log(response.data.data)
                    ticketNumber.value = response.data.data.ticket_number
                    orderCompletedDialog.value.show()
                })
                .catch((error)=>{
                    toast.error('Order was not created due to ' + error.response.data.message)
                })
            return
        }

        if(paymentReference.value.length == 0){
            errors.value = {
                default: ["Empty Reference Value"]
            }
            toast.error('Order was not created due to validation errors!')
            return 
        }

        if(store.totalPrice < 0){
            errors.value = {
                default: ["Price Must Be Higher Than Zero!"]
            }
            toast.error('Order was not created due to validation errors!')
            return
        }

        if(paymentReferenceValidations() == -1){
            return
        }

        const requestBody = {
                'type': paymentType.value.toLowerCase(),
                'reference': paymentReference.value,
                'value': finalPrice.value
        }
        
        axios.post(`${PAYMENT_URL}/api/payments`, requestBody)
            .then(() => {
                axiosLaravel.post('/orders', paymentBody)
                .then((response)=>{
                    console.log(response.data.data)
                    ticketNumber.value = response.data.data.ticket_number
                    orderCompletedDialog.value.show()
                })
                .catch((error)=>{
                    console.log(error)
                    toast.error('Order was not created due to ' + error.response.data.message)
                })
            })
            .catch((error) => {
                if (error.response.status == 422) {
                toast.error('Order was not created due to validation errors - ' + error.response.data.message)
                } else {
                toast.error('Order was not created due to unknown server error!')
                }
            })
    }

    const paymentReferenceValidations = () => {
        if(paymentType.value == 'VISA'){
            if(!paymentReference.value.match('[1-9][0-9]{15}')){
                errors.value = {
                visa: ["Invalid Visa Reference"]
                }
                toast.error('Order was not created due to validation errors!')
                return -1
            }
        }else if(paymentType.value == 'MBWAY'){
            if(!paymentReference.value.match('[1-9][0-9]{8}')){
                errors.value = {
                mbway: ["Invalid Phone Number"]
                }
                toast.error('Order was not created due to validation errors!')
                return -1
            }
        }else if(paymentType.value == 'PAYPAL'){
            if(!paymentReference.value.match('[a-zA-Z0-9.+_]+@[a-zA-Z0-9.+_]+.[a-zA-Z]')){
                errors.value = {
                paypal: ["Invalid Email Format"]
                }
                toast.error('Order was not created due to validation errors!')
                return -1
            }
        }else{
            errors.value = {
                default: ["Payment Type Not Supported"]
            }
            toast.error('Order was not created due to validation errors!')
            return -1
        }
    }

    const checkOrderStatus = () => {
        for(let elem of store.items){
            if(elem.type.toLowerCase() == 'hot dish'){
                return 'P'
            }
        }
        return 'R'
    }

    const getTimestamp = () => {
        var date = new Date()
        return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`
    }

    const LoadCustomerInfo = () => {
        axiosLaravel.get(`/customers/user/${user.userId}`)
            .then((response) => {
                customer.value = response.data.data
                paymentReference.value = customer.value.default_payment_reference
                paymentType.value = customer.value.default_payment_type
                calculateAvailablePointsOptions()
            })
            .catch((error)=> {
                console.log(error)
                // toast.error("Couldn't load customer")
            })
    }

    const calculateAvailablePointsOptions = () => {
        var total = Math.trunc(customer.value.points)
        //Desired transformation - Example 1: 23 -> 20, Example 2: 46 -> 40
        while(total % 10 != 0){
            total--
        }
        var arrayElement = 10
        while(arrayElement <= total){ 
            pointsAvailableToUse.value.push(arrayElement)
            arrayElement += 10
        }
    }

    const calculatePointsGained = () => {
        var total = Math.trunc(finalPrice.value)
        while(total % 10 != 0){
            total--
        }
        if(total == 0){
            return 0
        }
        return total / 10
    }

    onMounted(()=>{
        pointsAvailableToUse.value = [0]
        if(store.items.length != 0 && user.userId != -1){
            LoadCustomerInfo()
        }
    })

    const finalPrice = computed(()=> {
        if(customer){
            var total = store.totalPrice - transformatePointsToEuros(pointsToUse.value) 
            if(total < 0){
                return 0
            }
            return Math.round(total * 100)/100
        }
        return store.totalPrice
    })

    const transformatePointsToEuros = (points) => {
        return Math.trunc(points / 10) * 5
    }

    const dialogConfirm = () => {
        store.resetOrderItems()
        //redirect to somewhere

    }

</script>

<template>
    <ConfirmationDialog
    ref="orderCompletedDialog"
    confirmationBtn="Confirm"
    :msg="`You order has been successfully placed with the ticket number: ${ticketNumber}`"
    @confirmed="dialogConfirm"
    >
    </ConfirmationDialog>
    <div v-if="store.items.length == 0">
        <h2>No items to order</h2>
        <p>Please add some items from the <router-link :to="{ name: 'Menu' }">menu</router-link> first.</p>
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
                <li><input @change="refocus" type="radio" name="payment" v-model="paymentType" value="VISA" id="visa">Visa</li>
                <li><input @change="refocus" type="radio" name="payment" v-model="paymentType" value="MBWAY" id="mbway">MbWay</li>
                <li><input @change="refocus" type="radio" name="payment" v-model="paymentType" value="PAYPAL" id="paypal">PayPal</li>
            </ul>
        </div>
        <div v-if="paymentType=='VISA'">
            <div>
                <label>Visa Card ID:</label>
                <input @keydown="(errors = null)" ref="referenceFocus" type="text" v-model="paymentReference"/>
                <field-error-message :errors="errors" fieldName="visa"></field-error-message>
            </div>
        </div>
        <div v-else-if="paymentType=='MBWAY'">
            <div>
                <label>Phone Number:</label>
                <input @keydown="(errors = null)" ref="referenceFocus" type="text" v-model="paymentReference"/>
                <field-error-message :errors="errors" fieldName="mbway"></field-error-message>
            </div>
        </div>
        <div v-else-if="paymentType=='PAYPAL'">
            <div>
                <label>Email:</label>
                <input @keydown="(errors = null)" ref="referenceFocus" type="text" v-model="paymentReference"/>
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
        <div>
            <div v-if="customer">
                <b>Available Points:</b> {{ customer.points }}
                <div>
                    <label for="points"><b>Points To Use:</b> </label>
                    <select class="form-select" id="selectType" v-model="pointsToUse">
                        <option v-for="points in pointsAvailableToUse" :value="points" :disabled="(transformatePointsToEuros(points) - store.totalPrice > 5)">
                            {{ points }}
                        </option>
                    </select>
                    <field-error-message :errors="errors" fieldName="pointsToUse"></field-error-message>
                </div>
            </div>
            <div v-else>
                <b>Available Points:</b> Not Available - Must Login First
            </div>
            
            <div>
                <b>Total Discount:</b> {{ store.totalPrice == finalPrice ? 0 : Math.round((store.totalPrice - finalPrice) * 100) / 100 }} €
            </div>
            <div>
                <b>Total Price To Pay:</b> {{ finalPrice }} €
            </div>
        </div>

        <button
            type="button"
            class="btn btn-success hvr-grow"
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