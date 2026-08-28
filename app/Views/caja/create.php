<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Registrar Movimiento</h1>
    <a href="/cpee/caja" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a Caja
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="/cpee/caja/guardar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="row mb-3">
                <div class="col-md-6 text-left">
                    <label for="tipo" class="col-form-label">Tipo de Movimiento *</label>
                    <select class="form-control" id="tipo" name="tipo" required>
                        <option value="Ingreso" <?= ($tipoPreseleccionado ?? '') === 'Ingreso' ? 'selected' : '' ?>>Ingreso (Cobro)</option>
                        <option value="Egreso" <?= ($tipoPreseleccionado ?? '') === 'Egreso' ? 'selected' : '' ?>>Egreso (Pago)</option>
                    </select>
                </div>
                <div class="col-md-6 text-left">
                    <label for="profesional_id" class="col-form-label">Profesional / Matriculado (Opcional)</label>
                    <select class="form-control" id="profesional_id" name="profesional_id">
                        <option value="">-- No aplica --</option>
                        <?php foreach ($profesionales as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (int)($profesionalPreseleccionado ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                                [<?= $p['nro_matricula'] ?>] <?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 text-left">
                <label for="concepto" class="col-form-label">Concepto / Detalle *</label>
                <input type="text" class="form-control" id="concepto" name="concepto" required placeholder="Ej: Pago a Proveedor de Internet...">
            </div>

            <h5 class="text-primary mt-4 border-bottom pb-2">Datos del Comprobante (Opcional)</h5>
            <div class="row mb-3">
                <div class="col-md-3 text-left">
                    <label for="tipo_comprobante" class="col-form-label">Tipo (Fact A, B, C)</label>
                    <input type="text" class="form-control" id="tipo_comprobante" name="tipo_comprobante">
                </div>
                <div class="col-md-3 text-left">
                    <label for="punto_venta" class="col-form-label">Pto Venta</label>
                    <input type="text" class="form-control" id="punto_venta" name="punto_venta" placeholder="0001">
                </div>
                <div class="col-md-3 text-left">
                    <label for="nro_comprobante" class="col-form-label">Nro Comprobante</label>
                    <input type="text" class="form-control" id="nro_comprobante" name="nro_comprobante" placeholder="00001234">
                </div>
                <div class="col-md-3 text-left">
                    <label for="cuit" class="col-form-label">CUIT</label>
                    <input type="text" class="form-control" id="cuit" name="cuit" placeholder="20-12345678-9">
                </div>
            </div>

            <h5 class="text-primary mt-4 border-bottom pb-2">Documento de Respaldo (Opcional)</h5>
            <div class="row mb-3">
                <div class="col-md-6 text-left">
                    <label for="archivo" class="col-form-label">PDF o Foto (Factura / Comprobante / Respaldo)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="archivo" name="archivo"
                            accept="application/pdf,image/jpeg,image/png,image/webp,image/gif">
                        <label class="custom-file-label" for="archivo">Seleccionar archivo…</label>
                    </div>
                    <small class="form-text text-muted">Máx. 5 MB. Formatos: PDF, JPG, PNG, WEBP, GIF.</small>
                </div>
                <div class="col-md-6 text-left">
                    <div id="previewArea" class="d-none">
                        <img id="previewImg" alt="Vista previa" class="img-fluid rounded border" style="max-height:160px; display:none;">
                        <div id="previewPdf" class="alert alert-info p-2 mb-0">Documento PDF seleccionado.</div>
                    </div>
                </div>
            </div>

            <h5 class="text-primary mt-4 border-bottom pb-2">Importes</h5>
            <div class="row mb-3">
                <div class="col-md-4 text-left">
                    <label for="monto_neto" class="col-form-label">Monto Neto *</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" class="form-control" id="monto_neto" name="monto_neto" required value="0.00">
                    </div>
                </div>
                <div class="col-md-4 text-left">
                    <label for="iva" class="col-form-label">IVA (Opcional)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" class="form-control" id="iva" name="iva" value="0.00">
                    </div>
                </div>
            </div>

            <hr>
            <div class="text-right">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Registrar Movimiento</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tipo = document.getElementById('tipo');
        const profesional = document.getElementById('profesional_id');

        const cambioTipo = function() {
            if (tipo.value === 'Ingreso') {
                profesional.disabled = false;
            } else {
                profesional.value = '';
                profesional.disabled = true;
            }
        };
        tipo.addEventListener('change', cambioTipo);
        cambioTipo();

        const inputArchivo = document.getElementById('archivo');
        const labelArchivo = document.querySelector('.custom-file-label');
        const previewArea = document.getElementById('previewArea');
        const previewImg = document.getElementById('previewImg');
        const previewPdf = document.getElementById('previewPdf');

        const TIPOS_OK = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        const MAX_SIZE = 5 * 1024 * 1024;

        inputArchivo.addEventListener('change', function() {
            const f = this.files[0];
            if (!f) {
                labelArchivo.textContent = 'Seleccionar archivo…';
                previewArea.classList.add('d-none');
                return;
            }
            labelArchivo.textContent = f.name;

            if (f.size > MAX_SIZE) {
                alert('El archivo supera el tamaño máximo de 5 MB.');
                this.value = '';
                labelArchivo.textContent = 'Seleccionar archivo…';
                return;
            }
            if (!TIPOS_OK.includes(f.type)) {
                alert('Formato no permitido. Solo PDF o imágenes (JPG/PNG/WEBP/GIF).');
                this.value = '';
                labelArchivo.textContent = 'Seleccionar archivo…';
                return;
            }

            previewArea.classList.remove('d-none');
            if (f.type === 'application/pdf') {
                previewImg.style.display = 'none';
                previewPdf.style.display = '';
            } else {
                previewImg.src = URL.createObjectURL(f);
                previewImg.style.display = '';
                previewPdf.style.display = 'none';
            }
        });
    });
</script>