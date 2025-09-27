<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  data: Object,
  listKelas: Array,
  listMapel: Array,
  rowStart: { type: Number, default: 0 }
})

const params = new URLSearchParams(location.search)
const filter = ref({
  kelas: params.get('kelas') || '',
  mapel: params.get('mapel') || '',
  q:     params.get('q')     || '',
  per_page: params.get('per_page') || '10',
})

function applyFilter() {
  router.get(route('dashboard'), filter.value, { preserveState: true, replace: true })
}

function clearFilter() {
  filter.value.kelas = ''
  filter.value.mapel = ''
  filter.value.q = ''
  filter.value.per_page = '10'
  router.get(route('dashboard'), {}, { preserveState: false, replace: true })
}

const summary = computed(() => {
  if (!props.data?.data?.length) return 'Tidak ada data.'
  return `Menampilkan ${props.data.from}–${props.data.to} dari ${props.data.total} data`
})

const fSiswa = useForm({ nama:'', kelas:'' })
const fNilai = useForm({ siswa_id:'', kelas:'', mapel:'', nilai:'' })
const kelasMismatch = ref(false)
const showEditModal = ref(false)
const showAddModal = ref(false)
const showDeleteModal = ref(false)
const deletingRow = ref(null)
const editingRow = ref(null)
const fImport= useForm({ file: null })

function submitSiswa() { fSiswa.post(route('siswa.store'), { onSuccess: () => fSiswa.reset() }) }
function openAdd() {
  fNilai.reset()
  fNilai.clearErrors()
  siswaQuery.value = ''
  fNilai.siswa_id = ''
  fNilai.kelas = ''
  kelasMismatch.value = false
  editingRow.value = null
  showSiswaDropdown.value = false
  showAddModal.value = true
}
function submitNilai() {
  if(fNilai.siswa_id){
    const selected = siswaOptions.value.find(o=>o.id === fNilai.siswa_id)
    kelasMismatch.value = !!(selected && selected.kelas !== fNilai.kelas)
    if(kelasMismatch.value) return
  }
  fNilai.post(route('nilai.store'), {
    preserveScroll: true,
    onSuccess: () => {
      fNilai.reset();
      fNilai.clearErrors();
      siswaQuery.value='';
      kelasMismatch.value = false;
      showAddModal.value = false;
    }
  })
}
function submitImport(e) {
  fImport.file = e.target.files[0]
  if (!fImport.file) return
  fImport.post(route('nilai.import'), {
    forceFormData: true,
    onSuccess: () => { e.target.value = null }
  })
}

function openEdit(row) {
  editingRow.value = row
  fNilai.reset()
  fNilai.siswa_id = row.siswa_id
  fNilai.kelas = row.kelas
  fNilai.mapel = row.mapel
  fNilai.nilai = row.nilai
  siswaQuery.value = (row.siswa?.nama ? row.siswa.nama + ' (' + row.kelas + ')' : '')
  showEditModal.value = true
}

function submitEdit() {
  if (!editingRow.value) return
  if(fNilai.siswa_id){
    const selected = siswaOptions.value.find(o=>o.id === fNilai.siswa_id)
    kelasMismatch.value = !!(selected && selected.kelas !== fNilai.kelas)
    if(kelasMismatch.value) return
  }
  fNilai.put(route('nilai.update', editingRow.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false
      editingRow.value = null
      fNilai.reset()
      fNilai.clearErrors()
      siswaQuery.value=''
      kelasMismatch.value = false
    }
  })
}

function askDelete(row){
  deletingRow.value = row
  showDeleteModal.value = true
}

function destroyNilai(){
  if(!deletingRow.value) return
  router.delete(route('nilai.destroy', deletingRow.value.id), {
    preserveScroll: true,
    onFinish: () => { showDeleteModal.value = false; deletingRow.value=null }
  })
}

function closeEditNilai(){
  showEditModal.value = false
  editingRow.value = null
  fNilai.reset()
  fNilai.clearErrors()
  siswaQuery.value=''
  kelasMismatch.value = false
  showSiswaDropdown.value = false
}

const hasData = computed(()=> props.data?.data?.length > 0)

