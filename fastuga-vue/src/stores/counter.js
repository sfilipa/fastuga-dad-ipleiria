import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

import { inject } from 'vue'

export const useCounterStore = defineStore('counter', () => {
  const count = ref(0)
  const doubleCount = computed(() => count.value * 2)
  function increment() {
    count.value++
  }

  const axios = inject('axios')
  const teste = ref([])

  function loadTeste() {
    console.log(axios)
    axios.get('projects').then(result => {
      console.log(result)
      console.log(result.data.data)
      teste.value = result.data.data
      console.log(teste.value)
    })
  }


  return { count, doubleCount, increment, teste, loadTeste }
})
