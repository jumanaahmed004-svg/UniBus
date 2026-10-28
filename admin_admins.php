<?php
/** Admin: add and remove administrator accounts, and change your own password. */
require_once __DIR__ . '/includes/functions.php';
$me = require_admin();

$errors = [];
$name   = '';

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['admin_id'] ?? 0);
        if ($id === $me['admin_id']) {
            flash('error', 'You cannot remove your own account while logged in.');
        } elseif ((int) q_val('SELECT COUNT(*) FROM admin') <= 1) {
            flash('error', 'There must always be at least one administrator.');
        } else {
            q('DELETE FROM admin WHERE admin_id = ?', [$id]);   // their trips keep existing (admin_id becomes empty)
            flash('success', 'Administrator removed.');
        }
        redirect('admin_admins.php');
    }

    if ($action === 'add' || $action === 'password') {
        $name     = trim($_POST['admin_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if ($action === 'add') {
            if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $name)) {
                $errors[] = 'Username must be 3–30 characters: letters, numbers, dot, dash or underscore.';
            } elseif (q_val('SELECT 1 FROM admin WHERE LOWER(admin_name) = LOWER(?)', [$name])) {
                $errors[] = 'This username is already taken.';
            }
        }
        if ($problem = password_problem($password)) $errors[] = $problem;
        if ($password !== $confirm)                 $errors[] = 'The two passwords do not match.';

        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($action === 'add') {
                q('INSERT INTO admin (admin_name, password) VALUES (?, ?)', [$name, $hash]);
                flash('success', "Administrator “{$name}” was added.");
            } else {
                q('UPDATE admin SET password = ? WHERE admin_id = ?', [$hash, $me['admin_id']]);
                flash('success', 'Your password was changed.');
            }
            redirect('admin_admins.php');
        }
        $formError = $action;
    }
}

$admins = q_all(
    'SELECT a.*, (SELECT COUNT(*) FROM trips t WHERE t.admin_id = a.admin_id) AS trips
       FROM admin a ORDER BY a.admin_name'
);

$pageTitle = 'Administrators';
$activeNav = 'admins';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <div>
            <h1>Administrators</h1>
            <p><?= count($admins) ?> account(s) can manage UniBus.</p>
        </div>
    </div>

    <div class="layout-side">
        <div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Username</th><th>Trips created</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($admins as $a): $isMe = (int) $a['admin_id'] === $me['admin_id']; ?>
                        <tr>
                            <td class="cell-strong"><?= e($a['admin_name']) ?> <?php if ($isMe): ?><span class="badge badge-info">You</span><?php endif; ?></td>
                            <td><?= (int) $a['trips'] ?></td>
                            <td>
                                <?php if (!$isMe && count($admins) > 1): ?>
                                    <form method="post" data-confirm="Remove administrator <?= e($a['admin_name']) ?>?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="admin_id" value="<?= (int) $a['admin_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><?= icon('trash', 'icon icon-sm') ?> Remove</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card mt-3">
                <h2 class="card-title"><?= icon('shield') ?> Change my password</h2>
                <?php if ($errors && ($formError ?? '') === 'password'): ?>
                    <div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?><span><?= implode('<br>', array_map('e', $errors)) ?></span></div>
                <?php endif; ?>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="password">
                    <div class="form-row">
                        <div class="form-group"><label for="np">New password</label><input type="password" id="np" name="password" required></div>
                        <div class="form-group"><label for="nc">Confirm</label><input type="password" id="nc" name="confirm" required></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Change password</button>
                </form>
            </div>
        </div>

        <aside class="card">
            <h2 class="card-title"><?= icon('plus') ?> Add administrator</h2>
            <?php if ($errors && ($formError ?? '') === 'add'): ?>
                <div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?><span><?= implode('<br>', array_map('e', $errors)) ?></span></div>
            <?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="admin_name">Username</label>
                    <input type="text" id="admin_name" name="admin_name" value="<?= e($name) ?>" maxlength="30" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm password</label>
                    <input type="password" id="confirm" name="confirm" required>
                    <span class="hint">At least 8 characters, with uppercase, lowercase, a number and a special character.</span>
                </div>
                <button type="submit" class="btn btn-accent btn-block"><?= icon('plus', 'icon icon-sm') ?> Add administrator</button>
            </form>
        </aside>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
