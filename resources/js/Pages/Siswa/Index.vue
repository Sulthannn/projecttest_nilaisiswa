<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  data: Object,
  filters: Object,
})

const params = new URLSearchParams(location.search)
const filter = ref({
  q: params.get('q') || '',
  per_page: params.get('per_page') || (props.filters?.per_page || 10),
})

function applyFilter() {
  router.get(route('siswa.index'), filter.value, { preserveState: true, replace: true })
}

function clearFilter() {
  filter.value.q = ''
  filter.value.per_page = 10
  router.get(route('siswa.index'), {}, { preserveState: false, replace: true })
}

const summary = computed(()=>{
  if (!props.data?.data?.length) return 'Tidak ada data.'
  return `Menampilkan ${props.data.from}–${props.data.to} dari ${props.data.total} siswa`
})

const showAdd = ref(false)
const showEdit = ref(false)
const showDelete = ref(false)
const deleting = ref(null)
const editing = ref(null)
const fSiswa = useForm({ nama:'', kelas:'' })

function openAdd() {
  editing.value = null
  fSiswa.reset()
  fSiswa.clearErrors()
  fSiswa.nama = ''
  fSiswa.kelas = ''
  showAdd.value = true
}

function submitAdd() {
  fSiswa.post(route('siswa.store'), {
    preserveScroll: true,
    onSuccess: () => { showAdd.value = false }
  })
}

function openEdit(row) {
  editing.value = row
  fSiswa.nama = row.nama
  fSiswa.kelas = row.kelas
  showEdit.value = true
}

function submitEdit() {
  if (!editing.value) return
  fSiswa.put(route('siswa.update', editing.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showEdit.value = false
      editing.value = null
      fSiswa.reset()
      fSiswa.clearErrors()
    }
  })
}

function confirmDelete(row){
  deleting.value = row
  showDelete.value = true
}
function destroySiswa(){
  if(!deleting.value) return
  router.delete(route('siswa.destroy', deleting.value.id), {
    preserveScroll: true,
    onFinish: () => { showDelete.value=false; deleting.value=null }
  })
}

function closeEdit(){
  showEdit.value = false
  editing.value = null
  fSiswa.reset()
  fSiswa.clearErrors()
}
</script>

