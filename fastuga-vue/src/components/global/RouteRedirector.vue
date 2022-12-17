<script setup>
import { useRouter } from "vue-router"
import { onMounted } from "vue"
import { useUserStore } from "../../stores/user.js"
const router = useRouter()
const userStore = useUserStore()
const props = defineProps(['redirectTo'])
onMounted(async () => {
    await userStore.restoreToken()
    if (props.redirectTo) {
        router.push(props.redirectTo)
    }
})
</script>
<template>
  <div class="loader center"></div>
</template>

<style scoped>

.loader {
  border: 10px solid #f3f3f3;
  border-radius: 50%;
  border-top: 10px solid grey;
  width: 100px;
  height: 100px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.center {
  margin: 0;
  position: absolute;
  top: 35%;
  left: 50%;
  margin-right: -50%;
  transform: translate(-50%, -50%)
}

</style>