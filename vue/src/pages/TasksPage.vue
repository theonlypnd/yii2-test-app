<template>
  <div>
    <TaskForm @submit="handleCreate" />

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
      :tasks="tasks"
      :sort-field="sortField"
      :sort-order="sortOrder"
      :is-admin="isAdmin"
      @sort-field="setSort"
      @edit="editTask"
      @view="viewTask"
    />

    <div class="mt-3 d-flex justify-content-end">
      <Pagination :current-page="page" :total-pages="totalPages" @change="changePage" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
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

async function fetchTasks() {
  const res = await getTasks({ page: page.value, sort: sortField.value, order: sortOrder.value })
  const data = res.data || {}
  const items = Array.isArray(data) ? data : (data.tasks || data.items || data.data || [])
  tasks.value = items
  const total = data.total ?? data.totalCount ?? (data.pagination ? data.pagination.total : undefined)
  totalPages.value = data.pages ?? (total ? Math.max(1, Math.ceil(total / 3)) : 1)
}

function changePage(p) {
  if (p < 1 || p > totalPages.value) return
  page.value = p
}

function setSort(field) {
  sortField.value = field
}

function setOrder(order) {
  sortOrder.value = order
}

async function handleCreate(form) {
  await createTask(form)
  page.value = 1
  await fetchTasks()
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
  alert(task.text || '')
}

onMounted(fetchTasks)
watch([page, sortField, sortOrder], fetchTasks)
</script>
