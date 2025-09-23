<template>
  <div v-if="isOpen" class="modal-overlay" @click="closeModal" :class="{ 'behind-auth-modal': auth.loginModalOpen || auth.signupModalOpen }">
    <div class="modal-content slide-in-left" @click.stop>
      <div class="modal-header">
                <div class="header-info" v-if="spot">
                  <h2 class="header-title">{{ spot.name }}</h2>
                  <div class="header-type" v-if="spotTypes.length > 0">
                    {{ spotTypes[0] }}
                  </div>
                  <div class="header-meta">
                    <span class="header-address">{{ spot.address }}</span>
                    <div class="header-rating" v-if="spot.average_rating">
                      <div class="header-stars">
                        <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= Math.round(spot.average_rating) }">★</span>
                      </div>
                      <span class="header-rating-text">{{ spot.average_rating.toFixed(1) }}</span>
                    </div>
                  </div>

                  <!-- お気に入りボタン -->
                  <div class="favorite-section">
                    <button
                      @click="toggleFavorite"
                      class="favorite-btn"
                      :class="{ 'favorited': isFavorited }"
                      :disabled="favoriteLoading"
                    >
                      <svg v-if="!isFavorited" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.5783 8.50903 2.9987 7.05 2.9987C5.59096 2.9987 4.19169 3.5783 3.16 4.61C2.1283 5.6417 1.5487 7.04097 1.5487 8.5C1.5487 9.95903 2.1283 11.3583 3.16 12.39L12 21.23L20.84 12.39C21.351 11.8792 21.7563 11.2728 22.0329 10.6053C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.06211 22.0329 6.39467C21.7563 5.72723 21.351 5.1208 20.84 4.61Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="heart-filled">
                        <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.5783 8.50903 2.9987 7.05 2.9987C5.59096 2.9987 4.19169 3.5783 3.16 4.61C2.1283 5.6417 1.5487 7.04097 1.5487 8.5C1.5487 9.95903 2.1283 11.3583 3.16 12.39L12 21.23L20.84 12.39C21.351 11.8792 21.7563 11.2728 22.0329 10.6053C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.06211 22.0329 6.39467C21.7563 5.72723 21.351 5.1208 20.84 4.61Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <span class="favorite-text">
                        {{ isFavorited ? 'お気に入り済み' : 'お気に入りに追加' }}
                      </span>
                    </button>
                  </div>
                </div>
        <button @click="closeModal" class="close-btn">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <div class="modal-body">
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          <p>読み込み中...</p>
        </div>

        <div v-else-if="spot" class="spot-detail">

          <!-- タブナビゲーション -->
          <div class="tab-navigation">
            <button
              @click="switchTab('overview')"
              :class="{ active: activeTab === 'overview' }"
              class="tab-btn"
            >
              概要
            </button>
            <button
              @click="switchTab('benefits')"
              :class="{ active: activeTab === 'benefits' }"
              class="tab-btn"
            >
              ご利益
            </button>
            <button
              @click="switchTab('reviews')"
              :class="{ active: activeTab === 'reviews' }"
              class="tab-btn"
            >
              レビュー ({{ reviewCountDisplay }})
            </button>
            <button
              @click="switchTab('photos')"
              :class="{ active: activeTab === 'photos' }"
              class="tab-btn"
            >
              画像アップロード
            </button>
          </div>

          <!-- タブコンテンツ -->
          <div class="tab-content">
            <!-- 概要タブ -->
            <div v-if="activeTab === 'overview'" class="tab-panel">
              <!-- 画像ギャラリー -->
              <ImageGallery v-if="spotImages.length > 0" :images="spotImages" />

              <!-- 営業時間 -->
              <BusinessHours :hours="businessHours" />

              <div class="description-section">
                <p>{{ spot.description || 'このスポットの詳細情報はまだ登録されていません。' }}</p>
              </div>

              <!-- コンパクトなご利益表示 -->
              <div v-if="benefitTypesWithRating.some(b => b.hasRating)" class="benefits-summary">
                <h4>主なご利益</h4>
                <div class="benefits-compact">
                  <span
                    v-for="benefit in benefitTypesWithRating.filter(b => b.hasRating)"
                    :key="benefit.id"
                    class="benefit-tag"
                    :class="benefit.key"
                  >
                    {{ benefit.label }}
                    <span class="benefit-stars">
                      <span v-for="i in benefit.rating" :key="i" class="star">★</span>
                    </span>
                  </span>
                </div>
              </div>

              <!-- 経路リンク -->
              <div class="route-section">
                <a
                  @click="openRouteInMaps"
                  class="route-link"
                >
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 10C21 17 12 23 12 23S3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.3639 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span>現在地からの経路を調べる</span>
                </a>
              </div>
            </div>

            <!-- ご利益タブ -->
            <div v-if="activeTab === 'benefits'" class="tab-panel">
              <div v-if="benefitTypesWithRating.length > 0" class="benefits-section">
                <div class="benefits-grid">
                  <div
                    v-for="benefit in benefitTypesWithRating"
                    :key="benefit.id"
                    class="benefit-item"
                    :class="{ 'no-rating': !benefit.hasRating }"
                  >
                    <div class="benefit-label">{{ benefit.label }}</div>
                    <div class="benefit-rating">
                      <span v-if="benefit.hasRating">
                        <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= benefit.rating }">★</span>
                      </span>
                      <span v-else class="no-rating-text">-</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- レビュータブ -->
            <div v-if="activeTab === 'reviews'" class="tab-panel">
              <div class="reviews-section">
                <div class="reviews-header">
                  <button @click="openReviewForm" class="review-btn" :class="{ 'not-authenticated': !auth.isAuthenticated }">
                    <span v-if="auth.isAuthenticated">レビューを投稿</span>
                    <span v-else>ログインしてレビューを投稿</span>
                  </button>
                </div>

                <div v-if="reviews.length === 0" class="no-reviews">
                  <p>まだレビューがありません。最初のレビューを投稿してみませんか？</p>
                </div>

                <div v-else>
                  <div class="reviews-list">
                    <div v-for="review in paginatedReviews" :key="review.id" class="review-item">
                      <div class="review-header">
                        <div class="reviewer-info">
                          <span class="reviewer-name">{{ review.user?.name || '匿名ユーザー' }}</span>
                          <div class="review-stars">
                            <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= review.rating }">★</span>
                          </div>
                        </div>
                        <span class="review-date">{{ formatDate(review.created_at) }}</span>
                      </div>
                      <p class="review-comment">{{ review.comment }}</p>
                    </div>
                  </div>

                  <!-- ページネーション -->
                  <div v-if="totalReviewPages > 1" class="pagination">
                    <button
                      @click="currentReviewPage = currentReviewPage - 1"
                      :disabled="currentReviewPage === 1"
                      class="pagination-btn"
                    >
                      前
                    </button>
                    <span class="pagination-info">
                      {{ currentReviewPage }} / {{ totalReviewPages }}
                    </span>
                    <button
                      @click="currentReviewPage = currentReviewPage + 1"
                      :disabled="currentReviewPage === totalReviewPages"
                      class="pagination-btn"
                    >
                      次
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- 写真タブ -->
            <div v-if="activeTab === 'photos'" class="tab-panel">
              <div class="photos-section">
                <!-- ログインチェック -->
                <div v-if="!auth.isLoggedIn" class="login-required">
                  <div class="login-message">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                    </svg>
                    <p>画像をアップロードするにはログインが必要です</p>
                    <button @click="auth.openLoginModal" class="login-btn">
                      ログインする
                    </button>
                  </div>
                </div>

                <!-- アップロード機能 -->
                <div v-else>
                  <ImageUploader
                    title="おみくじ写真"
                    description="おみくじの写真をアップロードしてください（最大2枚）"
                    type="omikuji"
                    :spot-id="props.spotId"
                    :max-files="2"
                    :initial-images="omikujiImages"
                    @uploaded="handleImageUploaded"
                    @removed="handleImageRemoved"
                    @error="handleUploadError"
                  />

                  <ImageUploader
                    title="御朱印写真"
                    description="御朱印の写真をアップロードしてください（最大2枚）"
                    type="goshuin"
                    :spot-id="props.spotId"
                    :max-files="2"
                    :initial-images="goshuinImages"
                    @uploaded="handleImageUploaded"
                    @removed="handleImageRemoved"
                    @error="handleUploadError"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="error">
          <p>スポット情報の読み込みに失敗しました</p>
        </div>
      </div>
    </div>
  </div>

  <!-- レビュー投稿フォーム -->
  <ReviewForm
    :isOpen="showReviewForm"
    :spotId="savedSpotId || props.spotId"
    @close="closeReviewForm"
    @success="onReviewSubmitted"
  />

