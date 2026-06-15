<?php
/**
 * Handles the submission of the Feedback form.
 *
 * @package miniorange-saml-20-single-sign-on\handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Handler class for the feedback form. This class takes care of validating the feedback form data, making the feedback API request and performing post-feedback redirection.
 */
class Mo_SAML_Feedback_Form_Handler {

	/** Max deactivation reasons the user may select on the feedback form. */
	const MO_SAML_FEEDBACK_MAX_REASONS = 3;

	/**
	 * Sanitize a single reason slug from POST (or similar) input (wp_unslash + sanitize_text_field).
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	private static function mo_saml_sanitize_reason_slug( $value ) {
		return sanitize_text_field( wp_unslash( (string) $value ) );
	}

	/**
	 * Ordered slug => English label (single source for form, email body, and validation).
	 *
	 * @return array<string, string>
	 */
	public static function mo_saml_reason_labels() {
		static $labels = null;

		if ( null === $labels ) {
			$labels = array(
				'upgrading_paid'     => 'Upgrading to paid version',
				'difficult_setup'    => 'Difficult to Setup',
				'different_features' => 'Looking for different features',
				'facing_issues'      => 'Facing Issues in plugin',
				'too_expensive'      => 'Too Expensive',
				'lack_docs'          => 'Lack of documentation',
				'other'              => 'Other',
			);
		}

		return $labels;
	}

	/**
	 * Translated checkbox label for the feedback form.
	 *
	 * @param string $reason_slug Slug from mo_saml_reason_labels().
	 * @return string
	 */
	public static function mo_saml_get_feedback_reason_translated_label( $reason_slug ) {
		foreach ( self::mo_saml_reason_labels() as $slug => $label ) {
			if ( $slug === $reason_slug ) {
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Msgids are only those in mo_saml_reason_labels().
				return __( $label, 'miniorange-saml-20-single-sign-on' );
			}
		}

		return self::mo_saml_sanitize_reason_slug( $reason_slug );
	}

	/**
	 * Allowed reason slugs in definition order.
	 *
	 * @return array<int, string>
	 */
	private static function mo_saml_allowed_reason_slugs() {
		return array_keys( self::mo_saml_reason_labels() );
	}

	/**
	 * Human-readable reason list for the feedback email body.
	 *
	 * @param array<int, string> $reason_slugs Normalized or POST reason slugs.
	 * @return string
	 */
	private static function mo_saml_build_email_reasons_summary( array $reason_slugs ) {
		if ( empty( $reason_slugs ) ) {
			return '';
		}

		$slug_to_label = self::mo_saml_reason_labels();
		$labels        = array();

		foreach ( $reason_slugs as $reason_slug ) {
			$reason_slug = self::mo_saml_sanitize_reason_slug( $reason_slug );
			if ( '' === $reason_slug ) {
				continue;
			}
			$labels[] = isset( $slug_to_label[ $reason_slug ] ) ? $slug_to_label[ $reason_slug ] : $reason_slug;
		}

		return implode( '; ', $labels );
	}

	/**
	 * Sanitizes POSTed reason slugs: known slugs only, order preserved, max count enforced.
	 *
	 * @param mixed $posted_reasons Raw POST value for deactivate_reason.
	 * @return array<int, string>
	 */
	private static function mo_saml_normalize_deactivate_reasons( $posted_reasons ) {
		if ( ! is_array( $posted_reasons ) ) {
			return array();
		}

		$allowed_slugs    = self::mo_saml_allowed_reason_slugs();
		$normalized_slugs = array();

		foreach ( $posted_reasons as $posted_slug ) {
			$reason_slug = sanitize_text_field( wp_unslash( (string) $posted_slug ) );
			if ( '' === $reason_slug || ! in_array( $reason_slug, $allowed_slugs, true ) || in_array( $reason_slug, $normalized_slugs, true ) ) {
				continue;
			}
			$normalized_slugs[] = $reason_slug;
		}

		return array_slice( $normalized_slugs, 0, self::MO_SAML_FEEDBACK_MAX_REASONS );
	}

	/**
	 * Redirects to the Installed Plugin page with the correct message after the plugin is deactivated.
	 *
	 * @return void
	 */
	public static function mo_saml_skip_feedback() {
		deactivate_plugins( dirname( ( __DIR__ ) ) . '\login.php' );

		wp_safe_redirect( self_admin_url( 'plugins.php?deactivate=true' ) );
		exit;
	}

