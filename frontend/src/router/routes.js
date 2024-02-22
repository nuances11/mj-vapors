
const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      {
        path: '',
        component: () => import('pages/IndexPage.vue'),
        name: 'DASHBOARD',
        meta: { breadCrumb: "Dashboard" },
      },
      {
        path: '/products',
        component: () => import('pages/Products/index.vue'),
        meta: { breadCrumb: "Products" },
      },
      {
        path: '/products/attributes',
        component: () => import('pages/Products/AttributeList.vue'),
        meta: { breadCrumb: "Product Attribute List" },
      },
      {
        path: '/users',
        component: () => import('pages/Users/UserList.vue'),
        meta: { breadCrumb: "Users" },
      },
      {
        path: '/branches',
        component: () => import('pages/Branches/BranchList.vue'),
        meta: { breadCrumb: "Branches" },
      },
    ],
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: "/login",
    component: () => import("pages/LoginPage.vue"),
    name: "LOGIN",
    meta: { breadCrumb: "Login" },
  },



  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