</template>

<script setup>
import { ref, watch, computed, inject, nextTick } from 'vue'
import { useAuthStore } from '../stores/auth'
import ReviewForm from './ReviewForm.vue'
import ImageGallery from './ImageGallery.vue'
import BusinessHours from './BusinessHours.vue'
import ImageUploader from './ImageUploader.vue'
import axios from 'axios'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  spotId: {
    type: [Number, String],
    default: null
  }
})

const emit = defineEmits(['close', 'openReview', 'reopen'])

const auth = useAuthStore()
const spot = ref(null)
const reviews = ref([])
const loading = ref(false)
const showReviewForm = ref(false)
const savedSpotId = ref(null) // レビューフォーム用にspotIdを保存
const allBenefitTypes = ref([]) // 全てのご利益タイプ
const activeTab = ref('overview') // アクティブなタブ
const reviewsPerPage = 20 // ページあたりのレビュー数
const currentReviewPage = ref(1) // 現在のレビューページ
const spotImages = ref([]) // スポットの画像
const businessHours = ref([]) // 営業時間
const spotTypes = ref([]) // スポットのジャンル
const omikujiImages = ref([]) // おみくじ画像
const goshuinImages = ref([]) // 御朱印画像
const isFavorited = ref(false) // お気に入り状態
const favoriteLoading = ref(false) // お気に入り処理中

