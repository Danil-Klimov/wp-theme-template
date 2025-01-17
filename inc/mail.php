<?php
/**
 * Mail settings
 *
 * @package Theme_name
 * @since 1.0.0
 */

add_action( 'init', 'adem_register_mail_post_type' );
/**
 * Register custom post type - mail.
 */
function adem_register_mail_post_type() {
	register_post_type(
		'mail',
		array(
			'label'              => null,
			'labels'             => array(
				'name'               => 'Заявки',
				'singular_name'      => 'Заявка',
				'menu_name'          => 'Заявки',
				'all_items'          => 'Все заявки',
				'add_new'            => 'Добавить заявку',
				'add_new_item'       => 'Добавить заявку',
				'edit_item'          => 'Редактировать заявку',
				'new_item'           => 'Новая заявку',
				'view_item'          => 'Смотреть заявку',
				'search_items'       => 'Найти заявку',
				'not_found'          => 'Не найдено',
				'not_found_in_trash' => 'Не найдено в корзине',
			),
			'description'        => '',
			'public'             => true,
			'map_meta_cap'       => true,
			'rewrite'            => false,
			'menu_position'      => 23,
			'menu_icon'          => 'dashicons-email-alt',
			'supports'           => false,
			'publicly_queryable' => false,
		)
	);
}

add_action( 'acf/init', 'adem_acf_register_fields' );
/**
 * Register ACF options page and fields.
 */
function adem_acf_register_fields() {
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page(
			array(
				'page_title'      => 'Формы',
				'menu_title'      => 'Формы',
				'menu_slug'       => 'forms',
				'post_id'         => 'forms',
				'position'        => 64,
				'update_button'   => 'Обновить',
				'updated_message' => 'Настройки обновлены',
			)
		);
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_5c18f89ca825f',
			'title'    => 'Настройки форм',
			'fields'   => array(
				array(
					'key'          => 'field_5c18f8a29941c',
					'label'        => 'Адреса для отправки почты',
					'name'         => 'emails',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Добавить почту',
					'sub_fields'   => array(
						array(
							'key'   => 'field_5c18f8ba9941d',
							'label' => 'Email',
							'name'  => 'emails_item',
							'type'  => 'email',
						),
						array(
							'key'   => 'field_dfg45hhdg34rasdgf',
							'label' => 'Нужен ли трафик в письме?',
							'name'  => 'emails_traffic',
							'type'  => 'true_false',
							'ui'    => 1,
						),
					),
				),
				array(
					'key'   => 'field_dfs2825hdsdf8234',
					'label' => 'Почта отправителя',
					'name'  => 'sender-mail',
					'type'  => 'email',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'forms',
					),
				),
			),
		)
	);
}

add_action( 'add_meta_boxes', 'mail_meta_box', 1 );
/**
 * Register mail meta box.
 */
function mail_meta_box() {
	add_meta_box( 'mail_meta_box', 'Заявка', 'mail_meta_box_function', 'mail', 'normal', 'high' );
}

/**
 * Callback for display content in mail meta box.
 *
 * @param object $post Post object.
 */
function mail_meta_box_function( $post ) {
	$meta_body = get_post_meta( $post->ID, 'mail-body', true );

	echo wp_kses_post( $meta_body );
}

add_action( 'wp_ajax_send_mail', 'adem_send_mail' );
add_action( 'wp_ajax_nopriv_send_mail', 'adem_send_mail' );
/**
 * Handler for ajax mails.
 */
function adem_send_mail() {
	$nonce_field = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : null;
	$form_name   = isset( $_POST['form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : null;

	if ( ! $nonce_field && ! wp_verify_nonce( $nonce_field, $form_name ) ) {
		exit;
	}

	$mail    = isset( $_POST['name'] ) ? 'Имя: ' . sanitize_text_field( wp_unslash( $_POST['name'] ) ) . '<br/>' : '';
	$tel     = isset( $_POST['tel'] ) ? sanitize_text_field( wp_unslash( $_POST['tel'] ) ) : null;
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : null;
	$message = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : null;
	$order   = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : null;
	$referer = isset( $_POST['_wp_http_referer'] ) ? sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) : null;

	$mail .= isset( $tel ) ? 'Телефон: <a href="tel:' . adem_clear_tel( $tel ) . '">' . $tel . '</a><br/>' : '';
	$mail .= isset( $email ) ? 'Email: <a href="mailto:' . $email . '">' . $email . '</a><br/>' : '';
	$mail .= isset( $message ) ? 'Сообщение: ' . $message . '<br/>' : '';
	$mail .= ! empty( $order ) ? 'Заказ: ' . $order . '<br/>' : '';
	$mail .= isset( $referer ) ? 'Страница: ' . $referer . '<br/>' : '';

	if ( ! empty( $_FILES['files'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$files = $_FILES['files'];

		foreach ( $files['name'] as $key => $data ) {
			$file = array(
				'name'     => $files['name'][ $key ],
				'type'     => $files['type'][ $key ],
				'tmp_name' => $files['tmp_name'][ $key ],
				'error'    => $files['error'][ $key ],
				'size'     => $files['size'][ $key ],
			);

			$attach_id = media_handle_sideload( $file );

			if ( ! is_wp_error( $attach_id ) ) {
				$mail .= 'Файл: <a href="' . wp_get_attachment_url( $attach_id ) . '" target="_blank">Ссылка на файл</a><br/>';
			}
		}
	}

	$post_data = array(
		'post_title'  => $form_name,
		'post_status' => 'pending',
		'post_author' => 1,
		'post_type'   => 'mail',
	);
	$post_ID   = wp_insert_post( $post_data );
	$traffic   = isset( $_COOKIE['traffic_source'] ) ? 'Трафик: ' . sanitize_text_field( wp_unslash( $_COOKIE['traffic_source'] ) ) . '<br/>' : '';
	$meta_body = $mail . $traffic;

	update_post_meta( $post_ID, 'mail-body', $meta_body );

	if ( have_rows( 'emails', 'forms' ) ) {
		$subject = get_bloginfo( 'name' ) . ' - ' . $form_name;
		$headers = 'Content-type: text/html; charset="utf-8"';

		while ( have_rows( 'emails', 'forms' ) ) {
			the_row();

			$mail_to   = get_sub_field( 'emails_item' );
			$mail_body = $mail;

			if ( get_sub_field( 'emails_traffic' ) ) {
				$mail_body .= $traffic;
			}

			wp_mail( $mail_to, $subject, $mail_body, $headers );
		}
	}

	wp_send_json_success();

	wp_die();
}

add_action( 'admin_menu', 'adem_add_mail_menu_bubble' );
/**
 * Add new emails counter.
 */
function adem_add_mail_menu_bubble() {
	global $menu;
	$count = wp_count_posts( 'mail' )->pending;

	if ( $count ) {
		foreach ( $menu as $key => $value ) {
			if ( 'edit.php?post_type=mail' === $menu[ $key ][2] ) {
				$menu[ $key ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $count . '</span></span>';
				break;
			}
		}
	}
}

add_filter( 'wp_mail_from_name', 'adem_change_mail_name' );
/**
 * Changes the name of outgoing mail.
 */
function adem_change_mail_name(): string {
	return get_bloginfo( 'name' );
}

add_filter( 'wp_mail_from', 'adem_change_mail_email' );
/**
 * Changes the email of outgoing mail.
 */
function adem_change_mail_email(): string {
	return get_field( 'sender-mail', 'forms' ) ?? get_bloginfo( 'admin_email' );
}
