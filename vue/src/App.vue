<template>
  <div class="container">
    <div class="d-flex justify-content-between align-items-center my-3">
      <h1 class="mb-0">Tasks</h1>
      <div class="position-relative">
        <template v-if="!isAdmin">
          <button class="btn btn-sm btn-outline-primary" @click="toggleLogin">Log in as Admin</button>
          <div v-if="showLogin" class="card position-absolute end-0 mt-2" style="min-width: 280px; z-index: 100;">
            <div class="card-body">
              <div class="mb-2">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" v-model.trim="loginUsername" :class="{ 'is-invalid': loginErrors.username }" />
                <div v-if="loginErrors.username" class="invalid-feedback">{{ loginErrors.username }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" v-model.trim="loginPassword" :class="{ 'is-invalid': loginErrors.password }" />
                <div v-if="loginErrors.password" class="invalid-feedback">{{ loginErrors.password }}</div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-secondary" @click="cancelLogin">Cancel</button>
                <button class="btn btn-sm btn-primary" @click="submitLogin">Send</button>
              </div>
            </div>
          </div>
        </template>
        <template v-else>
          <button class="btn btn-sm btn-outline-danger" @click="submitLogout">Log Out</button>
        </template>
      </div>
    </div>

    <router-view />
  </div>
</template>

<script setup>
import { ref, provide, onMounted } from 'vue'

const isAdmin = ref(false)
provide('isAdmin', isAdmin)

const showLogin = ref(false)
const loginUsername = ref('')
const loginPassword = ref('')
const loginErrors = ref({ username: '', password: '' })

function toggleLogin() { showLogin.value = !showLogin.value }
function cancelLogin() {
  showLogin.value = false
  loginUsername.value = ''
  loginPassword.value = ''
  loginErrors.value = { username: '', password: '' }
}

async function submitLogin() {
  loginErrors.value = { username: '', password: '' }
  try {
    const res = await fetch('/site/login', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: loginUsername.value, password: loginPassword.value })
    })
    const data = await res.json()
    if (data.ok) {
      // Reload to ensure session state and server-side permissions apply
      window.location.reload()
    } else {
      const errs = data.errors || {}
      loginErrors.value = {
        username: Array.isArray(errs.username) ? errs.username[0] : (errs.username || ''),
        password: Array.isArray(errs.password) ? errs.password[0] : (errs.password || '')
      }
    }
  } catch (e) {
    loginErrors.value = { username: '', password: 'Login failed. Try again.' }
  }
}

async function submitLogout() {
  try {
    const res = await fetch('/site/logout', { method: 'POST', credentials: 'include' })
    const data = await res.json()
    if (data.ok) {
      window.location.reload()
    }
  } catch (e) {
    // ignore; stay on page
  }
}

async function fetchAuthStatus() {
  try {
    const res = await fetch('/site/auth-status', { credentials: 'include' })
    const data = await res.json()
    isAdmin.value = !!data.authenticated
  } catch (e) {
    isAdmin.value = false
  }
}

onMounted(fetchAuthStatus)
</script>