<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('monitor-save');

    // TODO: cleanup the options
    update_option('monitor', $_POST['data']);
}

$data = get_option('monitor', []);

?>
<div class="wrap">
    <h2>Monitor</h2>

    <p>The monitor main page. Nothing here works.</p>

    <form method="post">
        <?php wp_nonce_field('monitor-save'); ?>
        <table class="form-table" role=""presentation">
            <tr>
                <th>
                    Monitor emails
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[emails]" <?php echo isset($data['emails'])?'checked':''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Monitor abilities
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[abilities]" <?php echo isset($data['abilities'])?'checked':''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Monitor scheduler
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[scheduler]" <?php echo isset($data['scheduler'])?'checked':''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Keep logs for
                </th>
                <td>
                    <select name="data[days]">
                        <option value="30">30 days</option>
                        <option value="30">60 days</option>
                        <option value="30">90 days</option>
                    </select>
                </td>
            </tr>
        </table>
        <button>Save</button>
    </form>
    <pre><?php echo esc_html(print_r($data, true)); ?></pre>
</div>