import { watch, onMounted, onBeforeUnmount } from 'vue'
const siswaQuery = ref('')
const siswaOptions = ref([])
const loadingSiswa = ref(false)
let fetchTimeout
const noSiswa = computed(()=> !loadingSiswa.value && siswaOptions.value.length === 0)
function fetchSiswa(){
  loadingSiswa.value = true
  fetch(route('siswa.options', { q: siswaQuery.value }))
    .then(r=>r.json())
    .then(d=> { siswaOptions.value = d })
    .finally(()=> loadingSiswa.value=false)
}
watch(siswaQuery, ()=>{
  clearTimeout(fetchTimeout)
  fetchTimeout = setTimeout(fetchSiswa, 300)
})
function selectSiswa(opt){
  fNilai.siswa_id = opt.id
  fNilai.kelas = opt.kelas
  siswaQuery.value = opt.nama + ' ('+opt.kelas+')'
  showSiswaDropdown.value = false
  kelasMismatch.value = false
}
const showSiswaDropdown = ref(false)
function onClickOutside(e){
  const dropdownEl = document.getElementById('siswa-autocomplete-wrapper')
  if(dropdownEl && !dropdownEl.contains(e.target)){
    showSiswaDropdown.value = false
  }
}
onMounted(()=> { fetchSiswa(); document.addEventListener('click', onClickOutside) })
onBeforeUnmount(()=> document.removeEventListener('click', onClickOutside))

