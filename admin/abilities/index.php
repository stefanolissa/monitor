<?php

defined('ABSPATH') || exit;

class Monitor_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => __('Ability', 'satollo-monitor'),
            'plural' => __('Abilities', 'satollo-monitor'),
            'ajax' => false,
        ]);
    }

    public function get_columns() {
        $columns = [
            'name' => __('Name', 'satollo-monitor'),
            'label' => __('Label', 'satollo-monitor'),
            'category' => __('Category', 'satollo-monitor'),
            'description' => __('Description', 'satollo-monitor'),
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
                return esc_html($item->get_name());
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
<?php include __DIR__ . '/../menu.php'; ?>
<div class="wrap">
    <?php include __DIR__ . '/nav.php'; ?>

    <p>
        Abilities are available since WP 6.9 and a foundation to integrate AI into WP.
    </p>

    <?php $table->display(); ?>
</div>