/**
 * Scanner de code-barres basé sur ZXing (UMD).
 * Utilisation :
 *   initScanner({
 *     btnStart, btnStop, container, video, status,
 *     onDetect: function(code) { ... }
 *   });
 */
function initScanner(opts) {
  const btnStart = document.getElementById(opts.btnStart);
  const btnStop  = document.getElementById(opts.btnStop);
  const container = document.getElementById(opts.container);
  const videoEl  = document.getElementById(opts.video);
  const statusEl = document.getElementById(opts.status);

  if (!btnStart || !videoEl) return;
  if (typeof ZXing === 'undefined') {
    statusEl.textContent = "❌ La bibliothèque ZXing n'a pas pu se charger (vérifiez la connexion).";
    return;
  }

  const reader = new ZXing.BrowserMultiFormatReader();
  let courant = false;

  btnStart.addEventListener('click', async () => {
    try {
      statusEl.textContent = "Initialisation de la caméra…";
      container.style.display = 'block';
      btnStart.style.display = 'none';
      btnStop.style.display = 'inline-block';
      courant = true;

      const devices = await reader.listVideoInputDevices();
      // Préférence : caméra arrière
      let deviceId = null;
      const back = devices.find(d => /back|rear|arrière|environment/i.test(d.label));
      deviceId = back ? back.deviceId : (devices[0] && devices[0].deviceId);

      reader.decodeFromVideoDevice(deviceId, videoEl, (result, err) => {
        if (result && courant) {
          statusEl.textContent = "✅ Code détecté : " + result.getText();
          if (typeof opts.onDetect === 'function') {
            opts.onDetect(result.getText());
          }
          // Pause anti-rebond
          courant = false;
          setTimeout(() => { courant = true; }, 1500);
        }
      });
    } catch (e) {
      statusEl.textContent = "❌ Erreur caméra : " + e.message;
    }
  });

  btnStop.addEventListener('click', () => {
    reader.reset();
    container.style.display = 'none';
    btnStop.style.display = 'none';
    btnStart.style.display = 'inline-block';
    statusEl.textContent = "Scanner arrêté.";
    courant = false;
  });
}
