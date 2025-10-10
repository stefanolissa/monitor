<?php
global $wpdb;

defined('ABSPATH') || exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('monitor-reset');
    // TODO
}

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'Activation', // Singular name of the listed records.
            'plural' => 'Activations', // Plural name of the listed records.
            'ajax' => false, // Does this table support ajax?
        ]);
    }

    public function get_columns() {
        $columns = [
            'created' => 'Created',
            'type' => 'Type',
            'text' => 'Text'
        ];
        return $columns;
    }

    public function prepare_items() {
        global $wpdb;

        // Define columns and sortable columns (if needed).
        $columns = $this->get_columns();
        $hidden = []; // You can specify columns to hide here.
        $sortable = []; // You can specify sortable columns here.
        $this->_column_headers = [$columns, $hidden, $sortable];

        // This is where you would implement pagination logic.
        $per_page = 50; // Number of items to display per page.
        $current_page = $this->get_pagenum();
        $total_items = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_scheduler");

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        // Slice the data for the current page.
        $this->items = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}monitor_scheduler order by id desc limit %d offset %d",
                        $per_page, ($current_page - 1) * $per_page));
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'created':
                return esc_html($item->created);
            case 'text':
                return esc_html($item->text);
            case 'type':
                return esc_html($item->type);
            default:
                return '?';
        }
    }
}

$table = new Monitor_List_Table();
$table->prepare_items();

?>
<div class="wrap">
    <h2>Scheduler logs</h2>
    <p>
        <a href="?page=monitor-scheduler">Overview</a> | <a href="?page=monitor-scheduler&subpage=logs">Logs</a>
    </p>

    <form method="post">
        <?php wp_nonce_field('monitor-reset'); ?>
        <button name="reset">Reset</button>
    </form>

    <?php $table->display(); ?>


</div>
