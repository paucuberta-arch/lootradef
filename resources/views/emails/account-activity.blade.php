<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Lootra</title></head>
<body style="margin:0;background:#080811;color:#e2e8f0;font-family:Arial,sans-serif;padding:30px 12px">
<table role="presentation" style="width:100%;max-width:620px;margin:auto;border-collapse:collapse;background:#11111f;border:1px solid #292942;border-radius:18px;overflow:hidden">
    <tr><td style="padding:24px 30px;background:linear-gradient(135deg,#7c3aed,#06b6d4);color:#fff"><strong style="font-size:25px">LOOTRA</strong><div style="font-size:12px;margin-top:4px;opacity:.8">Aviso de actividad de tu cuenta</div></td></tr>
    <tr><td style="padding:30px">
        <p style="margin-top:0">Hola, <strong>{{ $details['name'] }}</strong>.</p>

        @if($event === 'registered')
            <h1 style="font-size:23px;color:#c4b5fd">Tu cuenta ya está preparada</h1>
            <p>El registro se ha completado correctamente. Tu saldo inicial es de <strong>{{ number_format($details['balance'], 2, ',', '.') }} €</strong>.</p>
        @elseif($event === 'login')
            <h1 style="font-size:23px;color:#67e8f9">Nuevo inicio de sesión</h1>
            <p>Se ha iniciado sesión el {{ $details['time']->format('d/m/Y H:i') }} UTC desde la IP <strong>{{ $details['ip'] ?: 'no disponible' }}</strong>.</p>
            <p>Si no has sido tú, cambia tu contraseña y contacta con soporte.</p>
        @elseif($event === 'deposit')
            <h1 style="font-size:23px;color:#6ee7b7">Depósito confirmado</h1>
            <p>Se han añadido <strong>{{ number_format($details['amount'], 2, ',', '.') }} €</strong> a tu cartera.</p>
            <p>Saldo disponible: <strong>{{ number_format($details['balance'], 2, ',', '.') }} €</strong>.</p>
        @elseif($event === 'sports_bet_settled')
            <h1 style="font-size:23px;color:#fbbf24">Apuesta finalizada</h1>
            <p><strong>{{ $details['match'] }}</strong> · Resultado {{ $details['score'] }}</p>
            <p>Tu apuesta de {{ number_format($details['stake'], 2, ',', '.') }} € ha sido <strong>{{ $details['status'] }}</strong>.</p>
            <p>Ganancia abonada: <strong>{{ number_format($details['winnings'], 2, ',', '.') }} €</strong>.</p>
        @endif

        <p style="margin:28px 0 0;color:#64748b;font-size:12px">Este es un correo automático relacionado con la seguridad o los movimientos de tu cuenta.</p>
    </td></tr>
</table>
</body>
</html>
