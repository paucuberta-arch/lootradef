<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lootra') — Lootra</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #060812; color: #f8fafc; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; background: radial-gradient(circle at 15% 10%, #16203b, transparent 42%), #060812; }
        .notice { border-bottom: 1px solid rgba(252, 211, 77, .2); background: rgba(252, 211, 77, .08); padding: .65rem 1rem; color: #fef3c7; font-size: .75rem; font-weight: 700; text-align: center; }
        .wrap { display: grid; min-height: calc(100vh - 42px); place-items: center; padding: 2rem 1rem; }
        .card { width: min(100%, 34rem); border: 1px solid rgba(255,255,255,.12); border-radius: 1.5rem; background: rgba(15, 22, 38, .9); padding: 2rem; text-align: center; box-shadow: 0 24px 80px rgba(0,0,0,.35); }
        .code { color: #67e8f9; font-size: .8rem; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; }
        h1 { margin: .75rem 0; font-size: clamp(1.75rem, 5vw, 2.5rem); }
        p { color: #94a3b8; line-height: 1.6; }
        a { display: inline-flex; margin-top: 1rem; border-radius: .8rem; background: #a7f3d0; color: #07111d; font-weight: 800; padding: .8rem 1.1rem; text-decoration: none; }
        a:focus-visible { outline: 3px solid #67e8f9; outline-offset: 3px; }
    </style>
</head>
<body>
    <div class="notice">Proyecto de demostración. No utiliza dinero real. Los créditos, depósitos, premios y retiradas son ficticios y no tienen valor económico.</div>
    <main class="wrap">
        <section class="card" role="alert">
            <div class="code">Error @yield('code')</div>
            <h1>@yield('heading', 'No hemos podido completar la página')</h1>
            <p>@yield('message', 'Vuelve a intentarlo o regresa al inicio de Lootra.')</p>
            <a href="{{ route('inicio') }}">Volver al inicio</a>
        </section>
    </main>
</body>
</html>
