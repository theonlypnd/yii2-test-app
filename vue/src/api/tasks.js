import axios from 'axios'

const api = axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
})

export function getTasks({ page = 1, sort = 'is_done', order = 'desc' } = {}) {
  const sortParam = order === 'desc' ? `-${sort}` : `${sort}`
  return api.get('/api/tasks', { params: { page, sort: sortParam } })
}

export function createTask(formData) {
  return api.post('/api/tasks', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

export function updateTask(id, payload) {
  const isFormData = payload instanceof FormData
  return api.put(`/api/tasks/${id}`, payload, {
    headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : undefined
  })
}
