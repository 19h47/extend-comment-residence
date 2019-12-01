<?php
/**
 * Plugin Name: Extend Residence Comment
 * Version: 1.0.1
 * Plugin URI: http://github.com/19h47/extend-residence-comment
 * Description: A plugin to add additional fields in the residence comment form.
 * Author: Jérémy Levron
 * Author URI: http://19h47.fr
 *
 * @package ExtendResidenceComment
 */

define(
	'RATINGS',
	array(
		array(
			'id'    => 'accommodation',
			'label' => __( 'Logement' ),
		),
		array(
			'id'    => 'cleanliness',
			'label' => __( 'Propreté' ),
		),
		array(
			'id'    => 'equipments',
			'label' => __( 'Équipements' ),
		),
		array(
			'id'    => 'environment',
			'label' => __( 'Environnement' ),
		),
		array(
			'id'    => 'on-site-reception',
			'label' => __( 'Accueil sur place' ),
		),
	)
);

add_filter( 'comment_form_default_fields', 'custom_fields' );

/**
 * Add custom meta (ratings) fields to the default comment form
 *
 * Default comment form includes name, email and URL
 * Default comment form elements are hidden when user is logged in
 *
 * @param arr $fields The array of fields.
 */
function custom_fields( $fields ) {
	$commenter     = wp_get_current_commenter();
	$require       = get_option( 'require_name_email' );
	$aria_required = $require ? " aria-required='true'" : '';

	$fields['author'] = '<p class="comment-form-author">' .
		'<label for="author">' . __( 'Name' ) . '</label>' .
		( $require ? '<span class="required">*</span>' : '' ) .
		'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .
		'" size="30" tabindex="1"' . $aria_required . ' /></p>';

	$fields['email'] = '<p class="comment-form-email">' .
		'<label for="email">' . __( 'Email' ) . '</label>' .
		( $require ? '<span class="required">*</span>' : '' ) .
		'<input id="email" name="email" type="text" value="' . esc_attr( $commenter['comment_author_email'] ) .
		'" size="30"  tabindex="2"' . $aria_required . ' /></p>';

	$fields['url'] = '<p class="comment-form-url">' .
		'<label for="url">' . __( 'Website' ) . '</label>' .
		'<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .
		'" size="30"  tabindex="3" /></p>';

	$fields['phone'] = '<p class="comment-form-phone">' .
		'<label for="phone">' . __( 'Website' ) . '</label>' .
		'<input id="phone" name="phone" type="tel" value="' . esc_attr( $commenter['comment_author_phone'] ) .
		'" size="30"  tabindex="3" /></p>';

	return $fields;
}


add_action( 'comment_form_logged_in_after', 'additional_fields' );
add_action( 'comment_form_after_fields', 'additional_fields' );

/**
 * Add fields after default fields above the comment box, always visible
 */
function additional_fields() {
	$html = '';

	// Title.
	$html .= '<p class="comment-form-title">';
	$html .= '<label for="title">' . __( 'Titre du commentaire' ) . '</label>';
	$html .= '<input id="title" name="title" type="text" size="30"  tabindex="5" /></p>';

	// Date visit.
	$html .= '<p class="comment-form-date-visit">';
	$html .= '<label for="date_visit">' . __( 'Date de visite' ) . '</label>';
	$html .= '<input id="date_visit" name="date_visit" type="date"/></p>';

	// Phone.
	$html .= '<p class="comment-form-phone">';
	$html .= '<label for="phone">' . __( 'Téléphone' ) . '</label>';
	$html .= '<input id="phone" name="phone" type="tel"/></p>';

	// Ratings.
	foreach ( RATINGS as $rating ) {
		$html .= "<p class=\"comment-form-{$rating['id']}\">";
		$html .= "<label for=\"{$rating['id']}\">{$rating['label']}<span class=\"required\">*</span></label>";
		$html .= '<span class="commentratingbox">';

		for ( $i = 1; $i <= 5; $i++ ) {
			$html .= "<span class=\"commentrating\"><input type=\"radio\" name=\"{$rating['id']}\" id=\"{$rating['id']}\" value=\"{$i}\"/>{$i}</span>";
		}
		$html .= '</span></p>';
	}

	echo esc_html( $html );
}


add_action( 'comment_post', 'save_comment_meta_data', 10, 1 );

/**
 * Save the comment meta data along with comment
 *
 * @param int $comment_id The comment ID.
 *
 * @see https://developer.wordpress.org/reference/hooks/comment_post/
 * @return void
 */
function save_comment_meta_data( $comment_id ) {
	add_comment_meta(
		$comment_id,
		'title',
		wp_filter_nohtml_kses(  wp_unslash( $_POST['title'] ) )
	);

	add_comment_meta(
		$comment_id,
		'date_visit',
		wp_filter_nohtml_kses( wp_unslash( $_POST['date_visit'] ) )
	);

	add_comment_meta(
		$comment_id,
		'phone',
		wp_filter_nohtml_kses( wp_unslash( $_POST['phone'] ) )
	);

	foreach ( RATINGS as $data ) {
		if ( ( isset( $_POST[ $data['id'] ] ) ) && ( ! empty( $_POST[ $data['id'] ] ) ) ) {
			add_comment_meta(
				$comment_id,
				$data['id'],
				wp_filter_nohtml_kses( (int) $_POST[ $data['id'] ] )
			);
		}
	}
}


add_filter( 'preprocess_comment', 'verify_comment_meta_data' );

/**
 * Add the filter to check if the comment meta data has been filled or not
 *
 * @param arr $commentdata Comment data.
 * @see https://developer.wordpress.org/reference/hooks/preprocess_comment/
 */
