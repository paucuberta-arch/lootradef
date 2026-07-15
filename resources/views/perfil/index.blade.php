@extends('layouts.app')

@section('title', 'Mi perfil — Lootra Casino')

@section('contenido')

    <div class="relative min-h-[80vh] flex items-center justify-center px-4 py-16">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative w-full max-w-lg">

            @if(session('success'))
                <div class="mb-6 rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-400 text-sm text-center">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8 backdrop-blur-sm">

                <div class="text-center mb-8">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black text-3xl font-black mx-auto mb-4 shadow-lg shadow-brand-500/20">
                        {{ strtoupper(substr($usuario->name, 0, 1)) }}
                    </div>
                    <h1 class="text-2xl font-bold text-white">{{ $usuario->name }}</h1>
                    <p class="text-slate-500 text-sm mt-1">Mi perfil</p>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</p>
                            <p class="text-white font-semibold mt-0.5">{{ $usuario->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Correo electronico</p>
                            <p class="text-white font-semibold mt-0.5">{{ $usuario->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Miembro desde</p>
                            <p class="text-white font-semibold mt-0.5">{{ $usuario->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-brand-500/5 border border-brand-500/20">
                        <div>
                            <p class="text-xs font-medium text-brand-400 uppercase tracking-wider">Saldo</p>
                            <p class="text-white font-semibold mt-0.5">€{{ number_format($usuario->saldo, 2) }}</p>
                        </div>
                        <button onclick="window.location.reload()" class="px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20 transition text-sm font-bold">+</button>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ url('/') }}" class="block w-full text-center rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold py-3 border border-white/10 hover:border-white/20 transition-all">
                        Volver al casino
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 font-semibold py-3 border border-red-500/20 hover:border-red-500/30 transition-all">
                            Cerrar sesion
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
