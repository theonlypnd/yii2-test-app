<template>
  <div class="card mb-4">
    <div class="card-header">Create Task</div>
    <div class="card-body">
      <form @submit.prevent="onSubmit">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" v-model.trim="username" required />
          </div>
          <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" v-model.trim="email" required />
          </div>
          <div class="col-12">
            <label class="form-label">Text</label>
            <textarea class="form-control" rows="3" v-model.trim="text" required></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Image</label>
            <input type="file" class="form-control" accept="image/*" @change="onImageChange" />
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
            <div class="row g-3">
              <div class="col-md-4">
                <div><strong>Username:</strong> <span class="text-truncate d-inline-block" style="max-width: 200px;">{{ username }}</span></div>
              </div>
              <div class="col-md-4">
                <div><strong>Email:</strong> <span class="text-truncate d-inline-block" style="max-width: 220px;">{{ email }}</span></div>
              </div>
              <div class="col-12">
                <div><strong>Text:</strong> <span class="text-truncate d-inline-block" style="max-width: 280px;">{{ text }}</span></div>
              </div>
              <div class="col-md-6">
                <div class="text-center">
                  <template v-if="previewUrl">
                    <img :src="previewUrl" alt="Task image" width="80" height="80" style="object-fit: cover;" />
                  </template>
                  <template v-else>
                    <span>No image</span>
                  </template>
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
import { ref } from 'vue'

const emit = defineEmits(['submit'])

const username = ref('')
const email = ref('')
const text = ref('')
const imageFile = ref(null)
const previewUrl = ref(null)
const showPreview = ref(false)

function onImageChange(e) {
  const file = e.target.files && e.target.files[0]
  imageFile.value = file || null
  previewUrl.value = file ? URL.createObjectURL(file) : null
}

function togglePreview() {
  showPreview.value = !showPreview.value
}

async function onSubmit() {
  const form = new FormData()
  form.append('username', username.value)
  form.append('email', email.value)
  form.append('text', text.value)
  if (imageFile.value) form.append('image', imageFile.value)
  emit('submit', form)
}
</script>
