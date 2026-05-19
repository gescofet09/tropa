import './bootstrap';

import { createApp } from 'vue';
import PedidoBuilder from './components/PedidoBuilder.vue';

function closeModal(modal) {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openModal(modal) {
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

document.addEventListener('click', (event) => {
    const openButton = event.target.closest('[data-modal-open]');
    const closeButton = event.target.closest('[data-modal-close]');

    if (openButton) {
        const modal = document.getElementById(openButton.dataset.modalOpen);

        if (modal) {
            openModal(modal);
        }
    }

    if (closeButton) {
        const modal = closeButton.closest('[data-modal]');

        if (modal) {
            closeModal(modal);
        }
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    document.querySelectorAll('[data-modal]:not(.hidden)').forEach(closeModal);
});

function autoDismissMessages() {
    const messages = document.querySelectorAll('.alert-success, .alert-danger, [data-auto-dismiss]');

    messages.forEach((message) => {
        if (message.dataset.dismissScheduled === 'true') {
            return;
        }

        message.dataset.dismissScheduled = 'true';

        window.setTimeout(() => {
            message.classList.add('is-dismissing');

            window.setTimeout(() => {
                message.remove();
            }, 300);
        }, 3000);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoDismissMessages);
} else {
    autoDismissMessages();
}

// Vue solo se monta en la pantalla de cliente, donde existe este contenedor.
const pedidoBuilderRoot = document.getElementById('pedido-builder-root');

if (pedidoBuilderRoot) {
    // Blade pasa categorias, URL y token CSRF mediante atributos data-*.
    createApp(PedidoBuilder, {
        categories: JSON.parse(pedidoBuilderRoot.dataset.categories || '[]'),
        storeUrl: pedidoBuilderRoot.dataset.storeUrl || '',
        csrfToken: pedidoBuilderRoot.dataset.csrfToken || '',
    }).mount(pedidoBuilderRoot);
}
