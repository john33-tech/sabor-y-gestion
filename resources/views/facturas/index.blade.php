<x-app-layout>
    <x-slot name="title">Gestión de Facturas</x-slot>

    <div class="py-8" x-data="facturaManager()">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="mr-2 text-blue-600 fas fa-file-invoice-dollar"></i>Módulo de Facturación
                        </h2>
                        <x-alert-messages />
                    </div>

                    <!-- Tabs -->
                    <div class="mb-6 border-b border-gray-200">
                        <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'pendientes'"
                                    :class="activeTab === 'pendientes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="flex items-center px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                                <i class="mr-2 fas fa-clock"></i> Pendientes
                                <span class="ml-2 bg-yellow-100 text-yellow-800 py-0.5 px-2.5 rounded-full text-xs font-medium">{{ $pendientes->count() }}</span>
                            </button>
                            <button @click="activeTab = 'pagadas'"
                                    :class="activeTab === 'pagadas' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="flex items-center px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                                <i class="mr-2 fas fa-check-circle"></i> Pagadas
                                <span class="ml-2 bg-green-100 text-green-800 py-0.5 px-2.5 rounded-full text-xs font-medium">{{ $pagadas->count() }}</span>
                            </button>
                            <button @click="activeTab = 'anuladas'"
                                    :class="activeTab === 'anuladas' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="flex items-center px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                                <i class="mr-2 fas fa-ban"></i> Anuladas
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
                <h2 class="mb-4 text-lg font-medium text-gray-900">
                    Editar Factura <span x-text="selectedFactura?.numero_factura"></span>
                </h2>

                <form :action="'{{ url('facturas') }}/' + (selectedFactura?.id || '')" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="cliente_nombre" value="Nombre del Cliente" />
                            <x-text-input id="cliente_nombre" name="cliente_nombre" type="text" class="block w-full mt-1" x-model="selectedFactura.cliente_nombre" required />
                        </div>
                        <div>
                            <x-input-label for="cliente_nit" value="NIT/CI" />
                            <x-text-input id="cliente_nit" name="cliente_nit" type="text" class="block w-full mt-1" x-model="selectedFactura.cliente_nit" />
                        </div>
                        <div>
                            <x-input-label for="cliente_telefono" value="Teléfono" />
                            <x-text-input id="cliente_telefono" name="cliente_telefono" type="text" class="block w-full mt-1" x-model="selectedFactura.cliente_telefono" />
                        </div>
                        <div>
                            <x-input-label for="descuento" value="Descuento" />
                            <x-text-input id="descuento" name="descuento" type="number" step="0.01" class="block w-full mt-1" x-model="selectedFactura.descuento" @input="selectedFactura.total = (parseFloat(selectedFactura.subtotal) + parseFloat(selectedFactura.impuesto) - parseFloat($event.target.value)).toFixed(2)" />
                        </div>
                        <div>
                            <x-input-label for="metodo_pago" value="Método de Pago" />
                            <select id="metodo_pago" name="metodo_pago" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="selectedFactura.metodo_pago">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="qr">QR</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="w-full p-2 bg-gray-100 rounded">
                                <span class="text-sm text-gray-600">Total Recalculado:</span>
                                <span class="block text-lg font-bold" x-text="'Bs. ' + selectedFactura?.total"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
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
                <h2 class="mb-2 text-lg font-medium text-gray-900">
                    <i class="mr-2 text-green-600 fas fa-cash-register"></i>
                    Procesar Pago - <span x-text="selectedFactura?.numero_factura"></span>
                </h2>
                <p class="mb-4 text-sm text-gray-600">
                    Factura de <strong x-text="selectedFactura?.cliente_nombre"></strong>
                    por un total de <strong x-text="'Bs. ' + selectedFactura?.total"></strong>.
                </p>

                <!-- Selección de método de pago -->
                <div class="mb-4">
                    <x-input-label for="pay_metodo_pago" value="Método de Pago" />
                    <select id="pay_metodo_pago" x-model="payMetodo"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="tarjeta">💳 Tarjeta</option>
                        <option value="qr">📱 Código QR</option>
                        <option value="transferencia">🏦 Transferencia</option>
                    </select>
                </div>

                <!-- Botones según método -->
                <div class="flex justify-end gap-3 mt-6">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>

                    <!-- Pago normal (efectivo/tarjeta/transferencia) -->
                    <form x-show="payMetodo !== 'qr'"
                          :action="'{{ url('facturas') }}/' + (selectedFactura?.id || '') + '/pagar'"
                          method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="metodo_pago" :value="payMetodo">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                            <i class="mr-2 fas fa-check"></i> Confirmar y Pagar
                        </button>
                    </form>

                    <!-- Pago QR -->
                    <button x-show="payMetodo === 'qr'"
                            @click="generarQr()"
                            :disabled="qrLoading"
                            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-purple-600 border border-transparent rounded-md hover:bg-purple-700 disabled:opacity-50">
                        <i class="mr-2 fas fa-qrcode"></i>
                        <span x-show="!qrLoading">Generar Código QR</span>
                        <span x-show="qrLoading"><i class="mr-1 fas fa-spinner fa-spin"></i> Generando...</span>
                    </button>
                </div>
            </div>
        </x-modal>

        <!-- ============================================ -->
        <!-- MODAL: Enviar Factura por Correo            -->
        <!-- ============================================ -->
        <x-modal name="send-mail" focusable>
            <div class="p-6" x-show="selectedFactura">
                <h2 class="mb-2 text-lg font-medium text-gray-900">
                    <i class="mr-2 text-indigo-600 fas fa-envelope"></i>
                    Enviar Factura - <span x-text="selectedFactura?.numero_factura"></span>
                </h2>
                <p class="mb-4 text-sm text-gray-600">
                    Se generará un PDF de la factura y se enviará por correo electrónico a <strong x-text="selectedFactura?.cliente_nombre"></strong>.
                </p>

                <form :action="'{{ url('facturas') }}/' + (selectedFactura?.id || '') + '/enviar-correo'" method="POST">
                    @csrf
                    <div>
                        <x-input-label for="email" value="Correo Electrónico del Cliente" />
                        <x-text-input id="email" name="email" type="email" class="block w-full mt-1" placeholder="ejemplo@correo.com" required />
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700">
                            <i class="mr-2 fas fa-paper-plane"></i> Enviar Factura
                        </button>
                    </div>
                </form>
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
            <div class="flex items-center justify-center min-h-screen p-4">
                <div x-show="qrModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-md overflow-hidden bg-white shadow-2xl rounded-2xl">

                    <!-- Header con gradiente -->
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600">
                        <div class="flex items-center justify-between">
                            <h3 class="flex items-center text-lg font-bold text-white">
                                <i class="mr-2 fas fa-qrcode"></i> Pago con QR
                            </h3>
                            <button @click="cerrarQrModal()"
                                    class="transition text-white/80 hover:text-white"
                                    x-show="!qrPagado">
                                <i class="text-xl fas fa-times"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-purple-200" x-text="'Factura: ' + (selectedFactura?.numero_factura || '')"></p>
                    </div>

                    <!-- Cuerpo -->
                    <div class="p-6">
                        <!-- Estado: Esperando pago -->
                        <div x-show="!qrPagado">
                            <!-- QR Code -->
                            <div class="flex justify-center mb-5">
                                <div class="p-3 bg-white border-2 border-gray-200 shadow-inner rounded-xl"
                                     x-html="qrSvg">
                                </div>
                            </div>

                            <!-- Detalles del pago -->
                            <div class="p-4 mb-5 space-y-2 bg-gray-50 rounded-xl">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Cliente:</span>
                                    <span class="text-sm font-semibold text-gray-800" x-text="qrFacturaData?.cliente_nombre"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Subtotal + IVA:</span>
                                    <span class="text-sm text-gray-700" x-text="'Bs. ' + qrFacturaData?.subtotal + ' + ' + qrFacturaData?.impuesto"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Descuento:</span>
                                    <span class="text-sm text-red-500" x-text="'- Bs. ' + qrFacturaData?.descuento"></span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                    <span class="text-base font-bold text-gray-800">Total a Pagar:</span>
                                    <span class="text-xl font-black text-purple-700" x-text="'Bs. ' + qrFacturaData?.total"></span>
                                </div>
                            </div>

                            <!-- Indicador de espera animado -->
                            <div class="flex items-center justify-center gap-3 py-3">
                                <div class="relative flex w-3 h-3">
                                    <span class="absolute inline-flex w-full h-full bg-purple-400 rounded-full opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex w-3 h-3 bg-purple-500 rounded-full"></span>
                                </div>
                                <span class="text-sm font-medium text-gray-500 animate-pulse">Esperando confirmación de pago...</span>
                            </div>

                            <p class="mt-2 text-xs text-center text-gray-400">
                                Escanee el código QR con su celular para realizar el pago.
                                <br>Canal: <code class="px-1 text-purple-600 bg-gray-100 rounded" x-text="'emisor-' + qrEmisor"></code>
                            </p>
                        </div>

                        <!-- Estado: Pago confirmado -->
                        <div x-show="qrPagado" class="py-8 text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 mb-4 bg-green-100 rounded-full qr-success-check">
                                <i class="text-4xl text-green-600 fas fa-check"></i>
                            </div>
                            <h3 class="mb-2 text-2xl font-bold text-green-700">¡Pago Confirmado!</h3>
                            <p class="mb-1 text-gray-600">El pago de <strong x-text="'Bs. ' + qrFacturaData?.total"></strong> fue procesado.</p>
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

                // Abrir modal de envío de correo
                openSendMail(factura) {
                    this.selectedFactura = JSON.parse(JSON.stringify(factura));
                    this.$dispatch('open-modal', 'send-mail');
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
                        console.log('🔗 URL:', data);

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
