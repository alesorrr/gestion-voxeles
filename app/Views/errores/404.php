<?php
declare(strict_types=1);
/** Vista de error 404. */
$base = BASE_URL;
?>
<div class="text-center py-5">
    <i class="bi bi-exclamation-diamond display-1 text-muted"></i>
    <h1 class="h3 mt-3">No encontrado</h1>
    <p class="text-muted">El recurso que buscás no existe o fue eliminado.</p>
    <a href="<?= $base ?>/" class="btn btn-primary mt-2">
        <i class="bi bi-house me-1"></i>Volver al panel
    </a>
</div>
