<style type="text/css">
    .red-pill {
        font-size: 10px;
        font-family: Verdana, Tahoma, Arial;
        font-weight: bold;
        display: inline-block;
        margin-left: 5px;
        background: #f00;
        color: #fff;
        padding: 0px 8px;
        border-radius: 20px;
        vertical-align: super;
    }
</style>
<form action="admin-post.php" method="post" enctype="multipart/form-data">

    <h3><?php _e('Permissions', 'follow_up_emails'); ?></h3>

    <p><?php _e('Select the User Roles that will be given permission to manage Follow-Up Emails.', 'follow_up_emails'); ?></p>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th><label for="roles"><?php _e('Roles', 'follow_up_emails'); ?></label></th>
            <td>
                <select name="roles[]" id="roles" multiple style="width: 400px;">
                    <?php
                    $roles = get_editable_roles();
                    foreach ( $roles as $key => $role ) {
                        $selected = false;
                        $readonly = '';
                        if (array_key_exists('manage_follow_up_emails', $role['capabilities'])) {
                            $selected = true;

                            if ( $key == 'administrator' ) {
                                $readonly = 'readonly';
                            }
                        }
                        echo '<option value="'. $key .'" '. selected($selected, true, false) .'>'. $role['name'] .'</option>';

                    }
                    ?>
                </select>
                <script>jQuery("#roles").select2();</script>
            </td>
        </tr>
        </tbody>
    </table>


    <h3><?php _e('Daily Emails Summary', 'follow_up_emails'); ?></h3>
    
    <p><?php _e('Turn on a daily summary of all emails sent to users, and sent the email addresses that you want to be notified with this summary.', 'follow_up_emails'); ?></p>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th><label for="enable_daily_summary"><?php _e('Enable', 'follow_up_emails'); ?></label></th>
            <td>
                <input type="checkbox" name="enable_daily_summary" id="enable_daily_summary" value="yes" <?php checked( 'yes', $enable_daily_summary ); ?> />
                <span class="description"><?php _e('Enable the Daily Email Summary', 'follow_up_emails'); ?></span>
            </td>
        </tr>
        <tr valign="top" class="summary_row">
            <th><label for="daily_emails"><?php _e('Email Address(es)', 'follow_up_emails'); ?></label></th>
            <td>
                <input type="text" name="daily_emails" id="daily_emails" value="<?php echo esc_attr( get_option('fue_daily_emails', '') ); ?>" />
                <span class="description"><?php _e('comma separated', 'follow_up_emails'); ?></span>
            </td>
        </tr>
        <tr valign="top" class="summary_row">
            <th><label for="daily_emails_time_hour"><?php _e('Preferred Time', 'follow_up_emails'); ?></label></th>
            <td>
                <?php
                $time   = get_option('fue_daily_emails_time', '12:00 AM');
                $parts  = explode(':', $time);
                $parts2 = explode(' ', $parts[1]);
                $hour   = $parts[0];
                $minute = $parts2[0];
                $ampm   = $parts2[1];
                ?>
                <select name="daily_emails_time_hour" id="daily_emails_time_hour">
                    <?php
                    for ($x = 1; $x <= 12; $x++):
                        $val = ($x >= 10) ? $x : '0'.$x;
                        ?>
                        <option value="<?php echo $val; ?>" <?php selected($hour, $val); ?>><?php echo $val; ?></option>
                    <?php endfor; ?>
                </select>

                <select name="daily_emails_time_minute" id="daily_emails_time_minute">
                    <?php
                    for ($x = 0; $x <= 55; $x+=15):
                        $val = ($x >= 10) ? $x : '0'. $x;
                        ?>
                        <option value="<?php echo $val; ?>" <?php selected($minute, $val); ?>><?php echo $val; ?></option>
                    <?php endfor; ?>
                </select>

                <select name="daily_emails_time_ampm" id="daily_emails_time_ampm">
                    <option value="AM" <?php selected($ampm, 'AM'); ?>>AM</option>
                    <option value="PM" <?php selected($ampm, 'PM'); ?>>PM</option>
                </select>
            </td>
        </tr>
        </tbody>
    </table>

    <h3><?php _e('Email Settings', 'follow_up_emails'); ?></h3>
    
    <p><?php _e('You can change the default from and reply-to name and email for all your emails. You can also customize these on every individual email.', 'follow_up_emails'); ?></p>    

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th>
                <label for="bcc"><?php _e('BCC', 'follow_up_emails'); ?></label>
                <img class="help_tip" title="<?php _e('All emails will be blind carbon copied to this address', 'follow_up_emails'); ?>" src="<?php echo FUE_TEMPLATES_URL .'/images/help.png'; ?>" width="16" height="16" />
            </th>
            <td>
                <input type="text" name="bcc" id="bcc" value="<?php echo esc_attr( $bcc ); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th>
                <label for="from_name"><?php _e('From/Reply-To Name', 'follow_up_emails'); ?></label>
                <img class="help_tip" title="<?php _e('The name that your emails will come from and replied to', 'follow_up_emails'); ?>" src="<?php echo FUE_TEMPLATES_URL .'/images/help.png'; ?>" width="16" height="16" />
            </th>
            <td>
                <input type="text" name="from_name" id="from_name" value="<?php echo esc_attr( $from_name ); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th>
                <label for="from_email"><?php _e('From/Reply-To Email', 'follow_up_emails'); ?></label>
                <img class="help_tip" title="<?php _e('The email address that your emails will come from and replied to', 'follow_up_emails'); ?>" src="<?php echo FUE_TEMPLATES_URL .'/images/help.png'; ?>" width="16" height="16" />
            </th>
            <td>
                <input type="text" name="from_email" id="from_email" value="<?php echo esc_attr( $from ); ?>" />
            </td>
        </tr>
        </tbody>
    </table>

    <h3><?php _e('Bounce Settings', 'follow_up_emails'); ?></h3>
    
    <p><?php _e('To which email address should all of your bounced emails go? No premium version needed.', 'follow_up_emails'); ?></p>

    <table id="emails_form" class="form-table">
        <tbody>
        <tr valign="top">
            <th class="titledesc">
                <label for="bounce_email"><?php _e('Bounce Address', 'follow_up_emails'); ?></label>
                <img class="help_tip" title="<?php _e('Undelivered emails will be sent to this address', 'follow_up_emails'); ?>" src="<?php echo FUE_TEMPLATES_URL .'/images/help.png'; ?>" width="16" height="16" />
            </th>
            <td>
                <input type="text" name="bounce[email]" id="bounce_email" value="<?php echo esc_attr( $bounce['email'] ); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th class="titledesc">
                <label for="bounce_handling"><?php _e('Automatic bounce handling', 'follow_up_emails'); ?></label>
            </th>
            <td>
                <input type="checkbox" name="bounce[handle_bounces]" id="bounce_handling" value="1" <?php checked( 1, $bounce['handle_bounces'] ); ?> />
            </td>
        </tr>
        <tr valign="top" class="bounce_enabled">
            <td colspan="2">
                <?php _e('To enable the automatic handling of bounced emails, enter the POP3 account of the bounce address above.', 'follow_up_emails'); ?>
            </td>
        </tr>
        <tr valign="top" class="bounce_enabled">
            <th class="titledesc">
                <label for="bounce_server"><?php _e('Server Address', 'follow_up_emails'); ?></label>
            </th>
            <td>
                <input type="text" name="bounce[server]" id="bounce_server" value="<?php echo esc_attr( $bounce['server'] ); ?>" />
            </td>
        </tr>
        <tr valign="top" class="bounce_enabled">
            <th class="titledesc">
                <label for="bounce_port"><?php _e('Port', 'follow_up_emails'); ?></label>
            </th>
            <td>
                <input type="text" name="bounce[port]" id="bounce_port" size="3" value="<?php echo esc_attr( $bounce['port'] ); ?>" />
            </td>
        </tr>
        <tr valign="top" class="bounce_enabled">
            <th class="titledesc">
                <label for="bounce_ssl"><?php _e('Use SSL', 'follow_up_emails'); ?></label>
            </th>
            <td>
                <input type="checkbox" name="bounce[ssl]" id="bounce_ssl" value="1" <?php checked( 1, $bounce['ssl'] ); ?> />
            </td>
        </tr>
        <tr valign="top" class="bounce_enabled">
            <th class="titledesc">
                <label for="bounce_username"><?php _e('Username', 'follow_up_emails'); ?></label>
            </th>
            <td>
                <input type="text" name="bounce[username]" id="bounce_username" value="<?php echo esc_attr( $bounce['username'] ); ?>" />
            </td>
        </tr>
        <tr valign="top" class="bounce_enabled">
            <th class="titledesc">
                <label for="bounce_password"><?php _e('Password', 'follow_up_emails'); ?></label>
            </th>
            <td>
                <input type="password" name="bounce[password]" id="bounce_password" value="<?php echo esc_attr( $bounce['password'] ); ?>" />
            </td>
        </tr>
        <tr valign="top" class="bounce_enabled">
            <th class="titledesc">
                <label for="bounce_delete_messages"><?php _e('Delete Messages', 'follow_up_emails'); ?></label>
            </th>
            <td>
                <input type="checkbox" name="bounce[delete_messages]" id="bounce_delete_messages" value="1" <?php checked( 1, $bounce['delete_messages'] ); ?> />
                <span class="description"><?php _e('Delete emails to keep the mailbox clean', 'follow_up_emails'); ?></span>
            </td>
        </tr>
        <tr valign="top" class="bounce_enabled">
            <th class="titledesc">
                <label for="bounce_soft_bounce_resend_interval"><?php _e('Soft Bounces', 'follow_up_emails'); ?></label>
            </th>
            <td>
                <?php
                printf(
                    __('Attemp to resend up to %s times with an interval of %s minutes between each send before marking as a Hard Bounce.', 'follow_up_emails'),
                    '<input type="number" name="bounce[soft_bounce_resend_limit]" id="bounce_soft_bounce_resend_limit" style="width: 50px;" value="'. $bounce['soft_bounce_resend_limit'] .'" />',
                    '<input type="number" name="bounce[soft_bounce_resend_interval]" id="bounce_soft_bounce_resend_interval" style="width: 50px;" value="'. $bounce['soft_bounce_resend_interval'] .'" />'
                );
                ?>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="submit" style="width: auto;">
        <input class="button button-secondary test-bounce" type="button" value="<?php _e('Test Bounce Settings', 'follow_up_emails'); ?>" />
        <div class="spinner test-bounce-spinner" style="float: none;"></div>
        <div class="test-bounce-status" style="display: none;"><?php _e('Sending test email...', 'follow_up_emails'); ?></div>
    </div>

    <h3><?php _e('Backup &amp; Restore', 'follow_up_emails'); ?></h3>

    <p>
        <?php _e('Backup your emails using the WordPress import and export functionality.', 'follow_up_emails' ); ?>
        <br />
        <a href="<?php echo admin_url('import.php?import=wordpress'); ?>"><?php _e('Import', 'follow_up_email'); ?></a> |
        <a href="<?php echo admin_url('export.php'); ?>"><?php _e('Export', 'follow_up_emails'); ?></a>
    </p>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <td colspan="2">
                <a class="button" href="<?php echo wp_nonce_url('admin-post.php?action=fue_backup_settings', 'fue_backup'); ?>"><?php _e('Download a Backup of the Settings', 'follow_up_emails'); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <td colspan="2">
                <strong><?php _e('Restore Backup', 'follow_up_emails'); ?></strong>
                <table class="form-table">
                    <tbody>
                    <tr valign="top">
                        <td width="200"><label for="json_file"><?php _e('Emails JSON', 'follow_up_emails'); ?></label></td>
                        <td><input type="file" name="emails_json" id="emails_json" /></td>
                    </tr>
                    <tr valign="top">
                        <td width="200"><label for="emails_file"><?php _e('Emails CSV from pre-4.0 installs only', 'follow_up_emails'); ?></label></td>
                        <td><input type="file" name="emails_file" id="emails_file" /></td>
                    </tr>
                    <tr valign="top">
                        <td><label for="settings_file"><?php _e('Settings CSV from all versions', 'follow_up_emails'); ?></label></td>
                        <td><input type="file" name="settings_file" id="settings_file" /></td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>

    <h3><?php _e('Follow-Up RESTful API', 'follow_up_emails'); ?></h3>

    <p>
        <input type="checkbox" name="api_enabled" id="api_enabled" value="yes" <?php checked( 'yes', $api_enabled ); ?> />
        <label for="api_enabled"><?php _e('Enable the Follow-up REST API <a href="admin.php?page=followup-emails-settings&tab=documentation">Learn More</a>', 'follow_up_emails'); ?></label>
    </p>

    <h3><?php _e('Manual Emails Sending Schedule', 'follow_up_emails'); ?></h3>
    <p><strong><?php _e('Sending manual emails at to large numbers of recipients could cause mail server issues with your host. For example, Gmail limits you to 500 sends per day to limit spam. <a href="http://www.75nineteen.com/how-many-emails-can-i-send-at-once-with-follow-up-emails/">Read here for more</a>.', 'follow_up_emails'); ?></strong></p>

    <p>
        <input type="checkbox" name="email_batch_enabled" value="1" <?php checked( 1, $email_batches ); ?> />
        <?php
        printf(
            __('Send manual emails in batches of %s emails every %s minutes'),
            '<input type="text" name="emails_per_batch" value="'. $emails_per_batch .'" size="3" />',
            '<input type="text" name="email_batch_interval" value="'. $email_batch_interval .'" size="2" />'
        );
        ?>
    </p>

    <h3><?php _e('Action Scheduler Logging', 'follow_up_emails'); ?></h3>
    <p><strong><?php _e('The Action Scheduler, by default, logs completed actions to the comments for debugging purposes. Some users have inquired, but this is not a bug. You can turn off, and/or delete the actions log with the settings below. <a href="admin.php?page=followup-emails-settings&tab=documentation">Learn More</a>', 'follow_up_emails'); ?></strong></p>
    <p>
        <input type="checkbox" name="action_scheduler_disable_logging" id="action_scheduler_disable_logging" value="1" <?php checked( 1, $disable_logging ); ?> />
        <label for="action_scheduler_disable_logging"><?php _e( 'Disable email logging', 'follow_up_emails' ) ?></label>
    </p>

    <p>
        <input type="checkbox" name="action_scheduler_delete_logs" id="action_scheduler_delete_logs" value="1" />
        <label for="action_scheduler_delete_logs"><?php _e( 'Delete existing logs', 'follow_up_emails' ) ?></label>
    </p>

    <h3><?php _e('Remove Old Daily Summary Data', 'follow_up_emails'); ?></h3>

    <p>
        <input class="button clean_daily_summary" type="button" value="<?php _e('Delete Old Summary Data', 'follow_up_emails'); ?>" />

        <span id="clean_daily_summary_status" style="display: none;">
            <img id="clean_daily_summary_loader" src="<?php echo FUE_TEMPLATES_URL .'/images/ajax-loader.gif'; ?>" />
            <span id="clean_daily_summary_message"><?php _e('Please wait...', 'follow_up_emails'); ?></span>
        </span>
    </p>
    <script>
        (function($) {
            var fue_summary_posts = 0;
            var fue_summary_deleted = 0;
            $(".clean_daily_summary").click(function() {
                $(this).attr("disabled", true);
                $("#clean_daily_summary_status").show();

                fue_init_delete_daily_summary();
            });

            function fue_init_delete_daily_summary() {
                $.post(ajaxurl, {action: "fue_count_daily_summary_posts"}, function(resp) {
                    resp = $.parseJSON(resp);

                    fue_summary_posts = resp.count;
                    fue_delete_daily_summary();
                });
            }

            function fue_delete_daily_summary() {
                $.post(ajaxurl, {action: "fue_delete_daily_summary"}, function(resp) {
                    resp = $.parseJSON(resp);

                    if ( resp.count && resp.count > 0 ) {
                        var remaining = resp.count;
                        fue_summary_deleted = fue_summary_posts - remaining;
                        percent = Math.round( (fue_summary_deleted / fue_summary_posts) * 100 );
                        $("#clean_daily_summary_message").html("Please wait... ("+ percent +"%)");

                        fue_delete_daily_summary();
                    } else {
                        // done
                        $("#clean_daily_summary_message").html("Completed!");
                        $("#clean_daily_summary_loader").hide();
                    }
                });
            }
        })(jQuery);
    </script>

	<!-- Future location of reporting data improvement settings -->

    <?php do_action( 'fue_settings_system' ); ?>
    <?php do_action( 'fue_settings_crm' ); ?>
    <?php do_action( 'fue_settings_email' ); ?>

    <p class="submit">
        <input type="hidden" name="action" value="fue_followup_save_settings" />
        <input type="hidden" name="section" value="<?php echo $tab; ?>" />
        <input type="submit" name="save" value="<?php _e('Save Settings', 'follow_up_emails'); ?>" class="button-primary" />
    </p>

</form>
<script>
    jQuery(document).ready(function($) {
        $("#enable_daily_summary").change(function() {
            if ( $(this).is(":checked") ) {
                $(".summary_row").show();
            } else {
                $(".summary_row").hide();
            }
        }).change();
    });
</script>