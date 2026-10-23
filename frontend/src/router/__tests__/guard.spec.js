import { describe, it, expect, beforeEach } from 'vitest'
import router from '../index'

describe('router guard', () => {
    beforeEach(async () => {
        localStorage.clear()
        await router.push('/')
        await router.isReady()
    })

    it('tanpa token, /buku dialihkan ke login', async () => {
        await router.push('/buku')
        expect(router.currentRoute.value.name).toBe('login')
    })

    it('dengan token, boleh membuka /buku', async () => {
        localStorage.setItem('token', 'abc')
        await router.push('/buku')
        expect(router.currentRoute.value.name).toBe('buku')
    })

    it('dengan token, halaman login dialihkan ke /buku', async () => {
        localStorage.setItem('token', 'abc')
        await router.push('/buku')
        await router.push('/') // user yang sudah login mencoba kembali ke halaman login
        expect(router.currentRoute.value.name).toBe('buku')
    })
})