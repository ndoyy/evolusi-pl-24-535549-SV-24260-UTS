import axios from 'axios'

// Alamat API dari variabel lingkungan (.env -> VITE_API_URL), tidak ditulis mati di komponen.
const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: { Accept: 'application/json' },
})

// Kirim token pada setiap permintaan
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

// Token kedaluwarsa/tidak valid -> kembali ke halaman login
api.interceptors.response.use(
    (res) => res,
    (error) => {
        const isLogin = error.config?.url === '/login'
        if (error.response?.status === 401 && !isLogin) {
            localStorage.removeItem('token')
            window.location.href = '/'
        }
        return Promise.reject(error)
    },
)

export default api