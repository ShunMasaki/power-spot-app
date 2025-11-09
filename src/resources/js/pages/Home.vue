<template>
    <div class="home">
        <!-- 全画面マップ -->
        <div id="map" class="fullscreen-map" ref="mapContainer"></div>

        <!-- 検索バー（マップ上にオーバーレイ） -->
        <div class="search-overlay">
            <input
                v-model="searchKeyword"
                type="text"
                placeholder="スポット名で検索"
                class="search-bar"
            />
        </div>

        <!-- 現在地ボタン -->
        <div class="location-button-overlay">
            <button @click="getCurrentLocation" class="location-btn" title="現在地に移動">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                </svg>
            </button>
        </div>

        <!-- スポット一覧（マップ上にオーバーレイ） -->
        <div class="spots-overlay" v-if="spots.length > 0">
            <div class="spots-header">
                <h3>パワースポット一覧 ({{ totalVisibleCount }}件)</h3>
                <button @click="showSpotsList = !showSpotsList" class="toggle-btn">
                    {{ showSpotsList ? '閉じる' : '開く' }}
                </button>
            </div>
            <transition name="slide-up">
                <div v-if="showSpotsList" class="spots-list">
                    <div v-if="filteredSpots.length === 0" class="no-results">
                        検索結果が見つかりませんでした
                    </div>
                    <div
                        v-for="spot in filteredSpots"
                        :key="spot.id"
                        @click="openSpotModal(spot.id)"
                        @mouseenter="highlightMarker(spot.id)"
                        @mouseleave="unhighlightMarker(spot.id)"
                        class="spot-item"
                    >
                        <div class="spot-name">{{ spot.name }}</div>
                        <div v-if="spot.spot_benefits && spot.spot_benefits.length > 0" class="spot-benefits">
                            <span
                                v-for="benefit in spot.spot_benefits"
                                :key="benefit.id"
                                class="benefit-tag"
                                :title="benefit.benefit_type.label"
                            >
                                {{ benefit.benefit_type.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </div>

    <!-- スポット詳細モーダル -->
    <SpotDetailModal
        :isOpen="showSpotModal"
        :spotId="selectedSpotId"
        :initialTab="initialTab"
        @close="closeSpotModal"
        @openReview="openReviewForm"
        @reopen="reopenSpotModal"
    />
</template>

<script setup>
import { onMounted, ref, computed, watch, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import SpotDetailModal from '../components/SpotDetailModal.vue'

const router = useRouter()
const route = useRoute()
const spots = ref([])
const searchKeyword = ref('')
const map = ref(null)
const mapContainer = ref(null)
const markers = ref([])
const showSpotsList = ref(false)
let updateMarkersTimeout = null

// スポット詳細モーダル
const showSpotModal = ref(false)
const selectedSpotId = ref(null)
const initialTab = ref('overview')

// 地図の表示範囲内のスポット
const visibleSpots = ref([])

// 地図の中心座標
const mapCenter = ref({ lat: 35.6812, lng: 139.7671 })

// 2点間の距離を計算（Haversine formula）
const calculateDistance = (lat1, lon1, lat2, lon2) => {
  const R = 6371 // 地球の半径（km）
  const dLat = (lat2 - lat1) * Math.PI / 180
  const dLon = (lon2 - lon1) * Math.PI / 180
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2)
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  return R * c
}

// 住所から都道府県を抽出
const extractPrefecture = (address) => {
  if (!address) return null
  const match = address.match(/^(東京都|北海道|.+?[都道府県])/)
  return match ? match[1] : null
}

// 東京都の住所から区を抽出
const extractWard = (address) => {
  if (!address || !address.startsWith('東京都')) return null
  const match = address.match(/^東京都(.+?区)/)
  return match ? match[1] : null
}

// 絞り込み済みスポット一覧（地図表示範囲内 + 検索キーワード + 距離順50件）
const filteredSpots = computed(() => {
  // 地図表示範囲内のスポットのみを使用
  let spots = visibleSpots.value.filter(spot =>
    spot.name.includes(searchKeyword.value)
  )

  // 地図の中心からの距離で並び替え
  spots = spots.map(spot => ({
    ...spot,
    distance: calculateDistance(
      mapCenter.value.lat,
      mapCenter.value.lng,
      spot.latitude,
      spot.longitude
    )
  })).sort((a, b) => a.distance - b.distance)

  // 最大50件まで
  return spots.slice(0, 50)
})

// 全体の件数（50件以上なら「50+」表示用）
const totalVisibleCount = computed(() => {
  const total = visibleSpots.value.filter(spot =>
    spot.name.includes(searchKeyword.value)
  ).length
  return total > 50 ? '50+' : total
})

// スポット詳細モーダルを開く
const openSpotModal = (spotId) => {
  selectedSpotId.value = spotId
  showSpotModal.value = true
}

// スポット詳細モーダルを閉じる
const closeSpotModal = () => {
  showSpotModal.value = false
  selectedSpotId.value = null
}

// スポット詳細モーダルを再度開く（ログイン成功後）
const reopenSpotModal = (spotId) => {
  selectedSpotId.value = spotId
  showSpotModal.value = true
}

// レビューフォームを開く（将来の実装用）
const openReviewForm = (spotId) => {
  // TODO: レビューフォーム実装時に追加
}

// スポット取得
const loadSpots = async () => {
    try {
        const res = await axios.get('/api/spots')
        spots.value = res.data
        updateVisibleSpots()
    } catch (err) {
        console.error('スポット取得エラー:', err)
    }
}

// 地図の表示範囲内のスポットを更新
const updateVisibleSpots = () => {
    if (!map.value) return

    const bounds = map.value.getBounds()
    if (!bounds) return

    // 地図の中心座標を更新
    const center = map.value.getCenter()
    if (center) {
        mapCenter.value = {
            lat: center.lat(),
            lng: center.lng()
        }
    }

    visibleSpots.value = spots.value.filter(spot => {
        const position = new google.maps.LatLng(parseFloat(spot.latitude), parseFloat(spot.longitude))
        return bounds.contains(position)
    })

    // updateVisibleSpotsの後にupdateMarkersを呼び出し（デバウンス）
    if (updateMarkersTimeout) {
        clearTimeout(updateMarkersTimeout)
    }
    updateMarkersTimeout = setTimeout(() => {
        updateMarkers()
    }, 100)
}

        // マーカー生成・更新
        const updateMarkers = () => {
            // 既存のマーカーをクリア
            markers.value.forEach(marker => {
                marker.setMap(null)
            })
            markers.value = []

            if (!map.value) return

            // 検索キーワードでフィルタリング
            const spotsToDisplay = spots.value.filter(spot =>
                spot.name.includes(searchKeyword.value)
            )

            // 個別マーカーを表示
            spotsToDisplay.forEach((spot) => {
                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(spot.latitude), lng: parseFloat(spot.longitude) },
                    title: spot.name,
                    icon: {
                        url: '/spot.png',
                        scaledSize: new google.maps.Size(30, 30),
                        anchor: new google.maps.Point(15, 30)
                    },
                    map: map.value
                })

                marker.spotId = spot.id

                marker.addListener('click', () => {
                    openSpotModal(spot.id)
                })

                markers.value.push(marker)
            })
        }

