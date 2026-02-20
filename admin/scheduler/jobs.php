<?php
global $wpdb;

defined('ABSPATH') || exit;

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => __('Job', 'monitor'),
            'plural' => __('Jobs', 'monitor'),
            'ajax' => false,
        ]);
    }

    public function get_columns() {
        $columns = [
            'hook' => __('Event', 'monitor'),
            'timestamp' => __('Timestamp', 'monitor'),
            'when' => __('When', 'monitor'),
            'functions' => __('Functions', 'monitor'),
        ];
        return $columns;
    }

    public function prepare_items() {
        global $wpdb;

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $per_page = 50;
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
                    return '<span style="color: red">' . monitor_format_interval(-$delta) . ' ago</span>';
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
<div class="wrap">
    <h2><?php esc_html_e('Scheduler jobs', 'monitor'); ?></h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <?php $table->display(); ?>

</div>
