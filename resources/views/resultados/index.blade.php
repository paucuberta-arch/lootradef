@extends('layouts.app')

@section('title', 'Resultados — Lootra')

@section('menu')
    @include('partials.menu')
@endsection

@section('contenido')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-16">

        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">Resultados</h1>
            <p class="text-slate-500">Historial de todas tus operaciones.</p>
        </div>

        <div class="glass-card rounded-2xl overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Operación</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Resultado</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($operaciones as $op)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-600 font-mono">
                                    #{{ $op->id }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-300">
                                    {{ $op->operacion }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-brand-400 text-right">
                                    {{ $op->resultado }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('resultados.show', $op->id) }}"
                                       class="inline-flex items-center px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white text-xs font-semibold border border-white/10 hover:border-white/20 transition-all">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center text-slate-600">
                                    <div class="text-4xl mb-3">&#x1F4CB;</div>
                                    <p class="text-sm">No hay resultados todavía.</p>
                                    <a href="{{ route('suma') }}" class="inline-block mt-4 px-5 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition-all">
                                        Ir a la calculadora
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

@endsection