const initMap = async () => {
    // Google Maps APIのライブラリを動的に読み込み
    const { Map } = await google.maps.importLibrary("maps")

    // デフォルトの中心位置（東京）
    let center = { lat: 35.681236, lng: 139.767125 }

    // 現在位置を取得を試行
    if (navigator.geolocation) {
        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 300000 // 5分間キャッシュ
                })
            })

                    center = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    }
        } catch (error) {
            // 現在位置の取得に失敗した場合は東京を表示
        }
    }

    const mapOptions = {
        center: center,
        zoom: 12,
        minZoom: 5, // 最小ズームレベル（日本全体が見える程度）
        maxZoom: 18, // 最大ズームレベル
        streetViewControl: false, // ストリートビューコントロールを無効化
        fullscreenControl: false, // フルスクリーンコントロールを無効化
        mapTypeControl: false, // マップタイプコントロールを無効化
        zoomControl: false, // ズームコントロールを無効化
        disableDefaultUI: true, // デフォルトのUIをすべて無効化
        styles: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            }
        ]
    }

    map.value = new Map(mapContainer.value, mapOptions)

    // マップの読み込み完了後にスポットを読み込み
    google.maps.event.addListenerOnce(map.value, 'idle', () => {
        loadSpots()
    })

    // 地図の移動やズーム時に表示範囲内のスポットを更新（デバウンス）
    let boundsChangedTimeout
    google.maps.event.addListener(map.value, 'bounds_changed', () => {
        clearTimeout(boundsChangedTimeout)
        boundsChangedTimeout = setTimeout(() => {
            updateVisibleSpots()
        }, 300) // 300ms後に実行
    })

    // （削除）zoom_changedの個別監視は行わない（従来挙動に戻す）
}

    // 検索キーワードが変更されたときにマーカーを更新（デバウンス）
    watch(searchKeyword, () => {
        if (map.value) {
            if (updateMarkersTimeout) {
                clearTimeout(updateMarkersTimeout)
            }
            updateMarkersTimeout = setTimeout(() => {
                updateVisibleSpots()
            }, 100)
        }
    })

    // 現在地ボタンのクリック処理
    const getCurrentLocation = () => {
        if (!navigator.geolocation) {
            alert('このブラウザでは位置情報がサポートされていません。')
            return
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                }

                    // マップの中心を現在地に移動
                    map.value.setCenter(userLocation)
                    map.value.setZoom(15) // 現在地では少しズームイン
            },
            (error) => {
                alert('位置情報の取得に失敗しました。')
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 // 5分間キャッシュ
            }
        )
    }

    // マーカーのハイライト機能
    const highlightMarker = (spotId) => {
        const marker = markers.value.find(m => m.spotId === spotId)
        if (marker) {
            // ピンクグラデーションマーカーに変更（ログインボタンと同じ色）
            marker.setIcon({
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#e91e63;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#c2185b;stop-opacity:1" />
                            </linearGradient>
                            <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#000000" flood-opacity="0.3"/>
                            </filter>
                        </defs>
                        <circle cx="15" cy="15" r="12" fill="url(#grad1)" stroke="#ffffff" stroke-width="2" filter="url(#shadow)"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(30, 30),
                anchor: new google.maps.Point(15, 30)
            })
        }
    }

    const unhighlightMarker = (spotId) => {
        const marker = markers.value.find(m => m.spotId === spotId)
        if (marker) {
            // マーカーを元の画像に戻す
            marker.setIcon({
                url: '/spot.png',
                scaledSize: new google.maps.Size(30, 30),
                anchor: new google.maps.Point(15, 30)
            })
        }
    }

