@extends('layouts.admin')
@section('admin-title', 'Gráficos y analítica')

@section('admin-content')
<div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
    <div><p class="text-xs font-black uppercase tracking-[.2em] text-cyan-400">Business intelligence</p><h2 class="mt-1 text-3xl font-black">Toda la plataforma, de un vistazo</h2><p class="mt-2 text-sm text-slate-500">Juegos, apuestas deportivas, cajas y crecimiento de usuarios.</p></div>
    <div class="inline-flex self-start rounded-xl border border-white/10 bg-white/[.04] p-1">
        @foreach([7, 30, 90] as $period)<a href="{{ route('admin.charts', ['period' => $period]) }}" class="rounded-lg px-4 py-2 text-xs font-bold {{ $days === $period ? 'bg-cyan-400 text-slate-950' : 'text-slate-400 hover:text-white' }}">{{ $period }} días</a>@endforeach
    </div>
</div>

<div class="grid gap-5 xl:grid-cols-3">
    <section class="xl:col-span-2 rounded-2xl border border-white/10 bg-white/[.025] p-5"><div class="mb-5"><h3 class="font-bold">Flujo económico diario</h3><p class="text-xs text-slate-500">Volumen por producto frente a pagos totales</p></div><div class="h-80"><canvas id="financeChart"></canvas></div></section>
    <section class="rounded-2xl border border-white/10 bg-white/[.025] p-5"><div class="mb-5"><h3 class="font-bold">Actividad total</h3><p class="text-xs text-slate-500">Operaciones registradas por día</p></div><div class="h-80"><canvas id="activityChart"></canvas></div></section>
    <section class="rounded-2xl border border-white/10 bg-white/[.025] p-5"><h3 class="mb-1 font-bold">Mix de juegos</h3><p class="mb-5 text-xs text-slate-500">Partidas por modalidad</p><div class="h-64"><canvas id="gamesChart"></canvas></div></section>
    <section class="rounded-2xl border border-white/10 bg-white/[.025] p-5"><h3 class="mb-1 font-bold">Estado de apuestas</h3><p class="mb-5 text-xs text-slate-500">Deportivas pendientes y liquidadas</p><div class="h-64"><canvas id="sportsChart"></canvas></div></section>
    <section class="rounded-2xl border border-white/10 bg-white/[.025] p-5"><h3 class="mb-1 font-bold">Rareza de premios</h3><p class="mb-5 text-xs text-slate-500">Distribución de artículos obtenidos</p><div class="h-64"><canvas id="boxesChart"></canvas></div></section>
    <section class="xl:col-span-3 rounded-2xl border border-white/10 bg-white/[.025] p-5"><div class="mb-5"><h3 class="font-bold">Adquisición de usuarios</h3><p class="text-xs text-slate-500">Nuevas cuentas creadas diariamente</p></div><div class="h-64"><canvas id="usersChart"></canvas></div></section>
</div>
@endsection

@push('admin-scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Chart) {
        document.querySelectorAll('canvas').forEach(canvas => canvas.replaceWith(Object.assign(document.createElement('p'), {className: 'py-12 text-center text-sm text-red-300', textContent: 'No se pudieron cargar los gráficos.'})));
        return;
    }
    Chart.defaults.color='#94a3b8'; Chart.defaults.borderColor='rgba(255,255,255,.06)'; Chart.defaults.font.family='Inter';
    const series=@json($series); const palette=['#22d3ee','#a78bfa','#f472b6','#34d399','#fbbf24','#fb7185'];
    const tooltip={backgroundColor:'#111827',padding:12,cornerRadius:10};
    new Chart(document.getElementById('financeChart'),{type:'line',data:{labels:series.labels,datasets:[
        {label:'Casino',data:series.games,borderColor:'#a78bfa',backgroundColor:'#a78bfa22',fill:true,tension:.35},
        {label:'Deportivas',data:series.sports,borderColor:'#22d3ee',backgroundColor:'#22d3ee18',fill:true,tension:.35},
        {label:'Cajas',data:series.boxes,borderColor:'#f472b6',tension:.35},
        {label:'Pagos',data:series.payouts,borderColor:'#34d399',borderDash:[6,5],tension:.35}
    ]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{tooltip},scales:{y:{beginAtZero:true,ticks:{callback:v=>v+' €'}}}}});
    new Chart(document.getElementById('activityChart'),{type:'bar',data:{labels:series.labels,datasets:[{label:'Operaciones',data:series.activity,backgroundColor:'#22d3ee99',borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip},scales:{y:{beginAtZero:true}}}});
    const doughnut=(id,labels,data)=>new Chart(document.getElementById(id),{type:'doughnut',data:{labels,datasets:[{data,backgroundColor:palette,borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'66%',plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:8,padding:15}},tooltip}}});
    doughnut('gamesChart',@json($games->pluck('label')),@json($games->pluck('total')));
    doughnut('sportsChart',@json($sports->pluck('label')),@json($sports->pluck('total')));
    doughnut('boxesChart',@json($rarities->pluck('label')),@json($rarities->pluck('total')));
    new Chart(document.getElementById('usersChart'),{type:'bar',data:{labels:series.labels,datasets:[{label:'Nuevos usuarios',data:series.users,backgroundColor:series.users.map((_,i)=>i%2?'#22d3ee99':'#a78bfa99'),borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
});
</script>
@endpush
