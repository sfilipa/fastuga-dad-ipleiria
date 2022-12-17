<script setup>
import { useRouter, RouterLink, RouterView } from "vue-router"
import { ref, onMounted, inject } from "vue";

import { useUserStore } from './stores/user.js'

const router = useRouter()
const axios = inject("axios")
const toast = inject("toast")

const userStore = useUserStore()

const workInProgressProjects = ref([]);

const buttonSidebarExpand = ref(null)

const logout = async () => {
  if (await userStore.logout()) {
    toast.success('User has logged out of the application.')
    clickMenuOption()
    router.push({ name: 'Home' })
    userStore.clearUser()
  } else {
    toast.error('There was a problem logging out of the application!')
  }
}

const clickMenuOption = () => {
  if (window.getComputedStyle(buttonSidebarExpand.value).display !== "none") {
    buttonSidebarExpand.value.click()
  }
}

onMounted(() => {
  // const userId = 1
  // axios.get("users/" + userId + "/projects/inprogress")
  //   .then((response) => {
  //     workInProgressProjects.value = response.data.data;
  //   })
  //   .catch((error) => {
  //     console.log(error);
  //   })
})
</script>

<template>
  <nav class="navbar navbar-dark navbar-expand-md sticky-top flex-md-nowrap p-0 shadow fastuga-navbar">
    <div class="container-fluid">
      <router-link class="fastuga-colored-font fastuga-logo" :to="{ name: 'Home' }">
        <div class="menu-header">
          <img src="@/assets/restaurantFastugaLogo.png" alt="" class="d-inline-block align-text-top menu-logo" />
        </div>
      </router-link>
      <button id="buttonSidebarExpandId" ref="buttonSidebarExpand" class="navbar-toggler" type="button"
        data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav">
          <li class="nav-item" v-show="!userStore.user">
            <router-link class="nav-link fastuga-colored-font" :class="{ active: $route.name === 'Register' }" :to="{ name: 'Register' }"
              @click="clickMenuOption">
              <i class="bi bi-box-arrow-in-right"></i>
              Register
            </router-link>
          </li>
          <li class="nav-item" v-show="!userStore.user">
            <router-link class="nav-link fastuga-colored-font" :class="{ active: $route.name === 'Login' }" :to="{ name: 'Login' }"
              @click="clickMenuOption">
              <i class="bi bi-box-arrow-in-right"></i>
              Login
            </router-link>
          </li>
          <li class="nav-item dropdown" v-show="userStore.user">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              <img :src="userStore.userPhotoUrl" class="rounded-circle z-depth-0 avatar-img" alt="avatar image" />
              <span class="avatar-text">{{ userStore.user?.name ?? 'Anonymous' }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
              <li>
                <router-link class="dropdown-item fastuga-font" :class="{ active: $route.name == 'User' && $route.params.id == 1 }"
                  :to="{ name: 'User', params: { id: 1 } }" @click="clickMenuOption">
                  <!--Onde tem id = 1 $route.params.id == 1 e tem de se trocar para userStore.userId-->
                  <i class="bi bi-person-square"></i>Profile
                </router-link>
              </li>
              <li>
                <router-link class="dropdown-item fastuga-font" :class="{ active: $route.name === 'ChangePassword' }"
                  :to="{ name: 'ChangePassword' }" @click="clickMenuOption">
                  <i class="bi bi-key-fill"></i>
                  Change password
                </router-link>
              </li>
              <li>
                <hr class="dropdown-divider" />
              </li>
              <li>
                <a class="dropdown-item" @click.prevent="logout">
                  <i class="bi bi-arrow-right"></i>Logout
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid menu-full-height">
    <div class="row menu-full-height">
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
        <div class="position-sticky pt-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <router-link class="nav-link fastuga-font" :class="{ active: $route.name === 'PublicBoard' }"
                :to="{ name: 'PublicBoard' }" @click="clickMenuOption">
                <i class="bi bi-card-checklist"></i>
                Public Board
              </router-link>
            </li>
            <!-- <li class="nav-item">
              <router-link
                class="nav-link"
                :class="{ active: $route.name === 'CurrentTasks' }"
                :to="{ name: 'CurrentTasks' }"
              >
                <i class="bi bi-list-stars"></i>
                Menu
              </router-link>
            </li> -->
            <li class="nav-item">
              <router-link class="nav-link fastuga-font" :class="{ active: $route.name === 'Menu' }" :to="{ name: 'Menu' }"
                @click="clickMenuOption">
                <i class="bi bi-cup-straw"></i>
                Menu
              </router-link>
            </li>

            <li class="nav-item">
              <router-link class="nav-link fastuga-font" :class="{ active: $route.name === 'Orders' }" :to="{ name: 'Orders' }"
                @click="clickMenuOption">
                <i class="bi bi-list-stars"></i>
                Orders
              </router-link>
            </li>

            <li class="nav-item">
              <router-link class="nav-link fastuga-font" :class="{ active: $route.name === 'Employees' }" :to="{ name: 'Employees' }"
                @click="clickMenuOption">
                <i class="bi bi-people"></i>
                Employees
              </router-link>
            </li>

            <li class="nav-item">
              <router-link class="nav-link fastuga-font" :class="{ active: $route.name === 'Tasks' }" :to="{ name: 'Tasks' }"
                @click="clickMenuOption">
                <i class="bi bi-bell"></i>
                Notifications
              </router-link>
            </li>
            <!-- <li class="nav-item">
              <router-link
                class="nav-link"
                :class="{ active: $route.name === 'EmployeesMenu' }"
                :to="{ name: 'EmployeesMenu' }"
              >
                <i class="bi bi-person-workspace"></i>
                Employees
              </router-link>
            </li> -->
            <!-- <li class="nav-item">
              <router-link
                class="nav-link"
                :class="{ active: $route.name === 'Projects' }"
                :to="{ name: 'Projects' }"
              >
                <i class="bi bi-files"></i>
                Projects
              </router-link>
            </li>
            <li class="nav-item">
              <router-link
                class="nav-link"
                :class="{ active: $route.name === 'Users' }"
                :to="{ name: 'Users' }"
              >
                <i class="bi bi-people"></i>
                Team Members
              </router-link>
            </li> -->
            <li class="nav-item" v-show="userStore.user">
                <router-link class="nav-link fastuga-font" :class="{ active: $route.name === 'Statistics' }" :to="{ name: 'Statistics' }"
                  @click="clickMenuOption">
                  <i class="bi bi-graph-up"></i>
                  Statistics
                </router-link>
            </li>
          </ul>

          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted fastuga-font">
            <span>My Orders</span>
            <router-link class="link-secondary" :to="{ name: 'NewOrder' }" aria-label="Make a new order"
              @click="clickMenuOption">
              <i class="bi bi-xs bi-plus-circle"></i>
            </router-link>
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item" v-for="prj in workInProgressProjects" :key="prj.id">
              <router-link class="nav-link w-100 me-3"
                :class="{ active: $route.name == 'ProjectTasks' && $route.params.id == prj.id }"
                :to="{ name: 'ProjectTasks', params: { id: prj.id } }" @click="clickMenuOption">
                <i class="bi bi-file-ruled"></i>
                {{ prj.name }}
              </router-link>
            </li>
          </ul>

          <div class="d-block d-md-none">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
              <span>User</span>
            </h6>
            <ul class="nav flex-column mb-2">
              <li class="nav-item dropdown" v-show="userStore.user">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink2" role="button"
                  data-bs-toggle="dropdown" aria-expanded="false">
                  <img :src="userStore.userPhotoUrl" class="rounded-circle z-depth-0 avatar-img" alt="avatar image" />
                  <span class="avatar-text">{{ userStore.user?.name ?? 'Anonymous' }}</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink2">
                  <li>
                    <router-link class="dropdown-item"
                      :class="{ active: $route.name == 'User' && $route.params.id == 1 }"
                      :to="{ name: 'User', params: { id: 1 } }" @click="clickMenuOption">
                      <!--Onde tem id = 1 $route.params.id == 1 e tem de se trocar para userStore.userId-->
                      <i class="bi bi-person-square"></i>Profile
                    </router-link>
                  </li>
                  <li>
                    <router-link class="dropdown-item" :class="{ active: $route.name === 'ChangePassword' }"
                      :to="{ name: 'ChangePassword' }" @click="clickMenuOption">
                      <i class="bi bi-key-fill"></i>
                      Change password
                    </router-link>
                  </li>
                  <li>
                    <hr class="dropdown-divider" />
                  </li>
                  <li>
                    <a class="dropdown-item" href="#">
                      <i class="bi bi-arrow-right"></i>Logout
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 menu-full-height">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<style>
@import "./assets/dashboard.css";
@import url("https://fonts.googleapis.com/css2?family=Maven+Pro&display=swap");


.fastuga-logo{
  background-color: transparent;
}

.menu-header{
  vertical-align: middle;
  display: flex;
}

.menu-logo{
  width: 189px;
}

.fastuga-colored-font{
  color: white !important;
  font-size: 15px;
  font-family: "Maven Pro", sans-serif;
}

.fastuga-font {
  font-size: 15px;
  font-family: "Maven Pro", sans-serif;
}

.fastuga-navbar{
  background-color: #362222;
  height: 4rem;
}


.avatar-img {
  margin: -1.2rem 0.8rem -2rem 0.8rem;
  width: 3.3rem;
  height: 3.3rem;
}

.avatar-text {
  line-height: 2.2rem;
  margin: 1rem 0.5rem -2rem 0;
  padding-top: 1rem;
}

.dropdown-item {
  font-size: 0.875rem;
}

.btn:focus {
  outline: none;
  box-shadow: none;
}

#sidebarMenu {
  overflow-y: auto;
}

.spinner-font {
  margin: 2%;
  font-size: 10px;
  font-family: "Maven Pro", sans-serif;
}

.form-select:focus{
  border-color: rgba(164, 108, 108, 0.31) !important;
  box-shadow: 0 0 0 .25rem rgba(164, 108, 108, 0.31) !important;
}

.form-control:focus{
  border-color: rgba(164, 108, 108, 0.31) !important;
  box-shadow: 0 0 0 .25rem rgba(164, 108, 108, 0.31) !important;
}

.sidebar .nav-link.active{
  background-color: #ffa71dd6;
}

a:hover{
    color: #af7777 !important;
  }

html, body, #app, .menu-full-height{
  height: 100%;
}
</style>