// Query parameter handling for spot modal
watch(() => route.query.spotId, (spotId) => {
    if (spotId) {
        initialTab.value = route.query.tab || 'overview'
        openSpotModal(parseInt(spotId))
        router.replace({ path: '/' })
    }
})

onMounted(async () => {
    // Check if spotId is in query params on mount
    if (route.query.spotId) {
        await nextTick()
        initialTab.value = route.query.tab || 'overview'
        openSpotModal(parseInt(route.query.spotId))
        router.replace({ path: '/' })
    }

    // Google Maps APIの読み込み完了を待つ
    await new Promise((resolve) => {
        if (window.google && window.google.maps && window.google.maps.importLibrary) {
            resolve()
        } else {
            const checkGoogle = () => {
                if (window.google && window.google.maps && window.google.maps.importLibrary) {
                    resolve()
                } else {
                    setTimeout(checkGoogle, 100)
                }
            }
            checkGoogle()
        }
    })

    await nextTick()
    await initMap()
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
  top: 80px; /* ヘッダーの高さ分下げる */
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  width: 90%;
  max-width: 400px;
}

/* 現在地ボタンオーバーレイ */
.location-button-overlay {
  position: absolute;
  top: 80px; /* ヘッダーの高さ分下げる */
  right: 20px;
  z-index: 1000;
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

/* 現在地ボタン */
.location-btn {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #28a745, #20c997);
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  color: white;
}

.location-btn:hover {
  background: linear-gradient(135deg, #20c997, #17a2b8);
  box-shadow: 0 6px 16px rgba(40, 167, 69, 0.5);
  transform: translateY(-2px);
}

.location-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
}

/* スポット一覧オーバーレイ */
.spots-overlay {
  position: absolute;
  bottom: 20px;
  right: 20px;
  width: 300px; /* 固定幅でコンパクトに */
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
  padding: 8px 16px; /* 上下の余白を狭く */
  border-radius: 12px 12px 0 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.spots-header h3 {
  margin: 0;


  font-size: 16px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-weight: 400; /* 細く */
  letter-spacing: -0.02em;
  color: #333;
}

.toggle-btn {
  background: linear-gradient(135deg, #20c997, #17a2b8);
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-weight: 500;
  letter-spacing: -0.01em;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(32, 201, 151, 0.3);
}

.toggle-btn:hover {
  background: linear-gradient(135deg, #17a2b8, #138496);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(32, 201, 151, 0.4);
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
  padding: 8px 16px; /* 上下の余白を狭く */
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
            transform: translateX(4px);
            transition: all 0.2s ease;
        }

        .spot-name {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .spot-benefits {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 4px;
        }

        .benefit-tag {
            background: linear-gradient(135deg, #e91e63, #c2185b);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 8px;
            font-weight: 400;
            white-space: nowrap;
        }

/* 検索結果なしメッセージ */
.no-results {
  padding: 16px;
  text-align: center;
  color: #666;
  font-size: 14px;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-style: italic;
}

/* スライドアップアニメーション */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  transform-origin: bottom;
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(30px) scaleY(0);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translateY(30px) scaleY(0);
}

.slide-up-enter-to,
.slide-up-leave-from {
  opacity: 1;
  transform: translateY(0) scaleY(1);
}

/* レスポンシブデザイン */
@media (max-width: 768px) {
  .search-overlay {
    top: 70px; /* モバイルでもヘッダーと被らないように */
    width: 95%;
  }

  .location-button-overlay {
    top: 70px; /* モバイルでもヘッダーと被らないように */
    right: 10px;
  }

  .location-btn {
    width: 44px;
    height: 44px;
  }

  .spots-overlay {
    bottom: 10px;
    right: 10px;
    width: 280px; /* モバイルでは少し小さく */
  }

  .spots-header {
    padding: 6px 12px; /* モバイルでも上下の余白を狭く */
  }

  .spots-header h3 {
    font-size: 14px;
  }

  .spot-item {
    padding: 6px 12px; /* モバイルでも上下の余白を狭く */
    font-size: 13px;
  }
}

</style>
