<x-app-layout>
    <x-slot name="title">Gestión de Facturas</x-slot>

    <div class="py-8" x-data="facturaManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-file-invoice-dollar mr-2 text-blue-600"></i>Módulo de Facturación
                        </h2>
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
                        <div x-show="activeTab === 'pendientes'">
                            @include('facturas.partials.table', ['facturas' => $pendientes, 'tipo' => 'pendiente'])
                        </div>
                        <div x-show="activeTab === 'pagadas'" style="display:none;">
                            @include('facturas.partials.table', ['facturas' => $pagadas, 'tipo' => 'pagada'])
                        </div>
                        <div x-show="activeTab === 'anuladas'" style="display:none;">
                            @include('facturas.partials.table', ['facturas' => $anuladas, 'tipo' => 'anulada'])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- MODAL: Editar Factura                        -->
        <!-- ============================================ -->
        <x-modal name="edit-factura" focusable>
            <div class="p-6" x-show="selectedFactura">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Editar Factura <span x-text="selectedFactura?.numero_factura"></span>
                </h2>

                <form :action="'{{ url('facturas') }}/' + (selectedFactura?.id || '')" method="POST">
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
                                <span class="text-lg font-bold block" x-text="'Bs. ' + selectedFactura?.total"></span>
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

        <!-- ============================================ -->
        <!-- MODAL: Procesar Pago                         -->
        <!-- ============================================ -->
        <x-modal name="pay-factura" focusable>
            <div class="p-6" x-show="selectedFactura">
                <h2 class="text-lg font-medium text-gray-900 mb-2">
                    <i class="fas fa-cash-register mr-2 text-green-600"></i>
                    Procesar Pago - <span x-text="selectedFactura?.numero_factura"></span>
                </h2>
                <p class="text-sm text-gray-600 mb-4">
                    Factura de <strong x-text="selectedFactura?.cliente_nombre"></strong>
                    por un total de <strong x-text="'Bs. ' + selectedFactura?.total"></strong>.
                </p>

                <!-- Selección de método de pago -->
                <div class="mb-4">
                    <x-input-label for="pay_metodo_pago" value="Método de Pago" />
                    <select id="pay_metodo_pago" x-model="payMetodo"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="tarjeta">💳 Tarjeta</option>
                        <option value="qr">📱 Código QR</option>
                        <option value="transferencia">🏦 Transferencia</option>
                    </select>
                </div>

                <!-- Botones según método -->
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>

                    <!-- Pago normal (efectivo/tarjeta/transferencia) -->
                    <form x-show="payMetodo !== 'qr'"
                          :action="'{{ url('facturas') }}/' + (selectedFactura?.id || '') + '/pagar'"
                          method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="metodo_pago" :value="payMetodo">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                            <i class="fas fa-check mr-2"></i> Confirmar y Pagar
                        </button>
                    </form>

                    <!-- Pago QR -->
                    <button x-show="payMetodo === 'qr'"
                            @click="generarQr()"
                            :disabled="qrLoading"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition disabled:opacity-50">
                        <i class="fas fa-qrcode mr-2"></i>
                        <span x-show="!qrLoading">Generar Código QR</span>
                        <span x-show="qrLoading"><i class="fas fa-spinner fa-spin mr-1"></i> Generando...</span>
                    </button>
                </div>
            </div>
        </x-modal>

        <!-- ============================================ -->
        <!-- MODAL: QR de Pago (Fullscreen elegante)      -->
        <!-- ============================================ -->
        <div x-show="qrModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">

            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>

            <!-- Modal Content -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="qrModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">

                    <!-- Header con gradiente -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-white font-bold text-lg flex items-center">
                                <i class="fas fa-qrcode mr-2"></i> Pago con QR
                            </h3>
                            <button @click="cerrarQrModal()"
                                    class="text-white/80 hover:text-white transition"
                                    x-show="!qrPagado">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <p class="text-purple-200 text-sm mt-1" x-text="'Factura: ' + (selectedFactura?.numero_factura || '')"></p>
                    </div>

                    <!-- Cuerpo -->
                    <div class="p-6">
                        <!-- Estado: Esperando pago -->
                        <div x-show="!qrPagado">
                            <!-- QR Code -->
                            <div class="flex justify-center mb-5">
                                <div class="p-3 bg-white border-2 border-gray-200 rounded-xl shadow-inner"
                                     x-html="qrSvg">
                                </div>
                            </div>

                            <!-- Detalles del pago -->
                            <div class="bg-gray-50 rounded-xl p-4 space-y-2 mb-5">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Cliente:</span>
                                    <span class="text-sm font-semibold text-gray-800" x-text="qrFacturaData?.cliente_nombre"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Subtotal + IVA:</span>
                                    <span class="text-sm text-gray-700" x-text="'Bs. ' + qrFacturaData?.subtotal + ' + ' + qrFacturaData?.impuesto"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Descuento:</span>
                                    <span class="text-sm text-red-500" x-text="'- Bs. ' + qrFacturaData?.descuento"></span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                    <span class="text-base font-bold text-gray-800">Total a Pagar:</span>
                                    <span class="text-xl font-black text-purple-700" x-text="'Bs. ' + qrFacturaData?.total"></span>
                                </div>
                            </div>

                            <!-- Indicador de espera animado -->
                            <div class="flex items-center justify-center gap-3 py-3">
                                <div class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-500"></span>
                                </div>
                                <span class="text-sm text-gray-500 font-medium animate-pulse">Esperando confirmación de pago...</span>
                            </div>

                            <p class="text-xs text-gray-400 text-center mt-2">
                                Escanee el código QR con su celular para realizar el pago.
                                <br>Canal: <code class="bg-gray-100 px-1 rounded text-purple-600" x-text="'emisor-' + qrEmisor"></code>
                            </p>
                        </div>

                        <!-- Estado: Pago confirmado -->
                        <div x-show="qrPagado" class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-4 qr-success-check">
                                <i class="fas fa-check text-4xl text-green-600"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-green-700 mb-2">¡Pago Confirmado!</h3>
                            <p class="text-gray-600 mb-1">El pago de <strong x-text="'Bs. ' + qrFacturaData?.total"></strong> fue procesado.</p>
                            <p class="text-sm text-gray-500">La página se actualizará automáticamente...</p>
                            <div class="mt-4">
                                <div class="w-full bg-green-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-green-500 h-1.5 rounded-full qr-progress-bar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Estilos para animaciones del QR -->
    <style>
        .qr-success-check {
            animation: successPop 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        @keyframes successPop {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .qr-progress-bar {
            animation: progressFill 2.5s linear forwards;
        }
        @keyframes progressFill {
            0% { width: 0%; }
            100% { width: 100%; }
        }
    </style>

    <!-- Script Alpine.js para gestión de facturas y pago QR -->
    <script>
        function facturaManager() {
            return {
                activeTab: 'pendientes',
                selectedFactura: null,
                payMetodo: 'efectivo',

                // QR State
                qrModalOpen: false,
                qrSvg: '',
                qrEmisor: '',
                qrLoading: false,
                qrPagado: false,
                qrFacturaData: null,
                echoChannel: null,

                // Abrir modal de edición
                openEdit(factura) {
                    this.selectedFactura = JSON.parse(JSON.stringify(factura));
                    this.$dispatch('open-modal', 'edit-factura');
                },

                // Abrir modal de pago
                openPay(factura) {
                    this.selectedFactura = JSON.parse(JSON.stringify(factura));
                    this.payMetodo = factura.metodo_pago || 'efectivo';
                    this.$dispatch('open-modal', 'pay-factura');
                },

                // Generar QR y abrir modal QR
                async generarQr() {
                    if (!this.selectedFactura) return;

                    this.qrLoading = true;

                    try {
                        const response = await fetch(`/facturas/${this.selectedFactura.id}/generar-qr`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        if (!response.ok) throw new Error('Error al generar QR');

                        const data = await response.json();

                        this.qrSvg = data.qr_svg;
                        this.qrEmisor = data.emisor;
                        this.qrFacturaData = data.factura;
                        this.qrPagado = false;

                        // Cerrar modal de pago y abrir modal QR
                        this.$dispatch('close-modal', 'pay-factura');
                        this.$dispatch('close');
                        this.qrModalOpen = true;

                        // Suscribirse al canal de Pusher
                        this.suscribirCanal(data.emisor);

                        console.log('📱 QR generado. Canal:', 'emisor-' + data.emisor);
                        console.log('🔗 URL:', data.url);

                    } catch (error) {
                        console.error('Error generando QR:', error);
                        alert('Error al generar el código QR. Intente nuevamente.');
                    } finally {
                        this.qrLoading = false;
                    }
                },

                // Suscribirse al canal Pusher para recibir confirmación de pago
                suscribirCanal(emisor) {
                    // Limpiar suscripción anterior si existe
                    if (this.echoChannel) {
                        window.Echo.leave('emisor-' + this.qrEmisor);
                        this.echoChannel = null;
                    }

                    if (!window.Echo) {
                        console.error('Laravel Echo no está inicializado.');
                        return;
                    }

                    console.log('🔌 Suscribiéndose al canal: emisor-' + emisor);

                    this.echoChannel = window.Echo.channel('emisor-' + emisor)
                        .listen('.pago.confirmado', (e) => {
                            console.log('✅ ¡Pago QR confirmado!', e);
                            this.procesarPagoConfirmado(e);
                        })
                        .subscribed(() => {
                            console.log('✅ Suscripción exitosa al canal emisor-' + emisor);
                        })
                        .error((error) => {
                            console.error('❌ Error en suscripción:', error);
                        });
                },

                // Procesar evento de pago confirmado
                async procesarPagoConfirmado(eventData) {
                    this.qrPagado = true;

                    // Actualizar factura en el servidor
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                        await fetch(`/facturas/${this.selectedFactura.id}/pagar`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ metodo_pago: 'qr' })
                        });
                    } catch (err) {
                        console.log('Factura posiblemente ya actualizada por webhook:', err);
                    }

                    // Recargar página después de la animación
                    setTimeout(() => {
                        window.location.reload();
                    }, 2800);
                },

                // Cerrar modal QR y limpiar
                cerrarQrModal() {
                    if (this.echoChannel && this.qrEmisor) {
                        window.Echo.leave('emisor-' + this.qrEmisor);
                        this.echoChannel = null;
                        console.log('🔌 Desuscrito del canal emisor-' + this.qrEmisor);
                    }
                    this.qrModalOpen = false;
                    this.qrSvg = '';
                    this.qrPagado = false;
                    this.qrFacturaData = null;
                }
            }
        }
    </script>
</x-app-layout>
