<template>
  <div class="image-uploader" :data-type="type">
    <div class="upload-section">
      <h4 class="upload-title">{{ title }}</h4>

      <!-- アップロードエリア -->
      <div
        class="upload-area"
        :class="{ 'drag-over': isDragOver && !isMobile, 'uploading': isUploading, 'mobile': isMobile }"
        @dragover.prevent="!isMobile && handleDragOver"
        @dragleave.prevent="!isMobile && handleDragLeave"
        @drop.prevent="!isMobile && handleDrop"
        @click="triggerFileInput"
      >
        <input
          ref="fileInput"
          type="file"
          multiple
          accept="image/*"
          @change="handleFileSelect"
          style="display: none"
        />

        <!-- スマホ用カメラ撮影 -->
        <input
          ref="cameraInput"
          type="file"
          accept="image/*"
          capture="environment"
          @change="handleCameraCapture"
          style="display: none"
        />

        <div v-if="!isUploading" class="upload-content">
          <!-- 装飾的背景パターン -->
          <div class="upload-pattern" v-if="type === 'omikuji'">
            <div class="pattern-fortune" v-for="n in 4" :key="n" :style="{
              left: Math.random() * 100 + '%',
              top: Math.random() * 100 + '%',
              animationDelay: Math.random() * 3 + 's'
            }">🍀</div>
          </div>

          <div class="upload-pattern" v-if="type === 'goshuin'">
            <div class="pattern-seal" v-for="n in 3" :key="n" :style="{
              left: Math.random() * 100 + '%',
              top: Math.random() * 100 + '%',
              animationDelay: Math.random() * 3 + 's'
            }">🌸</div>
          </div>

          <!-- タイプ別アイコン -->
          <div class="upload-icon">
            <!-- おみくじアイコン -->
            <svg v-if="type === 'omikuji'" width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="type-icon omikuji-icon">
              <!-- フラットなおみくじ箱デザイン -->
              <rect x="3" y="8" width="18" height="12" rx="2" fill="#f59e0b"/>
              <rect x="5" y="10" width="14" height="8" rx="1" fill="#fff"/>
              <rect x="7" y="12" width="10" height="1" fill="#f59e0b"/>
              <rect x="7" y="14" width="8" height="1" fill="#f59e0b"/>
              <rect x="7" y="16" width="6" height="1" fill="#f59e0b"/>
              <!-- おみくじ棒 -->
              <rect x="10" y="4" width="4" height="6" rx="2" fill="#8b4513"/>
              <circle cx="12" cy="5" r="1" fill="#f59e0b"/>
            </svg>

            <!-- 御朱印アイコン -->
            <svg v-else-if="type === 'goshuin'" width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="type-icon goshuin-icon">
              <rect x="3" y="4" width="18" height="16" rx="2" fill="#dc2626"/>
              <rect x="5" y="6" width="14" height="12" rx="1" fill="#fff"/>
              <rect x="7" y="9" width="10" height="1" fill="#dc2626"/>
              <rect x="7" y="12" width="8" height="1" fill="#dc2626"/>
              <rect x="7" y="15" width="6" height="1" fill="#dc2626"/>
              <circle cx="16" cy="14" r="2" fill="#dc2626"/>
            </svg>

            <!-- デフォルトアイコン -->
            <svg v-else width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="type-icon default-icon">
              <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" fill="#9CA3AF"/>
            </svg>
          </div>

          <p class="upload-text">
            <span v-if="!isMobile">クリックまたはドラッグ&ドロップ</span>
            <span v-else>タップして画像を選択</span>
          </p>
          <p class="upload-limit">{{ remainingSlots }}枚追加可能</p>

          <!-- スマホ用カメラボタン -->
          <div v-if="isMobile && remainingSlots > 0" class="camera-actions">
            <button @click.stop="triggerCamera" class="camera-btn" type="button">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 15.5C13.933 15.5 15.5 13.933 15.5 12S13.933 8.5 12 8.5 8.5 10.067 8.5 12 10.067 15.5 12 15.5Z" stroke="currentColor" stroke-width="1.5"/>
                <path d="M12 15.5C13.933 15.5 15.5 13.933 15.5 12S13.933 8.5 12 8.5 8.5 10.067 8.5 12 10.067 15.5 12 15.5Z" stroke="currentColor" stroke-width="1.5"/>
                <path d="M17 4H16.5L15 2H9L7.5 4H7C5.9 4 5 4.9 5 6V18C5 19.1 5.9 20 7 20H17C18.1 20 19 19.1 19 18V6C19 4.9 18.1 4 17 4Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              カメラで撮影
            </button>
          </div>
        </div>

        <div v-else class="uploading-content">
          <div class="spinner"></div>
          <p>アップロード中...</p>
        </div>
      </div>

      <!-- エラーメッセージ -->
      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <!-- アップロード済み画像一覧 -->
      <div v-if="images.length > 0" class="uploaded-images">
        <div
          v-for="(image, index) in images"
          :key="image.id || index"
          class="image-item"
        >
          <img :src="image.url" :alt="`${title} ${index + 1}`" />
          <button
            @click="removeImage(index)"
            class="remove-btn"
            :disabled="isUploading"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- 削除確認モーダル -->
    <div v-if="showDeleteConfirm" class="confirm-modal-overlay" @click="cancelDelete">
      <div class="confirm-modal" @click.stop>
        <div class="confirm-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h3 class="confirm-title">画像を削除しますか？</h3>
        <p class="confirm-message">この操作は取り消せません。</p>
        <div class="confirm-actions">
          <button @click="cancelDelete" class="confirm-btn cancel-btn">キャンセル</button>
          <button @click="confirmDelete" class="confirm-btn delete-btn">削除する</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, defineProps, defineEmits } from 'vue'
