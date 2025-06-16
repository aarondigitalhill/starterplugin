<?php

echo '<div class="wrap">';
echo '<h1 class="wp-heading-inline">Leads Settings</h1>';
echo '<form method="POST" action="options.php">';
settings_fields('dhwp_starter_settings');
do_settings_sections('dhwp_starter_settings');
submit_button();
echo '</form>';
echo DevLog::display();
echo '</div>';