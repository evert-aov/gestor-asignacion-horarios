import './bootstrap';
import './voice-recognition';

import Alpine from 'alpinejs';
import 'flowbite';

// Defer Alpine start for Livewire
window.Alpine = Alpine;
window.Alpine.plugin;

// Start Alpine after DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
});
