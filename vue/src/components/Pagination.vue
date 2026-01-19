<template>
  <nav>
    <ul class="pagination mb-0">
      <li class="page-item" :class="{ disabled: currentPage <= 1 }">
        <button class="page-link" :disabled="currentPage <= 1" @click="$emit('change', currentPage - 1)">Previous</button>
      </li>

      <li v-for="(item, idx) in pages" :key="itemKey(item, idx)" class="page-item" :class="{ active: isActive(item), disabled: item === '…' }">
        <button class="page-link" :disabled="item === '…' || isActive(item)" @click="onClick(item)">{{ item }}</button>
      </li>

      <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
        <button class="page-link" :disabled="currentPage >= totalPages" @click="$emit('change', currentPage + 1)">Next</button>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  totalPages: { type: Number, required: true }
})

const pages = computed(() => {
  const tp = props.totalPages || 1
  const cp = Math.min(Math.max(props.currentPage || 1, 1), tp)

  if (tp <= 5) {
    return Array.from({ length: tp }, (_, i) => i + 1)
  }

  const res = []
  const start = Math.max(2, cp - 1)
  const end = Math.min(tp - 1, cp + 1)

  // always first
  res.push(1)
  // left ellipsis if there's a gap between 1 and start
  if (start > 2) res.push('…')
  // pages window around current
  for (let p = start; p <= end; p++) {
    res.push(p)
  }
  // right ellipsis if there's a gap between end and last-1
  if (end < tp - 1) res.push('…')
  // always last
  res.push(tp)

  return res
})

function isActive(item) {
  return typeof item === 'number' && item === props.currentPage
}

function onClick(item) {
  if (typeof item === 'number') {
    // Emit only if different
    if (item !== props.currentPage) {
      emitChange(item)
    }
  }
}

function itemKey(item, idx) {
  return typeof item === 'number' ? `p-${item}` : `ellipsis-${idx}`
}

function emitChange(p) {
  // enforce bounds
  const tp = props.totalPages || 1
  const target = Math.min(Math.max(p, 1), tp)
  // Note: using $emit requires defineEmits in <script setup>
  emits('change', target)
}

const emits = defineEmits(['change'])
</script>
