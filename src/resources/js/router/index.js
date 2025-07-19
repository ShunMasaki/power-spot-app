import { createRouter, createWebHistory } from 'vue-router';

import Home from '../pages/Home.vue';
import SpotDetail from '../pages/SpotDetail.vue';
import MyPage from '../pages/MyPage.vue';

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/spot/:id', name: 'spot-detail', component: SpotDetail },
  { path: '/mypage', name: 'mypage', component: MyPage },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
