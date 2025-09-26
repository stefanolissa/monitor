<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {

    add_menu_page(
            'Monitor', 'Monitor', 'administrator', 'monitor',
            function () {
                include __DIR__ . '/index.php';
            },
            'dashicons-performance', 6
    );

    add_submenu_page(
            'monitor', 'Abilities', 'Abilities', 'administrator', 'monitor-abilities',
            function () {
                include __DIR__ . '/abilities/index.php';
            }
    );

    add_submenu_page(
            'monitor', 'Emails', 'Emails', 'administrator', 'monitor-emails',
            function () {
                include __DIR__ . '/emails/index.php';
            }
    );

    add_submenu_page(
            'monitor', 'Scheduler', 'Scheduler', 'administrator', 'monitor-scheduler',
            function () {
                include __DIR__ . '/scheduler/index.php';
            }
    );
});
