<script setup>
import { defineProps } from 'vue'
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
} from 'chart.js'


ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
    chartData: {
        type: Object,
        required: true
    },
    chartOptions: {
        type: Object,
        required: true
    },
    chartId: {
        type: String,
        default: 'bar-chart'
    },

})
</script>

<template>
    <Bar :chart-id="props.chartId" :chart-data="props.chartData" :chart-options="props.chartOptions"
        :width="props.width" :height="props.height" />
</template>

