<?php
/**
 * File Name: mo-saml-feedback-form.php
 * Description: This file will send us the feedback of the clients when they deactivate the plugin.
 *
 * @package miniorange-saml-20-single-sign-on\views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays the feedback form upon plugin deactivation.
 *
 * @return void
 */
function mo_saml_display_saml_feedback_form() {
	if ( isset( $_SERVER['PHP_SELF'] ) && 'plugins.php' !== basename( sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) ) ) ) {
		return;
	}

	$email = get_option( Mo_Saml_Customer_Constants::ADMIN_EMAIL );
	if ( empty( $email ) ) {
		$user  = wp_get_current_user();
		$email = $user->user_email;
	}

	$max_reason_selections = Mo_SAML_Feedback_Form_Handler::MO_SAML_FEEDBACK_MAX_REASONS;
	?>
	<div id="mo_saml_feedback_modal" class="mo_modal mo_saml_feedback_modal_outer">

		<div class="mo_modal-content mo_saml_feedback_modal_content">
			<div class="mo_saml_fb_head">
				<h3 class="mo_saml_fb_head_title"><?php esc_html_e( 'Thanks for trying our SAML SP plugin - what could we do better?', 'miniorange-saml-20-single-sign-on' ); ?></h3>
				<span class="mo_saml_close" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Close', 'miniorange-saml-20-single-sign-on' ); ?>">&times;</span>
			</div>
			<hr class="mo_saml_fb_rule" />
			<form name="f" method="post" action="" id="mo_feedback" data-max-selections="<?php echo (int) $max_reason_selections; ?>">
				<?php wp_nonce_field( 'mo_feedback' ); ?>
				<input type="hidden" name="option" value="mo_feedback"/>
				<div class="mo_saml_fb_prompt_row">
					<p class="mo_saml_fb_prompt"><strong><?php esc_html_e( 'Tell us what happened ?', 'miniorange-saml-20-single-sign-on' ); ?></strong></p>
					<span class="mo_saml_fb_selection_badge" id="mo_saml_fb_selection_badge" aria-live="polite">
						<span id="mo_saml_fb_selection_count">0</span>/<span id="mo_saml_fb_selection_max"><?php echo (int) $max_reason_selections; ?></span> <?php esc_html_e( 'Selected', 'miniorange-saml-20-single-sign-on' ); ?>
					</span>
				</div>
				<p class="mo_saml_fb_reason_error" id="mo_saml_fb_reason_error" role="alert" tabindex="-1" hidden><?php esc_html_e( 'Please select at least one reason before submitting.', 'miniorange-saml-20-single-sign-on' ); ?></p>
				<p class="mo_saml_fb_max_msg" id="mo_saml_fb_max_msg" role="status" aria-live="polite" hidden><?php echo esc_html( sprintf( /* translators: %d: maximum number of deactivation reasons the user may select */ __( 'You can select up to %d reasons.', 'miniorange-saml-20-single-sign-on' ), $max_reason_selections ) ); ?></p>
				<ul class="mo_saml_fb_reasons">
					<?php foreach ( array_keys( Mo_SAML_Feedback_Form_Handler::mo_saml_reason_labels() ) as $reason_slug ) : ?>
					<li>
						<input type="checkbox" name="deactivate_reason[]" class="mo_saml_fb_reason_cb" id="mo_saml_fb_<?php echo esc_attr( $reason_slug ); ?>" value="<?php echo esc_attr( $reason_slug ); ?>" />
						<label for="mo_saml_fb_<?php echo esc_attr( $reason_slug ); ?>"><?php echo esc_html( Mo_SAML_Feedback_Form_Handler::mo_saml_get_feedback_reason_translated_label( $reason_slug ) ); ?></label>
					</li>
					<?php endforeach; ?>
				</ul>
				<div id="mo_saml_fb_other_area">
					<textarea id="query_feedback" name="query_feedback" rows="4" placeholder="<?php esc_attr_e( 'Please describe your reason...', 'miniorange-saml-20-single-sign-on' ); ?>"></textarea>
				</div>
				<div class="mo_saml_fb_followup">
					<label class="mo_saml_fb_toggle" title="<?php esc_attr_e( 'Allow follow-up', 'miniorange-saml-20-single-sign-on' ); ?>">
						<input type="checkbox" name="get_reply" value="reply" checked="checked" />
						<span class="mo_saml_fb_toggle_slider"></span>
					</label>
					<div class="mo_saml_fb_followup_text">
						<?php esc_html_e( 'Follow up with me at', 'miniorange-saml-20-single-sign-on' ); ?>
						<span class="mo_saml_fb_email_row">
							<input type="email" id="query_mail" name="query_mail" value="<?php echo esc_attr( $email ); ?>" readonly="readonly" required />
							<button type="button" class="mo_saml_fb_edit_btn" aria-label="<?php esc_attr_e( 'Edit email', 'miniorange-saml-20-single-sign-on' ); ?>">
								<img class="editable" src="<?php echo esc_url( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() . 'images/edit-icon.webp' ); ?>" alt="" />
							</button>
						</span>
					</div>
				</div>
				<hr class="mo_saml_fb_rule" />
				<div class="mo_saml_fb_footer">
					<button type="button" name="miniorange_skip_feedback" class="mo_saml_fb_skip" id="mo_saml_fb_skip"><?php esc_html_e( 'Skip', 'miniorange-saml-20-single-sign-on' ); ?></button>
					<input type="submit" name="miniorange_feedback_submit" class="mo_saml_fb_submit" value="<?php esc_attr_e( 'Submit', 'miniorange-saml-20-single-sign-on' ); ?>" />
				</div>
			</form>
			<form name="f" method="post" action="" id="mo_saml_feedback_form_close">
				<?php wp_nonce_field( 'mo_skip_feedback' ); ?>
				<input type="hidden" name="option" value="mo_skip_feedback" />
			</form>
		</div>

	</div>
	<?php
}
