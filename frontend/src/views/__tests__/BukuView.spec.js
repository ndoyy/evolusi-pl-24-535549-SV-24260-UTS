import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('vue-router', () => ({ useRouter: () => ({ push: vi.fn() }) }))
vi.mock('../../plugins/axios', () => ({
    default: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

import api from '../../plugins/axios'
import BukuView from '../BukuView.vue'

const buku = {
    id: 1,
    judul: 'Pemrograman Laravel',
    penulis: 'Budi Santoso',
    penerbit: 'Informatika',
    tahun_terbit: 2024,
    isbn: '9786021234567',
    kategori: 'Teknologi',
    stok: 5,
}

describe('BukuView.vue (CRUD arsip buku)', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        window.scrollTo = vi.fn()
        api.get.mockResolvedValue({ data: { data: [buku] } })
    })

    it('READ: menampilkan daftar buku dari API', async () => {
        const wrapper = mount(BukuView)
        await flushPromises()

        expect(api.get).toHaveBeenCalledWith('/buku', { params: { search: undefined } })
        expect(wrapper.text()).toContain('Pemrograman Laravel')
        expect(wrapper.text()).toContain('1 buku')
    })

    it('READ: menampilkan pesan kosong jika belum ada buku', async () => {
        api.get.mockResolvedValue({ data: { data: [] } })
        const wrapper = mount(BukuView)
        await flushPromises()

        expect(wrapper.text()).toContain('Belum ada buku.')
    })

    it('CREATE: submit form memanggil POST /buku', async () => {
        let terkirim
        api.post.mockImplementation(async (_url, body) => {
            terkirim = { ...body } // salin: komponen mereset form setelah simpan
            return { data: {} }
        })
        const wrapper = mount(BukuView)
        await flushPromises()

        await wrapper.find('#judul').setValue('Buku Baru')
        await wrapper.find('#penulis').setValue('Penulis A')
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(api.post).toHaveBeenCalledTimes(1)
        expect(api.post.mock.calls[0][0]).toBe('/buku')
        expect(terkirim).toMatchObject({ judul: 'Buku Baru', penulis: 'Penulis A' })
        expect(wrapper.text()).toContain('Buku berhasil ditambahkan.')
    })

    it('CREATE: menampilkan error validasi 422 dari server', async () => {
        api.post.mockRejectedValue({
            response: { status: 422, data: { errors: { judul: ['Judul wajib diisi.'] } } },
        })
        const wrapper = mount(BukuView)
        await flushPromises()

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(wrapper.text()).toContain('Judul wajib diisi.')
    })

    it('UPDATE: tombol Ubah mengisi form lalu submit memanggil PUT', async () => {
        let terkirim
        api.put.mockImplementation(async (_url, body) => {
            terkirim = { ...body }
            return { data: {} }
        })
        const wrapper = mount(BukuView)
        await flushPromises()

        await wrapper.find('td.aksi button.secondary').trigger('click')
        expect(wrapper.text()).toContain('Ubah Buku')
        expect(wrapper.find('#judul').element.value).toBe('Pemrograman Laravel')

        await wrapper.find('#judul').setValue('Judul Diubah')
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(api.put).toHaveBeenCalledTimes(1)
        expect(api.put.mock.calls[0][0]).toBe('/buku/1')
        expect(terkirim).toMatchObject({ judul: 'Judul Diubah' })
    })

    it('DELETE: konfirmasi OK memanggil DELETE', async () => {
        vi.spyOn(window, 'confirm').mockReturnValue(true)
        api.delete.mockResolvedValue({ data: {} })
        const wrapper = mount(BukuView)
        await flushPromises()

        await wrapper.find('button.danger').trigger('click')
        await flushPromises()

        expect(api.delete).toHaveBeenCalledWith('/buku/1')
    })

    it('DELETE: konfirmasi dibatalkan tidak memanggil API', async () => {
        vi.spyOn(window, 'confirm').mockReturnValue(false)
        const wrapper = mount(BukuView)
        await flushPromises()

        await wrapper.find('button.danger').trigger('click')

        expect(api.delete).not.toHaveBeenCalled()
    })
})