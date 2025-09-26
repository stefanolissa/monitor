<?php
defined('ABSPATH') || exit;
class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'Ability calls', // Singular name of the listed records.
            'plural' => 'Ability call', // Plural name of the listed records.
            'ajax' => false, // Does this table support ajax?
        ]);
    }

    public function get_columns() {
        $columns = [
            'created' => 'Created',
            'name' => 'Name',
            'context' => 'Context',
            'method' => 'Method',
        ];
        return $columns;
    }

    public function prepare_items() {
        global $wpdb;

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = ['created'];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = (int)$wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_abilities");

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        // Slice the data for the current page.
        $this->items = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}monitor_abilities order by id desc limit %d offset %d",
                $per_page, ($current_page-1)*$per_page));
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'created':
                return esc_html($item->created);
            case 'name':
                return esc_html($item->name);
            case 'context':
                return esc_html($item->context);
            default:
                return '?';
        }
    }
}

$table = new Monitor_List_Table();
$table->prepare_items();
?>
<div class="wrap">
    <h2>Ability calls</h2>

    <?php $table->display(); ?>
</div>