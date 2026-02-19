<?php
defined('ABSPATH') || exit;
$ability = wp_get_ability($_GET['name']);

if (!$ability) {
    echo 'Ability not found';
    return;
}

$input_schema = $ability->get_input_schema();
$properties = $input_schema['properties'];

?>

<div class="wrap">
    <h2><?= esc_html($ability->get_label()); ?></h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <h3>Input Schema</h3>
    <pre><?= esc_html(wp_json_encode($ability->get_input_schema(), JSON_PRETTY_PRINT)) ?></pre>

    <h3>Output Schema</h3>
    <pre><?= esc_html(wp_json_encode($ability->get_output_schema(), JSON_PRETTY_PRINT)) ?></pre>

    <h3>Output Schema</h3>
    <pre><?= esc_html(wp_json_encode($ability->get_meta(), JSON_PRETTY_PRINT)) ?></pre>
    <table class="widefat" style="width: auto;">
        <thead>
            <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Item Type</th>
                <th>Enum</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($properties as $key => $values) { ?>
                <tr>
                    <td><?= esc_html($key); ?></td>
                    <td><?= esc_html($values['type'] ?? '?'); ?></td>
                    <td><?= esc_html($values['type'] === 'array' ? $values['items']['type'] : ''); ?></td>
                    <td>
                        <?php
                        if ($values['type'] === 'array') {
                            $enum = $values['items']['enum'] ?? [];
                        } else {
                            $enum = $values['enum'] ?? [];
                        }
                        echo esc_html(implode(', ', $enum));
                        ?>
                    </td>
                    <td><?= esc_html($values['description'] ?? ''); ?></td>

                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
