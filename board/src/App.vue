<template>
  <div class="container">
    <h1 class="title" v-if="showHeader">Доски</h1>
    <p class="description" v-if="showHeader">Войдите, чтобы воспользоваться сервисом</p>
    <router-link to="/login" class="button login-button" v-if="showHeader">Вход</router-link>
    <router-link to="/register" class="button register-button" v-if="showHeader">Регистрация</router-link>
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'App',
  computed: {
    showHeader() {
      // Показывать заголовок только если не находимся на маршруте /board
      console.log('Current route:', this.$route.path);
      return this.$route.path !== '/board';
    }
  }
};
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
}

.title {
  font-size: 48px;
  font-family: 'Inter', sans-serif;
  font-weight: 900;
  font-style: italic;
  color: #FFFFFF; /* Белый цвет текста */
  -webkit-text-stroke: 2px #1362A0; /* Обводка букв */
  margin-bottom: 20px;
}

.description {
  font-size: 20px;
  font-family: 'Inter', sans-serif;
  font-weight: 600; /* Semi Bold */
  color: #4D5057;
  margin-bottom: 40px;
}

.button {
  display: inline-block;
  width: 268px;
  height: 60px;
  border-radius: 16px;
  font-size: 24px;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  text-align: center;
  line-height: 60px;
  text-decoration: none;
  cursor: pointer;
  margin-bottom: 20px;
}

.login-button {
  background-color: #F4AE5B;
  color: white;
}

.register-button {
  background-color: #1362A0;
  color: white;
}
</style>


<!-- <template>
  <div class="container">
    <h1 class="title">Доски</h1>
    <p class="description">Войдите, чтобы воспользоваться сервисом</p>
    <router-link to="/login" class="button login-button">Вход</router-link>
    <router-link to="/register" class="button register-button">Регистрация</router-link>
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'App'
};
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
}

.title {
  font-size: 48px;
  font-family: 'Inter', sans-serif;
  font-weight: 900;
  font-style: italic;
  color: #FFFFFF; /* Белый цвет текста */
  -webkit-text-stroke: 2px #1362A0; /* Обводка букв */
  margin-bottom: 20px;
}

.description {
  font-size: 20px;
  font-family: 'Inter', sans-serif;
  font-weight: 600; /* Semi Bold */
  color: #4D5057;
  margin-bottom: 40px;
}

.button {
  display: inline-block;
  width: 268px;
  height: 60px;
  border-radius: 16px;
  font-size: 24px;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  text-align: center;
  line-height: 60px;
  text-decoration: none;
  cursor: pointer;
  margin-bottom: 20px;
}

.login-button {
  background-color: #F4AE5B;
  color: white;
}

.register-button {
  background-color: #1362A0;
  color: white;
}
</style> -->

