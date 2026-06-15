( function ( $ ) {
	'use strict';

	var feedbackModalCloseHandler = null;
	var feedbackModalBackdropHandler = null;
	var removeFeedbackModalListeners = null;

	function moSamlFbUpdateSelectionCount() {
		var feedbackForm = document.getElementById( 'mo_feedback' );
		var selectionCount = document.getElementById( 'mo_saml_fb_selection_count' );
		var selectionBadge = document.getElementById( 'mo_saml_fb_selection_badge' );
		if ( ! feedbackForm || ! selectionCount ) {
			return;
		}
		var checkedReasonCount = feedbackForm.querySelectorAll( 'input[name="deactivate_reason[]"]:checked' ).length;
		var maxSelectableReasons = parseInt( feedbackForm.getAttribute( 'data-max-selections' ), 10 );
		if ( isNaN( maxSelectableReasons ) || maxSelectableReasons < 1 ) {
			maxSelectableReasons = 3;
		}
		selectionCount.textContent = String( checkedReasonCount );
		if ( selectionBadge ) {
			selectionBadge.classList.toggle( 'mo_saml_fb_selection_badge--full', checkedReasonCount >= maxSelectableReasons );
		}
		var reasonCheckboxes = feedbackForm.querySelectorAll( 'input.mo_saml_fb_reason_cb' );
		var atMax = checkedReasonCount >= maxSelectableReasons;
		var i;
		for ( i = 0; i < reasonCheckboxes.length; i++ ) {
			reasonCheckboxes[ i ].disabled = atMax && ! reasonCheckboxes[ i ].checked;
		}
		var maxReasonMsg = document.getElementById( 'mo_saml_fb_max_msg' );
		if ( maxReasonMsg ) {
			maxReasonMsg.hidden = ! atMax;
		}
	}

	function moSamlFbBindModalClose( feedbackModal, closeButton ) {
		if ( removeFeedbackModalListeners ) {
			removeFeedbackModalListeners();
		}
		feedbackModalCloseHandler = function moSamlFbOnCloseClick() {
			feedbackModal.style.display = 'none';
			$( '#mo_saml_feedback_form_close' ).submit();
			if ( removeFeedbackModalListeners ) {
				removeFeedbackModalListeners();
			}
		};
		feedbackModalBackdropHandler = function moSamlFbOnBackdropClick( event ) {
			if ( event.target === feedbackModal ) {
				feedbackModal.style.display = 'none';
				if ( removeFeedbackModalListeners ) {
					removeFeedbackModalListeners();
				}
			}
		};
		closeButton.addEventListener( 'click', feedbackModalCloseHandler );
		window.addEventListener( 'click', feedbackModalBackdropHandler, true );
		removeFeedbackModalListeners = function moSamlFbRemoveModalListeners() {
			closeButton.removeEventListener( 'click', feedbackModalCloseHandler );
			window.removeEventListener( 'click', feedbackModalBackdropHandler, true );
			feedbackModalCloseHandler = null;
			feedbackModalBackdropHandler = null;
			removeFeedbackModalListeners = null;
		};
	}

	$( function () {
		$( '#mo_feedback' ).on( 'change', 'input[name="deactivate_reason[]"]', function () {
			var feedbackForm = document.getElementById( 'mo_feedback' );
			if ( ! feedbackForm ) {
				return;
			}
			var reasonRequiredNotice = document.getElementById( 'mo_saml_fb_reason_error' );
			if ( reasonRequiredNotice && ! reasonRequiredNotice.hidden && feedbackForm.querySelectorAll( 'input[name="deactivate_reason[]"]:checked' ).length > 0 ) {
				reasonRequiredNotice.hidden = true;
			}
			var maxSelectableReasons = parseInt( feedbackForm.getAttribute( 'data-max-selections' ), 10 );
			if ( isNaN( maxSelectableReasons ) || maxSelectableReasons < 1 ) {
				maxSelectableReasons = 3;
			}
			if ( this.checked && feedbackForm.querySelectorAll( 'input[name="deactivate_reason[]"]:checked' ).length > maxSelectableReasons ) {
				this.checked = false;
			}
			moSamlFbUpdateSelectionCount();
		} );
		moSamlFbUpdateSelectionCount();

		$( '#mo_feedback' ).on( 'submit', function ( event ) {
			var feedbackForm = this;
			var reasonRequiredNotice = document.getElementById( 'mo_saml_fb_reason_error' );
			if ( feedbackForm.querySelectorAll( 'input[name="deactivate_reason[]"]:checked' ).length === 0 ) {
				event.preventDefault();
				if ( reasonRequiredNotice ) {
					reasonRequiredNotice.hidden = false;
					reasonRequiredNotice.focus();
				}
				return false;
			}
			if ( reasonRequiredNotice ) {
				reasonRequiredNotice.hidden = true;
			}
		} );

		$( document ).on( 'click', 'a[id^="deactivate-miniorange-saml-20-single-sign-on"]', function ( event ) {
			event.preventDefault();
			var feedbackModal = document.getElementById( 'mo_saml_feedback_modal' );
			if ( ! feedbackModal ) {
				return false;
			}
			var closeButton = feedbackModal.getElementsByClassName( 'mo_saml_close' )[0];
			if ( ! closeButton ) {
				return false;
			}
			feedbackModal.style.display = 'block';
			var reasonRequiredNotice = document.getElementById( 'mo_saml_fb_reason_error' );
			if ( reasonRequiredNotice ) {
				reasonRequiredNotice.hidden = true;
			}
			moSamlFbUpdateSelectionCount();
			var feedbackCommentTextarea = document.querySelector( '#query_feedback' );
			if ( feedbackCommentTextarea ) {
				feedbackCommentTextarea.focus();
			} else {
				var firstReasonCheckbox = feedbackModal.querySelector( 'input[name="deactivate_reason[]"]' );
				if ( firstReasonCheckbox ) {
					firstReasonCheckbox.focus();
				}
			}
			moSamlFbBindModalClose( feedbackModal, closeButton );
			return false;
		} );

		var skipFeedbackButton = document.getElementById( 'mo_saml_fb_skip' );
		if ( skipFeedbackButton ) {
			skipFeedbackButton.addEventListener( 'click', function () {
				var skipAndCloseForm = document.getElementById( 'mo_saml_feedback_form_close' );
				if ( skipAndCloseForm ) {
					skipAndCloseForm.submit();
				}
			} );
		}

		$( document ).on( 'click', '.mo_saml_fb_edit_btn', function ( event ) {
			event.preventDefault();
			var emailInput = document.querySelector( '#query_mail' );
			if ( emailInput ) {
				emailInput.removeAttribute( 'readonly' );
				emailInput.focus();
			}
		} );
	} );
}( jQuery ) );