function clearSiswa(){
  siswaQuery.value=''
  fNilai.siswa_id=''
  fNilai.kelas=''
  showSiswaDropdown.value=false
  fetchSiswa()
  kelasMismatch.value = false
}
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Nilai Siswa</h1>
        <p class="text-sm text-gray-600 mt-1">Kelola data siswa & nilai akademik</p>
      </div>
    </div>

    <div class="bg-white rounded-xl p-4 shadow border border-gray-100 mb-2">
      <div class="grid gap-4 md:grid-cols-6">
        <div class="md:col-span-2 flex flex-col gap-2">
          <label class="text-xs font-medium text-gray-500">Cari Siswa</label>
          <input v-model="filter.q" type="text" placeholder="Nama siswa…" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" @keyup.enter="applyFilter">
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-medium text-gray-500">Kelas</label>
          <select v-model="filter.kelas" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua</option>
            <option v-for="k in listKelas" :key="k" :value="k">{{ k }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-medium text-gray-500">Mapel</label>
          <select v-model="filter.mapel" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua</option>
            <option v-for="m in listMapel" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-medium text-gray-500">Per Halaman</label>
          <select v-model="filter.per_page" @change="applyFilter" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button @click="applyFilter" class="flex-1 bg-indigo-600 hover:bg-indigo-700 transition text-white rounded-lg px-4 py-2 font-medium">Terapkan</button>
          <button @click="clearFilter" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg px-4 py-2 font-medium">Clear</button>
        </div>
      </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-2 items-center">
      <label class="bg-white border border-gray-200 rounded-lg px-4 py-2 shadow-sm cursor-pointer text-sm font-medium hover:bg-gray-50">
        <span class="inline-flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9M12 12v.01M12 12a8 8 0 017.938 7.062M12 20a8 8 0 01-7.938-7.062M12 20v.01" /></svg>
          Import Excel
        </span>
        <input type="file" class="hidden" accept=".xlsx,.xls" @change="submitImport">
      </label>
      <a :href="route('nilai.export')" class="bg-emerald-600 hover:bg-emerald-700 transition text-white rounded-lg px-4 py-2 text-sm font-medium inline-flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3a1 1 0 011-1h4a1 1 0 010 2H5v12h3a1 1 0 110 2H4a1 1 0 01-1-1V3z"/><path d="M9 12a1 1 0 001 1h2.586l-1.293 1.293a1 1 0 001.414 1.414l3.004-3.004a1.01 1.01 0 00.083-.094.997.997 0 000-1.32 1.01 1.01 0 00-.083-.094l-3.004-3.004a1 1 0 10-1.414 1.414L12.586 11H10a1 1 0 00-1 1z"/></svg>
        Export Excel
      </a>
      <div class="flex-1"></div>
      <button @click="openAdd" class="ml-auto inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Nilai
      </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-50 text-gray-700 text-xs uppercase tracking-wide">
              <th class="px-5 py-3 text-left font-semibold">No</th>
              <th class="px-5 py-3 text-left font-semibold">Nama</th>
              <th class="px-5 py-3 text-left font-semibold">Kelas</th>
              <th class="px-5 py-3 text-left font-semibold">Mapel</th>
              <th class="px-5 py-3 text-left font-semibold">Nilai</th>
              <th class="px-5 py-3 text-right font-semibold">Aksi</th>
            </tr>
          </thead>
          <tbody v-if="hasData">
            <tr v-for="(row, idx) in props.data.data" :key="row.id" class="border-t hover:bg-gray-50 transition">
              <td class="px-5 py-2">{{ props.data.from + idx }}</td>
              <td class="px-5 py-2 font-medium text-gray-800">{{ row.siswa?.nama || '-' }}</td>
              <td class="px-5 py-2">{{ row.kelas }}</td>
              <td class="px-5 py-2">{{ row.mapel }}</td>
              <td class="px-5 py-2">
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="
                    row.nilai >= 85 ? 'bg-emerald-100 text-emerald-700' :
                    row.nilai >= 70 ? 'bg-sky-100 text-sky-700' :
                    row.nilai >= 60 ? 'bg-amber-100 text-amber-700' :
                                      'bg-rose-100 text-rose-700'">
                  {{ row.nilai }}
                </span>
              </td>
              <td class="px-5 py-2 text-right space-x-2 whitespace-nowrap">
                <button
                  class="inline-flex items-center justify-center h-8 w-8 rounded-md bg-yellow-500/90 hover:bg-yellow-500 text-white text-xs font-medium shadow-sm"
                  title="Edit"
                  @click="openEdit(row)">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-7 6h8m-8 4h8m2.586-11.414l2.828 2.828M15 3l3.586 3.586L7.5 17.672a2 2 0 01-.878.518L4 19l.81-2.622a2 2 0 01.518-.878L15 3z"/></svg>
                </button>
                <button
                  class="inline-flex items-center justify-center h-8 w-8 rounded-md bg-red-600/90 hover:bg-red-600 text-white text-xs font-medium shadow-sm"
                  title="Hapus"
                  @click="askDelete(row)">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6M4 7h16l-1 12a2 2 0 01-2 2H7a2 2 0 01-2-2L4 7z"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6" class="px-6 py-10 text-center text-gray-500 text-sm">Belum ada data</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-4 py-3 bg-gray-50 border-t">
        <div class="text-xs text-gray-600" v-text="summary" />
        <div class="flex items-center gap-2">
          <button
            class="px-3 py-1.5 border rounded-md text-xs font-medium bg-white hover:bg-gray-100 disabled:opacity-40"
            :disabled="!props.data.prev_page_url"
            @click="router.visit(props.data.prev_page_url, { preserveState: true })">
            <<
          </button>
          <span class="text-xs text-gray-500">Hal {{ props.data.current_page }} / {{ props.data.last_page }}</span>
          <button
            class="px-3 py-1.5 border rounded-md text-xs font-medium bg-white hover:bg-gray-100 disabled:opacity-40"
            :disabled="!props.data.next_page_url"
            @click="router.visit(props.data.next_page_url, { preserveState: true })">
            >>
          </button>
        </div>
      </div>
    </div>

  <Modal :show="showEditModal" title="Edit Nilai" @close="closeEditNilai">
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div class="grid gap-3">
          <div id="siswa-autocomplete-wrapper" class="flex flex-col gap-1 relative">
            <label class="text-xs font-medium text-gray-500">Siswa</label>
            <div class="relative">
              <input
                v-model="siswaQuery"
                @focus="!noSiswa && (showSiswaDropdown=true)"
                placeholder="Ketik nama siswa..."
                :disabled="noSiswa"
                :readonly="noSiswa"
                class="border rounded-lg w-full pr-8 px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                :class="[fNilai.errors.siswa_id && 'border-red-500']"
              >
              <button v-if="siswaQuery && !noSiswa" type="button" @click="clearSiswa" class="absolute inset-y-0 right-0 px-2 text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <input type="hidden" :value="fNilai.siswa_id" />
            <p v-if="fNilai.errors.siswa_id" class="text-xs text-red-600">{{ fNilai.errors.siswa_id }}</p>
            <p v-else-if="noSiswa" class="text-xs text-amber-600">Belum ada data siswa. Tambahkan siswa terlebih dahulu.</p>
            <div v-if="showSiswaDropdown" class="absolute z-50 mt-1 w-full bg-white border rounded-md shadow max-h-56 overflow-auto">
              <div class="p-2 text-xs text-gray-500" v-if="loadingSiswa">Memuat...</div>
              <template v-else>
                <button v-for="o in siswaOptions" :key="o.id" type="button" @click="selectSiswa(o)" class="w-full text-left px-3 py-2 hover:bg-indigo-50 text-sm">
                  <span class="font-medium">{{ o.nama }}</span>
                  <span class="text-gray-500"> • {{ o.kelas }}</span>
                </button>
                <div v-if="!siswaOptions.length" class="px-3 py-2 text-sm text-gray-500">Tidak ada hasil</div>
              </template>
            </div>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Kelas</label>
            <input v-model="fNilai.kelas" disabled class="border rounded-lg px-3 py-2 bg-gray-100 text-gray-600 focus:outline-none" :class="[fNilai.errors.kelas && 'border-red-500', kelasMismatch && 'border-red-500']" required>
            <p v-if="fNilai.errors.kelas" class="text-xs text-red-600">{{ fNilai.errors.kelas }}</p>
            <p v-else-if="kelasMismatch" class="text-xs text-red-600">Kelas tidak sesuai dengan kelas siswa terpilih.</p>
          </div>
            <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Mapel</label>
            <input v-model="fNilai.mapel" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fNilai.errors.mapel && 'border-red-500'" required>
            <p v-if="fNilai.errors.mapel" class="text-xs text-red-600">{{ fNilai.errors.mapel }}</p>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Nilai</label>
            <input v-model.number="fNilai.nilai" type="number" min="0" max="100" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fNilai.errors.nilai && 'border-red-500'" required>
            <p v-if="fNilai.errors.nilai" class="text-xs text-red-600">{{ fNilai.errors.nilai }}</p>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="closeEditNilai" class="px-4 py-2 text-sm rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700" :disabled="fNilai.processing">Batal</button>
          <button type="submit" class="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white inline-flex items-center gap-2 disabled:opacity-60" :disabled="fNilai.processing">
            <svg v-if="fNilai.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"/><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"/></svg>
            <span>Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showAddModal" title="Tambah Nilai" @close="showAddModal=false">
      <form @submit.prevent="submitNilai" class="space-y-4">
        <div class="grid gap-3">
          <div id="siswa-autocomplete-wrapper" class="flex flex-col gap-1 relative">
            <label class="text-xs font-medium text-gray-500">Siswa</label>
            <div class="relative">
              <input v-model="siswaQuery" @focus="showSiswaDropdown=true" placeholder="Ketik nama siswa..." class="border rounded-lg w-full pr-8 px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fNilai.errors.siswa_id && 'border-red-500'">
              <button v-if="siswaQuery" type="button" @click="clearSiswa" class="absolute inset-y-0 right-0 px-2 text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <input type="hidden" :value="fNilai.siswa_id" />
            <p v-if="fNilai.errors.siswa_id" class="text-xs text-red-600">{{ fNilai.errors.siswa_id }}</p>
            <div v-if="showSiswaDropdown" class="absolute z-50 mt-1 w-full bg-white border rounded-md shadow max-h-56 overflow-auto">
              <div class="p-2 text-xs text-gray-500" v-if="loadingSiswa">Memuat...</div>
              <template v-else>
                <button v-for="o in siswaOptions" :key="o.id" type="button" @click="selectSiswa(o)" class="w-full text-left px-3 py-2 hover:bg-indigo-50 text-sm">
                  <span class="font-medium">{{ o.nama }}</span>
                  <span class="text-gray-500"> • {{ o.kelas }}</span>
                </button>
                <div v-if="!siswaOptions.length" class="px-3 py-2 text-sm text-gray-500">Tidak ada hasil</div>
              </template>
            </div>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Kelas</label>
            <input v-model="fNilai.kelas" disabled class="border rounded-lg px-3 py-2 bg-gray-100 text-gray-600 focus:outline-none" :class="[fNilai.errors.kelas && 'border-red-500', kelasMismatch && 'border-red-500']" required>
            <p v-if="fNilai.errors.kelas" class="text-xs text-red-600">{{ fNilai.errors.kelas }}</p>
            <p v-else-if="kelasMismatch" class="text-xs text-red-600">Kelas tidak sesuai dengan kelas siswa terpilih.</p>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Mapel</label>
            <input v-model="fNilai.mapel" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fNilai.errors.mapel && 'border-red-500'" required>
            <p v-if="fNilai.errors.mapel" class="text-xs text-red-600">{{ fNilai.errors.mapel }}</p>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Nilai</label>
            <input v-model.number="fNilai.nilai" type="number" min="0" max="100" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none" :class="fNilai.errors.nilai && 'border-red-500'" required>
            <p v-if="fNilai.errors.nilai" class="text-xs text-red-600">{{ fNilai.errors.nilai }}</p>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showAddModal=false" class="px-4 py-2 text-sm rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700" :disabled="fNilai.processing">Batal</button>
          <button type="submit" class="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white inline-flex items-center gap-2 disabled:opacity-60" :disabled="fNilai.processing">
            <svg v-if="fNilai.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"/><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"/></svg>
            <span>Simpan</span>
          </button>
        </div>
      </form>
    </Modal>

    <Modal :show="showDeleteModal" maxWidth="sm" title="Konfirmasi Hapus" @close="showDeleteModal=false">
      <p class="text-sm text-gray-600 mb-6">Yakin ingin menghapus nilai <strong>{{ deletingRow?.siswa?.nama }}</strong> untuk mapel <strong>{{ deletingRow?.mapel }}</strong> (nilai: {{ deletingRow?.nilai }})? Tindakan ini tidak dapat dibatalkan.</p>
      <div class="flex justify-end gap-3">
        <button class="px-4 py-2 text-sm rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700" @click="showDeleteModal=false">Batal</button>
        <button class="px-4 py-2 text-sm rounded-md bg-red-600 hover:bg-red-700 text-white" @click="destroyNilai">Hapus</button>
      </div>
    </Modal>
  </div>
  </AuthenticatedLayout>
</template>