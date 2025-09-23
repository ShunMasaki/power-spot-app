<template>
  <div class="image-gallery">
    <!-- メイン画像 -->
    <div class="main-image-container" @click="openFullscreen">
      <img
        v-if="currentImage"
        :src="currentImage.url"
        :alt="currentImage.alt || `画像 ${currentIndex + 1}`"
        class="main-image"
      />
      <div v-else class="no-image-placeholder">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M21 19V5C21 3.9 20.1 3 19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19ZM8.5 13.5L11 16.51L14.5 12L19 18H5L8.5 13.5Z" fill="#9CA3AF"/>
        </svg>
        <p>画像がありません</p>
      </div>

      <!-- メイン画像のナビゲーションボタン -->
      <button
        v-if="images.length > 1"
        @click="previousImage"
        class="main-nav-btn prev-btn"
        :disabled="currentIndex === 0"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <button
        v-if="images.length > 1"
        @click="nextImage"
        class="main-nav-btn next-btn"
        :disabled="currentIndex === images.length - 1"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <!-- サムネイル一覧 -->
    <div v-if="images.length > 1" class="thumbnail-list">
      <div
        v-for="(image, index) in images"
        :key="index"
        class="thumbnail-item"
        :class="{ active: index === currentIndex }"
        @click="setCurrentIndex(index)"
      >
        <img :src="image.url" :alt="image.alt || `画像 ${index + 1}`" />
      </div>
    </div>

    <!-- 画像カウンター -->
    <div v-if="images.length > 1" class="image-counter">
      {{ currentIndex + 1 }} / {{ images.length }}
    </div>
  </div>

  <!-- 全画面表示モーダル -->
  <div v-if="showFullscreen" class="fullscreen-overlay" @click="closeFullscreen">
    <div class="fullscreen-content" @click.stop>
      <button @click="closeFullscreen" class="fullscreen-close-btn">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <div class="fullscreen-image-container">
        <img
          v-if="currentImage"
          :src="currentImage.url"
          :alt="currentImage.alt || `画像 ${currentIndex + 1}`"
          class="fullscreen-image"
        />
      </div>

      <!-- 全画面ナビゲーション -->
      <div v-if="images.length > 1" class="fullscreen-nav">
        <button
          @click="previousImage"
          class="fullscreen-nav-btn prev"
          :disabled="currentIndex === 0"
        >
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button
          @click="nextImage"
          class="fullscreen-nav-btn next"
          :disabled="currentIndex === images.length - 1"
        >
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <!-- 全画面カウンター -->
      <div v-if="images.length > 1" class="fullscreen-counter">
        {{ currentIndex + 1 }} / {{ images.length }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  images: {
    type: Array,
    default: () => []
  }
})

const currentIndex = ref(0)
const showFullscreen = ref(false)

const currentImage = computed(() => {
  return props.images[currentIndex.value] || null
})

const setCurrentIndex = (index) => {
  currentIndex.value = index
}

const nextImage = () => {
  if (currentIndex.value < props.images.length - 1) {
    currentIndex.value++
  }
}

const previousImage = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
  }
}

const openFullscreen = () => {
  if (props.images.length > 0) {
    showFullscreen.value = true
  }
}

const closeFullscreen = () => {
  showFullscreen.value = false
}

// キーボードナビゲーション
const handleKeydown = (event) => {
  if (!showFullscreen.value) return

  switch (event.key) {
    case 'Escape':
      closeFullscreen()
      break
    case 'ArrowLeft':
      previousImage()
      break
    case 'ArrowRight':
      nextImage()
      break
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.image-gallery {
  width: 100%;
  margin-bottom: 24px;
}

/* サムネイル一覧 */
.thumbnail-list {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  overflow-x: auto;
  padding: 4px 0;
}

.thumbnail-item {
  flex: 0 0 60px;
  height: 60px;
  cursor: pointer;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.thumbnail-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.thumbnail-item:hover img {
  transform: scale(1.1);
}

.thumbnail-item.active {
  border-color: #e91e63;
  box-shadow: 0 0 0 1px rgba(233, 30, 99, 0.3);
}

/* メイン画像のナビゲーションボタン */
.main-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.6);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: white;
  transition: all 0.2s ease;
  z-index: 10;
}

.main-nav-btn:hover:not(:disabled) {
  background: rgba(0, 0, 0, 0.8);
  transform: translateY(-50%) scale(1.1);
}

.main-nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.main-nav-btn.prev-btn {
  left: 12px;
}

.main-nav-btn.next-btn {
  right: 12px;
}

.main-image-container {
  width: 100%;
  height: 300px;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.main-image-container:hover .main-image {
  transform: scale(1.02);
}

.no-image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  text-align: center;
}

.no-image-placeholder p {
  margin: 8px 0 0 0;
  font-size: 14px;
}

.image-counter {
  text-align: center;
  color: #6b7280;
  font-size: 12px;
  margin-top: 8px;
}

/* 全画面表示 */
.fullscreen-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.95);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.3s ease;
}

.fullscreen-content {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fullscreen-close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: white;
  transition: all 0.2s ease;
  z-index: 10;
}

.fullscreen-close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.fullscreen-image-container {
  max-width: 90%;
  max-height: 90%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fullscreen-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 8px;
}

.fullscreen-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 100%;
  display: flex;
  justify-content: space-between;
  padding: 0 20px;
  pointer-events: none;
}

.fullscreen-nav-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: white;
  transition: all 0.2s ease;
  pointer-events: auto;
}

.fullscreen-nav-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
}

.fullscreen-nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.fullscreen-counter {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* レスポンシブ */
@media (max-width: 768px) {
  .thumbnail-item {
    height: 150px;
  }

  .main-image-container {
    height: 250px;
  }

  .nav-btn {
    width: 28px;
    height: 28px;
  }

  .fullscreen-close-btn {
    top: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
  }

  .fullscreen-nav-btn {
    width: 40px;
    height: 40px;
  }
}
</style>
