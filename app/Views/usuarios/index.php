<?php
declare(strict_types=1);
/**
 * Gestión de usuarios del sistema (solo administradores).
 * @var array<int, array<string, mixed>> $usuarios
 * @var array{tipo:string,mensaje:string}|null $flash
 */
$base = BASE_URL;
$rolesLabel = ['admin' => 'Administrador', 'operador' => 'Operario', 'ventas' => 'Usuario Ventas'];
$rolesBadge = ['admin' => 'bg-primary', 'operador' => 'bg-secondary', 'ventas' => 'bg-info text-dark'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Usuarios</h1>
        <p class="text-muted mb-0"><?= count($usuarios) ?> usuario(s) en el sistema</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Formulario de alta -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-person-plus me-1"></i>Crear usuario</h2></div>
            <div class="card-body">
                <form method="post" action="<?= $base ?>/usuarios">
                    <div class="mb-3">
                        <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario <span class="text-danger">*</span></label>
                        <input type="text" name="usuario" class="form-control" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="text" name="password" class="form-control" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select">
                            <option value="admin">Administrador</option>
                            <option value="operador" selected>Operario</option>
                            <option value="ventas">Usuario Ventas</option>
                        </select>
                        <div class="form-text">
                            Administrador: acceso total · Operario: panel y Kanban ·
                            Usuario Ventas: órdenes, Kanban y presupuestos.
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Crear usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Listado -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <?php $rol = (string) $u['rol']; ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars((string) $u['nombre']) ?></td>
                            <td><?= htmlspecialchars((string) $u['usuario']) ?></td>
                            <td><span class="badge <?= $rolesBadge[$rol] ?? 'bg-secondary' ?>"><?= $rolesLabel[$rol] ?? $rol ?></span></td>
                            <td>
                                <?php if ((int) $u['activo'] === 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#editar<?= (int) $u['id'] ?>" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= $base ?>/usuarios/<?= (int) $u['id'] ?>/estado"
                                   class="btn btn-sm btn-outline-secondary" title="Activar / desactivar">
                                    <i class="bi bi-toggle-on"></i>
                                </a>
                                <a href="<?= $base ?>/usuarios/<?= (int) $u['id'] ?>/eliminar"
                                   class="btn btn-sm btn-outline-danger" title="Eliminar"
                                   onclick="return confirm('¿Eliminar al usuario <?= htmlspecialchars((string) $u['usuario']) ?>?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-5">No hay usuarios cargados.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modales de edición -->
<?php foreach ($usuarios as $u): ?>
    <?php $rol = (string) $u['rol']; ?>
    <div class="modal fade" id="editar<?= (int) $u['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post" action="<?= $base ?>/usuarios/<?= (int) $u['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Editar usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars((string) $u['nombre']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars((string) $u['usuario']) ?>" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="text" name="password" class="form-control" placeholder="Dejar vacío para no cambiarla" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select">
                            <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Administrador</option>
                            <option value="operador" <?= $rol === 'operador' ? 'selected' : '' ?>>Operario</option>
                            <option value="ventas" <?= $rol === 'ventas' ? 'selected' : '' ?>>Usuario Ventas</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