import axios from 'axios'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    required: true, // 'omikuji' or 'goshuin'
    validator: value => ['omikuji', 'goshuin'].includes(value)
  },
  spotId: {
    type: [String, Number],
    required: true
  },
  maxFiles: {
    type: Number,
    default: 2
  },
  initialImages: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['uploaded', 'removed', 'error'])

// リアクティブデータ
const images = ref([...props.initialImages])
const isDragOver = ref(false)
const isUploading = ref(false)
const errorMessage = ref('')
const fileInput = ref(null)
const cameraInput = ref(null)
const showDeleteConfirm = ref(false)
const deleteTargetIndex = ref(null)

// モバイルデバイス判定
const isMobile = computed(() => {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
})

// initialImagesが変更されたときに画像リストを更新
watch(() => props.initialImages, (newImages) => {
  images.value = [...newImages]
}, { deep: true, immediate: true })

// 計算プロパティ
const remainingSlots = computed(() => {
  return Math.max(0, props.maxFiles - images.value.length)
})

// ファイル選択トリガー
const triggerFileInput = () => {
  if (remainingSlots.value > 0 && !isUploading.value) {
    fileInput.value.click()
  }
}

// カメラ撮影トリガー
const triggerCamera = () => {
  if (remainingSlots.value > 0 && !isUploading.value) {
    cameraInput.value.click()
  }
}

// ドラッグオーバー処理
const handleDragOver = (event) => {
  if (remainingSlots.value > 0 && !isUploading.value) {
    isDragOver.value = true
  }
}

const handleDragLeave = () => {
  isDragOver.value = false
}

// ドロップ処理
const handleDrop = (event) => {
  isDragOver.value = false
  if (remainingSlots.value > 0 && !isUploading.value) {
    const files = Array.from(event.dataTransfer.files)
    handleFiles(files)
  }
}

// ファイル選択処理
const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  handleFiles(files)
  // ファイル入力をリセット
  event.target.value = ''
}

