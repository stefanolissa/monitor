<?php
global $wpdb;

defined('ABSPATH') || exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            'singular' => 'Activation', // Singular name of the listed records.
            'plural' => 'Activations', // Plural name of the listed records.
            'ajax' => false, // Does this table support ajax?
        ]);
    }

    public function get_columns() {
        $columns = [
            'created' => 'Created',
//            'type' => 'Type',
//            'text' => 'Text',
            'ip' => 'IP',
            'ready_jobs' => 'Ready jobs',
            'executed_jobs' => 'Executed jobs',
            'filters' => 'Filters',
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
//            case 'text':
//                return esc_html($item->text);
//            case 'type':
//                return esc_html($item->type);
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
<style>
    <?php include __DIR__ . '/../style.css'; ?>

    .column-ip {
        width: 5rem;
    }
    .column-filters {
        width: 3rem;
    }
</style>
<div class="wrap">
    <h2>Scheduler logs</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <form method="post">
        <?php wp_nonce_field('monitor-action'); ?>
        <button name="clear" class="button button-secondary">Clear</button>
        <button name="add" class="button button-secondary">Add and execute job</button>

    </form>
    <?php $table->display(); ?>


</div>
