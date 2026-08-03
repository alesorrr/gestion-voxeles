<?php
declare(strict_types=1);
/**
 * Formulario de login.
 * @var string|null $error
 */
$base = BASE_URL;
?>
<div class="auth-card card shadow-lg border-0">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <h1 class="auth-title mb-0">Bienvenido!</h1>
            <p class="auth-subtitle mb-0">APP Gestión Voxeles</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= $base ?>/login" autocomplete="off">
            <div class="mb-3">
                <input type="text" class="form-control form-control-lg" id="usuario" name="usuario"
                       placeholder="Usuario" required autofocus>
            </div>
            <div class="mb-4">
                <input type="password" class="form-control form-control-lg" id="password" name="password"
                       placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn btn-acceder btn-lg w-100">Acceder</button>
        </form>
    </div>
</div>
