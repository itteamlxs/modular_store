<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-globe me-2"></i>Conversor de Zonas Horarias</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Hora</label>
                <input type="time" id="horaInput" class="form-control" />
            </div>
            <div class="col-md-3">
                <label class="form-label">De</label>
                <select class="form-select" id="paisOrigen">
                    <option value="Europe/Madrid" selected>España</option>
                    <option value="America/New_York">USA (NY)</option>
                    <option value="America/Los_Angeles">USA (LA)</option>
                    <option value="America/Mexico_City">México</option>
                    <option value="Asia/Tokyo">Japón</option>
                    <option value="Asia/Shanghai">China</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">A</label>
                <select class="form-select" id="paisDestino">
                    <option value="Europe/Madrid">España</option>
                    <option value="America/New_York" selected>USA (NY)</option>
                    <option value="America/Los_Angeles">USA (LA)</option>
                    <option value="America/Mexico_City">México</option>
                    <option value="Asia/Tokyo">Japón</option>
                    <option value="Asia/Shanghai">China</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="convertirHora()">Convertir</button>
            </div>
        </div>
        
        <div id="resultado" class="mt-3" style="display:none;">
            <div class="alert alert-info">
                <strong id="horaConvertida"></strong>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
<script>
const { DateTime } = luxon;

function convertirHora() {
    const hora = document.getElementById('horaInput').value;
    const origen = document.getElementById('paisOrigen').value;
    const destino = document.getElementById('paisDestino').value;
    
    if (!hora) {
        alert('Selecciona una hora');
        return;
    }
    
    const hoy = DateTime.local().toFormat('yyyy-MM-dd');
    const fechaHoraOrigen = DateTime.fromISO(`${hoy}T${hora}`, { zone: origen });
    const fechaHoraDestino = fechaHoraOrigen.setZone(destino);
    
    const origenNombre = document.getElementById('paisOrigen').selectedOptions[0].text;
    const destinoNombre = document.getElementById('paisDestino').selectedOptions[0].text;
    
    document.getElementById('horaConvertida').innerHTML = 
        `${hora} en ${origenNombre} = <strong>${fechaHoraDestino.toFormat('HH:mm')}</strong> en ${destinoNombre}`;
    
    document.getElementById('resultado').style.display = 'block';
}

// Establecer hora actual
document.addEventListener('DOMContentLoaded', function() {
    const ahora = DateTime.local().toFormat('HH:mm');
    document.getElementById('horaInput').value = ahora;
});
</script>