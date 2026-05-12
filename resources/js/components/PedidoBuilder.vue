<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    storeUrl: {
        type: String,
        required: true,
    },
    csrfToken: {
        type: String,
        required: true,
    },
});

const isOpen = ref(false);
const modalPanelRef = ref(null);
const search = ref('');
const quantities = reactive({});
const openCategories = reactive({});

const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const filteredCategories = computed(() => {
    return props.categories
        .map((category) => ({
            ...category,
            productos: category.productos.filter((product) => {
                if (!normalizedSearch.value) {
                    return true;
                }

                return product.nombre.toLowerCase().includes(normalizedSearch.value);
            }),
        }))
        .filter((category) => category.productos.length > 0);
});

const hasResults = computed(() => filteredCategories.value.length > 0);
const igicRate = 0.07;

const selectedProducts = computed(() => {
    return props.categories.flatMap((category) =>
        category.productos
            .filter((product) => Number(quantities[product.id]) > 0)
            .map((product) => ({
                ...product,
                cantidad: Number(quantities[product.id]),
            }))
    );
});

const orderBase = computed(() => {
    return selectedProducts.value.reduce((total, product) => {
        return total + (Number(product.precio) * Number(product.cantidad));
    }, 0);
});

const orderIgic = computed(() => orderBase.value * igicRate);
const orderTotal = computed(() => orderBase.value + orderIgic.value);

function formatCurrency(value) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR',
    }).format(value);
}

function priceWithIgic(price) {
    return Number(price) * (1 + igicRate);
}

watch(
    filteredCategories,
    (categories) => {
        categories.forEach((category) => {
            openCategories[category.id] = normalizedSearch.value !== '' || openCategories[category.id] === true;
        });
    },
    { immediate: true }
);

watch(isOpen, (value) => {
    if (!value) {
        search.value = '';
    }
});

function blockEnterInsideModal(event) {
    if (event.key !== 'Enter') {
        return;
    }

    const modalElement = modalPanelRef.value;

    if (!modalElement || !modalElement.contains(event.target)) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
}

