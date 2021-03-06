<?php
/**
 * Plugin Name:  AWS SES wp_mail drop-in
 * Plugin URI:   https://github.com/humanmade/aws-ses-wp-mail
 * Description:  Drop-in replacement for wp_mail using the AWS SES.
 * Version:      0.1.0
 * Author:       Joe Hoyle | Human Made
 * Author URI:   https://github.com/humanmade
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('AWS_SES_WP_MAIL_KEY') ?: define( 'AWS_SES_WP_MAIL_KEY',  Env::get('AWS_SES_WP_MAIL_KEY') );
defined('AWS_SES_WP_MAIL_SECRET') ?: define( 'AWS_SES_WP_MAIL_SECRET', Env::get('AWS_SES_WP_MAIL_SECRET') );
defined('AWS_SES_WP_MAIL_REGION') ?: define( 'AWS_SES_WP_MAIL_REGION', Env::get('AWS_SES_WP_MAIL_REGION') );
defined('AWS_SES_WP_MAIL_USE_INSTANCE_PROFILE') ?: define( 'AWS_SES_WP_MAIL_USE_INSTANCE_PROFILE', Env::get('AWS_SES_WP_MAIL_USE_INSTANCE_PROFILE') );
if ((empty( AWS_SES_WP_MAIL_KEY ) || empty( AWS_SES_WP_MAIL_SECRET ) || empty( AWS_SES_WP_MAIL_REGION )) && !( AWS_SES_WP_MAIL_USE_INSTANCE_PROFILE )) {
	trigger_error(__('AWS SES wp_mail drop-in activated, but is missing required configuration.', 'aws-ses-wp-mail'), E_USER_NOTICE);
    return;
}

require_once dirname( __FILE__ ) . '/inc/class-ses.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/inc/class-wp-cli-command.php';
	WP_CLI::add_command( 'aws-ses', 'AWS_SES_WP_Mail\\WP_CLI_Command' );
}

if ( ! function_exists( 'wp_mail' ) ) :
function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
	$result = AWS_SES_WP_Mail\SES::get_instance()->send_wp_mail( $to, $subject, $message, $headers, $attachments );

	if ( is_wp_error( $result ) ) {
		trigger_error(
			sprintf( 'Sendmail SES Email failed: %d %s', $result->get_error_code(), $result->get_error_message() ),
			E_USER_WARNING
		);
		return false;
	}

	return $result;
}
endif;
