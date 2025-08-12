import { router } from '@inertiajs/vue3'
import 'bootstrap/dist/css/bootstrap.min.css'
import './bootstrap';
import './Assets/css/main.css';
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

import Vue3EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import NProgress from 'nprogress'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        // createApp({ render: () => h(App, props) })
        //     .use(plugin)
        //     .mount(el)
            const app = createApp({ render: () => h(App, props) })
            app.use(plugin)
            app.component('EasyDataTable', Vue3EasyDataTable)
            app.mount(el)
    },
})

router.on('start', () => {
    NProgress.start()
});

router.on('finish', () => {
    NProgress.done()
});
