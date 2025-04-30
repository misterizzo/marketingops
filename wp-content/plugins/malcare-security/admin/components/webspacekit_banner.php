<?php
$isAirliftPresent = false;
$message = 'Protect your site with automatic scans & firewall';
if (class_exists('ALAccount') &&
		class_exists('ALWPSettings') &&
		!ALAccount::isConfigured(new ALWPSettings())) {
	$isAirliftPresent = true;
	$message = 'Speed up and protect your website with one-click scans and firewall';
}
$hideDismissButton = false;
if (array_key_exists('page', $_REQUEST) && $_REQUEST['page'] == $this->bvinfo->plugname) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$hideDismissButton = true;
}
?>
<div class="wsk-wrapper">
	<?php if (!$hideDismissButton) : ?>
			<form method="POST">
				<?php wp_nonce_field('dismiss_wsk_banner'); ?>
				<button type="submit" class="notice-dismiss" name="dismiss_wsk_banner"></button>
			</form>
	<?php endif; ?>
	<div class="row wsk-banner">
		<div class="logo-header">
			<?php if ($isAirliftPresent) : ?>
				<img src="<?php echo esc_url(plugins_url('/../../img/wsk-airlift-malcare.png', __FILE__)); ?>" alt="WebSpaceKit" loading="lazy">
			<?php else: ?>
				<img src="<?php echo esc_url(plugins_url('/../../img/wsk-malcare.png', __FILE__)); ?>" alt="WebSpaceKit" loading="lazy">
			<?php endif; ?>
		</div>

		<div class="wsk-body">
			<div class="left-section">
				<div class="img-container">
					<?php if ($isAirliftPresent) : ?>
						<img src="<?php echo esc_url(plugins_url('/../../img/speedometer.svg', __FILE__)); ?>" alt="" loading="lazy">
					<?php endif; ?>
					<img src="<?php echo esc_url(plugins_url('/../../img/security.svg', __FILE__)); ?>" alt="" loading="lazy">
				</div>
				<h3><?php echo esc_html($message); ?></h3>
				<div class="wsk-grad-btn-wrapper">
					<form action="<?php echo esc_url($this->bvinfo->appUrl()); ?>/plugin/webspacekit_signup" onsubmit="document.getElementById('get-started').disabled = true;"  method="post" name="signup">
						<input type='hidden' name='bvsrc' value='wpplugin'/>
						<input type='hidden' name='origin' value='webspacekit'/>
						<input type='hidden' name='is_malcare_active' value='true'/>
						<input type='hidden' name='is_airlift_active' value='<?php echo $isAirliftPresent ? 'true' : 'false'; ?>'/>
						<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already Escaped
							echo $this->siteInfoTags();
						?>
						<input type="hidden" id="email" name="email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
						<input type="hidden" name="consent" value="1">
						<button class="wsk-btn" type="submit">Get Started</button>
					</form>
				</div>
			</div>

			<div class="right-section">
				<img src="<?php echo esc_url(plugins_url('/../../img/secure.svg', __FILE__)); ?>" alt="">
			</div>
		</div>
	</div>
</div>