<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

if (is_post()) {
    csrf_check();
    q('DELETE FROM feedback WHERE feedback_id = ?', [(int) ($_POST['feedback_id'] ?? 0)]);
    flash('success', 'Feedback removed.');
    redirect('admin_feedback.php');
}

$rows = q_all(
    'SELECT f.*, u.username, u.email FROM feedback f JOIN users u ON u.user_id = f.user_id
      ORDER BY f.feedback_date DESC'
);

$pageTitle = 'Feedback';
$activeNav = 'feedback';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-sm">
    <div class="page-head">
        <div>
            <h1>User feedback</h1>
            <p><?= count($rows) ?> message(s) from students and staff.</p>
        </div>
    </div>

    <?php if (!$rows): ?>
        <div class="card empty">
            <div class="feature-icon"><?= icon('message', 'icon icon-lg') ?></div>
            <p>No feedback yet.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($rows as $f): ?>
        <div class="card feedback-item">
            <div class="avatar"><?= e(mb_strtoupper(mb_substr($f['username'], 0, 1))) ?></div>
            <div style="flex:1; min-width:0">
                <strong><?= e($f['username']) ?></strong>
                <span class="muted small"> · <?= e($f['email']) ?> · <?= e(fmt_date($f['feedback_date'])) ?></span>
                <p><?= nl2br(e($f['message'])) ?></p>
            </div>
            <form method="post" data-confirm="Remove this feedback?">
                <?= csrf_field() ?>
                <input type="hidden" name="feedback_id" value="<?= (int) $f['feedback_id'] ?>">
                <button class="btn btn-ghost btn-sm" type="submit" title="Remove"><?= icon('trash', 'icon icon-sm') ?></button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
