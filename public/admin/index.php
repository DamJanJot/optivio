<?php
require_once __DIR__ . '/../../core/access_control.php';

optivio_require_admin();

$allApps = OPTIVIO_APPS;
$notice = '';
$error = '';

$roleTableExists = optivio_table_exists($pdo, 'optivio_roles');
$roleAppTableExists = optivio_table_exists($pdo, 'optivio_role_app_assignments');
$userAppTableExists = optivio_table_exists($pdo, 'optivio_user_app_assignments');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'update_user_role') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $newRole = optivio_normalize_role((string) ($_POST['role'] ?? 'user'));

            if ($userId <= 0) {
                throw new RuntimeException('Nieprawidłowy użytkownik.');
            }

            $stmt = $pdo->prepare('UPDATE uzytkownicy SET rola = ? WHERE id = ?');
            $stmt->execute([$newRole, $userId]);
            $notice = 'Zaktualizowano rolę użytkownika.';
        }

        if ($action === 'update_role_apps') {
            if (!$roleAppTableExists) {
                throw new RuntimeException('Brak tabeli optivio_role_app_assignments. Uruchom SQL migracji.');
            }

            $roleKey = optivio_normalize_role((string) ($_POST['role_key'] ?? ''));
            $apps = array_values(array_intersect(array_keys($allApps), (array) ($_POST['apps'] ?? [])));
            if ($roleKey === '') {
                throw new RuntimeException('Rola nie może być pusta.');
            }

            $pdo->beginTransaction();
            $del = $pdo->prepare('DELETE FROM optivio_role_app_assignments WHERE role_key = ?');
            $del->execute([$roleKey]);

            if (!empty($apps)) {
                $ins = $pdo->prepare('INSERT INTO optivio_role_app_assignments (role_key, app_key) VALUES (?, ?)');
                foreach ($apps as $appKey) {
                    $ins->execute([$roleKey, $appKey]);
                }
            }
            $pdo->commit();
            $notice = 'Zapisano przypisania aplikacji do roli.';
        }

        if ($action === 'update_user_apps') {
            if (!$userAppTableExists) {
                throw new RuntimeException('Brak tabeli optivio_user_app_assignments. Uruchom SQL migracji.');
            }

            $userId = (int) ($_POST['user_id'] ?? 0);
            $apps = array_values(array_intersect(array_keys($allApps), (array) ($_POST['apps'] ?? [])));

            if ($userId <= 0) {
                throw new RuntimeException('Nieprawidłowy użytkownik.');
            }

            $pdo->beginTransaction();
            $del = $pdo->prepare('DELETE FROM optivio_user_app_assignments WHERE user_id = ?');
            $del->execute([$userId]);

            if (!empty($apps)) {
                $ins = $pdo->prepare('INSERT INTO optivio_user_app_assignments (user_id, app_key) VALUES (?, ?)');
                foreach ($apps as $appKey) {
                    $ins->execute([$userId, $appKey]);
                }
            }
            $pdo->commit();
            $notice = 'Zapisano indywidualne dostępy użytkownika.';
        }

        optivio_refresh_access_cache();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}

$roles = [];
if ($roleTableExists) {
    $rolesStmt = $pdo->query('SELECT `key`, `name` FROM optivio_roles ORDER BY is_system DESC, name ASC');
    $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($roles)) {
    $rolesStmt = $pdo->query("SELECT DISTINCT LOWER(TRIM(COALESCE(`rola`, 'user'))) AS role_key FROM uzytkownicy ORDER BY role_key ASC");
    $rawRoles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($rawRoles as $rawRole) {
        $roleKey = optivio_normalize_role((string) $rawRole);
        if ($roleKey === '') {
            continue;
        }
        $roles[] = ['key' => $roleKey, 'name' => ucfirst($roleKey)];
    }
}

if (empty($roles)) {
    $roles[] = ['key' => 'user', 'name' => 'User'];
}

$roleKeys = array_map(static function (array $r): string {
    return (string) $r['key'];
}, $roles);

$usersStmt = $pdo->query("SELECT id, imie, nazwisko, email, COALESCE(`rola`, 'user') AS rola FROM uzytkownicy ORDER BY id DESC");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$roleAssignments = [];
if ($roleAppTableExists) {
    $rows = $pdo->query('SELECT role_key, app_key FROM optivio_role_app_assignments ORDER BY role_key, app_key')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $rk = optivio_normalize_role((string) $row['role_key']);
        $ak = strtolower(trim((string) $row['app_key']));
        $roleAssignments[$rk] ??= [];
        if (!in_array($ak, $roleAssignments[$rk], true)) {
            $roleAssignments[$rk][] = $ak;
        }
    }
}