// ログイン成功時のコールバック関数を取得
const setLoginSuccessCallback = inject('setLoginSuccessCallback')

// 全ご利益タイプと評価を組み合わせたデータを作成
const benefitTypesWithRating = computed(() => {
  if (!spot.value || !allBenefitTypes.value.length) return []

  return allBenefitTypes.value.map(benefitType => {
    const spotBenefit = spot.value.spot_benefits?.find(sb => sb.benefit_type_id === benefitType.id)
    return {
      ...benefitType,
      rating: spotBenefit ? spotBenefit.rating : 0,
      hasRating: !!spotBenefit
    }
  })
})

// ページネーション用のレビュー
const paginatedReviews = computed(() => {
  const startIndex = (currentReviewPage.value - 1) * reviewsPerPage
  const endIndex = startIndex + reviewsPerPage
  return reviews.value.slice(startIndex, endIndex)
})

// 総ページ数
const totalReviewPages = computed(() => {
  return Math.ceil(reviews.value.length / reviewsPerPage)
})

// レビュー件数の表示用computed
const reviewCountDisplay = computed(() => {
  const count = reviews.value.length
  if (count > 99) {
    return '99+'
  }
  return count.toString()
})

// タブ切り替え
const switchTab = (tab) => {
  activeTab.value = tab
  if (tab === 'reviews') {
    currentReviewPage.value = 1
  } else if (tab === 'photos' && auth.isLoggedIn) {
    // 写真タブがアクティブになったときに画像を再読み込み
    loadUserImages()
  }
}

const closeModal = () => {
  emit('close')
}

const openReviewForm = () => {
  // spotIdを保存
  savedSpotId.value = props.spotId

  if (auth.isLoggedIn) {
    showReviewForm.value = true
  } else {
    // ログイン成功後にスポット詳細モーダルを再表示してからレビューフォームを開くコールバックを設定
    if (setLoginSuccessCallback) {
      setLoginSuccessCallback(async () => {
        // まずスポット詳細モーダルを開く（親コンポーネントに通知）
        emit('reopen', savedSpotId.value)
        // DOMの更新を待ってからレビューフォームを開く
        await nextTick()
        setTimeout(() => {
          showReviewForm.value = true
        }, 300) // 少し長めの遅延でモーダルの表示を確実にする
      })
    }
    auth.openLoginModal()
  }
}

