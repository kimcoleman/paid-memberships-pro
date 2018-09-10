<?php
/**
 * The Memberships Dashboard admin page for Paid Memberships Pro
 * @since 1.9.4.4
 */

/**
 * Check user capabilities and only allow users that can manage options to view this page.
 */

/**
 * Load the Paid Memberships Pro dashboard-area header
 */
require_once( dirname( __FILE__ ) . '/admin_header.php' ); ?>

<div class="about-wrap">
	<div id="welcome-panel" class="welcome-panel">
		<div class="welcome-panel-content">
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<?php global $pmpro_level_ready, $pmpro_gateway_ready, $pmpro_pages_ready; ?>
					<h3><?php echo esc_attr_e( 'Membership Setup', 'paid-memberships-pro' ); ?></h3>
					<ul>
						<?php if ( current_user_can( 'pmpro_membershiplevels' ) ) { ?>
							<li>
								<?php if ( empty( $pmpro_level_ready ) ) { ?>
									<a href="<?php echo admin_url( 'admin.php?page=pmpro-membershiplevels&edit=-1' );?>"><i class="dashicons dashicons-admin-users"></i> <?php echo esc_attr_e( 'Create a Membership Level', 'paid-memberships-pro' ); ?></a>
								<?php } else { ?>
									<a href="<?php echo admin_url( 'admin.php?page=pmpro-membershiplevels' );?>"><i class="dashicons dashicons-admin-users"></i> <?php echo esc_attr_e( 'View Membership Levels', 'paid-memberships-pro' ); ?></a>
								<?php } ?>
							</li>
						<?php } ?>

						<?php if ( current_user_can( 'pmpro_pagesettings' ) ) { ?>
							<li>
								<?php if ( empty( $pmpro_pages_ready ) ) { ?>
									<a href="<?php echo admin_url( 'admin.php?page=pmpro-pagesettings' );?>"><i class="dashicons dashicons-welcome-add-page"></i> <?php echo esc_attr_e( 'Generate Membership Pages', 'paid-memberships-pro' ); ?></a>
								<?php } else { ?>
									<a href="<?php echo admin_url( 'admin.php?page=pmpro-pagesettings' );?>"><i class="dashicons dashicons-welcome-add-page"></i> <?php echo esc_attr_e( 'Manage Membership Pages', 'paid-memberships-pro' ); ?></a>
								<?php } ?>
							</li>
						<?php } ?>

						<?php if ( current_user_can( 'pmpro_pagesettings' ) ) { ?>
							<li>
								<?php if ( empty( $pmpro_gateway_ready ) ) { ?>
									<a href="<?php echo admin_url( 'admin.php?page=pmpro-paymentsettings' );?>"><i class="dashicons dashicons-cart"></i> <?php echo esc_attr_e( 'Configure Payment Settings', 'paid-memberships-pro' ); ?></a>
								<?php } else { ?>
									<a href="<?php echo admin_url( 'admin.php?page=pmpro-paymentsettings' );?>"><i class="dashicons dashicons-cart"></i> <?php echo esc_attr_e( 'Configure Payment Settings', 'paid-memberships-pro' ); ?></a>
								<?php } ?>
							</li>
						<?php } ?>

						<?php if ( current_user_can( 'pmpro_emailsettings' ) ) { ?>
							<li><a href="<?php echo admin_url( 'admin.php?page=pmpro-emailsettings' );?>"><i class="dashicons dashicons-email"></i> <?php _e( 'Confirm Email Settings', 'paid-memberships-pro' );?></a></li>
						<?php } ?>

						<?php if ( current_user_can( 'pmpro_advancedsettings' ) ) { ?>
							<li><a href="<?php echo admin_url( 'admin.php?page=pmpro-advancedsettings' );?>"><i class="dashicons dashicons-admin-settings"></i> <?php echo esc_attr_e( 'View Advanced Settings', 'paid-memberships-pro' ); ?></a></li>
						<?php } ?>

						<?php if ( current_user_can( 'pmpro_addons' ) ) { ?>
							<li><a href="<?php echo admin_url( 'admin.php?page=pmpro-addons' );?>"><i class="dashicons dashicons-admin-plugins"></i> <?php echo esc_attr_e( 'Explore Add Ons for Additional Features', 'paid-memberships-pro' ); ?></a></li>
						<?php } ?>
					</ul>
					<hr />
					<p>
						<?php echo esc_html( __( 'For guidance as your begin these steps,', 'paid-memberships-pro' ) ); ?>
						<a href="https://www.paidmembershipspro.com/documentation/initial-plugin-setup/?utm_source=plugin&utm_medium=banner&utm_campaign=welcome" target="_blank"><?php echo esc_attr_e( 'view the Initial Setup Video and Docs.', 'paid-memberships-pro' ); ?></a>
					</p>
					<?php /*
					<h3><?php echo esc_attr_e( 'Membership Management', 'paid-memberships-pro' ); ?></h3>
					<ul>
						<?php if(current_user_can('pmpro_memberslist')) { ?>
							<li><a href="<?php echo admin_url('admin.php?page=pmpro-memberslist');?>"><?php _e('Members List', 'paid-memberships-pro' );?></a></li>
						<?php } ?>

						<?php if(current_user_can('pmpro_reports')) { ?>
							<li><a href="<?php echo admin_url('admin.php?page=pmpro-reports');?>"><?php _e('Reports', 'paid-memberships-pro' );?></a></li>
						<?php } ?>

						<?php if(current_user_can('pmpro_orders')) { ?>
							<li><a href="<?php echo admin_url('admin.php?page=pmpro-orders');?>"><?php _e('Orders', 'paid-memberships-pro' );?></a></li>
						<?php } ?>

						<?php if(current_user_can('pmpro_discountcodes')) { ?>
							<li><a href="<?php echo admin_url('admin.php?page=pmpro-discountcodes');?>"><?php _e('Discount Codes', 'paid-memberships-pro' );?></a></li>
						<?php } ?>
					</ul>
					*/ ?>
				</div>
				<div class="welcome-panel-column">
					<h3><?php echo esc_attr_e( 'Support License', 'paid-memberships-pro' ); ?></h3>
					<?php
						// Get saved license.
						$key = get_option( 'pmpro_license_key', '' );
						$pmpro_license_check = get_option( 'pmpro_license_check', array( 'license' => false, 'enddate' => 0 ) );
					?>
					<?php if ( ! pmpro_license_isValid() && empty( $key ) ) { ?>
						<p class="pmpro_message pmpro_error">
							<strong><?php echo esc_html_e( 'No support license key found.', 'paid-memberships-pro' ); ?></strong><br />
							<?php printf(__( '<a href="%s">Enter your key here &raquo;</a>', 'paid-memberships-pro' ), admin_url( 'options-general.php?page=pmpro_license_settings' ) );?>
						</p>
					<?php } elseif ( ! pmpro_license_isValid() ) { ?>
						<p class="pmpro_message pmpro_alert">
							<strong><?php echo esc_html_e( 'Your license is invalid or expired.', 'paid-memberships-pro' ); ?></strong><br />
							<?php printf(__( '<a href="%s">View your membership account</a> to verify your license key.', 'paid-memberships-pro' ), 'https://www.paidmembershipspro.com/login/?redirect_to=/membership-account/?utm_source=plugin&utm_medium=banner&utm_campaign=welcome' );?>
					<?php } else { ?>
						<p class="pmpro_message pmpro_success"><?php printf(__( '<strong>Thank you!</strong> A valid <strong>%s</strong> license key has been used to activate your support license on this site.', 'paid-memberships-pro' ), ucwords($pmpro_license_check['license']));?></p>
					<?php } ?>

					<?php if ( ! pmpro_license_isValid() ) { ?>
						<p><?php esc_html_e( 'An annual support license is recommended for websites running Paid Memberships Pro.', 'paid-memberships-pro' ); ?><br /><a href="http://www.paidmembershipspro.com/pricing/?utm_source=plugin&utm_medium=banner&utm_campaign=welcome" target="_blank"><?php esc_html_e( 'View Pricing &raquo;' , 'paid-memberships-pro' ); ?></a></p>
						<p><a href="https://www.paidmembershipspro.com/membership-checkout/?level=20&utm_source=plugin&utm_medium=banner&utm_campaign=welcome" target="_blank" class="button button-action button-hero"><?php esc_attr_e( 'Upgrade', 'paid-memberships-pro' ); ?></a>
					<?php } ?>
					<hr />
					<p>Paid Memberships Pro and our add ons are distributed under the <a target="_blank" href='http://www.gnu.org/licenses/gpl-2.0.html'>GPLv2 license</a>. This means, among other things, that you may use the software on this site or any other site free of charge.</p>
				</div>
				<div class="welcome-panel-column welcome-panel-last">
					<h3><?php esc_html_e( 'Get Involved', 'paid-memberships-pro' ); ?></h3>
					<p><?php esc_html_e( 'There are many ways you can help support Paid Memberships Pro.', 'paid-memberships-pro' ); ?></p>
					<p><?php esc_html_e( 'Get involved with our plugin development via GitHub.', 'paid-memberships-pro' ); ?> <a href="https://github.com/strangerstudios/paid-memberships-pro" target="_blank"><?php esc_html_e( 'View on GitHub', 'paid-memberships-pro' ); ?></a></p>
					<p><a href="https://twitter.com/pmproplugin" target="_blank"><i class="dashicons dashicons-twitter"></i> <?php esc_html_e( 'Follow @pmproplugin on Twitter.', 'paid-memberships-pro' ); ?></a></p>
					<p><a href="https://wordpress.org/plugins/paid-memberships-pro/#reviews" target="_blank"><i class="dashicons dashicons-wordpress"></i> <?php esc_html_e( 'Share an honest review at WordPress.org.', 'paid-memberships-pro' ); ?></a></p>
					<hr />
					<p><?php esc_html_e( 'Help translate Paid Memberships Pro into your language.', 'paid-memberships-pro' ); ?> <a href="https://translate.wordpress.org/projects/wp-plugins/paid-memberships-pro" target="_blank"><?php esc_html_e( 'Translation Dashboard', 'paid-memberships-pro' ); ?></a></p>
				</div>
			</div>
		</div>
	</div>
<?php /*
	<p>
		<a href=" <?php echo get_admin_url( null, 'admin.php?page=' . AIOSEOP_PLUGIN_DIRNAME . '/aioseop_class.php' ); ?>  "><?php _e( 'Continue to the General Settings', 'paid-memberships-pro' ); ?></a> &raquo;
	</p>
*/ ?>

</div>


<?php
require_once(PMPRO_DIR . "/adminpages/reports.php");

/**
 * Load the Paid Memberships Pro dashboard-area footer
 */
require_once( dirname( __FILE__ ) . '/admin_footer.php' );