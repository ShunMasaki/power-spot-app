<template>
  <div v-if="isOpen" class="review-form-overlay" @click="closeForm">
    <div class="review-form-content" @click.stop>
      <div class="form-header">
        <h3>レビューを投稿</h3>
        <button @click="closeForm" class="close-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <form @submit.prevent="submitReview" class="review-form">
        <div class="form-group">
          <label class="form-label">評価</label>
          <div class="rating-input">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="setRating(star)"
              class="star-btn"
              :class="{ active: star <= rating }"
            >
              ★
            </button>
          </div>
        </div>

        <div class="form-group">
          <label for="comment" class="form-label">コメント</label>
          <textarea
            id="comment"
            v-model="comment"
            class="form-textarea"
            placeholder="このスポットについての感想をお聞かせください..."
            rows="4"
            required
          ></textarea>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <div class="form-actions">
          <button type="button" @click="closeForm" class="cancel-btn">
            キャンセル
          </button>
          <button type="submit" :disabled="!rating || !comment.trim() || submitting" class="submit-btn">
            <span v-if="submitting">投稿中...</span>
            <span v-else>投稿する</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
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

const emit = defineEmits(['close', 'success'])

const rating = ref(0)
const comment = ref('')
const submitting = ref(false)
const errorMessage = ref('')

const setRating = (star) => {
  rating.value = star
}

const closeForm = () => {
  emit('close')
}

const resetForm = () => {
  rating.value = 0
  comment.value = ''
  submitting.value = false
  errorMessage.value = ''
}

const submitReview = async () => {
  if (!rating.value || !comment.value.trim()) return

  const spotIdToUse = props.spotId

  if (!spotIdToUse) {
    alert('スポットIDが取得できませんでした。もう一度お試しください。')
    return
  }

  submitting.value = true
  errorMessage.value = '' // エラーメッセージをクリア

  try {
    await axios.post(`/api/spots/${spotIdToUse}/reviews`, {
      rating: rating.value,
      comment: comment.value.trim()
    })

    emit('success')
    resetForm()
    closeForm()
  } catch (error) {
    console.error('レビュー投稿エラー:', error)

    // バリデーションエラーの場合は具体的なメッセージを表示
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors
      if (errors && errors.comment && errors.comment.length > 0) {
        errorMessage.value = errors.comment[0]
      } else if (errors && errors.rating && errors.rating.length > 0) {
        errorMessage.value = errors.rating[0]
      } else {
        errorMessage.value = '入力内容に問題があります。確認してください。'
      }
    } else {
      errorMessage.value = 'レビューの投稿に失敗しました。もう一度お試しください。'
    }
  } finally {
    submitting.value = false
  }
}

// モーダルが開かれた時にフォームをリセット
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    resetForm()
  }
})
</script>

<style scoped>
.review-form-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(3px);
  z-index: 2500;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.review-form-content {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  font-family: 'Inter', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 0;
  margin-bottom: 24px;
}

.form-header h3 {
  font-size: 20px;
  font-weight: 400;
  color: #111827;
  letter-spacing: -0.01em;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  color: #6b7280;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.review-form {
  padding: 0 24px 24px;
}

.form-group {
  margin-bottom: 24px;
}

.form-label {
  display: block;
  font-size: 14px;
  font-weight: 400;
  color: #374151;
  margin-bottom: 8px;
  letter-spacing: -0.005em;
}

.rating-input {
  display: flex;
  gap: 4px;
}

.star-btn {
  background: none;
  border: none;
  font-size: 28px;
  color: #d1d5db;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 4px;
  border-radius: 4px;
}

.star-btn:hover {
  color: #fbbf24;
  transform: scale(1.1);
}

.star-btn.active {
  color: #fbbf24;
}

.form-textarea {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  transition: border-color 0.2s ease;
  box-sizing: border-box;
}

.form-textarea:focus {
  outline: none;
  border-color: #e91e63;
  box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.cancel-btn {
  padding: 10px 20px;
  border: 2px solid #e5e7eb;
  background: white;
  color: #6b7280;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 400;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  border-color: #d1d5db;
  background: #f9fafb;
}

.submit-btn {
  padding: 10px 20px;
  background: linear-gradient(135deg, #e91e63, #c2185b);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 400;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 100px;
}

.submit-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #c2185b, #ad1457);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.error-message {
  background: #fee2e2;
  color: #dc2626;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 14px;
  margin-bottom: 16px;
  border: 1px solid #fecaca;
}

/* モバイル対応 */
@media (max-width: 768px) {
  .review-form-overlay {
    padding: 10px;
  }

  .form-header {
    padding: 20px 20px 0;
  }

  .review-form {
    padding: 0 20px 20px;
  }

  .form-actions {
    flex-direction: column;
  }

  .cancel-btn,
  .submit-btn {
    width: 100%;
  }
}
</style>
