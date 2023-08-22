import { createApp } from 'vue';
import App from '../views/App.vue'
import store from './vuex/store';
import utility from '../utility';

import { DataService } from './DataService';
import VueFeather from 'vue-feather';

const app = createApp(App);

app.component(VueFeather.name, VueFeather);
app.use(store);

app.config.globalProperties.$utility = utility;
app.config.globalProperties.$http = DataService;

export default app;