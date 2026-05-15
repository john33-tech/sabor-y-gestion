<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SaborGestion - Restaurante de Alta Cocina</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        h1, h2, h3, h4, .playfair {
            font-family: 'Playfair Display', serif;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-scaleIn {
            animation: scaleIn 0.5s ease-out;
        }

        .hero-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .menu-card {
            transition: all 0.3s ease;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .testimonial-card {
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="text-gray-800 bg-gradient-to-br from-gray-50 via-white to-amber-50/30">

    <!-- Navbar mejorada -->
    <nav class="sticky top-0 z-50 transition-all duration-300 border-b shadow-sm bg-surface/90 backdrop-blur-lg border-border">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-3">
                <!-- Logo y nombre -->
                <div class="flex items-center space-x-3 cursor-pointer group">
                    <img src="{{ asset('logo.png') }}" alt="SaborGestion Logo"
                         class="object-contain w-12 h-12 md:w-14 md:h-14 rounded-full">
                    <div>
                        <h1 class="text-xl font-bold playfair text-primary md:text-2xl">
                            SaborGestion
                        </h1>
                        <p class="hidden text-xs text-muted sm:block">Restaurante de Alta Cocina</p>
                    </div>
                </div>

                <!-- Menú de navegación (escritorio) -->
                <div class="hidden space-x-8 md:flex">
                    <a href="#inicio" class="font-medium transition-colors hover:text-primary text-text">Inicio</a>
                    <a href="#nosotros" class="font-medium transition-colors hover:text-primary text-text">Nosotros</a>
                    <a href="#menu" class="font-medium transition-colors hover:text-primary text-text">Menú</a>
                    <a href="#testimonios" class="font-medium transition-colors hover:text-primary text-text">Testimonios</a>
                    <a href="#contacto" class="font-medium transition-colors hover:text-primary text-text">Contacto</a>
                </div>

                <!-- Botón de acción / Login -->
                <div>
                    @if (Route::has('login'))
                        @auth
                            @php
                                $user = auth()->user();

                                // Definir URLs según rol
                                $panelUrl = match($user->role) {
                                    'admin' => url('/dashboard/administrador'),
                                    'mesero' => url('/dashboard/mesero'),
                                    'cajero' => url('/dashboard/cajero'),
                                    'cliente' => url('/dashboard/cliente'),
                                    default => url('/'),
                                };
                            @endphp

                        <a href="{{ $panelUrl   }}"
                            class="group inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all duration-300 md:px-6 md:py-2.5 md:text-base bg-gradient-to-r from-primary to-secondary rounded-xl shadow-lg hover:shadow-xl hover:scale-105">
                            <i class="text-sm transition-transform fas fa-tachometer-alt group-hover:rotate-12"></i>
                            <span>Panel de Control</span>
                        </a>
                    @else
                            <a href="{{ route('login') }}"
                            class="group inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all duration-300 md:px-6 md:py-2.5 md:text-base bg-gradient-to-r from-primary to-secondary rounded-xl shadow-lg hover:shadow-xl hover:scale-105">
                                <i class="fas fa-sign-in-alt text-sm group-hover:translate-x-0.5 transition-transform"></i>
                                <span>Iniciar sesión</span>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>













    <!-- Hero Section - Inicio Mejorado -->
    <section id="inicio" class="relative min-h-screen overflow-hidden">
        <!-- Video/Imagen de fondo con parallax -->
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"
                 alt="Restaurante gourmet con vista elegante"
                 class="object-cover w-full h-full transform scale-105 transition-transform duration-[10s] hover:scale-110"
                 loading="lazy">
            <!-- Overlay degradado más sofisticado -->
            <div class="absolute inset-0 bg-gradient-to-br from-black/85 via-black/70 to-primary/40"></div>
            <!-- Overlay de patrón de textura -->
            <div class="absolute inset-0 hero-pattern opacity-5"></div>
        </div>

        <!-- Elementos decorativos flotantes -->
        <div class="absolute top-32 left-10 w-64 h-64 bg-primary/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-32 right-10 w-80 h-80 bg-secondary/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>

        <div class="relative z-10 flex items-center min-h-screen">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="max-w-5xl mx-auto text-center">
                    <!-- Badge con efecto de vidrio -->
                    <div class="inline-flex items-center gap-2 px-5 py-2.5 mb-8 rounded-full shadow-xl bg-white/10 backdrop-blur-md border border-white/20 animate-scaleIn">
                        <span class="relative flex w-2 h-2">
                            <span class="absolute inline-flex w-full h-full rounded-full bg-green-400 opacity-75 animate-ping"></span>
                            <span class="relative inline-flex w-2 h-2 rounded-full bg-green-500"></span>
                        </span>
                        <span class="text-sm font-medium tracking-wide text-white">Aceptamos Reservas Online</span>
                        <i class="text-xs text-yellow-300 fas fa-chevron-right"></i>
                    </div>

                    <!-- Título principal con efecto de escritura -->
                    <h1 class="mb-6 text-5xl font-bold leading-tight text-white playfair sm:text-6xl lg:text-7xl xl:text-8xl animate-fadeInUp">
                        Descubre el
                        <span class="relative inline-block">
                            <span class="relative z-10 text-transparent bg-gradient-to-r from-yellow-300 via-orange-300 to-secondary bg-clip-text">
                                Arte Culinario
                            </span>
                            <svg class="absolute bottom-0 left-0 w-full h-3 -z-0" viewBox="0 0 100 10" preserveAspectRatio="none">
                                <path d="M0,5 Q25,0 50,5 T100,5" stroke="#F97316" fill="none" stroke-width="2"/>
                            </svg>
                        </span>
                    </h1>

                    <p class="max-w-3xl mx-auto mb-12 text-base leading-relaxed text-white/90 sm:text-lg md:text-xl animate-fadeInUp" style="animation-delay: 0.1s">
                        Una experiencia gastronómica única donde la tradición y la innovación se fusionan
                        para crear momentos inolvidables en cada plato.
                    </p>

                    <!-- Botones CTA mejorados -->
                    <div class="flex flex-col justify-center gap-4 sm:flex-row animate-fadeInUp" style="animation-delay: 0.2s">
                        <a href="#menu"
                           class="group relative inline-flex items-center justify-center gap-3 px-8 py-4 overflow-hidden font-bold transition-all duration-300 bg-gradient-to-r from-primary to-secondary rounded-2xl hover:shadow-2xl hover:scale-105">
                            <span class="absolute inset-0 w-full h-full transition-all duration-300 transform translate-x-full bg-white/20 group-hover:translate-x-0"></span>
                            <i class="relative text-xl fas fa-utensils group-hover:animate-pulse"></i>
                            <span class="relative text-lg text-white">Explorar Menú</span>
                            <i class="relative text-sm transition-transform fas fa-arrow-right group-hover:translate-x-1"></i>
                        </a>
                        <a href="#contacto"
                           class="inline-flex items-center justify-center gap-2 px-8 py-4 font-semibold text-white transition-all duration-300 border-2 border-white/30 rounded-2xl backdrop-blur-sm hover:bg-white/20 hover:border-white/50 hover:scale-105">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Reservar Mesa</span>
                        </a>
                    </div>

                    <!-- Indicadores con contadores animados -->
                    <div class="grid grid-cols-1 gap-8 pt-12 mt-16 border-t sm:grid-cols-3 border-white/20 animate-fadeInUp" style="animation-delay: 0.3s">
                        <div class="group cursor-pointer">
                            <div class="text-4xl font-bold text-white sm:text-5xl playfair">
                                <span class="counter" data-target="40">0</span><span>+</span>
                            </div>
                            <p class="mt-2 text-sm font-medium tracking-wide text-white/70 transition-colors group-hover:text-white/90">
                                Años de Experiencia
                            </p>
                            <div class="w-0 h-0.5 mx-auto mt-2 transition-all duration-300 bg-gradient-to-r from-primary to-secondary group-hover:w-12"></div>
                        </div>
                        <div class="group cursor-pointer">
                            <div class="text-4xl font-bold text-white sm:text-5xl playfair">
                                <span class="counter" data-target="15">0</span><span>+</span>
                            </div>
                            <p class="mt-2 text-sm font-medium tracking-wide text-white/70 transition-colors group-hover:text-white/90">
                                Platos Exclusivos
                            </p>
                            <div class="w-0 h-0.5 mx-auto mt-2 transition-all duration-300 bg-gradient-to-r from-primary to-secondary group-hover:w-12"></div>
                        </div>
                        <div class="group cursor-pointer">
                            <div class="text-4xl font-bold text-white sm:text-5xl playfair">
                                <span class="counter" data-target="10">0</span><span>K+</span>
                            </div>
                            <p class="mt-2 text-sm font-medium tracking-wide text-white/70 transition-colors group-hover:text-white/90">
                                Clientes Satisfechos
                            </p>
                            <div class="w-0 h-0.5 mx-auto mt-2 transition-all duration-300 bg-gradient-to-r from-primary to-secondary group-hover:w-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <a href="#nosotros" class="flex flex-col items-center gap-2 text-white/60 hover:text-white/90 transition-colors">
                <span class="text-xs tracking-wider uppercase">Descubrir más</span>
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>










    <!-- Sección Sobre Nosotros -->
    <section id="nosotros" class="py-20 bg-surface lg:py-28">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <!-- Contenido izquierdo -->
                <div class="order-2 lg:order-1">
                    <span class="inline-block px-4 py-2 mb-4 text-sm font-semibold rounded-full bg-primary/10 text-primary">
                        Nuestra Historia
                    </span>
                    <h2 class="mb-6 text-3xl font-bold playfair sm:text-4xl lg:text-5xl text-text">
                        Donde la tradición se encuentra con la innovación
                    </h2>
                    <p class="mb-6 leading-relaxed text-muted">
                        Fundado en 1985 por el reconocido chef Antonio Rodríguez, SaborGestion nació con la visión de ofrecer una experiencia culinaria única que combinara las recetas tradicionales de nuestra abuela con técnicas modernas de vanguardia.
                    </p>
                    <p class="mb-8 leading-relaxed text-muted">
                        Hoy, nuestro restaurante es reconocido como uno de los mejores de la región, gracias a nuestro compromiso con la calidad, los ingredientes frescos y locales, y un equipo apasionado que hace de cada visita una experiencia memorable.
                    </p>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div class="flex items-center gap-3">
                            <i class="text-2xl text-primary fas fa-check-circle"></i>
                            <span class="font-medium">Ingredientes Frescos</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="text-2xl text-primary fas fa-check-circle"></i>
                            <span class="font-medium">Chef Estrella Michelin</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="text-2xl text-primary fas fa-check-circle"></i>
                            <span class="font-medium">Ambiente Único</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="text-2xl text-primary fas fa-check-circle"></i>
                            <span class="font-medium">Servicio Premium</span>
                        </div>
                    </div>

                    <a href="#contacto" class="inline-flex items-center gap-2 font-semibold transition-colors text-primary hover:text-secondary">
                        <span>Conoce más sobre nosotros</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Imagen derecha con logo -->
                <div class="order-1 lg:order-2">
                    <div class="relative">
                        <div class="absolute -top-4 -left-4 w-32 h-32 bg-primary/20 rounded-2xl"></div>
                        <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-secondary/20 rounded-2xl"></div>
                        <img src="https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"
                             alt="Chef preparando comida"
                             class="relative rounded-2xl shadow-2xl w-full object-cover h-[400px]">
                        <div class="absolute bottom-6 left-6 bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg flex items-center gap-3">
                            <img src="{{ asset('logo.png') }}" alt="Logo" class="w-12 h-12 rounded-full object-cover">
                            <div>
                                <p class="text-sm font-semibold text-primary">Chef Ejecutivo</p>
                                <p class="text-lg font-bold playfair">Antonio Rodríguez</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección Nuestro Menú -->
    <section id="menu" class="py-20 bg-background lg:py-28">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <span class="inline-block px-4 py-2 mb-4 text-sm font-semibold rounded-full bg-primary/10 text-primary">
                    Sabores Exclusivos
                </span>
                <h2 class="mb-4 text-3xl font-bold playfair sm:text-4xl lg:text-5xl text-text">
                    Nuestro Menú Destacado
                </h2>
                <p class="max-w-2xl mx-auto text-lg text-muted">
                    Una selección de nuestros platos más emblemáticos, preparados con los mejores ingredientes y mucho amor
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Plato 1 -->
                <div class="overflow-hidden transition-all duration-300 bg-white shadow-lg menu-card rounded-2xl">
                    <div class="relative h-64 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1544025162-d76694265947?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"
                             alt="Solomillo Wagyu"
                             class="object-cover w-full h-full transition-transform duration-500 hover:scale-110">
                        <div class="absolute top-4 right-4 bg-primary text-white px-3 py-1 rounded-full text-sm font-semibold">
                            ★ Plato Estrella
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between mb-2">
                            <h3 class="text-xl font-bold playfair text-text">Solomillo Wagyu</h3>
                            <span class="text-xl font-bold text-primary">$45</span>
                        </div>
                        <p class="mb-4 text-muted">Solomillo de wagyu asado a la perfección, acompañado de puré de trufa negra y espárragos verdes.</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="ml-2 text-sm text-muted">(128 reseñas)</span>
                            </div>
                            <button class="text-primary hover:text-secondary transition-colors">
                                <i class="fas fa-shopping-cart"></i> Ordenar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Plato 2 -->
                <div class="overflow-hidden transition-all duration-300 bg-white shadow-lg menu-card rounded-2xl">
                    <div class="relative h-64 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1563379926898-05f4575a45d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"
                             alt="Risotto de Mariscos"
                             class="object-cover w-full h-full transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between mb-2">
                            <h3 class="text-xl font-bold playfair text-text">Risotto de Mariscos</h3>
                            <span class="text-xl font-bold text-primary">$32</span>
                        </div>
                        <p class="mb-4 text-muted">Cremoso risotto con camarones, calamares y mejillones frescos, terminado con queso parmesano.</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="ml-2 text-sm text-muted">(95 reseñas)</span>
                            </div>
                            <button class="text-primary hover:text-secondary transition-colors">
                                <i class="fas fa-shopping-cart"></i> Ordenar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Plato 3 -->
                <div class="overflow-hidden transition-all duration-300 bg-white shadow-lg menu-card rounded-2xl">
                    <div class="relative h-64 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1551183053-bf91a1d81141?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80"
                             alt="Salmón Glaseado"
                             class="object-cover w-full h-full transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between mb-2">
                            <h3 class="text-xl font-bold playfair text-text">Salmón Glaseado</h3>
                            <span class="text-xl font-bold text-primary">$38</span>
                        </div>
                        <p class="mb-4 text-muted">Salmón noruego glaseado con salsa teriyaki, acompañado de vegetales salteados y arroz jazmín.</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="ml-2 text-sm text-muted">(112 reseñas)</span>
                            </div>
                            <button class="text-primary hover:text-secondary transition-colors">
                                <i class="fas fa-shopping-cart"></i> Ordenar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="#" class="inline-flex items-center gap-2 px-8 py-3 font-semibold transition-all duration-300 border-2 rounded-full border-primary text-primary hover:bg-primary hover:text-white">
                    <span>Ver Menú Completo</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Sección Testimonios de Clientes -->
    <section id="testimonios" class="py-20 bg-gradient-to-br from-primary/5 to-secondary/5 lg:py-28">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <span class="inline-block px-4 py-2 mb-4 text-sm font-semibold rounded-full bg-primary/10 text-primary">
                    Experiencias Reales
                </span>
                <h2 class="mb-4 text-3xl font-bold playfair sm:text-4xl lg:text-5xl text-text">
                    Lo que dicen nuestros comensales
                </h2>
                <p class="max-w-2xl mx-auto text-lg text-muted">
                    Más de 10,000 clientes satisfechos respaldan nuestra calidad y servicio
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Testimonio 1 -->
                <div class="p-8 transition-all duration-300 bg-white shadow-xl testimonial-card rounded-2xl">
                    <div class="flex mb-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="mb-6 leading-relaxed text-muted">
                        "Una experiencia gastronómica inolvidable. El solomillo wagyu estaba perfectamente cocinado y el servicio fue excepcional. Sin duda volveré."
                    </p>
                    <div class="flex items-center gap-4">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80"
                             alt="Cliente"
                             class="object-cover rounded-full w-14 h-14">
                        <div>
                            <h4 class="font-bold text-text">María González</h4>
                            <p class="text-sm text-muted">Cliente Frecuente</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonio 2 -->
                <div class="p-8 transition-all duration-300 bg-white shadow-xl testimonial-card rounded-2xl">
                    <div class="flex mb-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="mb-6 leading-relaxed text-muted">
                        "El mejor restaurante de la ciudad. El ambiente es acogedor, la comida espectacular y el personal muy atento. Recomiendo la paella."
                    </p>
                    <div class="flex items-center gap-4">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80"
                             alt="Cliente"
                             class="object-cover rounded-full w-14 h-14">
                        <div>
                            <h4 class="font-bold text-text">Carlos Méndez</h4>
                            <p class="text-sm text-muted">Food Blogger</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonio 3 -->
                <div class="p-8 transition-all duration-300 bg-white shadow-xl testimonial-card rounded-2xl">
                    <div class="flex mb-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="mb-6 leading-relaxed text-muted">
                        "Celebramos nuestro aniversario aquí y fue mágico. El chef nos preparó un menú especial y la atención fue personalizada. 100% recomendado."
                    </p>
                    <div class="flex items-center gap-4">
                        <img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80"
                             alt="Cliente"
                             class="object-cover rounded-full w-14 h-14">
                        <div>
                            <h4 class="font-bold text-text">Ana y Laura</h4>
                            <p class="text-sm text-muted">Clientes Especiales</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de satisfacción -->
            <div class="grid grid-cols-1 gap-8 mt-16 md:grid-cols-4">
                <div class="p-6 text-center bg-white rounded-2xl shadow-md">
                    <i class="text-4xl text-primary fas fa-smile"></i>
                    <p class="mt-2 text-2xl font-bold text-text">98%</p>
                    <p class="text-sm text-muted">Clientes Satisfechos</p>
                </div>
                <div class="p-6 text-center bg-white rounded-2xl shadow-md">
                    <i class="text-4xl text-primary fas fa-clock"></i>
                    <p class="mt-2 text-2xl font-bold text-text">15 min</p>
                    <p class="text-sm text-muted">Tiempo de Espera</p>
                </div>
                <div class="p-6 text-center bg-white rounded-2xl shadow-md">
                    <i class="text-4xl text-primary fas fa-trophy"></i>
                    <p class="mt-2 text-2xl font-bold text-text">12</p>
                    <p class="text-sm text-muted">Premios Recibidos</p>
                </div>
                <div class="p-6 text-center bg-white rounded-2xl shadow-md">
                    <i class="text-4xl text-primary fas fa-utensils"></i>
                    <p class="mt-2 text-2xl font-bold text-text">50+</p>
                    <p class="text-sm text-muted">Platos Únicos</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Reservas y Contacto -->
    <section id="contacto" class="py-20 bg-surface lg:py-28">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2">
                <!-- Información de contacto -->
                <div>
                    <span class="inline-block px-4 py-2 mb-4 text-sm font-semibold rounded-full bg-primary/10 text-primary">
                        Reservas
                    </span>
                    <h2 class="mb-6 text-3xl font-bold playfair sm:text-4xl lg:text-5xl text-text">
                        Reserva tu mesa
                    </h2>
                    <p class="mb-8 leading-relaxed text-muted">
                        Te invitamos a vivir una experiencia única. Reserva tu mesa y déjate sorprender por nuestros sabores exclusivos.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <i class="text-primary fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-text">Dirección</h4>
                                <p class="text-muted">Av. Gastronómica 123, Colonia Centro, Ciudad</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <i class="text-primary fas fa-phone"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-text">Teléfono</h4>
                                <p class="text-muted">+52 (55) 1234 5678</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <i class="text-primary fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-text">Email</h4>
                                <p class="text-muted">reservas@saborgestion.com</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <i class="text-primary fas fa-clock"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-text">Horario</h4>
                                <p class="text-muted">Lun - Dom: 1:00 PM - 11:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-8">
                        <a href="#" class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors">
                            <i class="fab fa-tripadvisor"></i>
                        </a>
                    </div>
                </div>

                <!-- Formulario de reserva -->
                <div class="p-8 bg-white shadow-xl rounded-2xl">
                    <h3 class="mb-6 text-2xl font-bold playfair text-text">Hacer una reserva</h3>
                    <form class="space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-text">Nombre completo</label>
                            <input type="text" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-primary border-border" placeholder="Tu nombre">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-text">Email</label>
                            <input type="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-primary border-border" placeholder="tu@email.com">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-text">Teléfono</label>
                            <input type="tel" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-primary border-border" placeholder="Tu teléfono">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-text">Fecha</label>
                                <input type="date" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-primary border-border">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-text">Hora</label>
                                <select class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-primary border-border">
                                    <option>7:00 PM</option>
                                    <option>8:00 PM</option>
                                    <option>9:00 PM</option>
                                    <option>10:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-text">Número de personas</label>
                            <select class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-primary border-border">
                                <option>1 persona</option>
                                <option>2 personas</option>
                                <option>3 personas</option>
                                <option>4 personas</option>
                                <option>5+ personas</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-text">Mensaje o solicitud especial</label>
                            <textarea rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-primary border-border" placeholder="¿Alguna solicitud especial?"></textarea>
                        </div>

                        <button type="submit" class="w-full py-3 font-semibold text-white transition-all duration-300 rounded-lg bg-gradient-to-r from-primary to-secondary hover:shadow-lg hover:scale-105">
                            Reservar Mesa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>



    <!-- DERECHA: Logout (siempre con texto) -->
    <form method="POST" action="{{ route('logout') }}" style="width:450px; display:flex; margin:auto; background:#cdcdcd; justify-content:center; padding:2px; margin-bottom:20px;">
        @csrf
        <button type="submit"
                class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors whitespace-nowrap">
            <i class="fas fa-sign-out-alt text-red-500 text-sm sm:text-base"></i>
            <span class="text-xs sm:text-sm">Cerrar Sesión</span>
        </button>
    </form>



    <!-- Footer -->
    <footer class="py-12 text-white bg-gray-900">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 mb-8 md:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <img src="{{ asset('logo.png') }}" alt="SaborGestion Logo"
                             class="object-contain w-12 h-12 rounded-full">
                        <h3 class="text-xl font-bold playfair">SaborGestion</h3>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-400">
                        Donde los sabores tradicionales se encuentran con la innovación culinaria.
                    </p>
                </div>

                <div>
                    <h4 class="mb-4 font-semibold">Enlaces Rápidos</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#inicio" class="transition-colors hover:text-primary">Inicio</a></li>
                        <li><a href="#nosotros" class="transition-colors hover:text-primary">Nosotros</a></li>
                        <li><a href="#menu" class="transition-colors hover:text-primary">Menú</a></li>
                        <li><a href="#testimonios" class="transition-colors hover:text-primary">Testimonios</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 font-semibold">Soporte</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="transition-colors hover:text-primary">Preguntas Frecuentes</a></li>
                        <li><a href="#" class="transition-colors hover:text-primary">Política de Privacidad</a></li>
                        <li><a href="#" class="transition-colors hover:text-primary">Términos y Condiciones</a></li>
                        <li><a href="#" class="transition-colors hover:text-primary">Trabaja con Nosotros</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 font-semibold">Newsletter</h4>
                    <p class="mb-3 text-sm text-gray-400">Recibe ofertas y novedades</p>
                    <div class="flex gap-2">
                        <input type="email" placeholder="Tu email" class="flex-1 px-3 py-2 text-sm text-white bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:border-primary">
                        <button class="px-3 py-2 transition-colors rounded-lg bg-primary hover:bg-primary/80">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-8 mt-8 border-t border-gray-800">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <p class="text-sm text-gray-400">
                        &copy; 2026 SaborGestion Restaurante. Todos los derechos reservados.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 transition-colors hover:text-primary">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 transition-colors hover:text-primary">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 transition-colors hover:text-primary">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 transition-colors hover:text-primary">
                            <i class="fab fa-tripadvisor"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication Form (preservado) -->
        <form method="POST" class="hidden" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link :href="route('logout')"
                    onclick="event.preventDefault();
                                this.closest('form').submit();">
                {{ __('Log Out') }}
            </x-responsive-nav-link>
        </form>
    </footer>

    <!-- Smooth scroll para anclas -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });




    // Función para animar los contadores
    function animateCounter(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const currentValue = Math.floor(progress * (end - start) + start);

            // Manejar el formato K+ para el tercer contador
            if (element.parentElement.querySelector('span') && element.parentElement.querySelector('span').innerText === 'K+') {
                element.innerText = currentValue;
            } else {
                element.innerText = currentValue;
            }

            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                element.innerText = end;
            }
        };
        window.requestAnimationFrame(step);
    }

    // Observer para detectar cuando los contadores son visibles
    const observerOptions = {
        threshold: 0.3,
        rootMargin: "0px 0px -100px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counters = entry.target.querySelectorAll('.counter');
                counters.forEach(counter => {
                    const target = parseInt(counter.getAttribute('data-target'));
                    const current = parseInt(counter.innerText) || 0;
                    if (current === 0) {
                        animateCounter(counter, 0, target, 2000);
                    }
                });
                // Dejar de observar después de animar
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observar la sección del hero
    const heroSection = document.querySelector('#inicio');
    if (heroSection) {
        observer.observe(heroSection);
    }

    // Smooth scroll para el indicador
    document.querySelector('.animate-bounce a')?.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
</script>



</body>
</html>
