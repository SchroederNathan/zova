import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Wait for DOM to be ready before starting Alpine
document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
});
