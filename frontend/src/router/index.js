import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'login', component: Login },
    { path: '/buku', name: 'buku', component: () => import('../views/BukuView.vue'), meta: { auth: true } },
    // Alamat yang tidak dikenal dikembalikan ke halaman awal
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const loggedIn = !!localStorage.getItem('token')
  if (to.meta.auth && !loggedIn) return { name: 'login' }
  if (to.name === 'login' && loggedIn) return { name: 'buku' }
})

export default router