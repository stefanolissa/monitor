<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

global $wpdb;

defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    check_admin_referer('monitor-action');
    if (isset($_POST['clear'])) {
        $wpdb->query("truncate {$wpdb->prefix}monitor_rest");
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
            'code' => 'Code',
            'method' => 'Method',
            'route' => 'Route',
            'params' => 'Params',
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
        $total_items = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_rest");

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        $this->items = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}monitor_rest order by id desc limit %d offset %d",
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
            case 'route':
                return esc_html($item->route);
            case 'context':
                return esc_html($item->context);
//            case 'duration':
//                return round($item->duration, 3);
//            case 'text':
//                return esc_html($item->text);
            case 'params':
                $url = admin_url('admin-ajax.php') . '?action=monitor-rest-params&id=' . rawurlencode($item->id);
                $url = wp_nonce_url($url, 'monitor-rest-params');
                $url .= '&TB_iframe=true'; // Add as last since Thickbox truncate the URL here
                return '<a class="thickbox" href="' . esc_attr($url) . '">View</a>';

            default:
                return '?';
        }
    }
}

$table = new Monitor_List_Table();
$table->prepare_items();

add_thickbox();
?>
<style>
<?php include __DIR__ . '/../style.css'; ?>
    .column-duration {
        width: 5rem;
    }
    .column-method {
        width: 4rem;
    }
    .column-code {
        width: 3rem;
    }
    .column-url {
        width: 20rem;
    }
</style>
<div class="wrap">
    <h2><?php esc_html_e('Logs', 'monitor'); ?></h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <form method="post">
        <?php wp_nonce_field('monitor-action'); ?>
        <button name="clear" class="button button-secondary"><?php esc_html_e('Clear', 'monitor'); ?></button>
        <a href="<?php echo esc_attr(get_rest_url()); ?>" class="button button-secondary" target="_blank"><?php esc_html_e('See main endpoint', 'monitor'); ?></a>
    </form>

    <?php $table->display(); ?>

</div>
