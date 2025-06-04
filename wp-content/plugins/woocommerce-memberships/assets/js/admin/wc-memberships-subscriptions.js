/**
 * The below code handles automatically updating the "Edit User Membership" UI when the linked subscription ID is changed.
 *
 * @var wcMembershipsSubscriptions
 */
document.addEventListener('DOMContentLoaded', () => {
	const subscriptionIdChangedNotice = document.getElementById('wc-memberships-subscription-id-changed-notice');
	const subscriptionLinkFields = document.querySelectorAll('.wc-memberships-edit-subscription-link-field');

	if (subscriptionLinkFields) {
		// hide all link fields initially
		subscriptionLinkFields.forEach(field => {
			field.style.display = 'none';
		});
	}

	// toggle visibility when link is clicked
	const subscriptionToggleLinks = document.querySelectorAll('.js-edit-subscription-link-toggle');
	if (subscriptionToggleLinks && subscriptionLinkFields) {
		subscriptionToggleLinks.forEach(toggleLink => {
			toggleLink.addEventListener('click', e => {
				e.preventDefault();

				subscriptionLinkFields.forEach(subscriptionField => {
					toggleElementVisibility(subscriptionField);
				});
			})
		})
	}

	// handle dropdown selection events -- these seem to only work with jQuery for some reason, hence the random switch away from vanilla JS
	// when a new subscription ID is selected we want to automatically update some membership values
	const subscriptionIdField = jQuery('#_subscription_id');
	subscriptionIdField.on('select2:select', function(e) {
		updateMembershipValues(e.params.data.id)
	});

	function toggleElementVisibility(element) {
		const currentDisplay = element.style.display || '';
		element.style.display = (currentDisplay === 'none') ? '' : 'none';
	}

	// updates the DOM with membership changes after the subscription ID switch
	function updateMembershipValues(newSubscriptionId) {
		const data = {
			action: 'wc_memberships_subscription_id_changed',
			newSubscriptionId: newSubscriptionId,
			currentStatus: document.querySelector('.post_status_field #post_status').value, // we have to be specific here because there are two "post_status" IDs on this page :facepalm:
			currentEndDate: document.getElementById('_end_date').value,
			subscriptionPlanId: document.getElementById('post_parent').value,
			nonce: wcMembershipsSubscriptions.subscriptionIdUpdatedNonce
		}

		jQuery.post(wcMembershipsSubscriptions.ajaxUrl, data, ( response ) => {
			if (response.success) {
				if (response.data.message) {
					subscriptionIdChangedNotice.innerHTML = response.data.message;
				}
				if (response.data.changes && response.data.changes.status) {
					updateMembershipStatus(response.data.changes.status);
				}
				if (response.data.changes && response.data.changes.hasOwnProperty('endDate')) {
					updateMembershipEndDate(response.data.changes.endDate);
				}
			} else {
				console.log('Error adjusting membership data after subscription change', response.data);
			}
		})
	}

	function updateMembershipStatus(newStatus) {
		const statusDropdown = jQuery('.post_status_field #post_status'); // we have to be specific here because there are two "post_status" IDs on this page :facepalm:
		statusDropdown.val(newStatus);
		statusDropdown.trigger('change');
	}

	function updateMembershipEndDate(newEndDate) {
		document.getElementById('_end_date').value = newEndDate;
	}
});

