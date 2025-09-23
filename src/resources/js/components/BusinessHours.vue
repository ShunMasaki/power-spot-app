<template>
  <div class="business-hours">
    <!-- ヘッダー部分（常に表示） -->
    <div class="hours-header" @click="toggleAccordion">
      <div class="header-content">
        <h4 class="hours-title">営業時間</h4>
        <div v-if="todayHours" class="today-hours">
          <span class="today-label">本日</span>
          <span class="today-time">{{ formatHours(todayHours) }}</span>
        </div>
      </div>
      <div class="accordion-icon" :class="{ 'expanded': isExpanded }">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    </div>

    <!-- アコーディオン内容 -->
    <div class="hours-content" :class="{ 'expanded': isExpanded }">
      <div v-if="hours && hours.length > 0" class="hours-list">
        <div
          v-for="(dayHours, index) in hours"
          :key="index"
          class="hours-item"
          :class="{ 'today': isToday(index) }"
        >
          <span class="day">{{ getDayName(index) }}</span>
          <span class="time">{{ formatHours(dayHours) }}</span>
        </div>
      </div>
      <div v-else class="no-hours">
        <p>営業時間情報がありません</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  hours: {
    type: Array,
    default: () => []
  }
})

const isExpanded = ref(false)

const dayNames = ['日', '月', '火', '水', '木', '金', '土']

const getDayName = (index) => {
  return dayNames[index] || ''
}

const isToday = (index) => {
  return new Date().getDay() === index
}

const todayHours = computed(() => {
  if (!props.hours || props.hours.length === 0) return null
  const todayIndex = new Date().getDay()
  return props.hours[todayIndex] || null
})

const formatHours = (dayHours) => {
  if (!dayHours || dayHours.length === 0) {
    return '定休日'
  }

  if (dayHours.length === 1) {
    return dayHours[0]
  }

  // 複数の営業時間がある場合（例：昼と夜で分かれている）
  return dayHours.join(' / ')
}

const toggleAccordion = () => {
  isExpanded.value = !isExpanded.value
}
</script>

<style scoped>
.business-hours {
  background: #fafbfc;
  border: 1px solid #e8eaed;
  border-radius: 12px;
  margin-bottom: 20px;
  overflow: hidden;
}

/* ヘッダー部分 */
.hours-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.hours-header:hover {
  background: #f5f7fa;
}

.header-content {
  flex: 1;
}

.hours-title {
  color: #1a1a1a;
  font-size: 15px;
  font-weight: 500;
  margin: 0 0 4px 0;
  letter-spacing: -0.01em;
}

.today-hours {
  display: flex;
  align-items: center;
  gap: 8px;
}

.today-label {
  background: #e91e63;
  color: white;
  font-size: 11px;
  font-weight: 500;
  padding: 2px 8px;
  border-radius: 8px;
  letter-spacing: 0.02em;
}

.today-time {
  color: #e91e63;
  font-size: 14px;
  font-weight: 500;
}

.accordion-icon {
  color: #5f6368;
  transition: transform 0.2s ease;
}

.accordion-icon.expanded {
  transform: rotate(180deg);
}

/* アコーディオン内容 */
.hours-content {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  background: white;
}

.hours-content.expanded {
  max-height: 400px;
}

.hours-list {
  display: flex;
  flex-direction: column;
  gap: 1px;
  padding: 0;
}

.hours-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: white;
  transition: background-color 0.2s ease;
}

.hours-item:hover {
  background: #f8f9fa;
}

.hours-item.today {
  background: #fef7f0;
  border-left: 3px solid #e91e63;
}

.day {
  font-weight: 400;
  color: #3c4043;
  font-size: 14px;
  min-width: 20px;
}

.hours-item.today .day {
  font-weight: 500;
  color: #e91e63;
}

.time {
  font-weight: 400;
  color: #5f6368;
  font-size: 14px;
  text-align: right;
}

.hours-item.today .time {
  color: #c2185b;
  font-weight: 500;
}

.no-hours {
  text-align: center;
  padding: 20px 16px;
  color: #9aa0a6;
  background: white;
}

.no-hours p {
  margin: 0;
  font-size: 14px;
}

/* レスポンシブ */
@media (max-width: 768px) {
  .hours-header {
    padding: 12px;
  }

  .hours-item {
    padding: 10px 12px;
  }

  .day, .time {
    font-size: 13px;
  }
}
</style>
