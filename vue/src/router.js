import { createRouter, createWebHistory } from 'vue-router';
import TaskList from './components/TaskList.vue';

const routes = [
  { path: '/', component: TaskList },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});
