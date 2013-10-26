<?php

/*
 * Options Page
 * Actual WPRPG Options Page
 */
function wp_rpg_options() {
    echo 'Test';
    echo '<br />Last Cron: ' . date('Y-m-d:H:i:s', get_option('WPRPG_last_cron'));
    echo '<br />Number of 30mins since then: ' . time_elapsed(get_option('WPRPG_last_cron'));
    echo '<br />Next Cron: ' . date('Y-m-d:H:i:s', get_option('WPRPG_next_cron'));
}

?>