$userAssignments = [];
if ($userAppTableExists) {
    $rows = $pdo->query('SELECT user_id, app_key FROM optivio_user_app_assignments ORDER BY user_id, app_key')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $uid = (int) $row['user_id'];
        $ak = strtolower(trim((string) $row['app_key']));
        $userAssignments[$uid] ??= [];
        if (!in_array($ak, $userAssignments[$uid], true)) {
            $userAssignments[$uid][] = $ak;
        }
    }
}
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Optivio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background:#0b1220; color:#e2e8f0; font-family:Segoe UI, Tahoma, sans-serif; }
        .card { background:rgba(15, 23, 42, .8); border:1px solid rgba(148, 163, 184, .25); color:#e2e8f0; }
        .app-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:8px; }
        .app-chip { border:1px solid rgba(148,163,184,.35); border-radius:10px; padding:8px 10px; background:rgba(255,255,255,.03); }
        .muted { color:#94a3b8; }
        .table { color:#e2e8f0; }
        .table thead th { color:#cbd5e1; }
        .table td, .table th { border-color:rgba(148,163,184,.2); }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Panel admina - dostęp do aplikacji</h1>
            <div class="muted">Zarządzanie rolami, użytkownikami i dostępami modułów pod CBA.</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-light" href="<?php echo htmlspecialchars(optivio_url('views/nav.php'), ENT_QUOTES, 'UTF-8'); ?>">Panel użytkownika</a>
            <a class="btn btn-danger" href="<?php echo htmlspecialchars(optivio_url('public/logout.php'), ENT_QUOTES, 'UTF-8'); ?>">Wyloguj</a>
        </div>
    </div>

    <?php if ($notice !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!$roleTableExists || !$roleAppTableExists || !$userAppTableExists): ?>
        <div class="alert alert-warning">
            Część tabel RBAC nie istnieje jeszcze w bazie. Uruchom SQL z pliku database/add_access_control_tables.sql.
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card p-3 h-100">
                <h2 class="h5">Przypisania aplikacji do roli</h2>
                <p class="muted mb-3">Użytkownik dostaje te aplikacje na podstawie swojej roli.</p>

                <?php foreach ($roles as $role): ?>
                    <?php $roleKey = (string) $role['key']; ?>
                    <form method="post" class="mb-4">
                        <input type="hidden" name="action" value="update_role_apps">
                        <input type="hidden" name="role_key" value="<?php echo htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8'); ?>">
                        <h3 class="h6 mb-2"><?php echo htmlspecialchars((string) $role['name'], ENT_QUOTES, 'UTF-8'); ?> <span class="muted">(<?php echo htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8'); ?>)</span></h3>
                        <div class="app-grid mb-2">
                            <?php foreach ($allApps as $appKey => $appLabel): ?>
                                <label class="app-chip">
                                    <input type="checkbox" name="apps[]" value="<?php echo htmlspecialchars($appKey, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo in_array($appKey, (array) ($roleAssignments[$roleKey] ?? []), true) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($appLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-sm btn-primary" type="submit">Zapisz dla roli</button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card p-3">
                <h2 class="h5">Użytkownicy</h2>
                <p class="muted">Edycja roli i indywidualnych dostępów per użytkownik.</p>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Użytkownik</th>
                                <th>Rola</th>
                                <th>Dostępy indywidualne</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php $uid = (int) $user['id']; ?>
                            <tr>
                                <td><?php echo $uid; ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars(trim(((string) $user['imie']) . ' ' . ((string) $user['nazwisko'])), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="muted"><?php echo htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td>
                                    <form method="post" class="d-flex gap-2 align-items-center">
                                        <input type="hidden" name="action" value="update_user_role">
                                        <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                                        <select class="form-select form-select-sm" name="role">
                                            <?php foreach ($roleKeys as $rk): ?>
                                                <option value="<?php echo htmlspecialchars($rk, ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?php echo optivio_normalize_role((string) $user['rola']) === $rk ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($rk, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-sm btn-outline-light" type="submit">Zmień</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="action" value="update_user_apps">
                                        <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                                        <div class="app-grid mb-2">
                                            <?php foreach ($allApps as $appKey => $appLabel): ?>
                                                <label class="app-chip">
                                                    <input type="checkbox" name="apps[]" value="<?php echo htmlspecialchars($appKey, ENT_QUOTES, 'UTF-8'); ?>"
                                                        <?php echo in_array($appKey, (array) ($userAssignments[$uid] ?? []), true) ? 'checked' : ''; ?>>
                                                    <?php echo htmlspecialchars($appLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <button class="btn btn-sm btn-outline-info" type="submit">Zapisz user access</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
