import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'

export const useOrderItemsStore = defineStore('orderItems', () => {
    const items = ref([])

    const totalPrice = computed(() => {
        let price = Number(0)
        items.value.forEach(p => price = Number(price) + Number(p.price))
        return Math.round(price * 100)/100
    })

    function addItem(item) {
        items.value.push(item)
    }

    function resetOrderItems() {
        items.value = []
    }

    return { items, totalPrice, addItem, resetOrderItems }
})