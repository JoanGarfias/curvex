import axios from 'axios';

// Configurar axios para incluir el token CSRF en todas las peticiones
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Obtener el token CSRF del meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = (token as HTMLMetaElement).content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

export default axios;
