<template>
    <div class="home">
        <h1>パワースポット一覧</h1>

        <!-- 検索バー -->
        <input
            v-model="searchKeyword"
            type="text"
            placeholder="スポット名で検索"
            class="search-bar"
        />

        <div id="map" style="height: 500px;"></div>

        <!-- スポット一覧 -->
        <ul>
            <li
            v-for="spot in filteredSpots"
            :key="spot.id"
            @click="goToSpotDetail(spot.id)"
            class="spot-item"
            >
            {{ spot.name }}
        </li>
        </ul>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import L from 'leaflet'
import axios from 'axios'

const router = useRouter()
const spots = ref([])
const searchKeyword = ref('')
const map = ref(null)
const markers = ref([])

const baseIconSize = 30
const minSize = 24
const maxSize = 80

const getIconSize = (zoom) =>
    Math.max(minSize, Math.min(maxSize, baseIconSize * Math.pow(1.15, zoom - 12)))

// 絞り込み済みスポット一覧
const filteredSpots = computed(() =>
  spots.value.filter(spot =>
    spot.name.includes(searchKeyword.value)
  )
)

// スポット詳細へ遷移
const goToSpotDetail = id => {
  router.push(`/spots/${id}`)
}

// スポット取得
const loadSpots = async () => {
    try {
        const res = await axios.get('/api/spots')
        spots.value = res.data
        updateMarkers()
    } catch (err) {
        console.error('スポット取得エラー:', err)
    }
}

// マーカー生成・リサイズ
const updateMarkers = () => {
    markers.value.forEach(marker => map.value.removeLayer(marker))
    markers.value = []

    const zoom = map.value.getZoom()
    const size = getIconSize(zoom)

    spots.value.forEach((spot, i) => {
        const icon = L.icon({
            iconUrl: '/spot.svg',
            iconSize: [size, size],
            iconAnchor: [size / 2, size]
        })
        const marker = L.marker([spot.latitude, spot.longitude], { icon })

        marker.addTo(map.value)
        marker.on('click', () => {
            router.push(`/spots/${spot.id}`)
        })
        markers.value.push(marker)
    })
}

const initMap = () => {
    map.value = L.map('map').setView([35.681236, 139.767125], 12)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map.value)

    loadSpots()
    map.value.on('zoomend', updateMarkers)
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

.search-bar {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.spot-item {
  cursor: pointer;
  padding: 8px 12px;
  border-bottom: 1px solid #eee;
  transition: background-color 0.2s;
}

.spot-item:hover {
  background-color: #f5f5f5;
}
</style>
