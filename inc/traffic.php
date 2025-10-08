<?php
/**
 * Get user traffic
 *
 * @package Theme_name
 * @since 1.0.0
 */

add_action( 'init', 'adem_get_user_traffic' );
function adem_get_user_traffic() {
	$search_systems   = array( 'google', 'yandex', 'mail.ru', 'rambler', 'bing' );
	$important_source = false;
	$source_array     = array(
		'medium'   => null,
		'source'   => null,
		'campaign' => null,
		'term'     => null,
		'content'  => null,
	);

	if ( isset( $_GET['utm_medium'] ) ) {
		$important_source         = true;
		$source_array['medium']   = sanitize_text_field( wp_unslash( $_GET['utm_medium'] ) );
		$source_array['source']   = isset( $_GET['utm_source'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_source'] ) ) : null;
		$source_array['campaign'] = isset( $_GET['utm_campaign'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_campaign'] ) ) : null;
		$source_array['term']     = isset( $_GET['utm_term'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_term'] ) ) : null;
		$source_array['content']  = isset( $_GET['utm_content'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_content'] ) ) : null;
	} elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		preg_match( '/(?:https?:\/\/)?(.*?)(?:\/|$)/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), $referrer );
		$referrer = preg_match( '/\.(.*\.\w*)/', $referrer[1], $new_referrer ) ? $new_referrer[1] : $referrer[1];

		if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			preg_match( '/(?:https?:\/\/)?(.*?)(?:\/|$)/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ), $host );
			$host = preg_match( '/\.(.*\.\w*)/', $host[1], $new_host ) ? $new_host[1] : $host[1];
		}

		if ( $host !== $referrer ) {
			$important_source       = true;
			$source_array['source'] = $referrer;
			$source_array['medium'] = 'referral';

			foreach ( $search_systems as $search_system ) {
				if ( strpos( $referrer, $search_system ) !== false ) {
					$source_array['source'] = $search_system;
					$source_array['medium'] = 'organic';
					break;
				}
			}
		} else {
			$source_array['source'] = 'none';
			$source_array['medium'] = 'direct';
		}
	} else {
		$source_array['source'] = 'none';
		$source_array['medium'] = 'direct';
	}

	if ( ! $important_source && isset( $_COOKIE['source_cookie'] ) ) {
		$source_cookie = json_decode( wp_unslash( $_COOKIE['source_cookie'] ), true );

		if ( is_array( $source_cookie ) && ! empty( $source_cookie['medium'] ) ) {
			$source_array = $source_cookie;
		}
	}

	if ( ! headers_sent() ) {
		setcookie( 'source_cookie', wp_json_encode( $source_array ), time() + MONTH_IN_SECONDS, '/' );
	}

	$html  = "Источник - {$source_array['source']}, Канал - {$source_array['medium']}";
	$html .= ! empty( $source_array['campaign'] ) ? ", Кампания - {$source_array['campaign']}" : '';
	$html .= ! empty( $source_array['term'] ) ? ", Объявление - {$source_array['term']}" : '';
	$html .= ! empty( $source_array['content'] ) ? ", Ключевое слово - {$source_array['content']}" : '';

	if ( ! headers_sent() ) {
		setcookie( 'traffic_source', $html, time() + MONTH_IN_SECONDS, '/' );
	}
}
