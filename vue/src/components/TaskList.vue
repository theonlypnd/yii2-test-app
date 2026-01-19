<template>
  <div class="card">
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
              <template v-if="editingId === task.id">
                <textarea class="form-control form-control-sm" rows="2" v-model.trim="editText"></textarea>
              </template>
              <template v-else>
                <span class="text-truncate d-inline-block" style="max-width: 280px;">{{ task.text }}</span>
              </template>
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
              <template v-if="editingId === task.id">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" :id="`done-${task.id}`" v-model="editDone">
                  <label class="form-check-label" :for="`done-${task.id}`">Completed</label>
                </div>
              </template>
              <template v-else>
                <span :class="['badge', isDone(task) ? 'bg-success' : 'bg-secondary']">{{ isDone(task) ? 'Completed' : 'Pending' }}</span>
              </template>
            </td>
            <td class="text-end">
              <div class="d-flex justify-content-end align-items-center gap-2 flex-nowrap">
                <template v-if="editingId === task.id">
                  <button class="btn btn-sm btn-success" @click="save(task)">Save</button>
                  <button class="btn btn-sm btn-secondary" @click="cancel()">Cancel</button>
                </template>
                <template v-else>
                  <button v-if="isAdmin" class="btn btn-sm btn-outline-primary" @click="startEdit(task)">Edit</button>
                  <button class="btn btn-sm btn-outline-secondary" @click="$emit('view', task)">View</button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  tasks: { type: Array, required: true },
  sortField: { type: String, required: true },
  sortOrder: { type: String, required: true },
  isAdmin: { type: Boolean, default: false }
})

const emit = defineEmits(['sort-field', 'view', 'save'])

const editingId = ref(null)
const editText = ref('')
const editDone = ref(false)

function isDone(task) {
  // prefer backend boolean is_done; fallback to other shapes if present
  if (typeof task.is_done !== 'undefined') return !!task.is_done
  if (typeof task.completed !== 'undefined') return !!task.completed
  if (typeof task.status === 'string') return task.status.toLowerCase() === 'completed'
  return false
}

function startEdit(task) {
  if (!props.isAdmin) return
  editingId.value = task.id
  editText.value = task.text || ''
  editDone.value = isDone(task)
}

function cancel() {
  editingId.value = null
  editText.value = ''
  editDone.value = false
}

function save(task) {
  emit('save', { id: task.id, text: editText.value, is_done: !!editDone.value })
  cancel()
}
</script>
