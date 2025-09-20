<template>
    <div class="home">
        <!-- 全画面マップ -->
        <div id="map" class="fullscreen-map"></div>

        <!-- 検索バー（マップ上にオーバーレイ） -->
        <div class="search-overlay">
            <input
                v-model="searchKeyword"
                type="text"
                placeholder="スポット名で検索"
                class="search-bar"
            />
        </div>

        <!-- スポット一覧（マップ上にオーバーレイ） -->
        <div class="spots-overlay" v-if="filteredSpots.length > 0">
            <div class="spots-header">
                <h3>パワースポット一覧</h3>
                <button @click="showSpotsList = !showSpotsList" class="toggle-btn">
                    {{ showSpotsList ? '閉じる' : '開く' }}
                </button>
            </div>
            <div v-if="showSpotsList" class="spots-list">
                <div
                    v-for="spot in filteredSpots"
                    :key="spot.id"
                    @click="goToSpotDetail(spot.id)"
                    class="spot-item"
                >
                    {{ spot.name }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import L from 'leaflet'
import axios from 'axios'

const router = useRouter()
const spots = ref([])
const searchKeyword = ref('')
const map = ref(null)
const markers = ref([])
const showSpotsList = ref(false)

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

    // 検索結果に基づいてマーカーを表示
    filteredSpots.value.forEach((spot, i) => {
        const icon = L.icon({
            iconUrl: '/spot.png',
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

// 検索キーワードが変更されたときにマーカーを更新
watch(searchKeyword, () => {
    if (map.value) {
        updateMarkers()
    }
})

onMounted(() => {
    initMap()
})
</script>

<style scoped>
.home {
  position: relative;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
}

.fullscreen-map {
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
}

/* 検索バーオーバーレイ */
.search-overlay {
  position: absolute;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  width: 90%;
  max-width: 400px;
}

.search-bar {
  width: 100%;
  padding: 12px 16px;
  font-size: 16px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-weight: 400;
  letter-spacing: -0.01em;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 25px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px); /* Safari対応 */
  outline: none;
  transition: all 0.3s ease;
}

.search-bar:focus {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  background: rgba(255, 255, 255, 1);
}

/* スポット一覧オーバーレイ */
.spots-overlay {
  position: absolute;
  bottom: 20px;
  left: 20px;
  right: 20px;
  z-index: 1000;
  max-height: 60vh;
}

.spots-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px); /* Safari対応 */
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 12px 16px;
  border-radius: 12px 12px 0 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.spots-header h3 {
  margin: 0;
  font-size: 16px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-weight: 600;
  letter-spacing: -0.02em;
  color: #333;
}

.toggle-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-weight: 500;
  letter-spacing: -0.01em;
  cursor: pointer;
  transition: background-color 0.2s;
}

.toggle-btn:hover {
  background: #0056b3;
}

.spots-list {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px); /* Safari対応 */
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-top: none;
  border-radius: 0 0 12px 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  max-height: 50vh;
  overflow-y: auto;
}

.spot-item {
  cursor: pointer;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  font-size: 14px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-weight: 400;
  letter-spacing: -0.01em;
  color: #333;
}

.spot-item:last-child {
  border-bottom: none;
}

.spot-item:hover {
  background: rgba(0, 123, 255, 0.1);
  color: #007bff;
}

/* レスポンシブデザイン */
@media (max-width: 768px) {
  .search-overlay {
    top: 10px;
    width: 95%;
  }

  .spots-overlay {
    bottom: 10px;
    left: 10px;
    right: 10px;
  }

  .spots-header {
    padding: 10px 12px;
  }

  .spots-header h3 {
    font-size: 14px;
  }

  .spot-item {
    padding: 10px 12px;
    font-size: 13px;
  }
}

/* ロゴとの重複を避ける */
@media (min-width: 769px) {
  .search-overlay {
    top: 80px; /* ロゴの高さ分下げる */
  }
}
</style>
