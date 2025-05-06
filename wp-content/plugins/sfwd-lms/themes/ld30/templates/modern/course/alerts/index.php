<?php
/**
 * View: Course Header - Alerts.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Template $this Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Template\Template;

?>
<div class="ld-alerts">
	<?php $this->template( 'modern/course/alerts/certificate' ); ?>

	<?php $this->template( 'modern/course/alerts/progress' ); ?>
</div>
