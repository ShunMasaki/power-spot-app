<template>
    <div>
        <!-- ロゴ：クリックでトップに戻る -->
        <router-link to="/" class="logo-link">
            <img
            src="./assets/logo/pawaspo_logo.svg"
            alt="パワスポ！ロゴ"
            class="logo-fixed"
            />
        </router-link>

        <!-- 各ページを Vue Router で切り替え -->
        <router-view />

        <div v-if="auth.isLoggedIn" class="fixed bottom-4 right-4">
            <button
                @click="auth.logout"
                class="bg-red-500 text-white px-4 py-2 rounded shadow"
            >
                ログアウト
            </button>
        </div>
    </div>
  </template>

  <script setup>
  import { useAuthStore } from '@/stores/auth'

  const auth = useAuthStore()
  </script>

  <style>
  body {
    font-family: 'Noto Sans JP', 'Noto Sans', 'Helvetica Neue', Arial, 'Meiryo', sans-serif;
  }
  .logo-fixed {
    position: fixed;
    top: 18px;
    left: 18px;
    width: 60px;
    z-index: 100;
  }
  </style>