const closeReviewForm = () => {
  showReviewForm.value = false
}

// お気に入り状態をチェック
const checkFavoriteStatus = async () => {
  if (!auth.isLoggedIn || !props.spotId) {
    isFavorited.value = false
    return
  }

  try {
    const response = await axios.get(`/api/spots/${props.spotId}/favorite/check`)
    isFavorited.value = response.data.is_favorited
  } catch (error) {
    console.error('お気に入り状態の確認に失敗:', error)
    isFavorited.value = false
  }
}

// お気に入り切り替え
const toggleFavorite = async () => {
  if (!auth.isLoggedIn) {
    // 非ログインの場合はログインフォームを表示
    auth.openLoginModal()
    return
  }

  if (favoriteLoading.value) return

  favoriteLoading.value = true

  try {
    if (isFavorited.value) {
      // お気に入り削除
      await axios.delete(`/api/spots/${props.spotId}/favorite`)
      isFavorited.value = false
    } else {
      // お気に入り追加
      await axios.post(`/api/spots/${props.spotId}/favorite`)
      isFavorited.value = true
    }
  } catch (error) {
    console.error('お気に入り操作に失敗:', error)
    if (error.response?.status === 401) {
      // 認証エラーの場合はログインフォームを表示
      auth.openLoginModal()
    }
  } finally {
    favoriteLoading.value = false
  }
}

const onReviewSubmitted = () => {
  // レビューが投稿されたら一覧を再読み込み
  loadReviews()
}

const loadSpotDetail = async () => {
  if (!props.spotId) return

  loading.value = true
  try {
    const [spotResponse, reviewsResponse, benefitTypesResponse] = await Promise.all([
      axios.get(`/api/spots/${props.spotId}`),
      axios.get(`/api/spots/${props.spotId}/reviews`),
      axios.get('/api/benefit-types')
    ])

    spot.value = spotResponse.data
    reviews.value = reviewsResponse.data
    allBenefitTypes.value = benefitTypesResponse.data

    // Google Places APIから画像と営業時間を取得
    await loadGooglePlacesData(spot.value)

    // ユーザーの画像を読み込み
    await loadUserImages()
  } catch (error) {
    console.error('スポット詳細の読み込みエラー:', error)
  } finally {
    loading.value = false
  }
}

const loadGooglePlacesData = async (spotData) => {
  try {
    // Google Places APIから画像と営業時間を取得
    const response = await axios.get(`/api/spots/${props.spotId}/google-places`)

    if (response.data.images) {
      spotImages.value = response.data.images.map((image, index) => ({
        url: image,
        alt: `${spotData.name}の画像 ${index + 1}`
      }))
    }

    if (response.data.businessHours) {
      businessHours.value = response.data.businessHours
    }

    if (response.data.types) {
      spotTypes.value = response.data.types
    }
  } catch (error) {
    console.error('Google Places データの読み込みエラー:', error)
    // エラーが発生してもアプリは継続動作
  }
}

