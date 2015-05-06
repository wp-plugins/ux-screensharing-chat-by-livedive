<div class="wrap livedive-settings">
	<h2>LiveDive Settings 
		<a href="http://admin.livedive.co/settings?utm_source=wp-plugin&utm_medium=link&utm_campaign=dashboard-header" target="_new">Go to Account Settings</a>
		<a href="http://livedive.co/support?utm_source=wp-plugin&utm_medium=link&utm_campaign=dashboard-header" target="_new">Support</a>
	</h2>
	<div class='livedive-settings-inner'>
		<form name="livedive-settings-form" method="post" action="options.php">
			<?php settings_fields( 'livedive_options' ); ?>
			<?php do_settings_sections( 'livedive' ); ?>
			<?php submit_button(); ?>
		</form>
		<?php if ( ! $this->site_id() ) { ?>
			<div class="livedive-settings-accountsignup">
				<h3>Need an Account? Join LiveDive Now</h3>
				<p>LiveDive is the insanely easy way to talk to your users over audio or video chat and watch them use your website or web app live.</p>
				<p>You can <a href="https://livedive.co/signup?utm_source=wp-plugin&utm_medium=link&utm_campaign=settings" target="_new">Sign Up for Free in 30 seconds!</a></p>
			</div>
		<?php } ?>
	</div>
</div>