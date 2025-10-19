<?php
defined('ABSPATH') || exit;

class Emails_List_Table extends WP_List_Table {

    /**
     * Constructor for the class.
     * Sets up the list table properties.
     */
    public function __construct() {
        parent::__construct([
            'singular' => 'Email', // Singular name of the listed records.
            'plural' => 'Emails', // Plural name of the listed records.
            'ajax' => false, // Does this table support ajax?
        ]);
    }

    /**
     * Defines the columns for our list table.
     *
     * @return array An associative array of column headers.
     */
    public function get_columns() {
        $columns = [
            'created' => 'Created',
            'subject' => 'Subject',
            'to' => 'To',
            'context' => 'Context',
            'filters' => 'Filters'
        ];
        return $columns;
    }

    /**
     * Prepares the data for the list table.
     * This is where you would fetch data from a database, file, or API.
     */
    public function prepare_items() {
        global $wpdb;

        // Define columns and sortable columns (if needed).
        $columns = $this->get_columns();
        $hidden = []; // You can specify columns to hide here.
        $sortable = []; // You can specify sortable columns here.
        $this->_column_headers = [$columns, $hidden, $sortable];

        // This is where you would implement pagination logic.
        $per_page = 20; // Number of items to display per page.
        $current_page = $this->get_pagenum();
        $total_items = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails");

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        // Slice the data for the current page.
        $this->items = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}monitor_emails order by id desc limit %d offset %d",
                        $per_page, ($current_page - 1) * $per_page));
    }

    /**
     * Handles the display of a single column's data.
     * This is the default handler for all columns without a dedicated method.
     *
     * @param \WP_Ability $item        A single item from the data array.
     * @param string $column_name The name of the current column.
     * @return string The content to display for the column.
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'created':
                return esc_html($item->created);
            case 'subject':
                return esc_html($item->subject);
            case 'to':
                return esc_html($item->to);
            case 'context':
                return esc_html($item->context);
            case 'filters':
                $url = admin_url('admin-ajax.php') . '?action=monitor-emails-filters&id=' . rawurlencode($item->id);
                $url = wp_nonce_url($url, 'monitor-emails-filters');
                $url .= '&TB_iframe=true'; // Add as last since Thickbox truncate the URL here
                return '<a class="thickbox" href="' . esc_attr($url) . '">Data</a>';
            default:
                return '?';
        }
    }
}

$table = new Emails_List_Table();
$table->prepare_items();

add_thickbox();
?>
<div class="wrap">
    <h2>Emails</h2>
    <?php include __DIR__ . '/nav.php'; ?>
    <p>
        Consider the plugin WP Mail Logging if you need a serious logging of sent emails.
    </p>

    <?php $table->display(); ?>
</div>