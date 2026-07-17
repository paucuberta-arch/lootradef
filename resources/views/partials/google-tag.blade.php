<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ZG7EW2QE96"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-ZG7EW2QE96');

  @if(session('ga_reto_iniciado'))
  let retoIniciadoRegistrado = false;
  try {
    retoIniciadoRegistrado = localStorage.getItem('reto_iniciado_reto_1') === 'true';
  } catch (error) {
    // El evento sigue siendo válido aunque el navegador bloquee localStorage.
  }

  if (!retoIniciadoRegistrado) {
    gtag('event', 'reto_iniciado', {
      reto_id: 'reto_1',
      reto_nombre: 'RickyEditXLootra'
    });

    try {
      localStorage.setItem('reto_iniciado_reto_1', 'true');
    } catch (error) {
      // No interrumpir el inicio del reto por restricciones de almacenamiento.
    }
  }
  @endif
</script>