const loadReviews = async () => {
  if (!props.spotId) return

  try {
    const reviewsResponse = await axios.get(`/api/spots/${props.spotId}/reviews`)
    reviews.value = reviewsResponse.data

    // スポットの平均評価も更新
    const spotResponse = await axios.get(`/api/spots/${props.spotId}`)
    spot.value.average_rating = spotResponse.data.average_rating
  } catch (error) {
    console.error('レビュー読み込みエラー:', error)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('ja-JP')
}

// Google Mapsの経路URLを生成
const getRouteUrl = () => {
  if (!spot.value) return '#'

  const destination = `${spot.value.latitude},${spot.value.longitude}`

  return `https://www.google.com/maps/dir/?api=1&destination=${destination}&destination_place_id=&travelmode=driving`
}

// スマホでGoogle Mapsアプリを優先で開く
const openRouteInMaps = (event) => {
  event.preventDefault()

  if (!spot.value) return

  const destination = `${spot.value.latitude},${spot.value.longitude}`

  const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)

  if (isMobile) {
    const appUrl = `comgooglemaps://?daddr=${destination}&directionsmode=driving`
    const webUrl = `https://www.google.com/maps/dir/?api=1&destination=${destination}&travelmode=driving`

    const testLink = document.createElement('a')
    testLink.href = appUrl
    testLink.style.display = 'none'
    document.body.appendChild(testLink)

    const startTime = Date.now()

    try {
      testLink.click()

      setTimeout(() => {
        if (Date.now() - startTime < 2000) {
          window.open(webUrl, '_blank')
        }
      }, 1000)
    } catch (error) {
      window.open(webUrl, '_blank')
    }

    document.body.removeChild(testLink)
  } else {
    window.open(getRouteUrl(), '_blank')
  }
}

// 画像アップロード成功時の処理
const handleImageUploaded = (image) => {
  if (image.type === 'omikuji') {
    omikujiImages.value.push(image)
  } else if (image.type === 'goshuin') {
    goshuinImages.value.push(image)
  }
}

// 画像削除時の処理
const handleImageRemoved = (image) => {
  if (image.type === 'omikuji') {
    const index = omikujiImages.value.findIndex(img => img.id === image.id)
    if (index !== -1) {
      omikujiImages.value.splice(index, 1)
    }
  } else if (image.type === 'goshuin') {
    const index = goshuinImages.value.findIndex(img => img.id === image.id)
    if (index !== -1) {
      goshuinImages.value.splice(index, 1)
    }
  }
}

// アップロードエラー時の処理
const handleUploadError = (error) => {
  console.error('Upload error:', error)
}

// ユーザーの画像を読み込み
const loadUserImages = async () => {
  if (!auth.isLoggedIn || !props.spotId) return

  try {
    const response = await axios.get(`/api/spots/${props.spotId}/user-images`)
    const images = response.data

    omikujiImages.value = images.filter(img => img.type === 'omikuji')
    goshuinImages.value = images.filter(img => img.type === 'goshuin')
  } catch (error) {
    console.error('Failed to load user images:', error)
  }
}

// spotIdが変更されたときにデータを読み込み
watch(() => props.spotId, (newSpotId) => {
  if (newSpotId && props.isOpen) {
    loadSpotDetail()
  }
}, { immediate: true })

// モーダルが開かれたときにデータを読み込み
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.spotId) {
    savedSpotId.value = props.spotId // モーダルが開かれた時にspotIdを保存
    activeTab.value = 'overview' // 常に概要タブから開始
    currentReviewPage.value = 1 // レビューページもリセット
    loadSpotDetail()
    checkFavoriteStatus() // お気に入り状態をチェック
  }
})

