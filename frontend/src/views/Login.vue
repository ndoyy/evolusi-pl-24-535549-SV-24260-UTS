<template>
  <div class="login card">
    <h1>Arsip Buku</h1>
    <p class="muted">Silakan login untuk mengelola koleksi buku.</p>
    <form @submit.prevent="handleLogin">
      <div>
        <label for="email">Email</label>
        <input id="email" v-model="email" type="email" required />
      </div>
      <div>
        <label for="password">Password</label>
        <input id="password" v-model="password" type="password" required />
      </div>
      <p v-if="error" class="error">{{ error }}</p>
      <button type="submit" :disabled="loading">{{ loading ? 'Memproses…' : 'Masuk' }}</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../plugins/axios'

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const router = useRouter()

const handleLogin = async () => {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/login', { email: email.value, password: password.value })
    localStorage.setItem('token', data.access_token) // disimpan, dikirim otomatis oleh axios
    router.push('/buku')
  } catch (e) {
    error.value = e.response?.data?.message || 'Login gagal, coba lagi.'
  } finally {
    loading.value = false
  }
}
</script>