<template>
  <div class="mahasiswa">
    <h1>Data Mahasiswa</h1>
    <button @click="logout">Logout</button>
    <ul>
      <li v-for="mhs in daftarMahasiswa" :key="mhs.id">
        {{ mhs.nim }} - {{ mhs.nama }} ({{ mhs.jurusan }})
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../plugins/axios';

const daftarMahasiswa = ref([]);
const router = useRouter();

const fetchData = async () => {
  try {
    const response = await api.get('/mahasiswa');
    daftarMahasiswa.value = response.data;
  } catch (error) {
    console.error('Gagal mengambil data:', error);
  }
};

const logout = () => {
  localStorage.removeItem('token');
  router.push('/');
};

onMounted(fetchData);
</script>