	/**
	 * Sends the feedback email based on the user input on the feedback form.
	 *
	 * @param array $post_array Contains the user input from the feedback form.
	 * @return void
	 */
	public static function mo_saml_send_feedback( $post_array ) {
		$posted_reasons              = isset( $post_array['deactivate_reason'] ) ? $post_array['deactivate_reason'] : null;
		$normalized_reason_slugs         = self::mo_saml_normalize_deactivate_reasons( $posted_reasons );
		$post_array['deactivate_reason'] = $normalized_reason_slugs;

		if ( empty( $normalized_reason_slugs ) ) {
			$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::ERROR, Mo_Saml_Messages::mo_saml_translate( 'FEEDBACK_REASON_REQUIRED' ) );
			$post_save->mo_saml_post_save_action();
			wp_safe_redirect( self_admin_url( 'plugins.php' ) );
			exit;
		}

		$email    = self::mo_saml_get_user_email( $post_array );
		$message  = self::mo_saml_get_feedback_message( $post_array );
		$phone    = get_option( Mo_Saml_Customer_Constants::ADMIN_PHONE );
		$customer = new Mo_SAML_Customer();

		$response = json_decode( $customer->mo_saml_send_email_alert( $email, $phone, $message ), true );

		deactivate_plugins( dirname( ( __DIR__ ) ) . '\login.php' );

		if ( ! self::mo_saml_validate_response( $response ) ) {
			return;
		}

		wp_safe_redirect( self_admin_url( 'plugins.php?deactivate=true' ) );
		exit;
	}

	/**
	 * Formats the feedback message for the feedback email.
	 *
	 * @param array $post_array Sanitized POST from Mo_SAML_Utilities::mo_saml_sanitize_post_array(); deactivate_reason[] normalized in mo_saml_send_feedback().
	 * @return string
	 */
	public static function mo_saml_get_feedback_message( $post_array ) {
		$user_feedback_comment        = isset( $post_array['query_feedback'] ) ? sanitize_text_field( wp_unslash( $post_array['query_feedback'] ) ) : '';
		$reply_required               = ! empty( $post_array['get_reply'] );
		$multisite_enabled            = is_multisite() ? 'True' : 'False';
		$reason_slugs                 = ( ! empty( $post_array['deactivate_reason'] ) && is_array( $post_array['deactivate_reason'] ) ) ? $post_array['deactivate_reason'] : array();
		$deactivation_reasons_summary = self::mo_saml_build_email_reasons_summary( $reason_slugs );

		$reply_line = $reply_required ? '[Reply : yes]' : '<b> &nbsp; [Reply : don\'t reply]</b>';

		$message  = $reply_line;
		$message .= '<br>[Multisite enabled: ' . $multisite_enabled . ']';
		$message .= '<br>[Deactivation reasons: ' . esc_html( $deactivation_reasons_summary ) . ']';
		$message .= '<br>Feedback : ' . esc_html( $user_feedback_comment );

		return $message;
	}

	/**
	 * Fetches the user's email address for the feedback email.
	 *
	 * @param array $post_array Contains the user input from the feedback form.
	 * @return string
	 */
	public static function mo_saml_get_user_email( $post_array ) {
		$query_mail = isset( $post_array['query_mail'] ) ? sanitize_text_field( wp_unslash( $post_array['query_mail'] ) ) : '';
		if ( '' !== $query_mail && filter_var( $query_mail, FILTER_VALIDATE_EMAIL ) ) {
			return $query_mail;
		}

		$email = get_option( Mo_Saml_Customer_Constants::ADMIN_EMAIL );
		if ( empty( $email ) ) {
			$user  = wp_get_current_user();
			$email = $user->user_email;
		}
		return $email;
	}

	/**
	 * Validates the feedback API call response and displays the relevant message.
	 *
	 * @param array $response Contains the response from the feedback API call.
	 * @return bool
	 */
	public static function mo_saml_validate_response( $response ) {
		if ( json_last_error() === JSON_ERROR_NONE ) {
			if ( ! empty( $response['status'] ) && Mo_Saml_Api_Status_Constants::ERROR === $response['status'] ) {
				$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::ERROR, $response['message'] );
			} elseif ( false === $response ) {
				$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::ERROR, Mo_Saml_Messages::mo_saml_translate( 'QUERY_NOT_SUBMITTED' ) );
			}
		}
		if ( isset( $post_save ) ) {
			$post_save->mo_saml_post_save_action();
			return false;
		}
		return true;
	}
}
