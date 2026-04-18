<?php

/**
 * Show checkboxes to restrict content when creating a new term.
 *
 * @since 2.10
 */
function pmpro_term_add_form_fields() {
	// Get all membership levels.
	$membership_levels = pmpro_getAllLevels( true, true );
	$membership_levels = pmpro_sort_levels_by_order( $membership_levels );

	// Get all levels for this term.
	// None because we're creating a new term.
	$term_levels = array();

	// Get level groups data.
	$level_groups    = pmpro_get_level_groups_in_order();
	$group_level_map = array();
	foreach ( $level_groups as $group ) {
		$group_level_ids               = pmpro_get_level_ids_for_group( $group->id );
		$group_level_map[ $group->id ] = array_map( 'intval', $group_level_ids );
	}

	// Build the selectors for the #memberships list based on level count.
	$pmpro_memberships_checklist_classes = array( 'pmpro_checkbox_box', 'list:category', 'categorychecklist', 'form-no-clear');
	if ( count( $membership_levels ) > 9 ) {
		$pmpro_memberships_checklist_classes[] = 'pmpro_scrollable';
	}
	$pmpro_memberships_checklist_classes = implode( ' ', array_unique( $pmpro_memberships_checklist_classes ) );

	// Render form field div.
	?>
	<script type="text/javascript">
	var pmpro_group_level_map_term = <?php echo wp_json_encode( $group_level_map ); ?>;
	function pmpro_update_group_checkboxes_term() {
		jQuery('.pmpro-group-checkbox-term').each(function() {
			var groupId   = jQuery(this).data('group-id');
			var levelIds  = pmpro_group_level_map_term[groupId] || [];
			var allChecked = levelIds.length > 0 && levelIds.every(function(levelId) {
				return jQuery('#in-membership-level-' + levelId).is(':checked');
			});
			jQuery(this).prop('checked', allChecked);
		});
	}
	</script>
	<div class="form-field">
		<label><?php esc_html_e( 'Require Membership', 'paid-memberships-pro' ); ?></label>
		<?php if ( count( $membership_levels ) > 1 ) { ?>
			<p><?php esc_html_e( 'Select:', 'paid-memberships-pro' ); ?> <a id="pmpro-memberships-checklist-select-all" href="javascript:void(0);"><?php esc_html_e( 'All', 'paid-memberships-pro' ); ?></a> | <a id="pmpro-memberships-checklist-select-none" href="javascript:void(0);"><?php esc_html_e( 'None', 'paid-memberships-pro' ); ?></a></p>
			<script type="text/javascript">
				jQuery('#pmpro-memberships-checklist-select-all').on('click',function(){
					jQuery('#pmpro-memberships-checklist input').prop('checked', true);
					pmpro_update_group_checkboxes_term();
				});
				jQuery('#pmpro-memberships-checklist-select-none').on('click',function(){
					jQuery('#pmpro-memberships-checklist input').prop('checked', false);
					pmpro_update_group_checkboxes_term();
				});
			</script>
		<?php }

		// Level group checkboxes.
		if ( ! empty( $level_groups ) ) { ?>
			<div class="pmpro-memberships-groups">
				<p><strong><?php esc_html_e( 'By Level Group', 'paid-memberships-pro' ); ?></strong></p>
				<?php foreach ( $level_groups as $group ) { ?>
					<div class="pmpro_clickable">
						<input
							id="pmpro-membership-group-term-<?php echo esc_attr( $group->id ); ?>"
							type="checkbox"
							class="pmpro-group-checkbox-term"
							data-group-id="<?php echo esc_attr( $group->id ); ?>"
						/>
						<label for="pmpro-membership-group-term-<?php echo esc_attr( $group->id ); ?>">
							<?php echo esc_html( $group->name ); ?>
						</label>
					</div>
				<?php } ?>
			</div>
			<script type="text/javascript">
			jQuery(document).on('change', '.pmpro-group-checkbox-term', function() {
				var groupId  = jQuery(this).data('group-id');
				var isChecked = jQuery(this).is(':checked');
				var levelIds  = pmpro_group_level_map_term[groupId] || [];
				jQuery.each(levelIds, function(i, levelId) {
					jQuery('#in-membership-level-' + levelId).prop('checked', isChecked);
				});
			});
			jQuery(document).on('change', '#pmpro-memberships-checklist input[type=checkbox]', function() {
				pmpro_update_group_checkboxes_term();
			});
			</script>
		<?php } ?>
		<div id="pmpro-memberships-checklist" class="<?php echo esc_attr( $pmpro_memberships_checklist_classes ); ?>">
			<input type="hidden" name="pmpro_noncename" id="pmpro_noncename" value="<?php echo esc_attr( wp_create_nonce( plugin_basename(__FILE__) ) )?>" />
			<?php
				$in_member_cat = false;
				foreach ( $membership_levels as $level ) { ?>
					<div id="membership-level-<?php echo esc_attr( $level->id ); ?>" class="pmpro_clickable">
						<input id="in-membership-level-<?php echo esc_attr( $level->id ); ?>" type="checkbox" <?php if ( in_array( $level->id, $term_levels ) ) { ?>checked="checked"<?php } ?> name="pmpro_term_restrictions[]" value="<?php echo esc_attr( $level->id ) ;?>" />
						<label for="in-membership-level-<?php echo esc_attr( $level->id ); ?>"><?php echo esc_html( $level->name ); ?></label>
					</div>
					<?php
				}
			?>
		</div>
	</div>
	<?php
}
add_action( 'category_add_form_fields', 'pmpro_term_add_form_fields' );
add_action( 'post_tag_add_form_fields', 'pmpro_term_add_form_fields' );

