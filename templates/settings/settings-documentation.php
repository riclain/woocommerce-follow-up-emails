<h2>Thank you for marketing with Follow-ups</h2>
	<p>You are currently running Follow-ups <em><strong>version <?php printf( __( '%s', 'follow-up-emails' ), FUE_VERSION ); ?></strong></em></p>

<h3>Documentation and Plugin Compatibility</h3>
<p>Follow-ups only require WordPress to be installed (version 4.0 or higher preferred). Optionally, and likely, you will also have <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> (free) or <a href="http://www.woothemes.com/products/sensei/" target="_blank">Sensei</a> (paid) installed.</p>

	<p><a class="button button-primary" href="http://docs.woothemes.com/document/automated-follow-up-emails/" target="_blank">Read the documentation now</a></p>
	
<h3>Getting Started</h3>
	<h4>Here are some of the most used features of Follow-ups. Remember, Follow-ups are much more that just emails.</h4>
	
<ul class="fue-templates">
	<li>
		<div>
			<h3><span class="dashicons dashicons-admin-settings"></span> General Settings</h3>
				<p>Review your settings. These settings include everything from marketing permissions, bounce setting, and from/reply-to addresses. You can also backup and import settings here as well, and enable the REST API.</p>
				<a class="button" href="<?php echo admin_url('admin.php?page=followup-emails-settings&tab=system'); ?>">Configure Settings</a> <a class="button-secondary button-primary" href="http://docs.woothemes.com/document/automated-follow-up-emails-docs/#section-14" target="_blank">Settings Docs</a>
		</div>
	</li>
	<li>
		<div>
			<h3><span class="dashicons dashicons-admin-network"></span> DKIM &amp; SPF</h3>	
				<p>Set up DKIM &amp; SPF to reduce spam. These two records in your DNS improve email deliverability and reduce spam. The DKIM check verifies that the message is signed and associated with the correct domain, SPF checks that your email comes from authorized servers.</p>
				<a class="button" href="<?php echo admin_url('admin.php?page=followup-emails-settings&tab=auth'); ?>">Setup DKIM &amp; SPF</a> <a class="button-secondary button-primary" href="http://docs.woothemes.com/document/automated-follow-up-emails-docs/#dkim-spf" target="_blank">DKIM &amp; SPF Docs</a>
		</div>
	</li>
	<li>
		<div>
			<h3><span class="dashicons dashicons-welcome-add-page"></span> Create Follow-ups</h3>
				<p>Create your first follow-up message. Create follow-up campaigns to target newsletter subscribers, users of specific lists, buyers of specific products, bookings, or subscriptions, and even tweet your customers.</p>
				<a class="button" href="<?php echo admin_url('post-new.php?post_type=follow_up_email'); ?>">Create a Follow-up</a> <a class="button-secondary button-primary" href="http://docs.woothemes.com/document/automated-follow-up-emails-docs/#section-7" target="_blank">Creation Docs</a>
		</div>
	</li>
	<li>
		<div>
			<h3><span class="dashicons dashicons-awards"></span> Customer Insights</h3>
				<p>Want to see details on your customers? How many emails they are opening? What they are spending? Set up reminders, create tasks and follow-ups for you and your team, and set manual emails campaigns? You can do all that with Follow-ups.</p>
				<a class="button" href="<?php echo admin_url('admin.php?page=followup-emails-reports-customers'); ?>">See Customer Insights</a>
		</div>
	</li>
	<li>
		<div>
			<h3><span class="dashicons dashicons-id-alt"></span> Custom Templates</h3>	
				<p>Create your own custom templates. Don't be stuck with the stock WooCommerce template. Create one or many custom email templates for your Follow-up Emails.</p>
				<a class="button" href="<?php echo admin_url('admin.php?page=followup-emails-templates'); ?>">Manage Templates</a> <a class="button-secondary button-primary" href="http://docs.woothemes.com/document/automated-follow-up-emails-docs/custom-email-templates/" target="_blank">Template Docs</a>
		</div>
	</li>
	<li>
		<div>
			<h3><span class="dashicons dashicons-groups"></span> Subscriber Lists &amp; Newsletters</h3>
				<p>Have existing subscribers? Manage and import your lists to get the most out of Follow-ups. Even segment your buyers into one or more lists to further target them with emails.</p>
				<a class="button" href="<?php echo admin_url('admin.php?page=followup-emails-subscribers'); ?>">Manage Lists</a> <a class="button-secondary button-primary" href="http://docs.woothemes.com/document/automated-follow-up-emails-docs/#section-13" target="_blank">Lists Docs</a>
		</div>
	</li>
</ul>	
	
<h3>Advanced Documentation</h3>
<p>Follow-ups is an advanced plugin with many features. Some of these features are documented below: the Follow-ups API, the email prioritization rules, and the scheduling component.</p> 

<ul class="fue-templates">
	<li>
		<div>
			<h3><span class="dashicons dashicons-twitter"></span> Twitter Follow-ups</h3>
				<p>Communication is changing. It is no longer all about email despite email still having the highest response rates. We've added the ability to Tweet your customers after their purchases. Continue to engage in a greater capacity on another medium your customers expect.</p>
				<a class="button button-primary" href="<?php echo admin_url('admin.php?page=followup-emails-settings&tab=integration'); ?>"><?php _e('Setup Twitter Now', 'follow_up_emails'); ?></a>
		</div>
	</li>
	<li>
		<div>
			<h3><span class="dashicons dashicons-networking"></span> Follow-ups API</h3>
				<p>Introduced in Follow-ups 4.0, the REST API allows store follow-ups to be created, read, updated, and deleted using the JSON format, and your own programmatic skills.</p>
				<a class="button button-primary" href="https://github.com/75nineteen/follow-up-email-docs/blob/master/fue-api.md" target="_blank"><?php _e('Follow-ups API', 'follow_up_emails'); ?></a>
		</div>
	</li>
	<li>
		<div>
			<h3><span class="dashicons dashicons-forms"></span> Email Prioritization Algorithm</h3>
				<p>Ever wondered how or why emails get sent in what order, or with which priority? You can now read a brief overview of how emails are prioritized when getting scheduled for delivery.</p>	
				<a class="button button-primary" href="http://docs.woothemes.com/document/automated-follow-up-emails-docs/faq/email-decision-tree/" target="_blank"><?php _e('Email Prioritization Rules', 'follow_up_emails'); ?></a>
		</div>
	</li>
</ul>
	
<h3>Need More Support?</h3>
<p>After <a href="http://docs.woothemes.com/document/automated-follow-up-emails/" target="_blank">reading the documentation</a>, as a WooThemes customer you can <a href="http://support.woothemes.com">use their helpdesk</a>.</p>
<p>Before asking for help we recommend checking the system status page to identify any problems with your configuration - like an out-of-date plugin, old PHP version, or out-of-date template files.</p>
<p><a href="admin.php?page=wc-status" class="button button-primary">System Status</a> <a href="http://support.woothemes.com" class="button">WooThemes Customer Support</a></p>
	
<h3>Third-Party Plugin Support</h3>
<p>Follow-ups are integrated with, including having support for, many of WooThemes supported extensions.</p>

<?php do_action( 'fue_settings_documentation' ); ?>