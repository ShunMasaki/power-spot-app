<template>
  <div>
    <router-link to="/" class="logo-link">
      <img
      src="./assets/logo/pawaspo_logo.svg"
      alt="パワスポ！ロゴ"
      class="logo-fixed"
    />
    </router-link>
    <LoginForm />
    <router-view />
  </div>
</template>

<script setup>
import LoginForm from './components/LoginForm.vue'
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