/**
 * Show checkboxes to restrict content when editing a term.
 *
 * @since 2.10
 *
 * @param WP_Term $term The term object.
 */
function pmpro_term_edit_form_fields( $term ) {
	global $wpdb;

	// Get all membership levels.
	$membership_levels = pmpro_getAllLevels( true, true );
	$membership_levels = pmpro_sort_levels_by_order( $membership_levels );

	// Get all levels for this term.
	$term_levels = $wpdb->get_col( "SELECT membership_id FROM $wpdb->pmpro_memberships_categories WHERE category_id = '" . intval( $term->term_id ) . "'" );

	// Get level groups data.
	$level_groups    = pmpro_get_level_groups_in_order();
	$group_level_map = array();
	foreach ( $level_groups as $group ) {
		$group_level_ids               = pmpro_get_level_ids_for_group( $group->id );
		$group_level_map[ $group->id ] = array_map( 'intval', $group_level_ids );
	}

	// Build the selectors for the #memberships list based on level count.
	$pmpro_memberships_checklist_classes = array( 'pmpro_checkbox_box', 'list:category', 'categorychecklist', 'form-no-clear');
	if ( count( $membership_levels ) > 9 ) {
		$pmpro_memberships_checklist_classes[] = 'pmpro_scrollable';
	}
	$pmpro_memberships_checklist_classes = implode( ' ', array_unique( $pmpro_memberships_checklist_classes ) );

	// Render table row.
	?>
	<script type="text/javascript">
	var pmpro_group_level_map_term = <?php echo wp_json_encode( $group_level_map ); ?>;
	function pmpro_update_group_checkboxes_term() {
		jQuery('.pmpro-group-checkbox-term').each(function() {
			var groupId   = jQuery(this).data('group-id');
			var levelIds  = pmpro_group_level_map_term[groupId] || [];
			var allChecked = levelIds.length > 0 && levelIds.every(function(levelId) {
				return jQuery('#in-membership-level-' + levelId).is(':checked');
			});
			jQuery(this).prop('checked', allChecked);
		});
	}
	</script>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'Require Membership', 'paid-memberships-pro' ); ?></label></th>
		<td>
			<?php
			// Level group checkboxes.
			if ( ! empty( $level_groups ) ) { ?>
				<p><strong><?php esc_html_e( 'By Level Group', 'paid-memberships-pro' ); ?></strong></p>
				<div class="<?php echo esc_attr( $pmpro_memberships_checklist_classes ); ?>">
					<?php foreach ( $level_groups as $group ) {
						$group_levels  = $group_level_map[ $group->id ];
						$group_checked = ! empty( $group_levels ) && empty( array_diff( $group_levels, array_map( 'intval', $term_levels ) ) );
						?>
						<div class="pmpro_clickable">
							<input
								id="pmpro-membership-group-term-<?php echo esc_attr( $group->id ); ?>"
								type="checkbox"
								class="pmpro-group-checkbox-term"
								data-group-id="<?php echo esc_attr( $group->id ); ?>"
								<?php checked( $group_checked ); ?>
							/>
							<label for="pmpro-membership-group-term-<?php echo esc_attr( $group->id ); ?>">
								<?php echo esc_html( $group->name ); ?>
							</label>
						</div>
					<?php } ?>
				</div>
				<script type="text/javascript">
				jQuery(document).on('change', '.pmpro-group-checkbox-term', function() {
					var groupId  = jQuery(this).data('group-id');
					var isChecked = jQuery(this).is(':checked');
					var levelIds  = pmpro_group_level_map_term[groupId] || [];
					jQuery.each(levelIds, function(i, levelId) {
						jQuery('#in-membership-level-' + levelId).prop('checked', isChecked);
					});
				});
				jQuery(document).on('change', '#pmpro-memberships-checklist input[type=checkbox]', function() {
					pmpro_update_group_checkboxes_term();
				});
				</script>
			<?php } ?>
			<hr />
			<p><strong><?php esc_html_e( 'By Level', 'paid-memberships-pro' ); ?></strong></p>
			<?php if ( count( $membership_levels ) > 1 ) { ?>
				<p><?php esc_html_e( 'Select:', 'paid-memberships-pro' ); ?> <a id="pmpro-memberships-checklist-select-all" href="javascript:void(0);"><?php esc_html_e( 'All', 'paid-memberships-pro' ); ?></a> | <a id="pmpro-memberships-checklist-select-none" href="javascript:void(0);"><?php esc_html_e( 'None', 'paid-memberships-pro' ); ?></a></p>
				<script type="text/javascript">
					jQuery('#pmpro-memberships-checklist-select-all').on('click',function(){
						jQuery('#pmpro-memberships-checklist input').prop('checked', true);
						pmpro_update_group_checkboxes_term();
					});
					jQuery('#pmpro-memberships-checklist-select-none').on('click',function(){
						jQuery('#pmpro-memberships-checklist input').prop('checked', false);
						pmpro_update_group_checkboxes_term();
					});
				</script>
			<?php } ?>
			<div id="pmpro-memberships-checklist" class="<?php echo esc_attr( $pmpro_memberships_checklist_classes ); ?>">
				<input type="hidden" name="pmpro_noncename" id="pmpro_noncename" value="<?php echo esc_attr( wp_create_nonce( plugin_basename(__FILE__) ) )?>" />
				<?php
					$in_member_cat = false;
					foreach ( $membership_levels as $level ) { ?>
						<div id="membership-level-<?php echo esc_attr( $level->id ); ?>" class="pmpro_clickable">
							<input id="in-membership-level-<?php echo esc_attr( $level->id ); ?>" type="checkbox" <?php if ( in_array( $level->id, $term_levels ) ) { ?>checked="checked"<?php } ?> name="pmpro_term_restrictions[]" value="<?php echo esc_attr( $level->id ) ;?>" />
							<label for="in-membership-level-<?php echo esc_attr( $level->id ); ?>"><?php echo esc_html( $level->name ); ?></label>
						</div>
						<?php
					}
				?>
			</div>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'pmpro_term_edit_form_fields', 10, 2 );
add_action( 'post_tag_edit_form_fields', 'pmpro_term_edit_form_fields', 10, 2 );

/**
 * Save checkboxes to restrict categories and tags when saving a term.
 *
 * @since 2.10
 *
 * @param int $term_id The ID of the term being saved.
 */
function pmpro_term_saved( $term_id ) {
	// Check nonce.
	if ( ! isset( $_REQUEST['pmpro_noncename'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['pmpro_noncename'] ), plugin_basename( __FILE__ ) ) ) {
		return;
	}

	// Remove all levels for this term.
	global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->pmpro_memberships_categories WHERE category_id = '" . intval( $term_id ) . "'" );

	// Add the levels that are now checked.
	if ( ! empty( $_REQUEST['pmpro_term_restrictions'] ) ) {
		foreach ( $_REQUEST['pmpro_term_restrictions'] as $level_id ) {
			$wpdb->query( "INSERT INTO $wpdb->pmpro_memberships_categories (membership_id, category_id) VALUES('" . intval( $level_id ) . "', '" . intval( $term_id ) . "')" );
		}
	}
}
add_action( 'saved_category', 'pmpro_term_saved' );
add_action( 'saved_post_tag', 'pmpro_term_saved' );
