<script setup>
  import { ref, onMounted, inject } from 'vue'
  import {useRouter} from 'vue-router'

  const axios = inject('axios')
  const router = useRouter()

  const ordersReady = ref(null)

  const LoadOrdersReady = () => {
      axios.get(`/orders/status/R`)
        .then((response) => {
          ordersReady.value = response.data
        })
        .catch((error) => {
          console.log(error)
        })
    }

  const groupItems = (orderItems) => {
    var counts = orderItems.reduce((newArray, array) => {
      if(array.product!=null){
        var name = array.product.name;

        if (!newArray.hasOwnProperty(name)) {
          newArray[name] = 0;
        }
        
        newArray[name]++;
        
      }
      return newArray;
    }, {});

    var countsExtended = Object.keys(counts).map(productName => {
      return {name: productName, count: counts[productName], price: (orderItems.find(order => order.product!=null ? order.product.name==productName : false).price*counts[productName]).toFixed(2)}; 
    });

    return countsExtended;
  }

onMounted(()=>{
  LoadOrdersReady()
})

</script>

<template>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
      <h1 class="h2">Public Board</h1>
    </div>
    <div style="margin-bottom: 5%;">
      <h4 style="text-align: center;">Orders Ready</h4>
      <div v-if="(ordersReady==null)" class="d-flex justify-content-center">
          <div class="spinner-border" role="status" style="margin-top: 5%;">
              <span class="sr-only"></span>
            </div>
      </div>
      <div v-else v-for="order in ordersReady" class="order-ready" :groupedItems = "order.order_items">
        <hr>
        <div class="middle">
            <p>Order - {{order.ticket_number}}</p>
            <hr class="underline">
        </div>
        <ul>
          <li v-for="orderItem in groupItems(order.order_items)" >
            <div>
              {{orderItem.count}} x {{orderItem.name}}<span style="float: right;">{{orderItem.price}}€</span>
            </div>
          </li>
          <li>
            <div>
              Points used: <span style="float: right">{{order.points_used_to_pay}} pts</span>
            </div>
            <hr style="margin: revert;">
          </li>
          
          <li>
            <div>
              Total Paid: <span style="float: right">{{order.total_paid}}€</span>
            </div>
          </li>
          <li>
            <div>
              Points Gained: <span style="float: right">{{order.points_gained}} pts</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
    
</template>
  
<style scoped>
  ul{
    /* columns: 2;
    -webkit-columns: 2;
    -moz-columns: 2; */
    list-style-type: none;
    width: 60%;
    margin: auto;
  }
  .middle{
    text-align: center;
  }
  /* .order-ready{
    align-items: center;
  } */
  .underline{
    width: 50%;
    margin: auto;
  }

</style>