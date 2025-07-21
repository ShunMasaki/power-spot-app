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

        <div v-if="reviews.length">
            <h3>レビュー一覧</h3>
            <ul>
                <li v-for="review in reviews" :key="review.id" class="review-item">
                <p><strong>評価：</strong>★{{ review.rating }}</p>
                <p><strong>コメント：</strong>{{ review.comment }}</p>
                <p v-if="review.user"><small>投稿者：{{ review.user.name }}</small></p>
                <p><small>投稿日：{{ formatDate(review.created_at) }}</small></p>
                </li>
            </ul>
        </div>

        <div v-if="isLoggedIn" class="review-form">
            <h3>レビューを投稿する</h3>
            <label>
                評価（1〜5）:
                <select v-model="newReview.rating">
                    <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                </select>
            </label>
            <br />
            <label>
                コメント:
                <textarea v-model="newReview.comment" rows="3" placeholder="未入力でも投稿できます（任意）"></textarea>
            </label>
            <br />

            <p v-if="errorMessage" style="color: red;">{{ errorMessage }}</p>

            <button @click="submitReview">投稿する</button>
        </div>
        <div v-else>
            <p>レビュー投稿にはログインが必要です。</p>
        </div>
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
const reviews = ref([])
const formatDate = (datetime) => {
    const date = new Date(datetime)
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
}
const newReview = ref({
    rating: 5,
    comment: ''
})

const fetchReviews = async () => {
    try {
        const res = await axios.get(`/api/spots/${route.params.id}/reviews`)
        reviews.value = res.data
    } catch (error) {
        console.log('レビュー取得エラー:', error)
    }
}

const errorMessage = ref('')
// 仮のログイン状態（後で連携）
const isLoggedIn = ref(true)

const submitReview = async () => {
    errorMessage.value = ''

    if (!newReview.value.comment.trim()) {
        newReview.value.comment = 'コメントなし'
    }
    if (newReview.value.comment.length > 300) {
        errorMessage.value = 'コメントは300文字以内で入力してください。'
        return
    }

    try {
        await axios.post(`/api/spots/${route.params.id}/reviews`, newReview.value)
        alert('レビューを投稿しました')
        newReview.value = { rating: 5, comment: '' }
        await fetchReviews()
    } catch (error) {
        if (error.response?.status === 422) {
            // Laravelからのバリデーションエラー
            const errors = error.response.data.errors
            if (errors.comment) {
                errorMessage.value = errors.comment[0]
            } else if (errors.rating) {
                errorMessage.value = errors.rating[0]
            } else {
                errorMessage.value = '入力内容に誤りがあります。'
            }
        } else {
            console.log('レビュー投稿エラー:', error)
            alert('投稿に失敗しました')
        }
    }
}

onMounted(async () => {
try {
    const res = await axios.get(`/api/spots/${route.params.id}`)
    spot.value = res.data
    await fetchReviews()
} catch (error) {
    console.error('スポット取得エラー:', error)
}
})
</script>
