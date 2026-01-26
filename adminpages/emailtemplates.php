<?php
// Only admins can get to this screen.
if ( ! function_exists( 'current_user_can' ) || ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'pmpro_emailsettings' ) ) ) {
	die (esc_html__( 'You do not have permissions to perform this action.', 'paid-memberships-pro' ) );
}

require_once(dirname(__FILE__) . "/admin_header.php");

global $wpdb, $msg, $msgt, $pmpro_email_templates_defaults, $current_user;

// Set the template based on the request or post value, if set.
$edit = isset( $_REQUEST['edit'] ) ? $_REQUEST['edit'] : ( isset( $_POST['edit'] ) ? $_POST['edit'] : null );
$template = isset( $pmpro_email_templates_defaults[ $edit ] ) ? $pmpro_email_templates_defaults[ $edit ] : null;

// Do we have a template to edit? If so, show the edit screen.
if ( ! empty( $template ) ) {
	require_once( PMPRO_DIR . '/adminpages/emailtemplates-edit.php' );
} else {
	// Showing the email templates list.
	?>
	<hr class="wp-header-end">
	<h1><?php esc_html_e( 'Edit Email Templates', 'paid-memberships-pro' ); ?></h1>
	<p><?php esc_html_e( 'Select an email template to customize the subject and body of emails sent through your membership site. You can also disable a specific email or send a test version through this admin page.', 'paid-memberships-pro' ); ?> <a href="https://www.paidmembershipspro.com/documentation/member-communications/list-of-pmpro-email-templates/" target="_blank"><?php esc_html_e( 'Click here for a description of each email sent to your members and admins at different stages of the member experience.', 'paid-memberships-pro'); ?></a></p>
	<?php
		/**
		 * Filter the columns displayed in the email templates list table.
		 *
		 * @since 3.5
		 *
		 * @param array $columns The columns to display. Keys are column IDs, values are column headers.
		 */
		$columns = apply_filters( 'pmpro_emailtemplates_list_columns', array(
			'name'      => esc_html__( 'Email Template Name', 'paid-memberships-pro' ),
			'recipient' => esc_html__( 'Default Recipient', 'paid-memberships-pro' ),
			'subject'   => esc_html__( 'Subject', 'paid-memberships-pro' ),
			'status'    => esc_html__( 'Status', 'paid-memberships-pro' ),
		) );
	?>
	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<?php foreach ( $columns as $column_key => $column_header ) : ?>
					<th scope="<?php echo $column_key === 'name' ? 'row' : 'col'; ?>">
						<?php echo esc_html( $column_header ); ?>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
		<?php
			/**
			 * Filter to show the "default" email template in the dropdown.
			 *
			 * @since 3.1
			 *
			 * @param bool $show_default_email_template Whether to show the default email template in the dropdown.
			 */
			$show_default_email_template = apply_filters( 'pmpro_show_default_email_template_in_dropdown', false );

			// Alphabetize the email templates by description.
			uasort( $pmpro_email_templates_defaults, function( $a, $b ) {
				return strcasecmp( $a['description'], $b['description'] );
			} );

			// Move the default, header, and footer email templates to the bottom of the list.
			$pmpro_email_templates_defaults = array_merge(
				array_filter(
					$pmpro_email_templates_defaults,
					function( $key ) {
						return ! in_array( $key, [ 'default', 'header', 'footer' ], true );
					},
					ARRAY_FILTER_USE_KEY
				),
				array_filter(
					$pmpro_email_templates_defaults,
					function( $key ) {
						return in_array( $key, [ 'default', 'header', 'footer' ], true );
					},
					ARRAY_FILTER_USE_KEY
				)
			);

			foreach ( $pmpro_email_templates_defaults as $key => $template ) {
				// If the template is the default template and we're not showing it in the dropdown, skip it.
				if ( 'default' === $key && ! $show_default_email_template ) {
					continue;
				}
				?>
				<tr>
					<?php foreach ( $columns as $column_key => $column_header ) : ?>
						<?php if ( $column_key === 'name' ) : ?>
							<td class="has-row-actions" data-colname="<?php echo esc_attr( $column_header ); ?>">
								<strong><a href="<?php echo esc_url( add_query_arg( [ 'page' => 'pmpro-emailtemplates', 'edit' => $key ] ), admin_url( 'admin.php' ) ); ?>"><?php echo esc_html( $template['description'] ); ?></a></strong>
								<div class="row-actions">
								<?php
									$actions = [
										'edit'   => sprintf(
											'<a title="%1$s" href="%2$s">%3$s</a>',
											esc_attr__( 'Edit', 'paid-memberships-pro' ),
											esc_url(
												add_query_arg(
													[
														'page' => 'pmpro-emailtemplates',
														'edit' => $key,
													],
													admin_url( 'admin.php' )
												)
											),
											esc_html__( 'Edit', 'paid-memberships-pro' )
										),
									];

									/**
									 * Filter the extra actions for this template.
									 *
									 * @since 3.2
									 *
									 * @param array  $actions The list of actions.
									 * @param object $template   The email template data.
									 */
									$actions = apply_filters( 'pmpro_emailtemplates_row_actions', $actions, $template );

									$actions_html = [];

									foreach ( $actions as $action => $link ) {
										$actions_html[] = sprintf(
											'<span class="%1$s">%2$s</span>',
											esc_attr( $action ),
											$link
										);
									}

									if ( ! empty( $actions_html ) ) {
										echo implode( ' | ', $actions_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
								</div>
							</td>
						<?php elseif ( $column_key === 'recipient' ) : ?>
							<td data-colname="<?php echo esc_attr( $column_header ); ?>">
								<?php
									// If the email has _admin in $key, it's an admin email.
									// If the email is default, header, or footer, show a dash.
									if ( strpos( $key, '_admin' ) !== false ) {
										echo esc_html__( 'Admin', 'paid-memberships-pro' );
									} elseif ( in_array( $key, [ 'default', 'header', 'footer' ], true ) ) {
										echo esc_html__( '&#8212;', 'paid-memberships-pro' );
									} else {
										echo esc_html__( 'Member', 'paid-memberships-pro' );
									}
								?>
							</td>
						<?php elseif ( $column_key === 'subject' ) : ?>
							<td data-colname="<?php echo esc_attr( $column_header ); ?>">
								<?php
									$subject = get_option( 'pmpro_email_' . $key . '_subject', $template['subject'] );
									echo ! empty( $subject ) ? esc_html( $subject ) : esc_html__( '&#8212;', 'paid-memberships-pro' );
								?>
							</td>
						<?php elseif ( $column_key === 'status' ) : ?>
							<td data-colname="<?php echo esc_attr( $column_header ); ?>">
								<?php
									if ( filter_var( get_option( 'pmpro_email_' . $key . '_disabled' ), FILTER_VALIDATE_BOOLEAN ) ) {
										echo '<span class="pmpro_tag pmpro_tag-alert">' . esc_html__( 'Disabled', 'paid-memberships-pro' ) . '</span>';
									} else {
										echo '<span class="pmpro_tag pmpro_tag-success">' . esc_html__( 'Enabled', 'paid-memberships-pro' ) . '</span>';
									}
								?>
							</td>
						<?php else : ?>
							<td data-colname="<?php echo esc_attr( $column_header ); ?>">
								<?php
									/**
									 * Action to output custom column values in the email templates list table.
									 *
									 * @since 3.5
									 *
									 * @param string $column_key   The column key.
									 * @param string $key          The template slug.
									 * @param array  $template     The template data.
									 */
									do_action( 'pmpro_emailtemplates_list_column_value', $column_key, $key, $template );
								?>
							</td>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
				<?php
			}
		?>
		</tbody>
	</table>
	<?php
}

require_once(dirname(__FILE__) . "/admin_footer.php");
