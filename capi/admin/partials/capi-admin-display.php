<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Capi
 * @subpackage Capi/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">

    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <form method="post" name="capi_options" action="options.php">

	    <?php settings_fields('capi_fields'); ?>
	    <?php do_settings_sections('capi_fields'); ?>


      <?php submit_button('Save all changes', 'primary','submit', TRUE); ?>

    </form>

</div>
