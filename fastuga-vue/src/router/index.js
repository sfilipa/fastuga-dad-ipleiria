import { createRouter, createWebHistory } from 'vue-router'

import { useUserStore } from "../stores/user.js"


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: () => import('../views/HomeView.vue')
    },
    {
      path: '/unauthorized',
      name: 'Unauthorized',
      component: () => import('../components/errors/Unauthorized.vue')
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: () => import('../components/errors/NotFound.vue')
    },
    {
      path: '/redirect/:redirectTo',
      name: 'Redirect',
      component: () => import('../components/global/RouteRedirector.vue'),
      props: route => ({ redirectTo: route.params.redirectTo })
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('../components/auth/Login.vue')
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('../components/auth/Register.vue')
    },
    {
      path: '/password',
      name: 'ChangePassword',
      component: () => import('../components/auth/ChangePassword.vue')
    },
    {
      path: '/publicboard',
      name: 'PublicBoard',
      component: () => import('../components/PublicBoard.vue')
    },
    {
      path: '/menu',
      name: 'Menu',
      component: () => import('../components/menu/Menu.vue'),
      props: { menuTitle: 'Menu' }
    },
    {
      path: '/menu/add',
      name: 'AddProduct',
      component: () => import('../components/menu/AddProduct.vue'),
    }, 
    {
      path: '/employees/add',
      name: 'AddEmployee',
      component: () => import('../components/employees/AddEmployee.vue'),
    },
    {
      path: '/orders',
      name: 'Orders',
      component: () => import('../components/orders/Orders.vue'),
    },
    {
      path: '/chefsDishes',
      name: 'ChefsDishes',
      component: () => import('../components/dishes/ChefsDishes.vue'),
    },
    {
      path: '/ordersEmployees',
      name: 'OrdersEmployees',
      component: () => import('../components/orders/OrdersEmployees.vue'),
    },
    {
      path: '/employees',
      name: 'Employees',
      component: () => import('../components/employees/Employees.vue'),
    },
    {
      path: '/orders/new',
      name: 'NewOrder',
      component: () => import('../components/orders/NewOrder.vue')
    },
    {
      path: '/users',
      name: 'Users',
      component: () => import('../components/users/Users.vue'),
    },
    {
      path: '/users/:id',
      name: 'User',
      component: () => import('../components/users/User.vue'),
      //props: true
      // Replaced with the following line to ensure that id is a number
      props: route => ({ id: parseInt(route.params.id) })
    },
    {
      path: '/statistics',
      name: 'Statistics',
      component: () => import('../components/statistics/Statistics.vue')
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
      component: () => import('../components/customers/Customers.vue'),
    },
  ]
})
let handlingFirstRoute = true

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

  if(to.name == 'Employees' || to.name=='Customers' || to.name=='AddProduct'){
    if(!userStore.user || userStore.user.type != 'EM'){
      next({
        name: "Unauthorized"
      })
      return
    }
  }

  if(to.name == 'Statistics'){
    if(!userStore.user){
      next({
        name: "Unauthorized"
      })
      return
    }
  }

  if(to.name == 'NewOrder' || to.name == 'AddProduct'){
    if(userStore.user && userStore.user.type != 'C'){
      next({
        name: "Unauthorized"
      })
      return
    }
  }

  if(to.name == 'ChefsDishes'){
    if(!userStore.user || userStore.user.type != 'EC'){
      next({
        name: "Unauthorized"
      })
      return
    }
  }

  if(to.name == 'OrdersEmployees'){
    if(!userStore.user || userStore.user.type != 'ED'){
      next({
        name: "Unauthorized"
      })
      return
    }
  }

   if(to.name == 'Orders'){
     if(!userStore.user || userStore.user.type == 'C'){
       next({
         name: "Unauthorized"
       })
       return
     }
   }

  next()
})

export default router