function verify_comment_meta_data( $commentdata ) {
	foreach ( RATINGS as $rating ) {
		if ( ! isset( $_POST[ $rating['id'] ] ) ) {
			wp_die( __( 'Error: You did not add your rating. Hit the BACK button of your Web browser and resubmit your comment with rating.' ) );
		}
	}
	return $commentdata;
}


add_action( 'add_meta_boxes_comment', 'extend_residence_comment_add_meta_box' );

/**
 * Add an edit option in comment edit screen
 *
 * @see https://developer.wordpress.org/reference/hooks/add_meta_boxes_comment/
 * @see https://developer.wordpress.org/reference/functions/add_meta_box/
 */
function extend_residence_comment_add_meta_box() {
	add_meta_box(
		'title',
		__( 'Commentaire Résidence' ),
		'extend_residence_comment_meta_box',
		'comment',
		'normal',
		'high'
	);
}


/**
 * Function that fills the box with the comment content
 *
 * @param obj $comment WP_Comment The comment object.
 *
 * @see https://developer.wordpress.org/reference/classes/wp_comment/
 */
function extend_residence_comment_meta_box( $comment ) {
	$title      = get_comment_meta( $comment->comment_ID, 'title', true );
	$date_visit = get_comment_meta( $comment->comment_ID, 'date_visit', true );
	$phone      = get_comment_meta( $comment->comment_ID, 'phone', true );

	$ratings = array();

	foreach ( RATINGS as $rating ) {
		array_push(
			$ratings,
			array(
				'label' => $rating['label'],
				'id'    => $rating['id'],
				'note'  => (int) get_comment_meta( $comment->comment_ID, $rating['id'], true ),
			)
		);
	}

	wp_nonce_field( 'extend_residence_comment_update', 'extend_residence_comment_update', false );

	include plugin_dir_path( __FILE__ ) . 'templates/meta-box.html.php';
}


add_action( 'edit_comment', 'extend_residence_comment_edit_metafields' );

/**
 * Update comment meta data from comment edit screen
 *
 * @param int $comment_id The comment ID.
 *
 * @see https://developer.wordpress.org/reference/hooks/edit_comment/
 */
function extend_residence_comment_edit_metafields( $comment_id ) {
	if ( ! isset( $_POST['extend_residence_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_residence_comment_update'], 'extend_residence_comment_update' ) ) {
		return;
	}

	if ( isset( $_POST['title'] ) && esc_html( ! empty( $_POST['title'] ) ) ) {
		$title = wp_filter_nohtml_kses( wp_unslash( $_POST['title'] ) );
		update_comment_meta( $comment_id, 'title', $title );
	} else {
		delete_comment_meta( $comment_id, 'title' );
	}

	if ( isset( $_POST['date_visit'] ) && esc_html( ! empty( $_POST['date_visit'] ) ) ) {
		$title = wp_filter_nohtml_kses( wp_unslash( $_POST['date_visit'] ) );
		update_comment_meta( $comment_id, 'date_visit', $title );
	} else {
		delete_comment_meta( $comment_id, 'date_visit' );
	}

	if ( isset( $_POST['phone'] ) && esc_html( ! empty( $_POST['phone'] ) ) ) {
		$title = wp_filter_nohtml_kses( wp_unslash( $_POST['phone'] ) );
		update_comment_meta( $comment_id, 'phone', $title );
	} else {
		delete_comment_meta( $comment_id, 'phone' );
	}

	foreach ( RATINGS as $rating ) {
		if ( ( isset( $_POST[ $rating['id'] ] ) ) && ( ! empty( (int) $_POST[ $rating['id'] ] ) ) ) {
			update_comment_meta(
				$comment_id,
				$rating['id'],
				wp_filter_nohtml_kses( (int) $_POST[ $rating['id'] ] )
			);
		} else {
			delete_comment_meta( $comment_id, (int) $rating['id'] );
		}
	}
}


add_filter( 'comment_text', 'modify_comment', 10, 1 );

/**
 * Add the comment meta (saved earlier) to the comment text
 * You can also output the comment meta values directly in comments template
 *
 * @param string $comment_text Text of the current comment.
 *
 * @see https://developer.wordpress.org/reference/functions/comment_text/
 */
function modify_comment( string $comment_text ) {
	$plugin_url_path = WP_PLUGIN_URL;

	$date_visit = get_comment_meta( get_comment_ID(), 'date_visit', true );
	$title      = get_comment_meta( get_comment_ID(), 'title', true );
	$phone      = get_comment_meta( get_comment_ID(), 'phone', true );

	if ( $phone ) {
		$comment_text = '<strong>' . esc_attr( $phone ) . '</strong><br/>' . $comment_text;
	}

	if ( $date_visit ) {
		$comment_text = '<strong>' . gmdate( 'j F Y', strtotime( $date_visit ) ) . '</strong><br/>' . $comment_text;
	}

	if ( $title ) {
		$comment_text = '<strong>' . esc_attr( $title ) . '</strong><br/>' . $comment_text;
	}

	$comment_text .= '<table class="widefat fixed striped"><thead><tr>';

	foreach ( RATINGS as $rating ) {
		$comment_text = $comment_text . "<th><small style=\"word-wrap: initial;\">{$rating['label']}</small></th>";
	}

	$comment_text .= '</tr></thead><tbody><tr class="even">';

	foreach ( RATINGS as $rating ) {
		$note = get_comment_meta( get_comment_ID(), $rating['id'], true );

		$comment_text .= $note ? "<td>{$note}</td>" : '<td></td>';
	}

	$comment_text .= '</tr></tbody></table>';

	return $comment_text;
}
