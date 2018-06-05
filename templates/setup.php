<?php
/** @var \Gianism\UI\SettingScreen $this */
?>
<h3><img alt="" src="<?php echo hameslack_asset_url() ?>/img/Slack_Mark_Web.png" width="40" height="40" />  Slack</h3>

<p class="description">
	<?php esc_html_e( 'Slack login is connected with your workspace and app.', 'hameslack' ); ?>
</p>

<h4>Step1. <?php esc_html_e( 'Create App' ); ?></h4>

<p><?php echo wp_kses_post( sprintf(
	__( 'Go to <a href="%s" target="_blank">Your Apps</a> in your slack dashboard and create new app. Fill informations below:', 'hameslack' ),
	'https://api.slack.com/apps'
) ); ?></p>

<table class="gianism-example-table">
	<tbody>
	<tr>
		<th>WordSpace</th>
		<td><?php esc_html_e( 'To which workspace you attach this app.', 'hameslack' ) ?></td>
	</tr>
	<tr>
		<th>Scopes</th>
		<td><?php echo wp_kses_post( __( 'At least, <code>identity.basic</code> and <code>identity.email</code> are required.', 'hameslack' ) ) ?></td>
	</tr>
	<tr>
		<th>Redirect URLs</th>
		<td><code><?php echo home_url( '/slack-auth/' ) ?></code></td>
	</tr>
	</tbody>
</table>

<h4>Step2. <?php $this->e( 'Input credentials' ); ?></h4>

<p><?php echo wp_kses_post( sprintf(
	__( 'Now come back to <a href="%1$s">WP admin panel</a>, enter <code>Client ID</code> and <code>Client Secret</code>. You can get Team ID from <a href="%2$s" target="_blank">here</a>.', 'hameslack' ),
	$this->setting_url(),
	'https://api.slack.com/methods/team.info/test'
) ); ?></p>