<template>
  <Head title="Siswa" />
  <AuthenticatedLayout>
  <div class="space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold tracking-tight">Data Siswa</h1>
      </div>

      <div class="bg-white rounded-xl p-4 shadow border border-gray-100">
        <div class="grid gap-4 md:grid-cols-4">
          <div class="flex flex-col gap-2 md:col-span-2">
            <label class="text-xs font-medium text-gray-500">Cari Nama</label>
            <input v-model="filter.q" @keyup.enter="applyFilter" placeholder="Nama siswa…" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
          </div>
          <div class="flex flex-col gap-2">
            <label class="text-xs font-medium text-gray-500">Per Halaman</label>
            <select v-model="filter.per_page" @change="applyFilter" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
          <div class="flex items-end gap-2">
            <button @click="applyFilter" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 text-sm font-medium">Terapkan</button>
            <button @click="clearFilter" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg px-4 py-2 text-sm font-medium">Clear</button>
          </div>
        </div>
      </div>

      <div class="flex justify-end mb-2">
        <button @click="openAdd" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Tambah Siswa
        </button>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-gray-700 text-xs uppercase tracking-wide">
                <th class="px-4 py-3 text-left font-semibold">No</th>
                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                <th class="px-4 py-3 text-left font-semibold">Kelas</th>
                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
              </tr>
            </thead>
            <tbody v-if="props.data.data.length">
              <tr v-for="row in props.data.data" :key="row.id" class="border-t hover:bg-gray-50 transition">
                <td class="px-4 py-2 text-gray-600">{{ row.id }}</td>
                <td class="px-4 py-2 font-medium text-gray-800">{{ row.nama }}</td>
                <td class="px-4 py-2">{{ row.kelas }}</td>
                <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                  <button @click="openEdit(row)" title="Edit" class="inline-flex items-center justify-center h-8 w-8 rounded-md bg-yellow-500/90 hover:bg-yellow-500 text-white shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-7 6h8m-8 4h8m2.586-11.414l2.828 2.828M15 3l3.586 3.586L7.5 17.672a2 2 0 01-.878.518L4 19l.81-2.622a2 2 0 01.518-.878L15 3z"/></svg>
                  </button>
                  <button @click="confirmDelete(row)" title="Hapus" class="inline-flex items-center justify-center h-8 w-8 rounded-md bg-red-600/90 hover:bg-red-600 text-white shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6M4 7h16l-1 12a2 2 0 01-2 2H7a2 2 0 01-2-2L4 7z"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="3" class="px-6 py-10 text-center text-gray-500 text-sm">Belum ada data</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-4 py-3 bg-gray-50 border-t">
          <div class="text-xs text-gray-600" v-text="summary" />
          <div class="flex items-center gap-2">
            <button class="px-3 py-1.5 border rounded-md text-xs font-medium bg-white hover:bg-gray-100 disabled:opacity-40" :disabled="!props.data.prev_page_url" @click="router.visit(props.data.prev_page_url, { preserveState: true })"><<</button>
            <span class="text-xs text-gray-500">Hal {{ props.data.current_page }} / {{ props.data.last_page }}</span>
            <button class="px-3 py-1.5 border rounded-md text-xs font-medium bg-white hover:bg-gray-100 disabled:opacity-40" :disabled="!props.data.next_page_url" @click="router.visit(props.data.next_page_url, { preserveState: true })">>></button>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="showAdd" title="Tambah Siswa" @close="showAdd=false">
      <form @submit.prevent="submitAdd" class="space-y-4">
        <div class="grid gap-3">
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Nama</label>
            <input v-model="fSiswa.nama" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fSiswa.errors.nama && 'border-red-500'" required>
            <p v-if="fSiswa.errors.nama" class="text-xs text-red-600">{{ fSiswa.errors.nama }}</p>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Kelas</label>
            <input v-model="fSiswa.kelas" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fSiswa.errors.kelas && 'border-red-500'" required>
            <p v-if="fSiswa.errors.kelas" class="text-xs text-red-600">{{ fSiswa.errors.kelas }}</p>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showAdd=false" class="px-4 py-2 text-sm rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700" :disabled="fSiswa.processing">Batal</button>
          <button type="submit" class="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white inline-flex items-center gap-2 disabled:opacity-60" :disabled="fSiswa.processing">
            <svg v-if="fSiswa.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"/><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"/></svg>
            <span>Simpan</span>
          </button>
        </div>
      </form>
    </Modal>

  <Modal :show="showEdit" title="Edit Siswa" @close="closeEdit">
    <form @submit.prevent="submitEdit" class="space-y-4">
        <div class="grid gap-3">
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Nama</label>
            <input v-model="fSiswa.nama" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fSiswa.errors.nama && 'border-red-500'" required>
            <p v-if="fSiswa.errors.nama" class="text-xs text-red-600">{{ fSiswa.errors.nama }}</p>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Kelas</label>
            <input v-model="fSiswa.kelas" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fSiswa.errors.kelas && 'border-red-500'" required>
            <p v-if="fSiswa.errors.kelas" class="text-xs text-red-600">{{ fSiswa.errors.kelas }}</p>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="closeEdit" class="px-4 py-2 text-sm rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700" :disabled="fSiswa.processing">Batal</button>
            <button type="submit" class="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white inline-flex items-center gap-2 disabled:opacity-60" :disabled="fSiswa.processing">
            <svg v-if="fSiswa.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"/><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"/></svg>
            <span>Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDelete" maxWidth="sm" title="Konfirmasi Hapus" @close="showDelete=false">
      <p class="text-sm text-gray-600 mb-6">Yakin ingin menghapus siswa <strong>{{ deleting?.nama }}</strong>? Data terkait nilai akan terhapus dan tidak dapat dibatalkan</p>
      <div class="flex justify-end gap-3">
        <button class="px-4 py-2 text-sm rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700" @click="showDelete=false">Batal</button>
        <button class="px-4 py-2 text-sm rounded-md bg-red-600 hover:bg-red-700 text-white" @click="destroySiswa">Hapus</button>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>