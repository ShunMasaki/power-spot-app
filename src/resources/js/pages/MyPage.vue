<template>
  <div class="my-page">
    <!-- ログインプロンプト -->
    <div v-if="!auth.isLoggedIn" class="login-prompt">
      <div class="prompt-content">
        <div class="prompt-icon">🔒</div>
        <h2>マイページを表示するにはログインしてください</h2>
        <p>ログインすると、お気に入りのスポットや訪問履歴を確認できます</p>
      </div>
    </div>

    <!-- マイページコンテンツ -->
    <div v-else class="page-content">
      <!-- ヘッダー -->
      <div class="header">
        <h1>マイページ</h1>
        <p class="welcome-message">ようこそ、{{ stats.nickname || 'ユーザー' }}さん</p>
      </div>

      <!-- 統計セクション -->
      <div class="stats-section">
        <div class="stat-card" @click="activeTab = 'visits'">
          <div class="stat-icon">
            <img :src="shrineIcon" alt="訪問済み" />
          </div>
          <div class="stat-info">
            <div class="stat-label">訪問済み</div>
            <div class="stat-value">{{ stats.visits }}</div>
          </div>
        </div>

        <div class="stat-card" @click="activeTab = 'favorites'">
          <div class="stat-icon">
            <img :src="goodIcon" alt="お気に入り" />
          </div>
          <div class="stat-info">
            <div class="stat-label">お気に入り</div>
            <div class="stat-value">{{ stats.favorites }}</div>
          </div>
        </div>

        <div class="stat-card" @click="activeTab = 'reviews'">
          <div class="stat-icon">
            <img :src="pencilIcon" alt="レビュー" />
          </div>
          <div class="stat-info">
            <div class="stat-label">レビュー</div>
            <div class="stat-value">{{ stats.reviews }}</div>
          </div>
        </div>

        <div class="stat-card" @click="activeTab = 'images'">
          <div class="stat-icon">
            <img :src="cameraIcon" alt="写真" />
          </div>
          <div class="stat-info">
            <div class="stat-label">写真</div>
            <div class="stat-value">{{ stats.images }}</div>
          </div>
        </div>
      </div>

      <!-- タブナビゲーション -->
      <div class="tab-navigation">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="['tab-btn', { active: activeTab === tab.id }]"
        >
          <span class="tab-icon">
            <img :src="tab.icon" :alt="tab.label" />
          </span>
          {{ tab.label }}
        </button>
      </div>

      <!-- タブコンテンツ -->
      <div class="tab-content">
        <!-- ローディング -->
        <div v-if="loading" class="loading">
          <div class="loading-spinner"></div>
          <p>読み込み中...</p>
        </div>

        <!-- 訪問履歴タブ -->
        <div v-else-if="activeTab === 'visits'">
          <div v-if="visits.length === 0" class="empty-state">
            <div class="empty-icon">
              <img :src="shrineIcon" alt="empty" />
            </div>
            <p>まだ訪問したスポットがありません</p>
            <p class="empty-hint">スポット詳細ページから「訪問済み」をマークしてみましょう</p>
          </div>
          <div v-else class="items-list">
            <div
              v-for="(visit, index) in visits"
              :key="visit.id"
              :class="['visit-item', 'item-card', { 'initial-load': !initialLoaded.visits && index < 10 }]"
              :style="{ animationDelay: !initialLoaded.visits && index < 10 ? `${index * 0.05}s` : '0s' }"
              @click="openSpotDetail(visit.spot.id)"
            >
              <div class="item-thumbnail">
                <img v-if="visit.thumbnail_image" :src="visit.thumbnail_image" alt="thumbnail" class="thumbnail-image" />
                <div v-else class="thumbnail-placeholder">
                  <img :src="shrineIcon" alt="shrine" />
                </div>
              </div>
              <div class="item-details">
                <div class="item-header">
                  <h3 class="item-name">{{ visit.spot.name }}</h3>
                </div>
                <p class="item-address">
                  {{ visit.spot.address }}
                </p>
                <div v-if="visit.spot.spot_benefits && visit.spot.spot_benefits.length > 0" class="item-benefits">
                  <span
                    v-for="benefit in visit.spot.spot_benefits"
                    :key="benefit.id"
                    class="benefit-tag"
                  >
                    {{ benefit.benefit_type.label || benefit.benefit_type.name }}
                  </span>
                </div>
                <p class="visit-date">
                  <img :src="cameraIcon" alt="date" class="date-icon" />
                  {{ formatDate(visit.visited_at) }}
                </p>
              </div>
            </div>
            <div v-if="pagination.visits.hasMore" class="load-more-container">
              <button @click="loadMoreData" class="load-more-btn" :disabled="loading">
                {{ loading ? '読み込み中...' : 'もっと見る' }}
              </button>
            </div>
          </div>
        </div>

        <!-- お気に入りタブ -->
        <div v-else-if="activeTab === 'favorites'">
          <div v-if="favorites.length === 0" class="empty-state">
            <div class="empty-icon">
              <img :src="goodIcon" alt="empty" />
            </div>
            <p>まだお気に入りのスポットがありません</p>
            <p class="empty-hint">スポット詳細ページから「お気に入り」に追加してみましょう</p>
          </div>
          <div v-else class="items-list">
            <div
              v-for="(favorite, index) in favorites"
              :key="favorite.id"
              :class="['favorite-item', 'item-card', { 'initial-load': !initialLoaded.favorites && index < 10 }]"
              :style="{ animationDelay: !initialLoaded.favorites && index < 10 ? `${index * 0.05}s` : '0s' }"
              @click="openSpotDetail(favorite.spot.id)"
            >
              <div class="item-thumbnail">
                <img v-if="favorite.thumbnail_image" :src="favorite.thumbnail_image" alt="thumbnail" class="thumbnail-image" />
                <div v-else class="thumbnail-placeholder">
                  <img :src="shrineIcon" alt="shrine" />
                </div>
              </div>
              <div class="item-details">
                <div class="item-header">
                  <h3 class="item-name">{{ favorite.spot.name }}</h3>
                </div>
                <p class="item-address">
                  {{ favorite.spot.address }}
                </p>
                <div v-if="favorite.benefits && favorite.benefits.length > 0" class="item-benefits">
                  <span
                    v-for="(benefit, idx) in favorite.benefits"
                    :key="idx"
                    class="benefit-tag"
                  >
                    {{ benefit }}
                  </span>
                </div>
              </div>
            </div>
            <div v-if="pagination.favorites.hasMore" class="load-more-container">
              <button @click="loadMoreData" class="load-more-btn" :disabled="loading">
                {{ loading ? '読み込み中...' : 'もっと見る' }}
              </button>
            </div>
          </div>
        </div>

        <!-- レビュータブ -->
        <div v-else-if="activeTab === 'reviews'">
          <div v-if="reviews.length === 0" class="empty-state">
            <div class="empty-icon">
              <img :src="pencilIcon" alt="empty" />
            </div>
            <p>まだレビューがありません</p>
            <p class="empty-hint">スポットを訪れたら、感想を書いてみましょう</p>
          </div>
          <div v-else class="items-list">
            <div
              v-for="(review, index) in reviews"
              :key="review.id"
              :class="['review-item', 'item-card', { 'initial-load': !initialLoaded.reviews && index < 10 }]"
              :style="{ animationDelay: !initialLoaded.reviews && index < 10 ? `${index * 0.05}s` : '0s' }"
              @click="openSpotDetailWithReview(review.spot.id)"
            >
              <div class="item-details">
                <div class="item-header">
                  <h3 class="item-name">{{ review.spot.name }}</h3>
                </div>
                <div class="review-rating">
                  <span v-for="star in 5" :key="star" :class="['star', { filled: star <= review.rating }]">
                    ★
                  </span>
                </div>
                <p class="review-comment">{{ review.comment }}</p>
                <p class="review-date">
                  {{ formatDate(review.created_at) }}
                </p>
              </div>
            </div>
            <div v-if="pagination.reviews.hasMore" class="load-more-container">
              <button @click="loadMoreData" class="load-more-btn" :disabled="loading">
                {{ loading ? '読み込み中...' : 'もっと見る' }}
              </button>
            </div>
          </div>
        </div>

        <!-- 写真タブ -->
        <div v-else-if="activeTab === 'images'" class="images-section">
          <!-- おみくじ写真 -->
          <div class="image-category">
            <div class="category-header">
              <img :src="tagIcon" alt="tag" class="category-header-icon" />
              <h3 class="category-title">マイおみくじ</h3>
              <span class="image-count">({{ omikujiImagesFiltered.length }}枚)</span>
            </div>
            <div v-if="omikujiImagesFiltered.length === 0" class="empty-category">
              <p>まだおみくじの写真がありません</p>
            </div>
            <div v-else class="images-scroll">
              <div
                v-for="image in omikujiImagesFiltered"
                :key="image.id"
                class="image-card-scroll"
              >
                <img :src="image.url" :alt="image.spot_name" class="scroll-thumbnail" @click="openImageModal(image)" />
                <p class="image-spot-name" @click="openSpotDetail(image.spot_id)">{{ image.spot_name }}</p>
              </div>
            </div>
          </div>

          <!-- 御朱印写真 -->
          <div class="image-category">
            <div class="category-header">
              <img :src="tagIcon" alt="tag" class="category-header-icon" />
              <h3 class="category-title">マイ御朱印</h3>
              <span class="image-count">({{ goshuinImagesFiltered.length }}枚)</span>
            </div>
            <div v-if="goshuinImagesFiltered.length === 0" class="empty-category">
              <p>まだ御朱印の写真がありません</p>
            </div>
            <div v-else class="images-scroll">
              <div
                v-for="image in goshuinImagesFiltered"
                :key="image.id"
                class="image-card-scroll"
              >
                <img :src="image.url" :alt="image.spot_name" class="scroll-thumbnail" @click="openImageModal(image)" />
                <p class="image-spot-name" @click="openSpotDetail(image.spot_id)">{{ image.spot_name }}</p>
              </div>
            </div>
          </div>
          <div v-if="pagination.images.hasMore" class="load-more-container">
            <button @click="loadMoreData" class="load-more-btn" :disabled="loading">
              {{ loading ? '読み込み中...' : 'もっと見る' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 画像拡大モーダル -->
    <div v-if="selectedImage" class="image-modal-overlay" @click="closeImageModal">
      <div class="image-modal-content" @click.stop>
        <button @click="closeImageModal" class="image-modal-close">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <!-- 前の画像ボタン -->
        <button @click.stop="showPreviousImage" class="image-nav-btn prev-btn">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <!-- 次の画像ボタン -->
        <button @click.stop="showNextImage" class="image-nav-btn next-btn">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <div class="modal-image-container">
          <transition name="image-fade" mode="out-in">
            <img
              :key="selectedImage.id"
              :src="selectedImage.url"
              :alt="selectedImage.spot_name"
              class="modal-image"
            />
          </transition>
          <transition name="fade" mode="out-in">
            <div :key="selectedImage.id" class="image-overlay-info">
              <p class="modal-spot-name-overlay">{{ selectedImage.spot_name }}</p>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

// Import icons
import spotIcon from '../assets/icons/spot.png';
import goodIcon from '../assets/icons/good.png';
import pencilIcon from '../assets/icons/pencil.png';
import cameraIcon from '../assets/icons/camera.png';
import shrineIcon from '../assets/icons/shrine.png';
import tagIcon from '../assets/icons/tag.png';

const router = useRouter();
const auth = useAuthStore();

// State
const loading = ref(false);
const activeTab = ref('visits');
const showSpotsList = ref(true);

// Data
const stats = ref({
  nickname: 'ユーザー',
  visits: 0,
  favorites: 0,
  reviews: 0,
  images: 0
});

const visits = ref([]);
const favorites = ref([]);
const reviews = ref([]);
const images = ref([]);
const selectedImage = ref(null);

// Pagination state
const pagination = ref({
  visits: { page: 1, hasMore: true },
  favorites: { page: 1, hasMore: true },
  reviews: { page: 1, hasMore: true },
  images: { page: 1, hasMore: true }
});

// Initial load flags
const initialLoaded = ref({
  visits: false,
  favorites: false,
  reviews: false,
  images: false
});


// Tabs configuration
const tabs = [
  { id: 'visits', label: '訪問済み', icon: shrineIcon },
  { id: 'favorites', label: 'お気に入り', icon: goodIcon },
  { id: 'reviews', label: 'レビュー', icon: pencilIcon },
  { id: 'images', label: '写真', icon: cameraIcon }
];

// Computed: Filter images by type
const omikujiImagesFiltered = computed(() => {
  return images.value.filter(img => img.type === 'omikuji');
});

const goshuinImagesFiltered = computed(() => {
  return images.value.filter(img => img.type === 'goshuin');
});

// 現在表示中の画像のインデックス
const currentImageIndex = computed(() => {
  if (!selectedImage.value) return -1;
  return images.value.findIndex(img => img.id === selectedImage.value.id);
});

// Methods
const loadStats = async () => {
  try {
    const response = await axios.get('/api/user/stats');
    stats.value = response.data;
  } catch (error) {
    console.error('統計情報の読み込みに失敗しました:', error);
    // 認証エラーの場合はログアウト状態にする
    if (error.response?.status === 401) {
      auth.isLoggedIn = false;
    }
  }
};

const loadVisits = async (append = false) => {
  if (!append) {
    loading.value = true;
    pagination.value.visits.page = 1;
  }

  try {
    const response = await axios.get('/api/user/visits', {
      params: {
        page: pagination.value.visits.page,
        per_page: 20
      }
    });

    if (append) {
      visits.value = [...visits.value, ...response.data.data];
    } else {
      visits.value = response.data.data;
    }

    pagination.value.visits.hasMore = response.data.current_page < response.data.last_page;
    if (!initialLoaded.value.visits) {
      initialLoaded.value.visits = true;
    }
  } catch (error) {
    console.error('訪問履歴の読み込みに失敗しました:', error);
  } finally {
    if (!append) {
      loading.value = false;
    }
  }
};

const loadFavorites = async (append = false) => {
  if (!append) {
    loading.value = true;
    pagination.value.favorites.page = 1;
  }

  try {
    const response = await axios.get('/api/user/favorites', {
      params: {
        page: pagination.value.favorites.page,
        per_page: 20
      }
    });

    if (append) {
      favorites.value = [...favorites.value, ...response.data.data];
    } else {
      favorites.value = response.data.data;
    }

    pagination.value.favorites.hasMore = response.data.current_page < response.data.last_page;
    if (!initialLoaded.value.favorites) {
      initialLoaded.value.favorites = true;
    }
  } catch (error) {
    console.error('お気に入りの読み込みに失敗しました:', error);
  } finally {
    if (!append) {
      loading.value = false;
    }
  }
};

const loadReviews = async (append = false) => {
  if (!append) {
    loading.value = true;
    pagination.value.reviews.page = 1;
  }

  try {
    const response = await axios.get('/api/user/reviews', {
      params: {
        page: pagination.value.reviews.page,
        per_page: 20
      }
    });

    if (append) {
      reviews.value = [...reviews.value, ...response.data.data];
    } else {
      reviews.value = response.data.data;
    }

    pagination.value.reviews.hasMore = response.data.current_page < response.data.last_page;
    if (!initialLoaded.value.reviews) {
      initialLoaded.value.reviews = true;
    }
  } catch (error) {
    console.error('レビューの読み込みに失敗しました:', error);
  } finally {
    if (!append) {
      loading.value = false;
    }
  }
};

const loadImages = async (append = false) => {
  if (!append) {
    loading.value = true;
    pagination.value.images.page = 1;
  }

  try {
    const response = await axios.get('/api/user/images', {
      params: {
        page: pagination.value.images.page,
        per_page: 20
      }
    });

    if (append) {
      images.value = [...images.value, ...response.data.data];
    } else {
      images.value = response.data.data;
    }

    pagination.value.images.hasMore = response.data.current_page < response.data.last_page;
  } catch (error) {
    console.error('写真の読み込みに失敗しました:', error);
  } finally {
    if (!append) {
      loading.value = false;
    }
  }
};

const openImageModal = (image) => {
  selectedImage.value = image;
};

const closeImageModal = () => {
  selectedImage.value = null;
};

const showPreviousImage = () => {
  const totalImages = images.value.length;
  if (totalImages === 0) return;

  let newIndex = currentImageIndex.value - 1;
  if (newIndex < 0) {
    newIndex = totalImages - 1; // 最初の画像から最後の画像へループ
  }
  selectedImage.value = images.value[newIndex];
};

const showNextImage = () => {
  const totalImages = images.value.length;
  if (totalImages === 0) return;

  let newIndex = currentImageIndex.value + 1;
  if (newIndex >= totalImages) {
    newIndex = 0; // 最後の画像から最初の画像へループ
  }
  selectedImage.value = images.value[newIndex];
};

const loadTabData = (tabId) => {
  if (loading.value) return;

  switch (tabId) {
    case 'visits':
      if (!initialLoaded.value.visits) loadVisits();
      break;
    case 'favorites':
      if (!initialLoaded.value.favorites) loadFavorites();
      break;
    case 'reviews':
      if (!initialLoaded.value.reviews) loadReviews();
      break;
    case 'images':
      loadImages();
      break;
  }
};

const loadMoreData = () => {
  const currentPagination = pagination.value[activeTab.value];

  if (!currentPagination.hasMore || loading.value) {
    return;
  }

  pagination.value[activeTab.value].page++;

  switch (activeTab.value) {
    case 'visits':
      loadVisits(true);
      break;
    case 'favorites':
      loadFavorites(true);
      break;
    case 'reviews':
      loadReviews(true);
      break;
    case 'images':
      loadImages(true);
      break;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
};

const openSpotDetail = (spotId) => {
  router.push({ path: '/', query: { spotId } });
};

const openSpotDetailWithReview = (spotId) => {
  router.push({ path: '/', query: { spotId, tab: 'reviews' } });
};

// Watchers
watch(() => auth.isLoggedIn, (newValue) => {
  if (newValue) {
    loadStats();
    loadTabData(activeTab.value);
  } else {
    // Reset data
    visits.value = [];
    favorites.value = [];
    reviews.value = [];
    images.value = [];
    stats.value = {
      nickname: 'ユーザー',
      visits: 0,
      favorites: 0,
      reviews: 0,
      images: 0
    };
    // Reset pagination
    pagination.value = {
      visits: { page: 1, hasMore: true },
      favorites: { page: 1, hasMore: true },
      reviews: { page: 1, hasMore: true },
      images: { page: 1, hasMore: true }
    };
    // Reset initial loaded flags
    initialLoaded.value = {
      visits: false,
      favorites: false,
      reviews: false,
      images: false
    };
  }
});

watch(activeTab, (newTab) => {
  loadTabData(newTab);
});

// Lifecycle
onMounted(() => {
  if (auth.isLoggedIn) {
    loadStats();
    loadTabData(activeTab.value);
  }
});
</script>

<style scoped>
.my-page {
  min-height: 100vh;
  background: #f8f9fa; /* 落ち着いたグレー背景 */
  padding-top: 80px; /* 固定ヘッダー分の余白 */
}

/* ログインプロンプト */
.login-prompt {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  padding: 20px;
}

.prompt-content {
  text-align: center;
  background: white;
  padding: 48px 32px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  max-width: 500px;
}

.prompt-icon {
  font-size: 64px;
  margin-bottom: 24px;
}

.prompt-content h2 {
  color: #d946a6;
  margin-bottom: 16px;
  font-size: 24px;
}

.prompt-content p {
  color: #666;
  margin-bottom: 32px;
  line-height: 1.6;
}


/* ページコンテンツ */
.page-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 80px; /* Wrapper的にしっかり余白 */
}

.header {
  text-align: center;
  margin-bottom: 32px;
}

.header h1 {
  color: #333;
  font-size: 32px;
  margin-bottom: 8px;
}

.welcome-message {
  color: #666;
  font-size: 16px;
}

/* 統計セクション */
.stats-section {
  display: grid;
  grid-template-columns: repeat(2, 1fr); /* 2カラム固定 */
  gap: 20px;
  margin-bottom: 32px;
  padding-bottom: 32px;
  border-bottom: 2px solid #e0e0e0;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 56px;
  height: 56px;
  background: #f0f0f0; /* 落ち着いたグレー */
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon img {
  width: 32px;
  height: 32px;
}

.stat-info {
  flex: 1;
}

.stat-label {
  color: #666;
  font-size: 14px;
  margin-bottom: 4px;
}

.stat-value {
  color: #e91e63; /* 少し落ち着いたピンク */
  font-size: 28px;
  font-weight: 700;
}

/* タブナビゲーション */
.tab-navigation {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  overflow-x: auto;
  padding: 4px;
}

.tab-btn {
  flex: 1;
  min-width: 120px;
  padding: 12px 20px;
  border: 2px solid #e0e0e0;
  background: white;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 15px;
  font-weight: 500;
  color: #666;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.tab-icon img {
  width: 20px;
  height: 20px;
}

.tab-btn:hover {
  background: #f5f5f5;
  border-color: #bdbdbd;
}

.tab-btn.active {
  background: #e0f2fe; /* 薄い水色 */
  color: #0369a1; /* 濃い水色のテキスト */
  border-color: #bae6fd;
}

/* タブコンテンツ */
.tab-content {
  min-height: 400px;
}

/* ローディング */
.loading {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e0e0e0;
  border-top-color: #e91e63;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* 空状態 */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 24px;
  background: #f0f0f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-icon img {
  width: 48px;
  height: 48px;
}

.empty-state p {
  color: #666;
  font-size: 16px;
  margin-bottom: 8px;
}

.empty-hint {
  color: #999;
  font-size: 14px;
}

/* アイテムリスト */
.items-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.item-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  cursor: pointer;
  transition: background 0.2s ease;
  display: flex;
  gap: 16px;
  opacity: 1;
}

.item-card.initial-load {
  opacity: 0;
  animation: slideInUp 0.4s ease-out forwards;
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.item-card:hover {
  background: #f5f5f5;
}

.item-thumbnail {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.thumbnail-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.item-card:hover .thumbnail-image {
  transform: scale(1.05);
}

.thumbnail-placeholder {
  width: 100%;
  height: 100%;
  background: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.thumbnail-placeholder img {
  width: 32px;
  height: 32px;
}

.item-details {
  flex: 1;
  min-width: 0;
}

.item-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.item-icon img,
.address-icon,
.date-icon {
  width: 16px;
  height: 16px;
}

.item-icon {
  flex-shrink: 0;
}

.item-name {
  color: #333;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.item-address {
  color: #666;
  font-size: 14px;
  margin: 8px 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.address-icon,
.date-icon {
  margin-right: 6px;
  vertical-align: middle;
}

.item-benefits {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 8px 0;
}

.benefit-tag {
  background: #e91e63;
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 8px;
  font-weight: 400;
  white-space: nowrap;
}

.visit-date,
.review-date {
  color: #999;
  font-size: 13px;
  margin-top: 8px;
  display: flex;
  align-items: center;
}

/* レビュー固有 */
.review-item {
  flex-direction: column;
}

.review-item .item-thumbnail {
  margin-top: 12px;
}

.review-rating {
  margin: 8px 0;
}

.star {
  color: #ddd;
  font-size: 18px;
}

.star.filled {
  color: #fbbf24;
}

.review-comment {
  color: #555;
  font-size: 15px;
  line-height: 1.6;
  margin: 12px 0;
}

/* 写真グリッド */
.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.image-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  cursor: pointer;
  transition: all 0.3s ease;
}

.image-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
}

.image-thumbnail {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.image-info {
  padding: 16px;
}

.image-type {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #e91e63;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;
}

.category-icon-img {
  width: 16px;
  height: 16px;
}

.image-spot-name {
  color: #333;
  font-size: 15px;
  font-weight: 500;
  margin: 0;
}

/* 画像セクション（おみくじ・御朱印） */
.images-section {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.image-category {
  width: 100%;
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.category-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 2px solid #f0f0f0;
}

.category-header-icon {
  width: auto;
  height: 28px;
  max-width: 40px;
  object-fit: contain;
}

.category-title {
  font-size: 20px;
  font-weight: 700;
  color: #333;
  margin: 0;
}

.image-count {
  font-size: 14px;
  color: #999;
  font-weight: 500;
  margin-left: auto;
}

.empty-category {
  text-align: center;
  padding: 48px 20px;
  color: #999;
  font-size: 15px;
  background: #fafbfc;
  border-radius: 12px;
  border: 2px dashed #e0e0e0;
}

.images-scroll {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  padding: 8px 4px;
  scrollbar-width: thin;
  scrollbar-color: #e0e0e0 transparent;
}

.images-scroll::-webkit-scrollbar {
  height: 8px;
}

.images-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.images-scroll::-webkit-scrollbar-thumb {
  background: #e0e0e0;
  border-radius: 4px;
}

.images-scroll::-webkit-scrollbar-thumb:hover {
  background: #bdbdbd;
}

.image-card-scroll {
  flex: 0 0 auto;
  width: 200px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.image-card-scroll:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.scroll-thumbnail {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
  cursor: pointer;
  transition: opacity 0.3s ease;
}

.scroll-thumbnail:hover {
  opacity: 0.85;
}

.image-card-scroll .image-spot-name {
  padding: 12px;
  font-size: 14px;
  color: #333;
  font-weight: 500;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  transition: all 0.3s ease;
}

.image-card-scroll .image-spot-name:hover {
  color: #e91e63;
  background: #f5f5f5;
}

/* 画像拡大モーダル */
.image-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.image-modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-modal-close {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(0, 0, 0, 0.5);
  border: none;
  color: white;
  cursor: pointer;
  padding: 12px;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  z-index: 10;
}

.image-modal-close:hover {
  background: rgba(0, 0, 0, 0.8);
  transform: scale(1.1);
}

.image-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.5);
  border: none;
  color: white;
  cursor: pointer;
  padding: 16px;
  border-radius: 50%;
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  z-index: 10;
}

.image-nav-btn:hover {
  background: rgba(0, 0, 0, 0.8);
  transform: translateY(-50%) scale(1.1);
}

.prev-btn {
  left: 20px;
}

.next-btn {
  right: 20px;
}

.modal-image-container {
  position: relative;
  max-width: 90vw;
  max-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-image {
  max-width: 90vw;
  max-height: 80vh;
  width: auto;
  height: auto;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.image-overlay-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 50%, transparent 100%);
  padding: 60px 24px 24px;
  border-radius: 0 0 8px 8px;
  text-align: center;
}

.modal-spot-name-overlay {
  font-size: 18px;
  font-weight: 600;
  color: white;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

/* 画像切り替えアニメーション */
.image-fade-enter-active,
.image-fade-leave-active {
  transition: all 0.4s ease;
}

.image-fade-enter-from {
  opacity: 0;
  transform: scale(0.95);
}

.image-fade-leave-to {
  opacity: 0;
  transform: scale(1.05);
}

.image-fade-enter-to,
.image-fade-leave-from {
  opacity: 1;
  transform: scale(1);
}

/* テキストフェードアニメーション */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
}

/* レスポンシブ */
@media (max-width: 768px) {
  .page-content {
    padding: 24px 20px; /* モバイルでは適度に */
  }
  .stats-section {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .stat-card {
    padding: 16px;
  }

  .stat-icon {
    width: 48px;
    height: 48px;
  }

  .stat-icon img {
    width: 24px;
    height: 24px;
  }

  .tab-btn {
    min-width: 100px;
    padding: 10px 16px;
    font-size: 14px;
  }

  .image-category {
    padding: 16px;
  }

  .category-header {
    margin-bottom: 16px;
    padding-bottom: 12px;
  }

  .category-header-icon {
    width: auto;
    height: 24px;
    max-width: 36px;
  }

  .category-title {
    font-size: 18px;
  }

  .image-count {
    font-size: 13px;
  }

  .empty-category {
    padding: 32px 16px;
    font-size: 14px;
  }

  .image-card-scroll {
    width: 160px;
  }

  .scroll-thumbnail {
    height: 160px;
  }

  .image-modal-close {
    top: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    padding: 8px;
  }

  .image-nav-btn {
    width: 48px;
    height: 48px;
    padding: 12px;
  }

  .prev-btn {
    left: 10px;
  }

  .next-btn {
    right: 10px;
  }

  .modal-image-container {
    max-width: 95vw;
    max-height: 70vh;
  }

  .modal-image {
    max-width: 95vw;
    max-height: 70vh;
  }

  .image-overlay-info {
    padding: 48px 16px 16px;
  }

  .modal-spot-name-overlay {
    font-size: 16px;
  }

  .images-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
  }
}

/* もっと見るボタン */
.load-more-container {
  display: flex;
  justify-content: center;
  padding: 24px 0;
  margin-top: 16px;
}

.load-more-btn {
  padding: 12px 32px;
  background: #fff;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  color: #666;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.load-more-btn:hover:not(:disabled) {
  background: #f8f9fa;
  border-color: #ccc;
  color: #333;
}

.load-more-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
