<template>
  <div class="card mb-4">
    <div class="card-header">Create Task</div>
    <div class="card-body">
      <form @submit.prevent="onSubmit" novalidate>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Username</label>
            <input
              type="text"
              class="form-control"
              :class="{ 'is-invalid': errors.username }"
              v-model.trim="username"
              @input="onFieldInput('username')"
              @blur="onFieldBlur('username')"
              required
            />
            <div v-if="errors.username" class="invalid-feedback">{{ errors.username }}</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Email</label>
            <input
              type="email"
              class="form-control"
              :class="{ 'is-invalid': errors.email }"
              v-model.trim="email"
              @input="onFieldInput('email')"
              @blur="onFieldBlur('email')"
              required
            />
            <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
          </div>
          <div class="col-12">
            <label class="form-label">Text</label>
            <textarea class="form-control" :class="{ 'is-invalid': errors.text }" rows="3" v-model.trim="text" @blur="onTextBlur" required></textarea>
            <div v-if="errors.text" class="invalid-feedback">{{ errors.text }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Image</label>
            <input type="file" class="form-control" :class="{ 'is-invalid': errors.image }" accept="image/*" @change="onImageChange" />
            <div v-if="errors.image" class="invalid-feedback">{{ errors.image }}</div>
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" @click="togglePreview"> Preview </button>
            <button type="submit" class="btn btn-primary"> Submit </button>
          </div>
        </div>
      </form>
      <div v-if="showPreview" class="mt-3">
        <div class="card">
          <div class="card-header">Preview</div>
          <div class="card-body">
            <div class="row g-3 align-items-start">
              <div v-if="previewUrl" class="col-md-3">
                <img :src="previewUrl" alt="Task image" width="120" height="120" style="object-fit: cover;" />
              </div>
              <div :class="previewUrl ? 'col-md-9' : 'col-12'">
                <div class="mb-2 d-flex align-items-center gap-2">
                  <strong class="mb-0">Username:</strong>
                  <span>{{ truncatePreview(username, 50) }}</span>
                </div>
                <div class="mb-2 d-flex align-items-center gap-2">
                  <strong class="mb-0">Email:</strong>
                  <span>{{ truncatePreview(email, 50) }}</span>
                </div>
                <div class="mb-2">
                  <strong>Text:</strong>
                  <div class="border rounded p-2" style="white-space: pre-wrap; word-break: break-word; line-height: 1.5; max-height: 7.5em; overflow-y: auto;">{{ text }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const emit = defineEmits(['submit'])

const username = ref('')
const email = ref('')
const text = ref('')
const imageFile = ref(null)
const previewUrl = ref(null)
const showPreview = ref(false)
const errors = reactive({ username: '', email: '', text: '', image: '' })

function onImageChange(e) {
  const file = e.target.files && e.target.files[0]
  imageFile.value = file || null
  previewUrl.value = file ? URL.createObjectURL(file) : null
}

function togglePreview() {
  showPreview.value = !showPreview.value
}

async function onSubmit() {
  clearErrors()
  if (!validate()) return

  const form = new FormData()
  form.append('username', username.value)
  form.append('email', email.value)
  form.append('text', text.value)
  if (imageFile.value) form.append('image', imageFile.value)
  emit('submit', form)
}

function validate() {
  let ok = true
  if (!username.value) {
    errors.username = 'Username is required.'
    ok = false
  } else if (username.value.length > 255) {
    errors.username = 'Username must be at most 255 characters.'
    ok = false
  }

  if (!email.value) {
    errors.email = 'Email is required.'
    ok = false
  } else if (email.value.length > 255) {
    errors.email = 'Email must be at most 255 characters.'
    ok = false
  } else if (!isValidEmail(email.value)) {
    errors.email = 'Email is not a valid email address.'
    ok = false
  }

  if (!text.value) {
    errors.text = 'Text is required.'
    ok = false
  }

  return ok
}

function isValidEmail(val) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(val)
}

function clearErrors() {
  errors.username = ''
  errors.email = ''
  errors.text = ''
  errors.image = ''
}

function reset() {
  username.value = ''
  email.value = ''
  text.value = ''
  imageFile.value = null
  previewUrl.value = null
  showPreview.value = false
  clearErrors()
}

function setServerErrors(serverErrors = {}) {
  clearErrors()
  if (serverErrors.username) errors.username = serverErrors.username
  if (serverErrors.email) errors.email = serverErrors.email
  if (serverErrors.text) errors.text = serverErrors.text
  if (serverErrors.image) errors.image = serverErrors.image
}

function onFieldInput(field) {
  const val = field === 'username' ? username.value : field === 'email' ? email.value : ''
  if (val === '') {
    // Don't show required errors on input per request
    if (field in errors) errors[field] = ''
    return
  }
  if ((field === 'username' || field === 'email') && val.length > 255) {
    errors[field] = (field === 'username')
      ? 'Username must be at most 255 characters.'
      : 'Email must be at most 255 characters.'
  } else {
    // Clear any previous error during input when within limits
    if (field in errors) errors[field] = ''
  }
}

function onFieldBlur(field) {
  const val = field === 'username' ? username.value : field === 'email' ? email.value : ''
  if (val === '') {
    // Do not validate empty field on blur (required enforced on submit)
    if (field in errors) errors[field] = ''
    return
  }
  if (field === 'username') {
    if (val.length > 255) {
      errors.username = 'Username must be at most 255 characters.'
    } else {
      errors.username = ''
    }
  }
  if (field === 'email') {
    if (val.length > 255) {
      errors.email = 'Email must be at most 255 characters.'
    } else if (!isValidEmail(val)) {
      errors.email = 'Email is not a valid email address.'
    } else {
      errors.email = ''
    }
  }
}

function onTextBlur() {
  if (text.value !== '') {
    errors.text = ''
  }
}

defineExpose({ reset, setServerErrors })

function truncatePreview(val, max = 50) {
  if (typeof val !== 'string') return val
  return val.length > max ? val.slice(0, max) + '…' : val
}
</script>
