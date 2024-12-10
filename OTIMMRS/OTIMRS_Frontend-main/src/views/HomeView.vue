<script setup>
// import TheWelcome from '../components/TheWelcome.vue'
</script>

<template>
  <div class="bg">
    <div class="texts">
      <h2>Admin Dashboard</h2>
      <h1>GENERAL LUNA TOURISM</h1>
      <router-link to="/admin" class="button">
        ADMIN LOGIN
        <i class="material-icons">arrow_forward</i>
      </router-link>
    </div>
  </div>
</template>

<style scoped lang="scss">
.bg {
  min-height: 100vh;
  background: radial-gradient(circle, rgba(0,0,0,0.12) 0%, rgba(0,0,0,0.73) 100%), 
              url(../assets/img/background.jpg) no-repeat center center;
  background-size: cover;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.texts {
  text-align: center;
  color: white;

  h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
    font-weight: 300;
  }

  h1 {
    font-size: 3.5rem;
    margin-bottom: 2rem;
    font-weight: bold;
  }

  .button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #4CAF50;
    color: white;
    padding: 15px 30px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s;

    &:hover {
      background: #45a049;
    }

    i {
      font-size: 24px;
    }
  }
}
</style>