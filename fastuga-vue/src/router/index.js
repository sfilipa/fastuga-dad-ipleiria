import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

import PublicBoard from "../components/PublicBoard.vue"
import Login from "../components/auth/Login.vue"
import Register from "../components/auth/Register.vue"
import ChangePassword from "../components/auth/ChangePassword.vue"
import Tasks from "../components/tasks/Tasks.vue"
import Orders from "../components/orders/Orders.vue"
import Projects from "../components/projects/Projects.vue"
import Users from "../components/users/Users.vue"
import User from "../components/users/User.vue"
import ProjectTasks from "../components/projects/ProjectTasks.vue"
import Task from "../components/tasks/Task.vue"
import Project from "../components/projects/Project.vue"
import Menu from "../components/menu/Menu.vue"
// import EmployeesMenu from "../components/users/EmployeesMenu.vue"

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/login',
      name: 'Login',
      component: Login
    },
    {
      path: '/register',
      name: 'Register',
      component: Register
    },
    {
      path: '/password',
      name: 'ChangePassword',
      component: ChangePassword
    },
    {
      path: '/publicboard',
      name: 'PublicBoard',
      component: PublicBoard
    },
    {
      path: '/menu',
      name: 'Menu',
      component: Menu,
      props: { menuTitle: 'Menu' }
    },
    {
      path: '/orders',
      name: 'Orders',
      component: Orders,
    },
    // {
    //   path: '/employeesMenu',
    //   name: 'EmployeesMenu',
    //   component: EmployeesMenu
    // },
    // {
    //   path: '/notifications',
    //   name: 'Notifications',
    //   component: Notifications,
    //   props: { menuTitle: 'Notifications' }
    // },
    {
      path: '/tasks',
      name: 'Tasks',
      component: Tasks,
    },
    {
      path: '/tasks/current',
      name: 'CurrentTasks',
      component: Tasks,
      props: { onlyCurrentTasks: true, tasksTitle: 'Current Tasks' }
    },
    {
      path: '/projects',
      name: 'Projects',
      component: Projects,
    },
    {
      path: '/projects/new',
      name: 'NewProject',
      component: Project,
      props: { id: -1 }
    },
    {
      path: '/projects/:id',
      name: 'Project',
      component: Project,
      props: route => ({ id: parseInt(route.params.id) })     
    },
    {
      path: '/users',
      name: 'Users',
      component: Users,
    },
    {
      path: '/users/:id',
      name: 'User',
      component: User,
      //props: true
      // Replaced with the following line to ensure that id is a number
      props: route => ({ id: parseInt(route.params.id) })
    }, 
    {
      path: '/projects/:id/tasks',
      name: 'ProjectTasks',
      component: ProjectTasks,
      props: route => ({ id: parseInt(route.params.id) })
    },
    {
      path: '/projects/:id/tasks/new',
      name: 'NewTaskOfProject',
      component: Task,
      props: route => ({ id:-1, fixedProject:  parseInt(route.params.id) })
    },
    {
      path: '/tasks/new',
      name: 'NewTask',
      component: Task,
      props: { id: -1 }
    },
    {
      path: '/tasks/:id',
      name: 'Task',
      component: Task,
      props: route => ({ id: parseInt(route.params.id) })    
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue')
    }
  ]
})

export default router
