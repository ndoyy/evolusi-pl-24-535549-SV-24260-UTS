<template>
  <div class="login">
    <h1>Login</h1>
    <form @submit.prevent="handleLogin">
      <input v-model="email" type="email" placeholder="Email" required />
      <input v-model="password" type="password" placeholder="Password" required />
      <button type="submit">Masuk</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../plugins/axios';

const email = ref('');
const password = ref('');
const router = useRouter();

const handleLogin = async () => {
  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    });
    localStorage.setItem('token', response.data.access_token);
    router.push('/mahasiswa');
  } catch (error) {
    alert('Login gagal: ' + (error.response?.data?.message || error.message));
  }
};
</script>