<?php
global $wpdb;

defined('ABSPATH') || exit;

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'Job', // Singular name of the listed records.
            'plural' => 'Jobs', // Plural name of the listed records.
            'ajax' => false, // Does this table support ajax?
        ]);
    }

    public function get_columns() {
        $columns = [
            'hook' => 'Event',
            'timestamp' => 'Timestamp',
            'when' => 'When',
            'functions' => 'Functions',
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

        $crons = _get_cron_array();
        $jobs = [];
        foreach ($crons as $ts => $hooks) {
            foreach ($hooks as $hook => $keys) {
                $jobs[] = ['timestamp' => $ts, 'hook' => $hook];
            }
        }

        $this->set_pagination_args([
            'total_items' => count($jobs),
            'per_page' => $per_page,
        ]);

        // Slice the data for the current page.
        $this->items = array_slice($jobs, ( ( $current_page - 1 ) * $per_page), $per_page);
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'timestamp':
                return esc_html(wp_date('Y-m-d h:i:s', $item['timestamp']));
            case 'hook':
                return esc_html($item['hook']);
            case 'when':
                $delta = $item['timestamp'] - time();

                if ($delta < 0) {
                    return '<span class="red">' . monitor_format_interval(-$delta) . ' ago</span>';
                } else {
                    $seconds = $delta % MINUTE_IN_SECONDS;
                    $minutes = floor($delta % HOUR_IN_SECONDS / MINUTE_IN_SECONDS);
                    $hours = floor($delta % DAY_IN_SECONDS / HOUR_IN_SECONDS);
                    $days = floor($delta / DAY_IN_SECONDS);
                    return monitor_format_interval($delta);
                }
            case 'functions':
                return implode('<br>', monitor_get_hook_functions($item['hook']));

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
    .red {
        color: red;
    }
    .orange {
        color: orange;
    }
</style>
<div class="wrap">
    <h2>Scheduler jobs</h2>
    <?php include __DIR__ . '/nav.php'; ?>


    <?php $table->display(); ?>


</div>
