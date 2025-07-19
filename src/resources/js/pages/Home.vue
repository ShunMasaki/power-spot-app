<template>
    <div id="map" style="height: 500px;"></div>
  </template>

  <script setup>
  import { onMounted, ref } from 'vue'
  import { useRouter } from 'vue-router'
  import L from 'leaflet'
  import axios from 'axios'

  const map = ref(null)
  const router = useRouter()

  const initMap = () => {
    map.value = L.map('map').setView([35.681236, 139.767125], 12)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map.value)

    loadSpots()
  }

  const loadSpots = async () => {
    try {
      const res = await axios.get('/api/spots')
      const spots = res.data

      spots.forEach(spot => {
        const marker = L.marker([spot.latitude, spot.longitude])
        marker.addTo(map.value)
        marker.on('click', () => {
          router.push(`/spots/${spot.id}`)
        })
      })
    } catch (err) {
      console.error('スポット取得エラー:', err)
    }
  }

  onMounted(() => {
    initMap()
  })
  </script>

  <style scoped>
  #map {
    width: 100%;
    height: 100%;
  }
  </style>
