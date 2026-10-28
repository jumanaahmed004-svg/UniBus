<?php
/** Admin: add, edit and remove drivers. */
require_once __DIR__ . '/includes/functions.php';
require_admin();

$errors = [];
$editId = (int) ($_GET['edit'] ?? 0);
$form   = ['driver_name' => '', 'phone' => ''];
if ($editId && ($d = q_one('SELECT * FROM drivers WHERE driver_id = ?', [$editId]))) {
    $form = $d;
} else {
    $editId = 0;
}

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id    = (int) ($_POST['driver_id'] ?? 0);
        $trips = (int) q_val('SELECT COUNT(*) FROM trips WHERE driver_id = ?', [$id]);
        q('DELETE FROM drivers WHERE driver_id = ?', [$id]);   // their trips become "Not assigned" (ON DELETE SET NULL)
        flash('success', 'Driver removed.' . ($trips ? " $trips trip(s) now need a new driver." : ''));
        redirect('admin_drivers.php');
    }

    if ($action === 'save') {
        $id   = (int) ($_POST['driver_id'] ?? 0);
        $form = [
            'driver_name' => trim(preg_replace('/\s+/', ' ', $_POST['driver_name'] ?? '')),
            'phone'       => trim($_POST['phone'] ?? ''),
        ];
        if (mb_strlen($form['driver_name']) < 3 || mb_strlen($form['driver_name']) > 60) {
            $errors[] = 'Please enter the driver\'s full name (3–60 characters).';
        }
        if (!preg_match('/^[0-9]{8}$/', $form['phone'])) {
            $errors[] = 'Phone number must be 8 digits.';
        } elseif (q_val('SELECT 1 FROM drivers WHERE phone = ? AND driver_id <> ?', [$form['phone'], $id])) {
            $errors[] = 'Another driver already uses this phone number.';
        }
        if (!$errors) {
            if ($id) {
                q('UPDATE drivers SET driver_name = ?, phone = ? WHERE driver_id = ?', [$form['driver_name'], $form['phone'], $id]);
                flash('success', 'Driver updated.');
            } else {
                q('INSERT INTO drivers (driver_name, phone) VALUES (?, ?)', [$form['driver_name'], $form['phone']]);
                flash('success', $form['driver_name'] . ' was added. They can log in with their name and phone number.');
            }
            redirect('admin_drivers.php');
        }
        $editId = $id;
    }
}

$drivers = q_all(
    'SELECT d.*,
            (SELECT COUNT(*) FROM trips t WHERE t.driver_id = d.driver_id AND t.date >= ?) AS upcoming,
            (SELECT COUNT(*) FROM trips t WHERE t.driver_id = d.driver_id) AS total
       FROM drivers d ORDER BY d.driver_name',
    [date('Y-m-d')]
);

$pageTitle = 'Drivers';
$activeNav = 'drivers';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <div>
            <h1>Drivers</h1>
            <p><?= count($drivers) ?> driver(s). Drivers log in with their full name and phone number.</p>
        </div>
    </div>

    <div class="layout-side">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Phone</th><th>Upcoming trips</th><th>All trips</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($drivers as $d): ?>
                    <tr>
                        <td class="cell-strong"><?= e($d['driver_name']) ?></td>
                        <td><?= e($d['phone']) ?></td>
                        <td><span class="badge <?= $d['upcoming'] ? 'badge-info' : 'badge-neutral' ?>"><?= (int) $d['upcoming'] ?></span></td>
                        <td><?= (int) $d['total'] ?></td>
                        <td>
                            <div class="actions">
                                <a href="?edit=<?= (int) $d['driver_id'] ?>" class="btn btn-ghost btn-sm" title="Edit"><?= icon('edit', 'icon icon-sm') ?></a>
                                <form method="post" data-confirm="Remove <?= e($d['driver_name']) ?>?<?= $d['total'] ? ' Their ' . (int) $d['total'] . ' trip(s) will show “Not assigned”.' : '' ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="driver_id" value="<?= (int) $d['driver_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Remove"><?= icon('trash', 'icon icon-sm') ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$drivers): ?><tr><td colspan="5" class="text-center muted">No drivers yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <aside class="card">
            <h2 class="card-title"><?= icon($editId ? 'edit' : 'plus') ?> <?= $editId ? 'Edit driver' : 'Add new driver' ?></h2>
            <?php if ($errors): ?>
                <div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?><span><?= implode('<br>', array_map('e', $errors)) ?></span></div>
            <?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="driver_id" value="<?= $editId ?>">
                <div class="form-group">
                    <label for="driver_name">Full name</label>
                    <input type="text" id="driver_name" name="driver_name" value="<?= e($form['driver_name']) ?>" placeholder="e.g. Said Al-Shukaili" maxlength="60" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone number</label>
                    <input type="tel" id="phone" name="phone" value="<?= e($form['phone']) ?>" placeholder="9XXXXXXX" maxlength="8" required>
                    <span class="hint">Used by the driver to log in.</span>
                </div>
                <button type="submit" class="btn btn-accent btn-block"><?= icon('check', 'icon icon-sm') ?> <?= $editId ? 'Save changes' : 'Add driver' ?></button>
                <?php if ($editId): ?><a href="admin_drivers.php" class="btn btn-ghost btn-block mt-1">Cancel</a><?php endif; ?>
            </form>
        </aside>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
