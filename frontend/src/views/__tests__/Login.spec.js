import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const push = vi.fn()
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('../../plugins/axios', () => ({ default: { post: vi.fn() } }))

import api from '../../plugins/axios'
import Login from '../Login.vue'

describe('Login.vue', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        localStorage.clear()
    })

    it('menampilkan form login', () => {
        const wrapper = mount(Login)
        expect(wrapper.text()).toContain('Arsip Buku')
        expect(wrapper.find('#email').exists()).toBe(true)
        expect(wrapper.find('#password').exists()).toBe(true)
    })

    it('login berhasil: simpan token lalu pindah ke /buku', async () => {
        api.post.mockResolvedValue({ data: { access_token: 'abc123' } })
        const wrapper = mount(Login)

        await wrapper.find('#email').setValue('admin@example.com')
        await wrapper.find('#password').setValue('password')
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(api.post).toHaveBeenCalledWith('/login', {
            email: 'admin@example.com',
            password: 'password',
        })
        expect(localStorage.getItem('token')).toBe('abc123')
        expect(push).toHaveBeenCalledWith('/buku')
    })

    it('login gagal: tampilkan pesan error dan tidak simpan token', async () => {
        api.post.mockRejectedValue({ response: { data: { message: 'Kredensial salah' } } })
        const wrapper = mount(Login)

        await wrapper.find('#email').setValue('x@example.com')
        await wrapper.find('#password').setValue('salah')
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(wrapper.text()).toContain('Kredensial salah')
        expect(localStorage.getItem('token')).toBeNull()
        expect(push).not.toHaveBeenCalled()
    })
})