// カメラキャプチャー処理
const handleCameraCapture = (event) => {
  const files = Array.from(event.target.files)
  if (files.length > 0) {
    // カメラで撮影した画像にタイムスタンプ付きファイル名を設定
    const capturedFile = files[0]
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-')
    const newFileName = `camera_${timestamp}.jpg`

    // Fileオブジェクトのnameプロパティは読み取り専用なので、新しいFileオブジェクトを作成
    const renamedFile = new File([capturedFile], newFileName, {
      type: capturedFile.type,
      lastModified: capturedFile.lastModified,
    })

    handleFiles([renamedFile])
  }
  // ファイル入力をリセット
  event.target.value = ''
}

// ファイル処理
const handleFiles = (files) => {
  errorMessage.value = ''

  // 画像ファイルのみフィルタリング
  const imageFiles = files.filter(file => file.type.startsWith('image/'))

  if (imageFiles.length === 0) {
    errorMessage.value = '画像ファイルを選択してください'
    return
  }

  // アップロード可能な枚数をチェック
  const filesToUpload = imageFiles.slice(0, remainingSlots.value)

  if (filesToUpload.length < imageFiles.length) {
    errorMessage.value = `最大${props.maxFiles}枚まで選択可能です`
  }

  // ファイルサイズチェック (5MB制限)
  const oversizedFiles = filesToUpload.filter(file => file.size > 5 * 1024 * 1024)
  if (oversizedFiles.length > 0) {
    errorMessage.value = 'ファイルサイズは5MB以下にしてください'
    return
  }

  // アップロード実行
  uploadFiles(filesToUpload)
}

// ファイルアップロード
const uploadFiles = async (files) => {
  if (files.length === 0) return

  isUploading.value = true
  errorMessage.value = ''

  try {
    for (const file of files) {
      await uploadSingleFile(file)
    }
  } catch (error) {
    console.error('Upload error:', error)
    errorMessage.value = 'アップロードに失敗しました'
    emit('error', error)
  } finally {
    isUploading.value = false
  }
}

// 単一ファイルアップロード
const uploadSingleFile = async (file) => {
  const formData = new FormData()
  formData.append('image', file)
  formData.append('type', props.type)
  formData.append('spot_id', props.spotId)

  const response = await axios.post(`/api/spots/${props.spotId}/images`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })

  const newImage = response.data
  images.value.push(newImage)

  emit('uploaded', newImage)
}

// 画像削除（確認モーダルを表示）
const removeImage = (index) => {
  if (isUploading.value) return
  deleteTargetIndex.value = index
  showDeleteConfirm.value = true
}

// 削除をキャンセル
const cancelDelete = () => {
  showDeleteConfirm.value = false
  deleteTargetIndex.value = null
}

// 削除を確定
const confirmDelete = async () => {
  const index = deleteTargetIndex.value
  const image = images.value[index]

  try {
    if (image.id) {
      await axios.delete(`/api/spots/${props.spotId}/images/${image.id}`)
    }

    images.value.splice(index, 1)
    emit('removed', image)
  } catch (error) {
    console.error('Delete error:', error)
    errorMessage.value = '削除に失敗しました'
    emit('error', error)
  } finally {
    showDeleteConfirm.value = false
    deleteTargetIndex.value = null
  }
}
</script>

<style scoped>
.image-uploader {
  margin-bottom: 48px;
  padding: 24px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.upload-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 2px solid #f3f4f6;
}

.upload-description {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 16px;
}

.upload-area {
  border: none;
  border-radius: 12px;
  padding: 32px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #fafbfc;
  margin-top: 16px;
  position: relative;
  overflow: hidden;
}

/* タイプ別背景色 */
.image-uploader[data-type="omikuji"] .upload-area {
  background: #fffbeb;
}

.image-uploader[data-type="goshuin"] .upload-area {
  background: #fef2f2;
}

.upload-area:hover {
  background: #fafbfc;
}

.image-uploader[data-type="omikuji"] .upload-area:hover {
  background: #fffbf0;
}

.image-uploader[data-type="goshuin"] .upload-area:hover {
  background: #fffafa;
}

