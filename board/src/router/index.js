import { createRouter, createWebHistory } from 'vue-router';
import LoginForm from '@/components/LoginForm.vue';
import RegistrationForm from '@/components/RegistrationForm.vue';
import BoardPage from '@/components/BoardPage.vue';

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    component: LoginForm
  },
  {
    path: '/register',
    component: RegistrationForm
  },
  {
    path: '/board',
    name: 'BoardPage',
    component: BoardPage,
    meta: { requiresAuth: true },
    props: ({ userId: localStorage.getItem('userid') })
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    const userId = localStorage.getItem('userid');
    if (!userId) {
      next('/login');
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
