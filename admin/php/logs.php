<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

global $wpdb;

defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    check_admin_referer('monitor-action');

    if (isset($_POST['clear'])) {
        $wpdb->query("truncate {$wpdb->prefix}monitor_php");
    }
}

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => __('Error', 'satollo-monitor'),
            'plural' => __('Errors', 'satollo-monitor'),
            'ajax' => false,
        ]);
    }

    public function get_columns() {
        $columns = [
            'created' => __('Created', 'satollo-monitor'),
            'context' => __('Context', 'satollo-monitor'),
            'code' => __('Code', 'satollo-monitor'),
            'message' => __('Message', 'satollo-monitor'),
            'file' => __('File', 'satollo-monitor'),
            'line' => __('Line', 'satollo-monitor'),
        ];
        return $columns;
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'created':
                return esc_html($item->created);
            case 'context':
                return esc_html($item->context);
            case 'code':
                return esc_html($item->code);
            case 'message':
                return esc_html($item->message);
            case 'file':
                return esc_html($item->file);
            case 'line':
                return esc_html($item->line);

            default:
                return '?';
        }
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
        $total_items = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_php");

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        $this->items = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}monitor_php order by id desc limit %d offset %d",
                        $per_page, ($current_page - 1) * $per_page));
    }


}

$table = new Monitor_List_Table();
$table->prepare_items();

add_thickbox();
?>
<style>
    .column-errno {
        width: 5rem;
    }
</style>
<?php include __DIR__ . '/../menu.php'; ?>
<div class="wrap">

    <?php include __DIR__ . '/nav.php'; ?>

        <form method="post">
            <?php wp_nonce_field('monitor-action'); ?>
        <button name="clear" class="button button-secondary"><?php esc_html_e('Clear', 'satollo-monitor'); ?></button>
        </form>

    <?php $table->display(); ?>


</div>
