<template>
  <div class="form-container">
    <div class="form-header">Регистрация</div>
    <form @submit.prevent="registerUser" class="form">
      <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div class="form-group">
        <label for="login">Логин</label>
        <input type="text" id="login" v-model="login" required>
      </div>
      <div class="form-group">
        <label for="password">Пароль</label>
        <div class="password-input">
          <input :type="showPassword ? 'text' : 'password'" id="password" v-model="password" required>
          <div class="password-icon" @click="toggleShowPassword">
            <img v-if="!showPassword" src="../icons/visibility.svg" alt="Показать пароль" class="icon">
            <img v-if="showPassword" src="../icons/visibility_off.svg" alt="Скрыть пароль" class="icon">
          </div>
        </div>
      </div>
      <button type="submit">Отправить</button>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      login: '',
      password: '',
      email: '',
      showPassword: false,
    };
  },
  methods: {
    async registerUser() {
      try {
        const data = JSON.stringify({
          login: this.login,
          password: this.password,
          email: this.email
        });
        const response = await fetch('http://localhost/X-men/back/register.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: data
        });
        const answer = await response.json();
        console.log(answer);
        
        if (answer.success) {
          this.$router.push('/login');
          alert('Вы успешно зарегистрированы!');
        } else {
          console.error('Ошибка регистрации:', answer.error);
        }
      } catch (err) {
        console.error('Ошибка:', err);
      }
    },
    toggleShowPassword() {
      this.showPassword = !this.showPassword;
    }
  }
};
</script>

<style>
.form-container {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
  border-radius: 16px;
  background-color: #D9D9D9;
}

.form-header {
  text-align: center;
  color: #032A4E;
  font-size: 24px;
  font-family: Inter, sans-serif;
  font-weight: 700;
  margin-bottom: 20px;
}

.form {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 15px;
}

label {
  color: black;
  font-size: 18px;
  font-family: Inter, sans-serif;
  font-weight: 600;
  margin-bottom: 5px;
  display: block;
}

input {
  width: 100%;
  height: 40px;
  border-radius: 16px;
  border: 2px солидный #032A4E;
  padding: 0 40px 0 10px;
  font-size: 16px;
  font-family: Inter, sans-serif;
  box-sizing: border-box;
}

.password-input {
  position: relative;
}

.password-icon {
  position: absolute;
  top: 50%;
  right: 10px;
  transform: translateY(-50%);
  cursor: pointer;
}

.icon {
  width: 24px;
  height: auto;
  position: relative;
  top: 4px;
}

button {
  width: 100%;
  height: 60px;
  background-color: #F4AE5B;
  color: white;
  font-size: 24px;
  font-family: Inter, sans-serif;
  font-weight: 600;
  border-radius: 16px;
  cursor: pointer;
  margin-top: 20px;
  border: none;
}
</style>
