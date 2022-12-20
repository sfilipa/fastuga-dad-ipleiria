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
  <div class="d-flex justify-content-center spinner-font">
    <div class="spinner-border" role="status">
      <span class="sr-only"></span>
    </div>
  </div>
</template>

<style scoped>


</style>