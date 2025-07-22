import './bootstrap';

import { createApp } from 'vue';
import LeagueApp from './components/LeagueApp.vue';

// Import Bootstrap JavaScript
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Create Vue application
const app = createApp({});

// Register global components
app.component('league-app', LeagueApp);

// Mount the app
app.mount('#app');
