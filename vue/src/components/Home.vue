<template>
  <div>
    <TaskForm ref="taskForm" @submit="handleCreate" />

    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <span class="me-2">Sort:</span>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-secondary" :class="{ active: sortField === 'username' }" @click="setSort('username')">Username</button>
          <button class="btn btn-outline-secondary" :class="{ active: sortField === 'email' }" @click="setSort('email')">Email</button>
          <button class="btn btn-outline-secondary" :class="{ active: sortField === 'status' }" @click="setSort('status')">Status</button>
        </div>
        <div class="btn-group btn-group-sm ms-2">
          <button class="btn btn-outline-secondary" :class="{ active: sortOrder === 'asc' }" @click="setOrder('asc')">Asc</button>
          <button class="btn btn-outline-secondary" :class="{ active: sortOrder === 'desc' }" @click="setOrder('desc')">Desc</button>
        </div>
      </div>
      <Pagination :current-page="page" :total-pages="totalPages" @change="changePage" />
    </div>

    <TaskList
      :tasks="displayTasks"
      :sort-field="sortField"
      :sort-order="sortOrder"
      :is-admin="isAdmin"
      @sort-field="onSortField"
      @edit="editTask"
      @view="viewTask"
    />

    <div class="mt-3 d-flex justify-content-end">
      <Pagination :current-page="page" :total-pages="totalPages" @change="changePage" />
    </div>

    <!-- Modal overlay for viewing task details -->
    <div
      v-if="viewingTask"
      class="position-fixed top-0 start-0 w-100 h-100"
      style="background: rgba(0,0,0,0.5); z-index: 1050;"
      @click.self="closeModal"
    >
      <div
        class="bg-white rounded shadow"
        style="width: 90vw; max-width: 1200px; margin: 5vh auto; padding: 16px;"
      >
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Task Details</h5>
          <button class="btn btn-sm btn-outline-secondary" @click="closeModal">Close</button>
        </div>
        <div class="row g-3 align-items-start">
          <div v-if="viewingTask.image" class="col-md-3">
            <img :src="viewingTask.image" alt="Task image" width="160" height="160" style="object-fit: cover;" />
          </div>
          <div :class="viewingTask.image ? 'col-md-9' : 'col-12'">
            <div class="mb-2 d-flex align-items-center gap-2">
              <strong class="mb-0">Username:</strong>
              <span>{{ truncatePreview(viewingTask.username, 50) }}</span>
            </div>
            <div class="mb-2 d-flex align-items-center gap-2">
              <strong class="mb-0">Email:</strong>
              <span>{{ truncatePreview(viewingTask.email, 50) }}</span>
            </div>
            <div class="mb-2">
              <strong>Text:</strong>
              <div class="border rounded p-2" style="white-space: pre-wrap; word-break: break-word; line-height: 1.5; max-height: 7.5em; overflow-y: auto;">{{ viewingTask.text }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { getTasks, createTask, updateTask } from '../api/tasks.js'
import TaskForm from '../components/TaskForm.vue'
import TaskList from '../components/TaskList.vue'
import Pagination from '../components/Pagination.vue'

const props = defineProps({ isAdmin: { type: Boolean, default: false } })

const tasks = ref([])
const page = ref(1)
const totalPages = ref(1)
const sortField = ref('status')
const sortOrder = ref('desc')
const viewingTask = ref(null)

async function fetchTasks() {
  const serverSort = sortField.value === 'status' ? 'is_done' : sortField.value
  const res = await getTasks({ page: page.value, sort: serverSort, order: sortOrder.value })
  const data = res.data || {}
  const items = Array.isArray(data) ? data : (data.tasks || data.items || data.data || [])
  tasks.value = items
  const headers = res.headers || {}
  const pageCount = parseInt(headers['x-pagination-page-count'] || headers['X-Pagination-Page-Count'])
  const current = parseInt(headers['x-pagination-current-page'] || headers['X-Pagination-Current-Page'])
  if (Number.isFinite(pageCount)) totalPages.value = Math.max(1, pageCount)
  else {
    const total = data.total ?? data.totalCount ?? (data.pagination ? data.pagination.total : undefined)
    totalPages.value = data.pages ?? (total ? Math.max(1, Math.ceil(total / 3)) : 1)
  }
  if (Number.isFinite(current)) page.value = current
}

function changePage(p) {
  if (p < 1 || p > totalPages.value) return
  page.value = p
}

function setSort(field) {
  sortField.value = field
}

function onSortField(field) {
  // Toggle order when clicking table header and set the field
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  sortField.value = field
}

function setOrder(order) {
  sortOrder.value = order
}

async function handleCreate(form) {
  try {
    await createTask(form)
    page.value = 1
    await fetchTasks()
    if (taskForm.value && taskForm.value.reset) taskForm.value.reset()
  } catch (e) {
    const errs = extractErrors(e)
    if (taskForm.value && taskForm.value.setServerErrors) taskForm.value.setServerErrors(errs)
  }
}

async function editTask(task) {
  if (!props.isAdmin) return
  const newText = window.prompt('Edit task text:', task.text || '')
  if (newText === null) return
  const payload = { text: newText }
  await updateTask(task.id, payload)
  await fetchTasks()
}

function viewTask(task) {
  const original = tasks.value.find(t => String(t.id) === String(task.id)) || task
  viewingTask.value = original
}

onMounted(fetchTasks)
watch([page, sortField, sortOrder], fetchTasks)

const taskForm = ref(null)

function extractErrors(error) {
  const result = {}
  const data = error && error.response && error.response.data
  if (!data) return result
  const source = (data && data.errors !== undefined) ? data.errors : data

  if (Array.isArray(source)) {
    // Handle array of { field, message } objects from backend
    source.forEach((item) => {
      const field = item && (item.field || item.attribute || item.name)
      const message = item && (item.message || item.error || item.msg)
      if (field && message && !result[field]) result[field] = String(message)
    })
  } else if (source && typeof source === 'object') {
    // Handle object-shaped errors: { field: [messages] } or { field: 'message' }
    const fields = ['username', 'email', 'text', 'image']
    fields.forEach((f) => {
      const v = source[f]
      if (Array.isArray(v) && v.length) result[f] = String(v[0])
      else if (typeof v === 'string') result[f] = v
    })
  }
  if (!Object.keys(result).length && (data.message || data.error)) {
    result._error = data.message || data.error
  }
  return result
}

function closeModal() {
  viewingTask.value = null
}

// 100-char truncation for modal/preview labels
function truncatePreview(val, max = 100) {
  if (typeof val !== 'string') return val
  return val.length > max ? val.slice(0, max) + '…' : val
}

function truncate(val, max) {
  if (typeof val !== 'string') return val
  return val.length > max ? val.slice(0, max) + '…' : val
}

const displayTasks = computed(() => {
  return tasks.value.map(t => ({
    ...t,
    username: truncate(t?.username ?? '', 30),
    email: truncate(t?.email ?? '', 30),
  }))
})
</script>
