import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

import PublicBoard from "../components/PublicBoard.vue"
import Login from "../components/auth/Login.vue"
import Register from "../components/auth/Register.vue"
import ChangePassword from "../components/auth/ChangePassword.vue"
import Tasks from "../components/tasks/Tasks.vue"
import Orders from "../components/orders/Orders.vue"
import OrdersEmployees from "../components/orders/OrdersEmployees.vue"
import NewOrder from "../components/orders/NewOrder.vue"
import Projects from "../components/projects/Projects.vue"
import Users from "../components/users/Users.vue"
import User from "../components/users/User.vue"
import ProjectTasks from "../components/projects/ProjectTasks.vue"
import Task from "../components/tasks/Task.vue"
import Project from "../components/projects/Project.vue"
import Menu from "../components/menu/Menu.vue"
import Employees from "../components/employees/Employees.vue"
import AddProduct from "../components/menu/AddProduct.vue"
import AddEmployee from "../components/employees/AddEmployee.vue"
import Statistics from "../components/statistics/Statistics.vue"
import ChefsDishes from "../components/dishes/ChefsDishes.vue"
import Customers from "../components/customers/Customers.vue"

import { useUserStore } from "../stores/user.js"

import RouteRedirector from "../components/global/RouteRedirector.vue"

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: HomeView
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: () => import('../components/errors/NotFound.vue')
    },
    {
      path: '/redirect/:redirectTo',
      name: 'Redirect',
      component: RouteRedirector,
      props: route => ({ redirectTo: route.params.redirectTo })
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
      path: '/menu/add',
      name: 'AddProduct',
      component: AddProduct,
    }, 
    {
      path: '/employees/add',
      name: 'AddEmployee',
      component: AddEmployee,
    },
    {
      path: '/orders',
      name: 'Orders',
      component: Orders,
    },
    {
      path: '/chefsDishes',
      name: 'ChefsDishes',
      component: ChefsDishes,
    },
    {
      path: '/ordersEmployees',
      name: 'OrdersEmployees',
      component: OrdersEmployees,
    },
    {
      path: '/employees',
      name: 'Employees',
      component: Employees,
    },
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
      path: '/orders/new',
      name: 'NewOrder',
      component: NewOrder
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
      props: route => ({ id: -1, fixedProject: parseInt(route.params.id) })
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
      path: '/statistics',
      name: 'Statistics',
      component: Statistics
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue')
    },
    // Customers
    {
      path: '/customers',
      name: 'Customers',
      component: Customers,
    },
  ]
})
let handlingFirstRoute = true

//esta função serve para definir que tabs do menu cada user pode ver ficha 7 ponto 8 exemplo reports so os managers podem ver
//2 ifs iniciais servem para ver se o user esta loggado ou nao, se nao tiver, so pode ver o login e a home page
router.beforeEach((to, from, next) => {
  if (handlingFirstRoute) {
    handlingFirstRoute = false
    next({ name: 'Redirect', params: { redirectTo: to.fullPath } })
    return
  } else if (to.name == 'Redirect') {
    next()
    return
  }
  const userStore = useUserStore()
 /* if ((to.name == 'Login') || (to.name == 'Home')) {
    next()
    return
  }
  if (!userStore.user) {
    next({ name: 'Login' })
    return
  }
  /*if (to.name == 'Reports') {
    if (userStore.user.type != 'EM') {
      next({ name: 'home' })
      return
    }
  }
 if (to.name == 'User') {
    if ((userStore.user.type == 'EM') || (userStore.user.id == to.params.id)) {//ver detalhes do user so o Manager e o proprio user podem ver 
      next()
      return
    }
    next({ name: 'home' })
    return
  }*/
  next()
})

export default router
