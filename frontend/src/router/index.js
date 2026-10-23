import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Mahasiswa from '../views/Mahasiswa.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Login',
      component: Login,
    },
    {
      path: '/mahasiswa',
      name: 'Mahasiswa',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/Mahasiswa.vue'),
    },
  ],
})

export default router