.upload-area.drag-over {
  background: #fdf2f8;
}

.upload-area.uploading {
  cursor: not-allowed;
  opacity: 0.7;
}

.upload-area.mobile {
  cursor: pointer;
}

.upload-area.mobile:hover {
  background: #f9fafb;
}

.image-uploader[data-type="omikuji"] .upload-area.mobile:hover {
  background: #fef3c7;
}

.image-uploader[data-type="goshuin"] .upload-area.mobile:hover {
  background: #fee2e2;
}

/* アイコンコンテナ */
.upload-icon {
  margin-bottom: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.type-icon {
  transition: all 0.3s ease;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}

.omikuji-icon {
  animation: pulse 2s ease-in-out infinite;
}

.goshuin-icon {
  animation: pulse 2s ease-in-out infinite;
}

.default-icon {
  opacity: 0.7;
}

/* アニメーション */

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

/* 装飾パターン */
.upload-pattern {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 0;
}

.pattern-fortune, .pattern-seal {
  position: absolute;
  font-size: 16px;
  opacity: 0.3;
  animation: gentle-drift 6s ease-in-out infinite;
}

.pattern-seal {
  animation: gentle-float 5s ease-in-out infinite;
}

@keyframes gentle-drift {
  0%, 100% { opacity: 0.2; transform: translateX(0px) translateY(0px) rotate(0deg); }
  25% { opacity: 0.4; transform: translateX(10px) translateY(-5px) rotate(90deg); }
  50% { opacity: 0.3; transform: translateX(-5px) translateY(-10px) rotate(180deg); }
  75% { opacity: 0.4; transform: translateX(-10px) translateY(5px) rotate(270deg); }
}

@keyframes gentle-float {
  0%, 100% { opacity: 0.2; transform: translateY(0px) rotate(0deg); }
  33% { opacity: 0.4; transform: translateY(-10px) rotate(120deg); }
  66% { opacity: 0.3; transform: translateY(5px) rotate(240deg); }
}

/* ホバー時のアイコン効果 */
.upload-area:hover .omikuji-icon {
  transform: scale(1.1);
  filter: drop-shadow(0 6px 12px rgba(245, 158, 11, 0.3));
}

.upload-area:hover .goshuin-icon {
  transform: scale(1.1);
  filter: drop-shadow(0 6px 12px rgba(220, 38, 38, 0.3));
}

/* ホバー時に装飾パターンを強調 */
.upload-area:hover .pattern-fortune {
  opacity: 0.6;
  animation-duration: 3s;
}

.upload-area:hover .pattern-seal {
  opacity: 0.6;
  animation-duration: 3s;
}

.upload-content {
  position: relative;
  z-index: 1;
}

.upload-content svg {
  margin-bottom: 16px;
}

.upload-text {
  font-size: 16px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.upload-limit {
  font-size: 14px;
  color: #6b7280;
}

.camera-actions {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.camera-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.camera-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.camera-btn:active {
  transform: translateY(0);
}

.camera-btn svg {
  flex-shrink: 0;
}

.uploading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top: 3px solid #ec4899;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  margin-top: 12px;
  padding: 12px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  color: #dc2626;
  font-size: 14px;
}

.uploaded-images {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 16px;
}

.image-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.image-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 24px;
  height: 24px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.remove-btn:hover {
  background: rgba(220, 38, 38, 0.8);
}

.remove-btn:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

/* 削除確認モーダル */
.confirm-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.confirm-modal {
  background: white;
  border-radius: 16px;
  padding: 32px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  text-align: center;
}

.confirm-icon {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.confirm-title {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.confirm-message {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 24px 0;
}

.confirm-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.confirm-btn {
  padding: 10px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cancel-btn {
  background: #f3f4f6;
  color: #374151;
}

.cancel-btn:hover {
  background: #e5e7eb;
}

.delete-btn {
  background: #ef4444;
  color: white;
}

.delete-btn:hover {
  background: #dc2626;
}
</style>
