<?php
/**
 * Command class
 */
if ( ! defined( 'WP_CLI' ) ) {
	return;
}

/**
 * Command Class for HameSlack.
 *
 * @package hameslack
 * @since 1.0.0
 */
class HameSlackCommand extends WP_CLI_Command {

	/**
	 * Show channel list
	 *
	 */
	public function channels() {
		$result = hameslack_bot_request( 'GET', 'channels.list' );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		$table = new cli\Table();
		$table->setHeaders( [ 'Name', 'id', 'Created', 'Member' ] );
		foreach ( $result->channels as $channel ) {
			$table->addRow( [
				$channel->name,
				$channel->id,
				date_i18n( 'Y-m-d H:i', $channel->created ),
				$channel->num_members,
			] );
		}
		$table->display();
	}

	/**
	 * Show channel list
	 *
	 * ## OPTIONS
	 * : <channel>
	 *   Channel name. Must be channel id, not name.
	 *
	 * @synopsis <channel>
	 * @param array $args
	 */
	public function history( $args ) {
		list( $channel ) = $args;
		$result = hameslack_bot_request( 'GET', 'channels.history', [
			'channel' => $channel,
			'count' => 10,
		] );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		$table = new cli\Table();
		$table->setHeaders( [ 'User', 'Time', 'Text' ] );
		foreach ( $result->messages as $message ) {
			$table->addRow( [
				$message->user,
				date_i18n( 'Y-m-d H:i', (int) $message->ts ),
				mb_substr( $message->text, 0, 20, 'utf-8' ).'...',
			] );
		}
		$table->display();
	}
}
