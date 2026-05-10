<x-app-layout>
    <x-slot name="title">Gestión de Facturas</x-slot>

    <div class="py-12" x-data="{ 
        activeTab: 'pendientes',
        editModal: false,
        payModal: false,
        selectedFactura: null,
        
        openEdit(factura) {
            this.selectedFactura = JSON.parse(JSON.stringify(factura));
            $dispatch('open-modal', 'edit-factura');
        },
        
        openPay(factura) {
            this.selectedFactura = JSON.parse(JSON.stringify(factura));
            $dispatch('open-modal', 'pay-factura');
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Módulo de Facturación (DEBUG: Vista Cargada)</h2>
                        <x-alert-messages />
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'pendientes'" 
                                    :class="activeTab === 'pendientes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                                <i class="fas fa-clock mr-2"></i> Pendientes
                                <span class="ml-2 bg-yellow-100 text-yellow-800 py-0.5 px-2.5 rounded-full text-xs font-medium">{{ $pendientes->count() }}</span>
                            </button>
                            <button @click="activeTab = 'pagadas'" 
                                    :class="activeTab === 'pagadas' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                                <i class="fas fa-check-circle mr-2"></i> Pagadas
                                <span class="ml-2 bg-green-100 text-green-800 py-0.5 px-2.5 rounded-full text-xs font-medium">{{ $pagadas->count() }}</span>
                            </button>
                            <button @click="activeTab = 'anuladas'" 
                                    :class="activeTab === 'anuladas' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                                <i class="fas fa-ban mr-2"></i> Anuladas
                                <span class="ml-2 bg-red-100 text-red-800 py-0.5 px-2.5 rounded-full text-xs font-medium">{{ $anuladas->count() }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="mt-4">
                        <!-- Pendientes -->
                        <div x-show="activeTab === 'pendientes'">
                            @include('facturas.partials.table', ['facturas' => $pendientes, 'tipo' => 'pendiente'])
                        </div>

                        <!-- Pagadas -->
                        <div x-show="activeTab === 'pagadas'">
                            @include('facturas.partials.table', ['facturas' => $pagadas, 'tipo' => 'pagada'])
                        </div>

                        <!-- Anuladas -->
                        <div x-show="activeTab === 'anuladas'">
                            @include('facturas.partials.table', ['facturas' => $anuladas, 'tipo' => 'anulada'])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <x-modal name="edit-factura" focusable>
            <div class="p-6" x-if="selectedFactura">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Editar Factura <span x-text="selectedFactura.numero_factura"></span>
                </h2>

                <form :action="'{{ url('facturas') }}/' + selectedFactura.id" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="cliente_nombre" value="Nombre del Cliente" />
                            <x-text-input id="cliente_nombre" name="cliente_nombre" type="text" class="mt-1 block w-full" x-model="selectedFactura.cliente_nombre" required />
                        </div>
                        <div>
                            <x-input-label for="cliente_nit" value="NIT/CI" />
                            <x-text-input id="cliente_nit" name="cliente_nit" type="text" class="mt-1 block w-full" x-model="selectedFactura.cliente_nit" />
                        </div>
                        <div>
                            <x-input-label for="cliente_telefono" value="Teléfono" />
                            <x-text-input id="cliente_telefono" name="cliente_telefono" type="text" class="mt-1 block w-full" x-model="selectedFactura.cliente_telefono" />
                        </div>
                        <div>
                            <x-input-label for="descuento" value="Descuento" />
                            <x-text-input id="descuento" name="descuento" type="number" step="0.01" class="mt-1 block w-full" x-model="selectedFactura.descuento" @input="selectedFactura.total = (parseFloat(selectedFactura.subtotal) + parseFloat(selectedFactura.impuesto) - parseFloat($event.target.value)).toFixed(2)" />
                        </div>
                        <div>
                            <x-input-label for="metodo_pago" value="Método de Pago" />
                            <select id="metodo_pago" name="metodo_pago" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="selectedFactura.metodo_pago">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="qr">QR</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="bg-gray-100 p-2 rounded w-full">
                                <span class="text-sm text-gray-600">Total Recalculado:</span>
                                <span class="text-lg font-bold block" x-text="'Bs. ' + selectedFactura.total"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <x-primary-button class="ml-3">Guardar Cambios</x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>

        <!-- Pay Modal -->
        <x-modal name="pay-factura" focusable>
            <div class="p-6" x-if="selectedFactura">
                <h2 class="text-lg font-medium text-gray-900 mb-2">
                    Procesar Pago - <span x-text="selectedFactura.numero_factura"></span>
                </h2>
                <p class="text-sm text-gray-600 mb-4">Confirme el método de pago para la factura de <strong x-text="selectedFactura.cliente_nombre"></strong> por un total de <strong x-text="'Bs. ' + selectedFactura.total"></strong>.</p>

                <form :action="'{{ url('facturas') }}/' + selectedFactura.id + '/pagar'" method="POST">
                    @csrf
                    <div>
                        <x-input-label for="pay_metodo_pago" value="Método de Pago Final" />
                        <select id="pay_metodo_pago" name="metodo_pago" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="selectedFactura.metodo_pago">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="qr">QR</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <x-primary-button class="ml-3 bg-green-600 hover:bg-green-700">Confirmar y Pagar</x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>
</x-app-layout>