watch(isOpen, (value) => {
    if (value) {
        document.addEventListener('keydown', blockEnterInsideModal, true);
        document.addEventListener('keypress', blockEnterInsideModal, true);
        document.addEventListener('keyup', blockEnterInsideModal, true);
        return;
    }

    document.removeEventListener('keydown', blockEnterInsideModal, true);
    document.removeEventListener('keypress', blockEnterInsideModal, true);
    document.removeEventListener('keyup', blockEnterInsideModal, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', blockEnterInsideModal, true);
    document.removeEventListener('keypress', blockEnterInsideModal, true);
    document.removeEventListener('keyup', blockEnterInsideModal, true);
});

function toggleCategory(categoryId) {
    openCategories[categoryId] = !openCategories[categoryId];
}

function updateQuantity(product, value) {
    const parsedValue = Number(value);

    if (!Number.isFinite(parsedValue) || parsedValue <= 0) {
        quantities[product.id] = '';
        return;
    }

    quantities[product.id] = Math.min(parsedValue, product.stock);
}

function clearSearch() {
    search.value = '';
}

function submitOrder() {
    if (!selectedProducts.value.length) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = props.storeUrl;
    form.style.display = 'none';

    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_token';
    csrfField.value = props.csrfToken;
    form.appendChild(csrfField);

    selectedProducts.value.forEach((product) => {
        const idField = document.createElement('input');
        idField.type = 'hidden';
        idField.name = `productos[${product.id}][id]`;
        idField.value = String(product.id);
        form.appendChild(idField);

        const quantityField = document.createElement('input');
        quantityField.type = 'hidden';
        quantityField.name = `productos[${product.id}][cantidad]`;
        quantityField.value = String(product.cantidad);
        form.appendChild(quantityField);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>

<template>
    <div class="space-y-4" @keydown.esc="isOpen = false">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Productos</h2>
                <p class="text-sm text-slate-500">Busca y prepara tu pedido desde aquí.</p>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <button class="btn-primary" type="button" @click="isOpen = true">Crear nuevo pedido</button>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="isOpen">
                <div class="modal-overlay" @click="isOpen = false"></div>

                <div ref="modalPanelRef" class="modal-panel max-h-[85vh] overflow-y-auto" @click.stop>
                    <div class="modal-header">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Crear nuevo pedido</h3>
                            <p class="text-sm text-slate-500">Busca, selecciona varios productos y define las cantidades.</p>
                        </div>

                        <button class="btn-secondary" type="button" @click="isOpen = false">Cerrar</button>
                    </div>

                    <div
                        class="space-y-4"
                        @keydown.capture.enter.prevent.stop
                    >
                        <div class="space-y-2">
                            <label for="buscar-producto-vue" class="text-sm font-medium text-slate-700">Buscar producto</label>
                            <div class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 shadow-sm transition">
                                <input
                                    id="buscar-producto-vue"
                                    v-model.trim="search"
                                    type="text"
                                    class="min-w-0 flex-1 appearance-none !border-0 bg-transparent py-3 text-sm text-slate-900 !outline-none !ring-0 !shadow-none focus:!border-0 focus:!outline-none focus:!ring-0 focus:!shadow-none focus-visible:!outline-none focus-visible:!ring-0"
                                    style="-webkit-appearance: none; border: 0; outline: none; box-shadow: none;"
                                    placeholder="Escribe el nombre del producto"
                                    autocomplete="off"
                                    autocorrect="off"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    @keydown.enter.prevent.stop
                                >

                                <button
                                    v-if="search"
                                    type="button"
                                    class="inline-flex h-5 w-5 shrink-0 items-center justify-center text-slate-500 transition hover:text-slate-900 focus:outline-none"
                                    aria-label="Limpiar búsqueda"
                                    @click="clearSearch"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 011.06 0L10 8.94l4.72-4.72a.75.75 0 111.06 1.06L11.06 10l4.72 4.72a.75.75 0 11-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 11-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div v-if="!categories.length" class="alert-warning">
                            No hay productos disponibles ahora mismo.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="category in filteredCategories"
                                :key="category.id"
                                class="rounded-2xl border border-slate-200 bg-white"
                            >
                                <button
                                    class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left text-sm font-semibold text-slate-700"
                                    type="button"
                                    @click="toggleCategory(category.id)"
                                >
                                    <span>{{ category.nombre }}</span>
                                    <svg
                                        class="h-4 w-4 text-slate-400 transition"
                                        :class="{ 'rotate-180': openCategories[category.id] }"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div v-if="openCategories[category.id]" class="space-y-3 border-t border-slate-100 px-4 py-4">
                                    <label
                                        v-for="product in category.productos"
                                        :key="product.id"
                                        class="block rounded-xl border border-slate-200 p-3"
                                    >
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p class="font-semibold text-slate-800">{{ product.nombre }}</p>
                                                <p class="text-sm text-slate-500">
                                                    {{ formatCurrency(priceWithIgic(product.precio)) }}/{{ product.unidad }} · IGIC incl. · Stock: {{ product.stock }}
                                                </p>
                                            </div>

                                            <div class="w-full sm:w-28">
                                                <input
                                                    :value="quantities[product.id] || ''"
                                                    type="number"
                                                    class="input-base"
                                                    min="1"
                                                    :max="product.stock"
                                                    placeholder="Cantidad"
                                                    @keydown.enter.prevent.stop
                                                    @input="updateQuantity(product, $event.target.value)"
                                                >
                                            </div>
                                        </div>

                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="search && !hasResults" class="alert-warning">
                            No se encontraron productos con esa búsqueda.
                        </div>

                        <div v-if="selectedProducts.length" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-700">Resumen del pedido</p>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                <div
                                    v-for="product in selectedProducts"
                                    :key="`selected-${product.id}`"
                                    class="flex items-center justify-between gap-3"
                                >
                                    <span>{{ product.nombre }}</span>
                                    <span>{{ product.cantidad }} x {{ formatCurrency(priceWithIgic(product.precio)) }}</span>
                                </div>
                            </div>
                            <div class="mt-4 space-y-1 border-t border-slate-200 pt-3 text-sm">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>Base imponible</span>
                                    <span>{{ formatCurrency(orderBase) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>IGIC  7%</span>
                                    <span>{{ formatCurrency(orderIgic) }}</span>
                                </div>
                                <div class="flex items-center justify-between font-semibold text-slate-900">
                                    <span>Total</span>
                                    <span>{{ formatCurrency(orderTotal) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" class="btn-success" @click="submitOrder">Realizar pedido</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
