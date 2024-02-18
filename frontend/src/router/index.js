import { route } from 'quasar/wrappers'
import { createRouter, createMemoryHistory, createWebHistory, createWebHashHistory } from 'vue-router'
import routes from './routes'
import Cookies from "js-cookie";

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default route(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory)

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE)
  })

  Router.beforeEach(  (to, from, next) => {

    // if (Cookies.get("user_type") === undefined) Cookies.remove("token");

    const isAuthenticated = typeof Cookies.get("token") === "string";
    const isRequiresAuth = to.meta.requiresAuth || false;

    console.log('isAuthenticated', isAuthenticated)
    console.log('isRequiresAuth', isRequiresAuth)

    if (isRequiresAuth && !isAuthenticated) {
      next({ name: 'LOGIN' });
    } else if (!isRequiresAuth && isAuthenticated) {
      next({ name: 'LOGIN' });
    } else {
      next();
    }

  })

  Router.afterEach((to) => {
    if (to?.meta?.breadCrumb)
      // from
      // Use next tick to handle router history correctly
      // see: https://github.com/vuejs/vue-router/issues/914#issuecomment-384477609
      // Vue.nextTick(() => {
      document.title = `${to.meta.breadCrumb} - MJ Vapors`;
  });

  function isAuthenticated() {
    return Cookies.get("token") != null;
  }

  return Router
})
