<?php
global $wpdb;

defined('ABSPATH') || exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('monitor-action');
    if (WP_DEBUG) {
        if (isset($_POST['error'])) {
            wp_remote_get('https://www.sato llo.net/');
        } elseif (isset($_POST['notfound'])) {
            wp_remote_get('https://www.satollo.net/not-existing');
        }
    }
}

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'Request',
            'plural' => 'Requests',
            'ajax' => false,
        ]);
    }

    public function get_columns() {
        $columns = [
            'created' => 'Created',
            'method' => 'Method',
            'code' => 'Code',
            'url' => 'URL',
            'duration' => 'Duration (ms)',
            'text' => 'Note',
            'context' => 'Context',
        ];
        return $columns;
    }

    public function prepare_items() {
        global $wpdb;

        // Define columns and sortable columns (if needed).
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $per_page = 50;
        $current_page = $this->get_pagenum();
        $total_items = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_http");

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        $this->items = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}monitor_http order by id desc limit %d offset %d",
                        $per_page, ($current_page - 1) * $per_page));
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'created':
                return esc_html($item->created);
            case 'method':
                return esc_html($item->method);
            case 'code':
                return esc_html($item->code);
            case 'url':
                return esc_html($item->url);
            case 'context':
                return esc_html($item->context);
            case 'duration':
                return round($item->duration, 3) . ' seconds';
            case 'text':
                return esc_html($item->text);
            case 'filters':
                if ($item->type === 'start') {
                    $url = admin_url('admin-ajax.php') . '?action=monitor-http-filters&id=' . rawurlencode($item->id);
                    $url = wp_nonce_url($url, 'monitor-http-filters');
                    $url .= '&TB_iframe=true'; // Add as last since Thickbox truncate the URL here
                    return '<a class="thickbox" href="' . esc_attr($url) . '">Open</a>';
                } else {
                    return '';
                }
            default:
                return '?';
        }
    }
}

$table = new Monitor_List_Table();
$table->prepare_items();

add_thickbox();
?>
<div class="wrap">
    <h2>HTTP Logs</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <?php if (WP_DEBUG) { ?>
        <form method="post">
            <?php wp_nonce_field('monitor-action'); ?>
            <button name="error" class="button button-secondary">Simulate bad URL</button>
            <button name="notfound" class="button button-secondary">Simulate not found</button>
        </form>
    <?php } ?>

    <?php $table->display(); ?>


</div>
