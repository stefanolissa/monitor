<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

global $wpdb;

defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    check_admin_referer('monitor-action');

    if (isset($_POST['test'])) {
        wp_mail(get_option('admin_email'), 'Email from Monitor plugin', 'Hi, this message confirms the emails are correctly delivered');
    }
    if (isset($_POST['test_bad'])) {
        wp_mail('wrong address', 'Email from Monitor plugin', 'Hi, this message confirms the emails are correctly delivered');
    }
    if (isset($_POST['clear'])) {
        $wpdb->query("truncate {$wpdb->prefix}monitor_emails");
    }
}

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'Email',
            'plural' => 'Emails',
            'ajax' => false,
        ]);
    }

    public function get_columns() {
        $columns = [
            'created' => 'Created',
            'status' => 'Status',
            'subject' => 'Subject',
            'to' => 'To',
            'context' => 'Context',
            'duration' => 'Duration (s)',
            'text' => 'Note',
            'filters' => 'Filters'
        ];
        return $columns;
    }

    public function prepare_items() {
        global $wpdb;

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $per_page = 30;
        $current_page = $this->get_pagenum();
        $total_items = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails");

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        $this->items = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}monitor_emails order by id desc limit %d offset %d",
                        $per_page, ($current_page - 1) * $per_page));
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'created':
                return esc_html($item->created);
            case 'status':
                return $item->status == 0 ? '<span class="green">✔</span>' : '<span class="red">✖</span>';
            case 'subject':
                return esc_html($item->subject);
            case 'to':
                return esc_html($item->to);
            case 'context':
                return esc_html($item->context);
            case 'duration':
                return round($item->duration, 3);
            case 'text':
                return esc_html($item->text);
            case 'filters':
                $url = admin_url('admin-ajax.php') . '?action=monitor-emails-filters&id=' . rawurlencode($item->id);
                $url = wp_nonce_url($url, 'monitor-emails-filters');
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
    .column-status {
        text-align: center;
        width: 3rem;
    }
</style>
<div class="wrap" id="monitor-emails">
    <h2>Emails</h2>
    <?php include __DIR__ . '/nav.php'; ?>
    <p>
        Consider the plugin WP Mail Logging if you need a serious logging of sent emails.
    </p>

    <?php if (true || WP_DEBUG) { ?>
        <form method="post">
            <?php wp_nonce_field('monitor-action'); ?>
            <button name="test" class="button button-secondary">Send test email</button>
            <button name="test_bad" class="button button-secondary">Test bad address</button>
            <button name="clear" class="button button-secondary">Clear</button>
        </form>
    <?php } ?>

    <?php $table->display(); ?>
</div>