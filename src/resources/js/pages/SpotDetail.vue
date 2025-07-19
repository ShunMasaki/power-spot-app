<template>
    <div v-if="spot">
        <h1>{{ spot.name }}</h1>
        <p>{{ spot.address }}</p>
        <p>{{ spot.description }}</p>

        <h2>ご利益</h2>
        <ul>
            <li
                v-for="benefit in spot.spot_benefits"
                :key="benefit.id"
            >
                <template v-if="benefit.benefit_type">
                {{ benefit.benefit_type.label }}：★{{ benefit.rating }}
                </template>
            </li>
        </ul>
    </div>
  </template>


<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const spot = ref({
name: '',
address: '',
description: '',
spot_benefits: []
})

onMounted(async () => {
try {
    const res = await axios.get(`/api/spots/${route.params.id}`)
    spot.value = res.data
} catch (error) {
    console.error('スポット取得エラー:', error)
}
})
</script>
