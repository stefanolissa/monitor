<?php
defined('ABSPATH') || exit;
$ability = wp_get_ability($_GET['name']);

if (!$ability) {
    echo 'Ability not found';
    return;
}

//print_r($ability->get_input_schema());

$input_schema = $ability->get_input_schema();
$properties = $input_schema['properties'];

print_r($properties);
?>

<div class="wrap">
    <h2><?php echo esc_html($ability->get_label()); ?></h2>

    <h3>Input</h3>

    <table class="form-table">
        <?php foreach ($properties as $key => $values) { ?>
        <tr>
            <td><?php echo esc_html($key); ?></td>
            <td><?php echo esc_html($values['type'] ?? '?'); ?></td>
            <td>
                <?php
                if (isset($values['enum'])) {
                        echo esc_html(implode(', ', $values['enum']));
                }
                ?>
            </td>
            <td><?php echo esc_html($values['description'] ?? ''); ?></td>

        </tr>
        <?php } ?>
    </table>
</div>
