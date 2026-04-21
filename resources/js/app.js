import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import PedidoBuilder from './components/PedidoBuilder.vue';

window.Alpine = Alpine;

Alpine.start();

const pedidoBuilderRoot = document.getElementById('pedido-builder-root');

if (pedidoBuilderRoot) {
    createApp(PedidoBuilder, {
        categories: JSON.parse(pedidoBuilderRoot.dataset.categories || '[]'),
        storeUrl: pedidoBuilderRoot.dataset.storeUrl || '',
        csrfToken: pedidoBuilderRoot.dataset.csrfToken || '',
    }).mount(pedidoBuilderRoot);
}
