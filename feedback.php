<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_user();

$error = '';
if (is_post()) {
    csrf_check();
    $message = trim($_POST['message'] ?? '');
    if (mb_strlen($message) < 3) {
        $error = 'Please write a little more.';
    } elseif (mb_strlen($message) > 1000) {
        $error = 'Feedback must be under 1000 characters.';
    } else {
        q('INSERT INTO feedback (user_id, message, feedback_date) VALUES (?, ?, ?)', [$user['user_id'], $message, date('Y-m-d H:i:s')]);
        flash('success', 'Thank you! Your feedback has been submitted.');
        redirect('feedback.php');
    }
}

$mine = q_all('SELECT * FROM feedback WHERE user_id = ? ORDER BY feedback_date DESC', [$user['user_id']]);

$pageTitle = 'Feedback';
$activeNav = 'feedback';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="layout-side">
        <div class="card">
            <span class="eyebrow">We are listening</span>
            <h1>Send us your feedback</h1>
            <p class="muted">Suggestions, problems with a trip or a driver, or ideas for new routes. Everything helps.</p>

            <?php if ($error): ?><div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?> <?= e($error) ?></div><?php endif; ?>

            <form method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="message">Your message</label>
                    <textarea id="message" name="message" maxlength="1000" placeholder="Write your feedback here..." required></textarea>
                </div>
                <button class="btn btn-primary btn-lg" type="submit"><?= icon('message', 'icon icon-sm') ?> Submit feedback</button>
            </form>
        </div>

        <aside>
            <h2>Your previous feedback</h2>
            <?php if (!$mine): ?>
                <div class="card empty"><p class="mb-0">You haven't sent any feedback yet.</p></div>
            <?php endif; ?>
            <?php foreach ($mine as $f): ?>
                <div class="card">
                    <span class="muted small"><?= e(fmt_date($f['feedback_date'])) ?></span>
                    <p class="mb-0"><?= nl2br(e($f['message'])) ?></p>
                </div>
            <?php endforeach; ?>
        </aside>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
