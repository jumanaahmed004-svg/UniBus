<?php
/**
 * Left panel used by the login / register pages.
 * Set $sideTitle, $sideText and $sidePoints before including.
 */
?>
<aside class="auth-side">
    <div class="logo-box"><img src="assets/img/logo.jpg" alt="UniBus logo" width="640" height="539"></div>
    <div>
        <h2><?= e($sideTitle) ?></h2>
        <p><?= e($sideText) ?></p>
    </div>
    <ul>
        <?php foreach ($sidePoints as $pt): ?>
            <li><?= icon('check', 'icon icon-sm') ?> <?= e($pt) ?></li>
        <?php endforeach; ?>
    </ul>
</aside>
