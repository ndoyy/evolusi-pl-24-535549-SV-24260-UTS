import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Login from '../views/Login.vue'
import Mahasiswa from '../views/Mahasiswa.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: Login,
    },
    {
      path: '/mahasiswa',
      name: 'mahasiswa',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: Mahasiswa,
    },
  ],
})

export default router
