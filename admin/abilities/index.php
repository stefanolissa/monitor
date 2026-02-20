<?php

defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- not relevant
$subpage = sanitize_key($_GET['subpage'] ?? '');

switch ($subpage) {
    case 'logs':
        include __DIR__ . '/logs.php';
        return;
    case 'view':
        include __DIR__ . '/view.php';
        return;
}

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'Ability',
            'plural' => 'Abilities',
            'ajax' => false,
        ]);
    }

    public function get_columns() {
        $columns = [
            'name' => 'Name',
            'label' => 'Label',
            'category' => 'Category',
            'description' => 'Description',
        ];
        return $columns;
    }

    public function prepare_items() {
        if (!function_exists('wp_get_abilities')) {
            $this->items = [];
            return;
        }

        $abilities = wp_get_abilities();

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $per_page = 200;
        $current_page = $this->get_pagenum();
        $total_items = count($abilities);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        $this->items = array_slice($abilities, (($current_page - 1) * $per_page), $per_page);
    }

    /**
     * @param \WP_Ability $item
     * @param string $column_name
     * @return string
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'name':
                return '<a href="?page=monitor_abilities&subpage=view&name=' . rawurlencode($item->get_name()) . '">' . esc_html($item->get_name()) . '</a>';
            case 'description':
                return esc_html($item->get_description());
            case 'label':
                return esc_html($item->get_label());
            case 'category':
                return esc_html($item->get_category());
            default:
                return '?';
        }
    }
}

$table = new Monitor_List_Table();
$table->prepare_items();
?>
<div class="wrap">
    <h2><?php esc_html_e('Abilities', 'monitor'); ?></h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <p>
        Abilities are available since WP 6.9 and a foundation to integrate AI into WP.
    </p>

    <?php $table->display(); ?>
</div>