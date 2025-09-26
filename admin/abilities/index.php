<?php
defined('ABSPATH') || exit;
$subpage = $_GET['subpage'] ?? '';

switch ($subpage) {
    case 'logs':
        include __DIR__ . '/logs.php';
        return;
    case 'view':
        include __DIR__ . '/view.php';
        return;
}

class Abilities_List_Table extends WP_List_Table {

    /**
     * Constructor for the class.
     * Sets up the list table properties.
     */
    public function __construct() {
        parent::__construct([
            'singular' => 'Ability', // Singular name of the listed records.
            'plural' => 'Abilities', // Plural name of the listed records.
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
            'name' => 'Name',
            'label' => 'Label',
            'description' => 'Description',
        ];
        return $columns;
    }

    /**
     * Prepares the data for the list table.
     * This is where you would fetch data from a database, file, or API.
     */
    public function prepare_items() {
        if (!function_exists('wp_get_abilities')) {
            $this->items = [];
            return;
        }

        $abilities = wp_get_abilities();

        // Define columns and sortable columns (if needed).
        $columns = $this->get_columns();
        $hidden = []; // You can specify columns to hide here.
        $sortable = []; // You can specify sortable columns here.
        $this->_column_headers = [$columns, $hidden, $sortable];

        // This is where you would implement pagination logic.
        $per_page = 2; // Number of items to display per page.
        $current_page = $this->get_pagenum();
        $total_items = count($abilities);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        // Slice the data for the current page.
        $this->items = array_slice($abilities, (($current_page - 1) * $per_page), $per_page);
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
            case 'name':
                return '<a href="?page=monitor-abilities&subpage=view&name=' . rawurlencode($item->get_name()) . '">' . esc_html($item->get_name()) . '</a>';
            case 'description':
                return esc_html($item->get_description());
            case 'label':
                return esc_html($item->get_label());
            default:
                return '?';
        }
    }
}

$table = new Abilities_List_Table();
$table->prepare_items();
?>
<div class="wrap">
    <h2>Abilities</h2>
    <p>
        <a href="?page=monitor-abilities">List</a> | <a href="?page=monitor-abilities&subpage=logs">Logs</a>
    </p>

    <?php $table->display(); ?>
</div>