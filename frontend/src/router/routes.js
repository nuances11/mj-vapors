export const userTypes = {
  ADMIN: "admin",
  ALL: "all",
};


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
        component: () => import('pages/Products/ProductList.vue'),
        meta: { breadCrumb: "Products", isAdminOnly: true },
      },
      {
        path: '/products/attributes',
        component: () => import('pages/Products/AttributeList.vue'),
        meta: { breadCrumb: "Product Attribute List", isAdminOnly: true },
      },
      {
        path: '/users',
        component: () => import('pages/Users/UserList.vue'),
        meta: { breadCrumb: "Users", isAdminOnly: true },
      },
      {
        path: '/branches',
        component: () => import('pages/Branches/BranchList.vue'),
        meta: { breadCrumb: "Branches", isAdminOnly: true },
      },
      {
        path: '/inventory',
        component: () => import('pages/Inventory/InventoryList.vue'),
        meta: { breadCrumb: "Inventory", isAdminOnly: true },
      },
      {
        path: '/transactions',
        component: () => import('pages/Transactions/TransactionDashboard.vue'),
        meta: { breadCrumb: "Transactions" },
      },
      {
        path: '/settings',
        component: () => import('pages/Settings/SettingsDashboard.vue'),
        meta: { breadCrumb: "Settings", isAdminOnly: true },
      },
      {
        path: '/reports/transactions',
        component: () => import('pages/Reports/TransactionReport.vue'),
        meta: { breadCrumb: "Transaction Report", isAdminOnly: true },
      },
      {
        path: '/reports/users',
        component: () => import('pages/Reports/UsersReport.vue'),
        meta: { breadCrumb: "Users Report", isAdminOnly: true },
      },
      {
        path: '/reports/time-tracking',
        component: () => import('pages/Reports/TimeTracking.vue'),
        meta: { breadCrumb: "Time Tracking", isAdminOnly: true },
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
