<template>
  <div class="card">
    <p>TaskList component loaded!</p>
    <div class="card-body p-0">
      <table class="table mb-0">
        <thead>
          <tr>
            <th role="button" @click="$emit('sort-field', 'username')"> Username <span v-if="sortField === 'username'" class="ms-2">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span></th>
            <th role="button" @click="$emit('sort-field', 'email')"> Email <span v-if="sortField === 'email'" class="ms-2">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span></th>
            <th>Text</th>
            <th>Image</th>
            <th role="button" @click="$emit('sort-field', 'status')"> Status <span class="ms-2">{{ sortField === 'status' ? (sortOrder === 'asc' ? '▲' : '▼') : '' }}</span></th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="task in tasks" :key="task.id" class="align-middle">
            <td>
              <span class="text-truncate d-inline-block" style="max-width: 200px;">{{ task.username }}</span>
            </td>
            <td>
              <span class="text-truncate d-inline-block" style="max-width: 220px;">{{ task.email }}</span>
            </td>
            <td>
              <span class="text-truncate d-inline-block" style="max-width: 280px;">{{ task.text }}</span>
            </td>
            <td class="text-center">
              <template v-if="task.image">
                <img :src="task.image" alt="Task image" width="80" height="80" style="object-fit: cover;" />
              </template>
              <template v-else>
                <span>No image</span>
              </template>
            </td>
            <td>
              <span :class="['badge', task.completed || task.status === 'Completed' ? 'bg-success' : 'bg-secondary']">{{ task.completed || task.status === 'Completed' ? 'Completed' : 'Pending' }}</span>
            </td>
            <td class="text-end">
              <button v-if="isAdmin" class="btn btn-sm btn-outline-primary" @click="$emit('edit', task)"> Edit </button>
              <button class="btn btn-sm btn-outline-secondary ms-2" @click="$emit('view', task)">View</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  tasks: { type: Array, required: true },
  sortField: { type: String, required: true },
  sortOrder: { type: String, required: true },
  isAdmin: { type: Boolean, default: false }
})
</script>
