<template>
  <nav>
    <ul class="pagination mb-0">
      <li class="page-item" :class="{ disabled: currentPage <= 1 }">
        <button class="page-link" :disabled="currentPage <= 1" @click="$emit('change', currentPage - 1)">Previous</button>
      </li>
      <li class="page-item active">
        <button class="page-link">{{ currentPage }}</button>
      </li>
      <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
        <button class="page-link" :disabled="currentPage >= totalPages" @click="$emit('change', currentPage + 1)">Next</button>
      </li>
    </ul>
  </nav>
</template>

<script setup>
defineProps({
  currentPage: { type: Number, required: true },
  totalPages: { type: Number, required: true }
})
</script>
