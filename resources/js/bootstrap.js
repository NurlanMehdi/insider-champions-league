import axios from 'axios';
import 'bootstrap';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Make Bootstrap available globally
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
