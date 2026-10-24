<template>
  <div>
    <div class="toolbar">
      <h1>Arsip Buku</h1>
      <button class="secondary" @click="logout">Logout</button>
    </div>

    <!-- Form tambah / ubah -->
    <form class="card" @submit.prevent="simpan">
      <h3 style="margin-top: 0">{{ editId ? 'Ubah Buku' : 'Tambah Buku' }}</h3>
      <div class="grid">
        <div v-for="f in fields" :key="f.key" :class="{ full: f.full }">
          <label :for="f.key">{{ f.label }}</label>
          <textarea v-if="f.type === 'textarea'" :id="f.key" v-model="form[f.key]" rows="2" />
          <input v-else :id="f.key" v-model="form[f.key]" :type="f.type" :min="f.type === 'number' ? 0 : null" />
          <p v-if="errors[f.key]" class="error">{{ errors[f.key][0] }}</p>
        </div>
      </div>
      <p v-if="pesan" class="muted">{{ pesan }}</p>
      <p v-if="galat" class="error" role="alert">{{ galat }}</p>
      <div style="margin-top: 1rem; display: flex; gap: 0.5rem">
        <button type="submit" :disabled="saving">{{ editId ? 'Simpan Perubahan' : 'Tambah' }}</button>
        <button v-if="editId" type="button" class="secondary" @click="reset">Batal</button>
      </div>
    </form>

    <!-- Daftar buku -->
    <div class="card">
      <div class="toolbar">
        <input v-model="search" placeholder="Cari judul / penulis / ISBN…" @input="debouncedFetch" />
        <span class="muted">{{ daftarBuku.length }} buku</span>
      </div>
      <p v-if="loading" class="muted">Memuat…</p>
      <table v-else>
        <thead>
          <tr><th>Judul</th><th>Penulis</th><th>Penerbit</th><th>Tahun</th><th>ISBN</th><th>Kategori</th><th>Stok</th><th></th></tr>
        </thead>
        <tbody>
          <tr v-for="b in daftarBuku" :key="b.id">
            <td>{{ b.judul }}</td><td>{{ b.penulis }}</td><td>{{ b.penerbit }}</td>
            <td>{{ b.tahun_terbit }}</td><td>{{ b.isbn }}</td><td>{{ b.kategori }}</td><td>{{ b.stok }}</td>
            <td class="aksi">
              <button class="secondary" @click="edit(b)">Ubah</button>
              <button class="danger" @click="hapus(b)">Hapus</button>
            </td>
          </tr>
          <tr v-if="!daftarBuku.length"><td colspan="8" class="muted">Belum ada buku.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../plugins/axios'

const fields = [
  { key: 'judul', label: 'Judul', type: 'text' },
  { key: 'penulis', label: 'Penulis', type: 'text' },
  { key: 'penerbit', label: 'Penerbit', type: 'text' },
  { key: 'tahun_terbit', label: 'Tahun Terbit', type: 'number' },
  { key: 'isbn', label: 'ISBN', type: 'text' },
  { key: 'kategori', label: 'Kategori', type: 'text' },
  { key: 'stok', label: 'Stok', type: 'number' },
  { key: 'deskripsi', label: 'Deskripsi', type: 'textarea', full: true },
]
const kosong = () => ({ judul: '', penulis: '', penerbit: '', tahun_terbit: '', isbn: '', kategori: '', stok: 1, deskripsi: '' })

const router = useRouter()
const daftarBuku = ref([])
const form = reactive(kosong())
const errors = ref({})
const editId = ref(null)
const search = ref('')
const loading = ref(false)
const saving = ref(false)
const pesan = ref('')
const galat = ref('')
let timer

const fetchData = async () => {
  loading.value = true
  galat.value = ''
  try {
    const { data } = await api.get('/buku', { params: { search: search.value || undefined } })
    daftarBuku.value = data.data
  } catch {
    galat.value = 'Gagal memuat daftar buku. Periksa koneksi lalu coba lagi.'
  } finally {
    loading.value = false
  }
}
const debouncedFetch = () => { clearTimeout(timer); timer = setTimeout(fetchData, 300) }

const reset = () => { Object.assign(form, kosong()); editId.value = null; errors.value = {} }

const simpan = async () => {
  saving.value = true
  errors.value = {}
  pesan.value = ''
  galat.value = ''
  try {
    if (editId.value) await api.put(`/buku/${editId.value}`, form)
    else await api.post('/buku', form)
    pesan.value = editId.value ? 'Buku berhasil diubah.' : 'Buku berhasil ditambahkan.'
    reset()
    await fetchData()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
    else galat.value = 'Terjadi kesalahan, coba lagi.'
  } finally {
    saving.value = false
  }
}

const edit = (b) => { editId.value = b.id; Object.assign(form, b); window.scrollTo({ top: 0, behavior: 'smooth' }) }

const hapus = async (b) => {
  if (!confirm(`Hapus buku "${b.judul}"?`)) return
  galat.value = ''
  pesan.value = ''
  try {
    await api.delete(`/buku/${b.id}`)
    pesan.value = 'Buku berhasil dihapus.'
    await fetchData()
  } catch {
    galat.value = 'Gagal menghapus buku, coba lagi.'
  }
}

const logout = async () => {
  try { await api.post('/logout') } catch { /* token mungkin sudah tidak valid */ }
  localStorage.removeItem('token')
  router.push('/')
}

onMounted(fetchData)
</script>