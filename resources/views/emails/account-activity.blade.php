<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title>Lootra</title>
    <style>
        @media only screen and (max-width:620px){.email-shell{width:100%!important}.email-pad{padding-left:22px!important;padding-right:22px!important}.email-title{font-size:28px!important;line-height:32px!important}.email-stat{display:block!important;width:auto!important;border-left:0!important;border-top:1px solid #273044!important}.email-button{display:block!important;text-align:center!important}}
    </style>
</head>
<body style="margin:0;padding:0;background-color:#060812;color:#e5e7eb;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased">
@php
    $content = match($event) {
        'registered' => ['eyebrow' => 'Cuenta creada', 'title' => 'Tu espacio en Lootra ya está listo.', 'accent' => '#f2cd75', 'intro' => 'Ya puedes descubrir el casino, participar en retos y gestionar todas tus recompensas desde un mismo lugar.', 'button' => 'Explorar Lootra', 'url' => route('inicio')],
        'login' => ['eyebrow' => 'Seguridad', 'title' => 'Hemos detectado un nuevo acceso.', 'accent' => '#67e8f9', 'intro' => 'Te avisamos cada vez que se inicia una nueva sesión para que mantengas el control de tu cuenta.', 'button' => 'Revisar mi perfil', 'url' => route('profile.show')],
        'deposit' => ['eyebrow' => 'Cartera', 'title' => 'Tu saldo se ha actualizado.', 'accent' => '#6ee7b7', 'intro' => 'El movimiento se ha procesado correctamente y el nuevo saldo ya está disponible en tu cartera.', 'button' => 'Abrir mi cartera', 'url' => route('wallet.show')],
        'sports_bet_settled' => ['eyebrow' => 'Resultado final', 'title' => 'Tu apuesta ya está resuelta.', 'accent' => '#f2cd75', 'intro' => 'El evento ha terminado. Aquí tienes el resumen final de tu jugada.', 'button' => 'Ver apuestas', 'url' => route('sports.index')],
        default => ['eyebrow' => 'Actividad', 'title' => 'Hay novedades en tu cuenta.', 'accent' => '#f2cd75', 'intro' => 'Consulta los detalles de la última actividad registrada en Lootra.', 'button' => 'Abrir Lootra', 'url' => route('inicio')],
    };
@endphp
<div style="display:none;max-height:0;overflow:hidden;opacity:0">{{ $content['title'] }} Consulta los detalles de tu cuenta Lootra.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#060812">
    <tr><td align="center" style="padding:34px 12px">
        <table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0" class="email-shell" style="width:620px;max-width:620px;border-collapse:separate;background:#0d1320;border:1px solid #242b3a;border-radius:24px;overflow:hidden;box-shadow:0 24px 70px rgba(0,0,0,.4)">
            <tr><td class="email-pad" style="padding:24px 34px;border-bottom:1px solid #242b3a;background:#0a0f1a">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>
                    <td><table role="presentation" cellspacing="0" cellpadding="0"><tr><td width="38" height="38" align="center" style="width:38px;height:38px;border-radius:11px;background:#f2cd75;color:#111827;font-size:16px;font-weight:900">L</td><td style="padding-left:11px;color:#fff;font-size:18px;font-weight:800;letter-spacing:-.5px">Lootra<span style="color:#f2cd75">Casino</span></td></tr></table></td>
                    <td align="right" style="color:#687386;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:1.5px">Notificación de cuenta</td>
                </tr></table>
            </td></tr>
            <tr><td class="email-pad" style="padding:42px 42px 30px">
                <div style="margin-bottom:17px;color:{{ $content['accent'] }};font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:2.4px">{{ $content['eyebrow'] }}</div>
                <h1 class="email-title" style="margin:0;color:#fff;font-size:36px;line-height:40px;letter-spacing:-1.4px">{{ $content['title'] }}</h1>
                <p style="margin:18px 0 0;color:#aab3c2;font-size:16px;line-height:26px">Hola, <strong style="color:#fff">{{ $details['name'] }}</strong>. {{ $content['intro'] }}</p>
            </td></tr>
            <tr><td class="email-pad" style="padding:0 42px 34px">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;background:#111927;border:1px solid #273044;border-radius:16px;overflow:hidden">
                    @if($event === 'registered')
                        <tr><td style="padding:20px 22px;color:#8290a5;font-size:12px;text-transform:uppercase;letter-spacing:1px">Saldo inicial</td><td align="right" style="padding:20px 22px;color:#fff;font-size:20px;font-weight:bold">{{ number_format($details['balance'], 2, ',', '.') }} €</td></tr>
                    @elseif($event === 'login')
                        <tr><td style="padding:17px 22px;color:#8290a5;font-size:12px">Fecha y hora</td><td align="right" style="padding:17px 22px;color:#fff;font-size:13px;font-weight:bold">{{ $details['time']->format('d/m/Y · H:i') }} UTC</td></tr>
                        <tr><td style="padding:17px 22px;border-top:1px solid #273044;color:#8290a5;font-size:12px">Dirección IP</td><td align="right" style="padding:17px 22px;border-top:1px solid #273044;color:#fff;font-size:13px;font-weight:bold">{{ $details['ip'] ?: 'No disponible' }}</td></tr>
                    @elseif($event === 'deposit')
                        <tr><td width="50%" style="padding:20px 22px;color:#8290a5;font-size:12px;text-transform:uppercase;letter-spacing:1px">Ingreso</td><td align="right" style="padding:20px 22px;color:#6ee7b7;font-size:20px;font-weight:bold">+{{ number_format($details['amount'], 2, ',', '.') }} €</td></tr>
                        <tr><td style="padding:17px 22px;border-top:1px solid #273044;color:#8290a5;font-size:12px">Saldo disponible</td><td align="right" style="padding:17px 22px;border-top:1px solid #273044;color:#fff;font-size:14px;font-weight:bold">{{ number_format($details['balance'], 2, ',', '.') }} €</td></tr>
                    @elseif($event === 'sports_bet_settled')
                        <tr><td colspan="2" style="padding:20px 22px;border-bottom:1px solid #273044"><div style="color:#fff;font-size:16px;font-weight:bold">{{ $details['match'] }}</div><div style="margin-top:6px;color:#8290a5;font-size:12px">Resultado final · {{ $details['score'] }}</div></td></tr>
                        <tr><td style="padding:16px 22px;color:#8290a5;font-size:12px">Apuesta · {{ ucfirst($details['status']) }}</td><td align="right" style="padding:16px 22px;color:#fff;font-size:14px;font-weight:bold">{{ number_format($details['stake'], 2, ',', '.') }} €</td></tr>
                        <tr><td style="padding:16px 22px;border-top:1px solid #273044;color:#8290a5;font-size:12px">Ganancia abonada</td><td align="right" style="padding:16px 22px;border-top:1px solid #273044;color:{{ $content['accent'] }};font-size:18px;font-weight:bold">{{ number_format($details['winnings'], 2, ',', '.') }} €</td></tr>
                    @endif
                </table>

                @if($event === 'login')<p style="margin:18px 0 0;padding:14px 16px;border-left:3px solid #f05d68;background:#1a1219;color:#d7a9b0;font-size:12px;line-height:19px">Si no reconoces este acceso, cambia tus credenciales y contacta con soporte cuanto antes.</p>@endif
                <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:28px"><tr><td bgcolor="{{ $content['accent'] }}" style="border-radius:12px"><a href="{{ $content['url'] }}" class="email-button" style="display:inline-block;padding:14px 22px;color:#0b101a;text-decoration:none;font-size:13px;font-weight:800">{{ $content['button'] }} &nbsp;→</a></td></tr></table>
            </td></tr>
            <tr><td class="email-pad" style="padding:24px 42px;border-top:1px solid #242b3a;background:#0a0f1a;color:#687386;font-size:11px;line-height:18px">Este correo se genera automáticamente por actividad relacionada con tu cuenta. Lootra nunca te pedirá tu contraseña por email.<br><span style="color:#475569">© {{ date('Y') }} Lootra Casino · Juega con responsabilidad.</span></td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
