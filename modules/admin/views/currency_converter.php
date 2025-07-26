<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-coins me-2"></i>Conversor de Monedas</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" id="cantidadInput" class="form-control" value="1" min="0" step="0.01" />
            </div>
            <div class="col-md-3">
                <label class="form-label">De</label>
                <select class="form-select" id="monedaOrigen">
                    <option value="EUR" selected>EUR - Euro</option>
                    <option value="USD">USD - Dólar</option>
                    <option value="GBP">GBP - Libra</option>
                    <option value="JPY">JPY - Yen</option>
                    <option value="CHF">CHF - Franco Suizo</option>
                    <option value="CAD">CAD - Dólar Canadiense</option>
                    <option value="AUD">AUD - Dólar Australiano</option>
                    <option value="CNY">CNY - Yuan</option>
                    <option value="MXN">MXN - Peso Mexicano</option>
                    <option value="BRL">BRL - Real Brasileño</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">A</label>
                <select class="form-select" id="monedaDestino">
                    <option value="EUR">EUR - Euro</option>
                    <option value="USD" selected>USD - Dólar</option>
                    <option value="GBP">GBP - Libra</option>
                    <option value="JPY">JPY - Yen</option>
                    <option value="CHF">CHF - Franco Suizo</option>
                    <option value="CAD">CAD - Dólar Canadiense</option>
                    <option value="AUD">AUD - Dólar Australiano</option>
                    <option value="CNY">CNY - Yuan</option>
                    <option value="MXN">MXN - Peso Mexicano</option>
                    <option value="BRL">BRL - Real Brasileño</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-success w-100" onclick="convertirMoneda()">
                    <i class="fas fa-exchange-alt me-1"></i>Convertir
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" onclick="intercambiarMonedas()">
                    <i class="fas fa-arrows-alt-h"></i>
                </button>
            </div>
        </div>
        
        <div id="resultadoMoneda" class="mt-3" style="display:none;">
            <div class="alert alert-success">
                <div class="d-flex justify-content-between align-items-center">
                    <span id="conversionResultado"></span>
                    <small class="text-muted" id="tasaCambio"></small>
                </div>
            </div>
        </div>
        
        <div id="errorMoneda" class="mt-3" style="display:none;">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="errorTexto"></span>
            </div>
        </div>
        
        <div id="loadingMoneda" class="mt-3" style="display:none;">
            <div class="text-center">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Obteniendo tasas de cambio...
            </div>
        </div>
    </div>
</div>

<script>
let exchangeRates = {};

async function obtenerTasasCambio() {
    try {
        const response = await fetch('https://api.exchangerate-api.com/v4/latest/EUR');
        if (!response.ok) throw new Error('Error al obtener tasas');
        const data = await response.json();
        exchangeRates = data.rates;
        exchangeRates.EUR = 1; // Agregar EUR como base
        return true;
    } catch (error) {
        console.error('Error:', error);
        return false;
    }
}

async function convertirMoneda() {
    const cantidad = parseFloat(document.getElementById('cantidadInput').value);
    const origen = document.getElementById('monedaOrigen').value;
    const destino = document.getElementById('monedaDestino').value;
    
    // Ocultar resultados previos
    document.getElementById('resultadoMoneda').style.display = 'none';
    document.getElementById('errorMoneda').style.display = 'none';
    
    if (isNaN(cantidad) || cantidad < 0) {
        mostrarErrorMoneda('Ingresa una cantidad válida');
        return;
    }
    
    if (origen === destino) {
        document.getElementById('conversionResultado').innerHTML = 
            `<strong>${cantidad.toFixed(2)} ${origen}</strong> = <strong>${cantidad.toFixed(2)} ${destino}</strong>`;
        document.getElementById('tasaCambio').textContent = 'Tasa: 1.00000';
        document.getElementById('resultadoMoneda').style.display = 'block';
        return;
    }
    
    // Mostrar loading
    document.getElementById('loadingMoneda').style.display = 'block';
    
    // Obtener tasas si no las tenemos
    if (Object.keys(exchangeRates).length === 0) {
        const success = await obtenerTasasCambio();
        if (!success) {
            document.getElementById('loadingMoneda').style.display = 'none';
            mostrarErrorMoneda('No se pudieron obtener las tasas de cambio');
            return;
        }
    }
    
    try {
        // Convertir a EUR primero, luego a la moneda destino
        let resultado;
        let tasa;
        
        if (origen === 'EUR') {
            resultado = cantidad * exchangeRates[destino];
            tasa = exchangeRates[destino];
        } else if (destino === 'EUR') {
            resultado = cantidad / exchangeRates[origen];
            tasa = 1 / exchangeRates[origen];
        } else {
            // Convertir de origen a EUR, luego a destino
            const enEUR = cantidad / exchangeRates[origen];
            resultado = enEUR * exchangeRates[destino];
            tasa = exchangeRates[destino] / exchangeRates[origen];
        }
        
        document.getElementById('loadingMoneda').style.display = 'none';
        document.getElementById('conversionResultado').innerHTML = 
            `<strong>${cantidad.toFixed(2)} ${origen}</strong> = <strong>${resultado.toFixed(2)} ${destino}</strong>`;
        document.getElementById('tasaCambio').textContent = `Tasa: ${tasa.toFixed(5)}`;
        document.getElementById('resultadoMoneda').style.display = 'block';
        
    } catch (error) {
        document.getElementById('loadingMoneda').style.display = 'none';
        mostrarErrorMoneda('Error en la conversión');
    }
}

function intercambiarMonedas() {
    const origen = document.getElementById('monedaOrigen');
    const destino = document.getElementById('monedaDestino');
    
    const temp = origen.value;
    origen.value = destino.value;
    destino.value = temp;
}

function mostrarErrorMoneda(mensaje) {
    document.getElementById('errorTexto').textContent = mensaje;
    document.getElementById('errorMoneda').style.display = 'block';
}

// Cargar tasas al inicializar
document.addEventListener('DOMContentLoaded', function() {
    obtenerTasasCambio();
});
</script>