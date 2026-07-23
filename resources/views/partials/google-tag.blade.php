@php
    $googleAnalyticsId = (string) config('services.google_analytics.measurement_id', '');
    $googleAnalyticsDebug = (bool) config('services.google_analytics.debug', false);
@endphp

@if($googleAnalyticsId !== '' && request()->cookie('lootra_analytics_consent') === 'granted')
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ rawurlencode($googleAnalyticsId) }}"></script>
<script>
(() => {
  const measurementId = @json($googleAnalyticsId);
  const analyticsDebugEnabled = @json($googleAnalyticsDebug);
  const shouldRegisterChallengeStart = @json((bool) session('ga_reto_iniciado'));
  const sentKey = 'reto_iniciado_reto_1';
  const pendingKey = 'reto_iniciado_reto_1_pending';
  const attemptedAtKey = 'reto_iniciado_reto_1_attempted_at';
  const retryDelayMilliseconds = 10000;

  window.dataLayer = window.dataLayer || [];
  function gtag(){window.dataLayer.push(arguments);}
  window.gtag = window.gtag || gtag;
  gtag('js', new Date());

  const configParameters = {};
  if (analyticsDebugEnabled) {
    configParameters.debug_mode = true;
  }
  gtag('config', measurementId, configParameters);

  let challengeStartSent = false;
  let challengeStartPending = shouldRegisterChallengeStart;
  let lastAttemptAt = 0;

  try {
    challengeStartSent = localStorage.getItem(sentKey) === 'true';
    challengeStartPending = challengeStartPending || localStorage.getItem(pendingKey) === 'true';
    lastAttemptAt = Number(localStorage.getItem(attemptedAtKey) || 0);

    if (shouldRegisterChallengeStart && !challengeStartSent) {
      localStorage.setItem(pendingKey, 'true');
    }
  } catch (error) {
    // El evento sigue funcionando aunque el navegador bloquee localStorage.
  }

  if (challengeStartSent || !challengeStartPending) {
    return;
  }

  const attemptedAt = Date.now();
  if (lastAttemptAt > 0 && attemptedAt - lastAttemptAt < retryDelayMilliseconds) {
    return;
  }

  try {
    localStorage.setItem(attemptedAtKey, String(attemptedAt));
  } catch (error) {
    // La deduplicación del servidor sigue evitando nuevos inicios del reto.
  }

  let callbackHandled = false;
  const markChallengeStartAsSent = () => {
    if (callbackHandled) {
      return;
    }

    callbackHandled = true;
    try {
      localStorage.setItem(sentKey, 'true');
      localStorage.removeItem(pendingKey);
      localStorage.removeItem(attemptedAtKey);
    } catch (error) {
      // No interrumpir la navegación por restricciones de almacenamiento.
    }
  };

  const eventParameters = {
    reto_id: 'reto_1',
    reto_nombre: 'RickyEditXLootra',
    send_to: measurementId,
    event_callback: markChallengeStartAsSent,
    event_timeout: 2000
  };

  if (analyticsDebugEnabled) {
    eventParameters.debug_mode = true;
  }

  gtag('event', 'reto_iniciado', eventParameters);
})();
</script>
@endif
