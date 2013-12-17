<?php
/*
	Plugin Name: Remove Administrators
	Description: Allows admins to hide the admin role from all other roles
	Author: Daniel J Griffiths
	Author URI: http://www.ghost1227.com
	Version: 1.0
*/

// Enqueue jQuery
add_action('admin_enqueue_scripts', 'ghost_ra_jquery');
function ghost_ra_jquery() {
	global $pagenow;
	('users.php' == $pagenow) && wp_enqueue_script('jquery');
}

// Remove admins from editable roles
add_action('editable_roles', 'ghost_ra_editable_roles');
function ghost_ra_editable_roles($roles) {
	if(isset($roles['administrator']) && !current_user_can('update_core')) {
		unset($roles['administrator']);
	}
	return $roles;
}

// Hide admins from user list
add_action('admin_head', 'ghost_ra_users');
function ghost_ra_users() {
	if(!current_user_can('update_core')) { ?>
		<script type='text/javascript'>
			jQuery(document).ready(function() {
				var admin_count;
				var total_count;

				jQuery(".subsubsub > li > a:contains(Administrator)").each(function() {
					admin_count = jQuery(this).children('.count').text();
					admin_count = admin_count.substring(1, admin_count.length - 1);
				});
				jQuery(".subsubsub > li > a:contains(Administrator)").parent().remove();
				jQuery(".subsubsub > li > a:contains(All)").each(function() {
					total_count = jQuery(this).children('.count').text();
					total_count = total_count.substring(1, total_count.length - 1) - admin_count;
					jQuery(this).children('.count').text('('+total_count+')');
				});
				jQuery("#the-list > tr > td:contains(Administrator)").parent().remove();
			});
		</script>
	<?php }
}
