<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const toasts = ref([])
let id = 0

function push(message, type='success') {
  if (!message) return
  const item = { id: ++id, message, type }
  toasts.value.push(item)
  setTimeout(()=> remove(item.id), 4000)
}
function remove(i){ toasts.value = toasts.value.filter(t=>t.id!==i) }

watch(()=> page.props.flash?.success, v=> v && push(v,'success'))
watch(()=> page.props.flash?.error, v=> v && push(v,'error'))

onMounted(()=> {
  if (page.props.flash?.success) push(page.props.flash.success,'success')
  if (page.props.flash?.error) push(page.props.flash.error,'error')
})
</script>
<template>
  <div class="fixed top-4 right-4 z-50 space-y-2 w-72">
    <div v-for="t in toasts" :key="t.id" class="flex items-start gap-3 rounded-lg px-4 py-3 shadow text-sm text-white"
      :class="t.type==='success' ? 'bg-emerald-600' : 'bg-rose-600'">
      <div class="flex-1">{{ t.message }}</div>
      <button class="opacity-70 hover:opacity-100" @click="remove(t.id)">&times;</button>
    </div>
  </div>
</template>