// 認証状態が変更されたときにお気に入り状態をチェック
watch(() => auth.isLoggedIn, (isLoggedIn) => {
  if (isLoggedIn && props.isOpen && props.spotId) {
    checkFavoriteStatus()
    loadUserImages() // ログイン後に画像も再読み込み
  } else if (!isLoggedIn) {
    isFavorited.value = false
    // ログアウト時に画像をクリア
    omikujiImages.value = []
    goshuinImages.value = []
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  z-index: 2000;
  backdrop-filter: blur(4px);
}

.modal-overlay.behind-auth-modal {
  backdrop-filter: blur(8px);
  background: rgba(0, 0, 0, 0.2);
}

.modal-content {
  position: fixed;
  top: 0;
  left: 0;
  width: 420px;
  height: 100vh;
  background: #fafbfc;
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.15);
  overflow-y: auto;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans JP', sans-serif;
}

.slide-in-left {
  animation: slideInLeft 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideInLeft {
  from {
    transform: translateX(-100%);
  }
  to {
    transform: translateX(0);
  }
}

.modal-header {
  position: sticky;
  top: 0;
  background: white;
  padding: 20px 24px;
  border-bottom: 1px solid #e8eaed;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  z-index: 10;
}

.header-info {
  flex: 1;
  min-width: 0;
}

.header-title {
  font-size: 18px;
  font-weight: 500;
  color: #1a1a1a;
  margin: 0 0 6px 0;
  letter-spacing: -0.02em;
  line-height: 1.3;
}

.header-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.header-address {
  color: #5f6368;
  font-size: 13px;
  line-height: 1.4;
}

.header-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}

.header-stars {
  display: flex;
  gap: 1px;
}

.header-stars .star {
  color: #dadce0;
  font-size: 12px;
}

.header-stars .star.filled {
  color: #fbbc04;
}

.header-rating-text {
  color: #5f6368;
  font-size: 12px;
  font-weight: 500;
}

.header-type {
  color: #5f6368;
  font-size: 12px;
  margin-bottom: 8px;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  color: #5f6368;
  transition: all 0.2s ease;
  flex-shrink: 0;
  margin-top: -4px;
}

.close-btn:hover {
  background: #f1f3f4;
  color: #1a1a1a;
}

.modal-body {
  padding: 0;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #5f6368;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 2px solid #f1f3f4;
  border-top: 2px solid #e91e63;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* タブナビゲーション */
.tab-navigation {
  display: flex;
  background: white;
  border-bottom: 1px solid #e8eaed;
  padding: 0 24px;
}

.tab-btn {
  background: none;
  border: none;
  padding: 16px 20px;
  font-size: 14px;
  font-weight: 500;
  color: #5f6368;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 2px solid transparent;
  position: relative;
}

.tab-btn:hover {
  color: #1a1a1a;
  background: #f8f9fa;
}

.tab-btn.active {
  color: #e91e63;
  border-bottom-color: #e91e63;
}

.tab-content {
  background: white;
  min-height: 400px;
}

.tab-panel {
  padding: 24px;
}

.spot-header {
  margin-bottom: 32px;
}

.spot-title {
  font-size: 28px;
  font-weight: 500;
  color: #111827;
  margin-bottom: 8px;
  line-height: 1.2;
  letter-spacing: -0.01em;
}

.spot-address {
  color: #6b7280;
  margin-bottom: 16px;
  font-size: 14px;
}

.spot-rating {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stars {
  display: flex;
  gap: 2px;
}

.star {
  color: #d1d5db;
  font-size: 16px;
}

.star.filled {
  color: #fbbf24;
}

.rating-text {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.spot-content {
  space-y: 32px;
}

.benefits-section {
  margin-bottom: 24px;
}

.benefits-section h3 {
  font-size: 16px;
  font-weight: 500;
  color: #1a1a1a;
  margin-bottom: 16px;
}

.benefits-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 8px;
}

.benefit-item {
  background: #f8f9fa;
  border: 1px solid #e8eaed;
  border-radius: 8px;
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.2s ease;
}

.benefit-item.no-rating {
  background: #f1f3f4;
  border-color: #dadce0;
  opacity: 0.7;
}

.benefit-label {
  font-weight: 500;
  color: #1a1a1a;
  font-size: 14px;
}

.benefit-item.no-rating .benefit-label {
  color: #5f6368;
}

.benefit-rating {
  display: flex;
  gap: 2px;
  align-items: center;
}

.benefit-rating .star {
  font-size: 14px;
  color: #dadce0;
}

.benefit-rating .star.filled {
  color: #fbbc04;
}

.no-rating-text {
  color: #9aa0a6;
  font-size: 14px;
  font-weight: 400;
}

.description-section {
  margin-bottom: 24px;
}

.description-section p {
  color: #3c4043;
  line-height: 1.6;
  font-size: 14px;
}

.reviews-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.review-btn {
  background: #e91e63;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 120px;
  text-align: center;
}

.review-btn:hover {
  background: #c2185b;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

/* 非ログイン時のボタンスタイル */
.review-btn.not-authenticated {
  background: #5f6368;
}

.review-btn.not-authenticated:hover {
  background: #3c4043;
  box-shadow: 0 2px 8px rgba(95, 99, 104, 0.3);
}

.reviews-list {
  margin-top: 8px;
}

.reviews-list > .review-item:not(:last-child) {
  margin-bottom: 16px;
}

.review-item {
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e8eaed;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.reviewer-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.reviewer-name {
  font-weight: 500;
  color: #1a1a1a;
  font-size: 14px;
}

.review-stars {
  display: flex;
  gap: 1px;
}

.review-stars .star {
  font-size: 14px;
}

.review-date {
  font-size: 12px;
  color: #5f6368;
}

.review-comment {
  color: #3c4043;
  line-height: 1.5;
  margin: 0;
  font-size: 14px;
}

.no-reviews {
  text-align: center;
  padding: 40px 20px;
  color: #5f6368;
}

.error {
  text-align: center;
  padding: 60px 20px;
  color: #ea4335;
}

/* コンパクトなご利益表示 */
.benefits-summary {
  margin-bottom: 24px;
}

.benefits-summary h4 {
  font-size: 15px;
  font-weight: 500;
  color: #1a1a1a;
  margin-bottom: 12px;
}

.benefits-compact {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.benefit-tag {
  display: flex;
  align-items: center;
  gap: 4px;
  border-radius: 16px;
  padding: 4px 12px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s ease;
}

/* 恋愛運 - ピンク */
.benefit-tag.love {
  background: #fce4ec;
  border: 1px solid #f48fb1;
  color: #ad1457;
}

/* 金運 - ゴールド */
.benefit-tag.money {
  background: #fff8e1;
  border: 1px solid #ffc107;
  color: #f57f17;
}

/* 仕事運 - ブルー */
.benefit-tag.job {
  background: #e3f2fd;
  border: 1px solid #2196f3;
  color: #1565c0;
}

/* 健康運 - グリーン */
.benefit-tag.health {
  background: #e8f5e8;
  border: 1px solid #4caf50;
  color: #2e7d32;
}

/* 願望成就 - パープル */
.benefit-tag.wish {
  background: #f3e5f5;
  border: 1px solid #9c27b0;
  color: #7b1fa2;
}

.benefit-stars {
  display: flex;
  gap: 1px;
}

.benefit-stars .star {
  font-size: 12px;
  color: #9ca3af !important;
}

/* ページネーション */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  padding: 16px 0;
}

.pagination-btn {
  background: #f8f9fa;
  border: 1px solid #e8eaed;
  border-radius: 6px;
  padding: 8px 12px;
  font-size: 14px;
  color: #3c4043;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #e8eaed;
  color: #1a1a1a;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn.active {
  background: #e91e63;
  color: white;
  border-color: #e91e63;
}

.pagination-info {
  font-size: 13px;
  color: #5f6368;
  margin: 0 8px;
}

/* 写真セクション */
.photos-section {
  text-align: center;
  padding: 40px 20px;
  color: #5f6368;
}

.upload-area {
  border: 2px dashed #dadce0;
  border-radius: 8px;
  padding: 40px 20px;
  background: #f8f9fa;
  transition: all 0.2s ease;
}

.upload-area:hover {
  border-color: #e91e63;
  background: #fef7f0;
}

.upload-placeholder {
  font-size: 14px;
  color: #5f6368;
}

/* タブナビゲーション */
.tab-navigation {
  display: flex;
  border-bottom: 2px solid #f3f4f6;
  margin-bottom: 24px;
  gap: 2px;
}

.tab-btn {
  flex: 1;
  padding: 12px 16px;
  border: none;
  background: none;
  color: #6b7280;
  font-weight: 400;
  font-size: 14px;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.2s ease;
  letter-spacing: -0.005em;
}

.tab-btn:hover {
  color: #374151;
  background: #f9fafb;
}

.tab-btn.active {
  color: #e91e63;
  border-bottom-color: #e91e63;
  background: #fef7f0;
  font-weight: 500;
}

/* タブコンテンツ */
.tab-content {
  min-height: 300px;
}

.tab-panel {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* 概要タブ */
.description-section {
  margin-bottom: 24px;
}

.description-section p {
  color: #374151;
  line-height: 1.6;
  margin: 0;
}

.benefits-summary {
  background: #f8fafc;
  border-radius: 12px;
  padding: 16px;
}

.benefits-summary h4 {
  color: #111827;
  font-size: 16px;
  font-weight: 500;
  margin: 0 0 12px 0;
  letter-spacing: -0.01em;
}

.benefits-compact {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.benefit-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: linear-gradient(135deg, #e91e63, #c2185b);
  color: white;
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 16px;
  font-weight: 500;
}

.benefit-stars {
  display: inline-flex;
}

.benefit-stars .star {
  font-size: 10px;
  color: rgba(255, 255, 255, 0.9);
}

/* レビューヘッダー */
.reviews-header {
  margin-bottom: 32px;
}

/* レビューページネーション */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 24px;
  padding: 16px 0;
}

.pagination-btn {
  background: #e91e63;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #c2185b;
  transform: translateY(-1px);
}

.pagination-btn:disabled {
  background: #d1d5db;
  cursor: not-allowed;
  transform: none;
}

.pagination-info {
  color: #6b7280;
  font-size: 14px;
  font-weight: 500;
}

/* 写真アップロードエリア */
.photos-section {
  padding: 24px 0;
}

.login-required {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
}

.login-message {
  text-align: center;
  padding: 48px 24px;
  background: #f8f9fa;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  max-width: 400px;
}

.login-message svg {
  margin-bottom: 16px;
  color: #6b7280;
}

.login-message p {
  color: #4b5563;
  font-size: 16px;
  margin-bottom: 20px;
}

.login-btn {
  background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.login-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
}

.upload-area p {
  color: #6b7280;
  margin-bottom: 24px;
}

.upload-placeholder {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 40px 20px;
  background: #f9fafb;
  transition: all 0.2s ease;
}

.upload-placeholder:hover {
  border-color: #e91e63;
  background: #fef7f0;
}

.upload-placeholder svg {
  margin-bottom: 16px;
}

.upload-placeholder p {
  color: #9ca3af;
  font-size: 14px;
  margin: 0;
}

/* お気に入りボタン */
.favorite-section {
  margin-top: 16px;
}

.favorite-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  color: #6b7280;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  justify-content: center;
}

.favorite-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
  color: #374151;
}

.favorite-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.favorite-btn.favorited {
  background: white;
  border-color: #e5e7eb;
  color: #6b7280;
}

.favorite-btn.favorited:hover {
  background: #f9fafb;
  border-color: #d1d5db;
  color: #374151;
}

.favorite-btn.favorited svg {
  color: #e91e63;
}

.favorite-btn svg {
  flex-shrink: 0;
  transition: all 0.3s ease;
}

/* ハートマークのアニメーション */
.heart-filled {
  animation: heartBeat 0.6s ease-in-out;
}

@keyframes heartBeat {
  0% {
    transform: scale(1);
  }
  25% {
    transform: scale(1.3);
  }
  50% {
    transform: scale(1.1);
  }
  75% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}

/* ハートマークのホバー効果 */
.favorite-btn:hover svg {
  transform: scale(1.1);
}

.favorite-btn.favorited:hover svg {
  transform: scale(1.1);
  filter: brightness(1.1);
}

.favorite-text {
  font-size: 14px;
  font-weight: 500;
}

/* 経路リンク */
.route-section {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e8eaed;
}

.route-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  background: #f8f9fa;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  color: #374151;
  text-decoration: none;
  font-size: 15px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.route-link:hover {
  background: #f8f9fa;
  border-color: #d1d5db;
  color: #374151;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.route-link svg {
  flex-shrink: 0;
  transition: all 0.2s ease;
  color: #6b7280;
}

.route-link:hover svg {
  transform: scale(1.1);
  color: #4285f4; /* Google Mapsの青 */
}

/* レスポンシブデザイン */
@media (max-width: 768px) {
  .modal-content {
    width: 100vw;
  }

  .spot-title {
    font-size: 24px;
  }

  .modal-body {
    padding: 0 16px 16px;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>
