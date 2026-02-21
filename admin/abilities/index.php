<?php
defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- not relevant
$subpage = sanitize_key($_GET['subpage'] ?? '');

switch ($subpage) {
    case 'logs':
        include __DIR__ . '/logs.php';
        return;
    case 'list':
        include __DIR__ . '/list.php';
        return;
    case 'view':
        include __DIR__ . '/view.php';
        return;
}
?>
<div class="wrap">
    <h2><?php esc_html_e('Overview', 'monitor'); ?></h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <p>
        Abilities are available since WP 6.9 and a foundation to integrate AI into WP.
    </p>

</div>