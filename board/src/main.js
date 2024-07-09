import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

const app = createApp(App);
app.use(router);
app.mount('#app');


// import { createApp } from 'vue'
// import App from './App.vue'

// createApp(App).mount('#app')

// import { createApp } from 'vue';
// import App from './App.vue';
// import router from './router';

// createApp(App).use(router).mount('#app');

// import { createApp } from 'vue';
// import App from './App.vue';
// import router from './router';
// import 'bootstrap/dist/css/bootstrap.css';
// import 'bootstrap-vue/dist/bootstrap-vue.css';

// createApp(App).use(router).mount('#app');
