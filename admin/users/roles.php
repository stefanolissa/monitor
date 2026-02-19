<?php
defined('ABSPATH') || exit;

add_thickbox();
?>
<div class="wrap">
    <h2>Roles</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th>Name</th>
                <th>Key</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (wp_roles()->roles as $key => $wp_role) { ?>
                <tr>
                    <td><?php echo esc_html($wp_role['name']) ?></td>
                    <td><?php echo esc_html($key); ?></td>
                    <td>
                        <?php
                        $url = admin_url('admin-ajax.php') . '?action=monitor-users-role&id=' . rawurlencode($key);
                        $url = wp_nonce_url($url, 'monitor-users-role');
                        $url .= '&TB_iframe=true'; // Add as last since Thickbox truncate the URL here
                        echo '<a class="thickbox" href="' . esc_attr($url) . '">View</a>';
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>
