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
            <div class="auth-logo mb-2">
                <i class="bi bi-printer-fill"></i>
            </div>
            <h1 class="h4 fw-bold mb-0"><?= htmlspecialchars(APP_NAME) ?></h1>
            <p class="text-muted small mb-0">Gestión de impresión 3D</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= $base ?>/login" autocomplete="off">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="usuario" name="usuario"
                           placeholder="admin" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i>Ingresar
            </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            Credenciales por defecto: <code>admin</code> / <code>admin123</code>
        </p>
    </div>
</div>
