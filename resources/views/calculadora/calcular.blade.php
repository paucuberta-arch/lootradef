@extends('layouts.app')

@section('title', 'Calculadora de ' . $nombre . ' — Lootra')

@section('menu')
    @include('partials.menu')
@endsection

@section('contenido')

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-16">

        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                Calculadora de <span class="text-brand-400">{{ $nombre }}</span>
            </h1>
            <p class="text-slate-500">Introduce los números y obtén el resultado al instante.</p>
        </div>

        {{-- Form Card --}}
        <div class="glass-card rounded-2xl p-6 sm:p-8">

            <form action="{{ route('calcular') }}" method="POST">
                @csrf
                <input type="hidden" name="tipo" value="{{ $tipo }}">

                @if($errors->any())
                    <div class="mb-6 rounded-xl bg-red-500/10 border border-red-500/20 p-4">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-red-400 text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Inputs --}}
                <div id="numeros-container" class="space-y-3">
                    @php
                        $numeros = old('numeros', ['', '']);
                    @endphp

                    @foreach($numeros as $numero)
                        <input
                            type="number"
                            name="numeros[]"
                            value="{{ $numero }}"
                            placeholder="Introduce un número"
                            required
                            class="numero-input w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white text-center text-lg placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition font-mono"
                        >
                    @endforeach
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full mt-6 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold py-3.5 transition-all shadow-lg shadow-brand-600/25 hover:shadow-brand-500/40 hover:-translate-y-0.5"
                >
                    Calcular {{ $nombre }}
                </button>
            </form>

            {{-- Add/Remove buttons --}}
            <div class="flex justify-center gap-3 mt-6 pt-6 border-t border-white/5">
                <button
                    type="button"
                    onclick="anadirNumero()"
                    class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm font-medium border border-white/10 hover:border-white/20 transition-all"
                >
                    <span class="mr-1">+</span> Añadir
                </button>
                <button
                    type="button"
                    onclick="borrarNumero()"
                    class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm font-medium border border-white/10 hover:border-white/20 transition-all"
                >
                    <span class="mr-1">&minus;</span> Quitar
                </button>
            </div>

        </div>

        {{-- Resultado --}}
        @if(session('resultado'))
            <div class="resultado mt-8 glass-card rounded-2xl p-8 text-center glow-brand">
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-3">Resultado</p>
                <p class="text-5xl sm:text-6xl font-black text-white">
                    {{ session('resultado') }}
                </p>
            </div>
        @endif

        {{-- Historial --}}
        <div class="mt-12 glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="text-lg font-bold text-white">Últimas operaciones</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[400px]">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Operación</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Resultado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($operaciones as $op)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-300 whitespace-nowrap">
                                    {{ $op->operacion }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-brand-400 text-right">
                                    {{ $op->resultado }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-10 text-center text-slate-600 text-sm">
                                    No hay operaciones todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    function anadirNumero() {
        let container = document.getElementById('numeros-container');
        let input = document.createElement('input');
        input.type = 'number';
        input.name = 'numeros[]';
        input.placeholder = 'Introduce un número';
        input.required = true;
        input.className = 'numero-input w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white text-center text-lg placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition font-mono';
        container.appendChild(input);
        input.focus();
    }

    function borrarNumero() {
        let container = document.getElementById('numeros-container');
        let inputs = container.querySelectorAll('.numero-input');
        if (inputs.length <= 2) {
            return;
        }
        inputs[inputs.length - 1].remove();
    }
</script>
@endpush
