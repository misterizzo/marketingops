<?php

namespace SkyVerge\WooCommerce\Memberships\Emails\Traits;

use Automattic\WooCommerce\Internal\Admin\EmailPreview\EmailPreview;

/**
 * Adds improved support for the experimental "Email Improvements" feature, by populating any missing properties
 * with default/placeholder values.
 *
 * {@see EmailPreview::set_email_type()}
 */
trait HasEmailPreviewValuesTrait
{
	/**
	 * Sets "Preview" values on the email object.
	 */
	abstract public function setPreviewValues() : void;
}
