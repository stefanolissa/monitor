<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

global $wpdb;

defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    check_admin_referer('monitor-action');
    if (isset($_POST['clear'])) {
        $wpdb->query("truncate {$wpdb->prefix}monitor_scheduler");
    }

    if (isset($_POST['add'])) {
        wp_schedule_single_event(time(), 'monitor_scheduler_test');
        delete_transient('doing_cron');
        spawn_cron();
        sleep(5);
    }
}

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => __('Activation', 'monitor'),
            'plural' => __('Activations', 'monitor'),
            'ajax' => false,
        ]);
    }

    public function get_columns() {
        $columns = [
            'created' => 'Created',
            'ip' => 'IP',
            'ready_jobs' => 'Ready jobs',
            'executed_jobs' => 'Executed jobs',
            'filters' => 'Filters',
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
        $total_items = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_scheduler");

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        $this->items = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}monitor_scheduler order by id desc limit %d offset %d",
                        $per_page, ($current_page - 1) * $per_page));
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'created':
                return esc_html($item->created);
            case 'ip':
                return esc_html($item->ip);
            case 'ready_jobs':
                $b = '<ol>';
                foreach (unserialize($item->ready_jobs) as $job) {
                    $b .= '<li>' . esc_html($job['hook']) . '</li>';
                }
                $b .= '</ol>';
                return $b;
            case 'executed_jobs':
                $b = '<ol>';
                foreach (unserialize($item->executed_jobs) as $job) {
                    $b .= '<li>' . esc_html($job['hook']) . ' (' . esc_html($job['delay']) . ' seconds delay)</li>';
                }
                $b .= '</ol>';
                return $b;
            case 'filters':

                    $url = admin_url('admin-ajax.php') . '?action=monitor-scheduler-filters&id=' . rawurlencode($item->id);
                    $url = wp_nonce_url($url, 'monitor-scheduler-filters');
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
<div class="wrap">
    <h2><?php esc_html_e('Logs', 'monitor'); ?></h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <form method="post">
        <?php wp_nonce_field('monitor-action'); ?>
        <button name="clear" class="button button-secondary"><?php esc_html_e('Clear', 'monitor'); ?></button>
        <button name="add" class="button button-secondary"><?php esc_html_e('Add and execute a job', 'monitor'); ?></button>

    </form>
    <?php $table->display(); ?>


</div>
