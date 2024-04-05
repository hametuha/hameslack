<?php
defined( 'ABSPATH' ) or die();

/** @var \Gianism\UI\SettingScreen $this */
/** @var \Hametuha\HameSlack\Service\Slack $instance */

?>
	<h3><img alt="" src="<?php echo hameslack_asset_url(); ?>/img/Slack_Mark_Web.png" width="40" height="40" /> Slack</h3>
	<table class="form-table">
		<tbody>
		<tr>
			<th>
				<label>
					<?php
					// translators: %s is service name.
					printf( __( 'Connect with %s', 'wp-gianism' ), 'Slack' );
					?>
				</label>
			</th>
			<td>
				<?php $this->switch_button( 'slack_enabled', $this->option->is_enabled( 'slack' ), 1 ); ?>
				<p class="description">
					<?php
					printf(
						// translators: %1$s is service name, %2$s is link to create app.
						__( 'You have to create %1$s App <a target="_blank" href="%2$s">here</a> to get required information.', 'wp-gianism' ),
						'Slack',
						'https://api.slack.com/apps'
					);
					// translators: %1$s is link to setup page, %2$s is link text.
					printf( __( 'See detail at <a href="%1$s">%2$s</a>.', 'wp-gianism' ), $this->setting_url( 'setup' ), __( 'How to set up', 'wp-gianism' ) );
					?>
				</p>
			</td>
		</tr>

		<tr>
			<th><label for="slack_client_id"><?php esc_html_e( 'Client ID', 'wp-gianism' ); ?></label></th>
			<td><input class="regular-text" type="text" name="slack_client_id" id="slack_client_id"
					value="<?php echo esc_attr( $instance->slack_client_id ); ?>"/></td>
		</tr>
		<tr>
			<th><label for="slack_client_secret"><?php esc_html_e( 'Client Secret', 'wp-gianism' ); ?></label></th>
			<td><input class="regular-text" type="text" name="slack_client_secret" id="slack_client_secret"
					value="<?php echo esc_attr( $instance->slack_client_secret ); ?>"/></td>
		</tr>
		<tr>
			<th><label for="slack_team_id"><?php esc_html_e( 'Team ID', 'hameslack' ); ?></label></th>
			<td>
				<input class="regular-text" type="text" name="slack_team_id" id="slack_team_id"
					value="<?php echo esc_attr( $instance->slack_team_id ); ?>"/>
				<p class="description">
					<?php
					echo wp_kses_post( sprintf(
						// translators: %s is link to get team id.
						__( 'Team ID is unique string(e.g. <code>TX43FFAC</code>) for your workspace. You can get it <a href="%s" target="_blank">here</a>.', 'hameslack' ),
						'https://api.slack.com/methods/team.info/test'
					) )
					?>
				</p>
			</td>
		</tr>
		<tr>
			<th><label for="slack_redirect_uri"><?php esc_html_e( 'Redirect URI', 'wp-gianism' ); ?></label></th>
			<td>
				<p class="description">
					<?php
					$end_point = home_url( '/slack-auth/' );
					printf(
						// translators: %1$s is service name, %2$s is redirect URL, %3$s is link to create app, %4$s is URL..
						$this->_( 'Please set %1$s to %2$s on <a target="_blank" href="%4$s">%3$s</a>.' ),
						'Redirect URLs',
						'<code>' . $end_point . '</code>',
						'Slack App',
						'https://api.slack.com/apps'
					);
					?>
					<a class="button" href="<?php echo esc_attr( $end_point ); ?>"
						onclick="window.prompt('<?php esc_html_e( 'Please copy this URL.', 'wp-gianism' ); ?>', this.href); return false;"><?php esc_html_e( 'Copy', 'wp-gianism' ); ?></a>
				</p>
			</td>
		</tr>
		</tbody>
	</table>
<?php submit_button(); ?>
