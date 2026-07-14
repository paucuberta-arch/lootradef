@extends('layouts.app')

@section('title', 'Lootra — Calculadora Profesional')

@section('contenido')

    {{-- HERO --}}
    <section class="relative overflow-hidden">
        {{-- Background glow --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] bg-brand-600/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-brand-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 sm:pt-28 sm:pb-32 text-center">

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-300 text-sm font-medium mb-8">
                <span class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
                Calculadora profesional
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black tracking-tight leading-[1.1] mb-6">
                <span class="text-white">Realiza cálculos</span><br>
                <span class="bg-gradient-to-r from-brand-400 via-brand-300 to-blue-400 bg-clip-text text-transparent">
                    de forma inteligente.
                </span>
            </h1>

            <p class="max-w-2xl mx-auto text-lg sm:text-xl text-slate-400 leading-relaxed mb-10">
                Suma, resta, multiplica y divide con una interfaz moderna.
                Guarda tu historial y accede a tus resultados en cualquier momento.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('suma') }}"
                   class="group relative px-8 py-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-lg transition-all shadow-xl shadow-brand-600/25 hover:shadow-brand-500/40 hover:-translate-y-0.5">
                    Empezar a calcular
                    <span class="inline-block ml-2 transition-transform group-hover:translate-x-1">&rarr;</span>
                </a>
                <a href="{{ route('registro') }}"
                   class="px-8 py-4 rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold text-lg border border-white/10 hover:border-white/20 transition-all">
                    Crear cuenta gratis
                </a>
            </div>

        </div>
    </section>

    {{-- FEATURES --}}
    <section class="relative py-20 sm:py-28">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-16">
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">Todo lo que necesitas</h2>
                <p class="text-slate-400 text-lg max-w-xl mx-auto">Herramientas diseñadas para hacer tu vida más fácil.</p>
            </div>

            <div class="grid sm:grid-cols-3 gap-6">

                <div class="glass-card rounded-2xl p-6 sm:p-8 hover:bg-white/[0.05] transition-colors group">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center text-2xl mb-5 group-hover:scale-110 transition-transform">
                        &#x2795;
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Cuatro operaciones</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Suma, resta, multiplicación y división con tantos números como necesites.</p>
                </div>

                <div class="glass-card rounded-2xl p-6 sm:p-8 hover:bg-white/[0.05] transition-colors group">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-2xl mb-5 group-hover:scale-110 transition-transform">
                        &#x1F4CA;
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Historial completo</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Cada cálculo se guarda automáticamente para que puedas consultarlo después.</p>
                </div>

                <div class="glass-card rounded-2xl p-6 sm:p-8 hover:bg-white/[0.05] transition-colors group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-2xl mb-5 group-hover:scale-110 transition-transform">
                        &#x1F512;
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Cuenta personal</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Regístrate para guardar tus datos de forma segura y acceder desde cualquier dispositivo.</p>
                </div>

            </div>

        </div>
    </section>

    {{-- CTA --}}
    <section class="py-20 sm:py-28">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card rounded-3xl p-10 sm:p-16 text-center glow-brand">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">¿Listo para empezar?</h2>
                <p class="text-slate-400 text-lg mb-8 max-w-lg mx-auto">Comienza a calcular ahora mismo o crea una cuenta para guardar tu historial.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('suma') }}"
                       class="px-8 py-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-lg transition-all shadow-xl shadow-brand-600/25 hover:shadow-brand-500/40 hover:-translate-y-0.5">
                        Ir a la calculadora
                    </a>
                    <a href="{{ route('registro') }}"
                       class="px-8 py-4 rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold text-lg border border-white/10 hover:border-white/20 transition-all">
                        Crear cuenta
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
