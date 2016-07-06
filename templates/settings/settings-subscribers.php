<form action="admin-post.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="fue_followup_save_settings" />
    <input type="hidden" name="section" value="<?php echo $tab; ?>" />

    <h3><?php _e('Upload CSV of Emails', 'follow_up_emails'); ?></h3>

    <p><?php _e('Import your existing mailing lists and email addresses. Then go to <a href="admin.php?page=followup-emails-subscribers">Subscribers</a> to assign to lists and manage your addresses.', 'follow_up_emails'); ?></p>    

    <p class="form-field">
        <input type="file" name="csv" />
    </p>
    <p class="submit">
        <input type="submit" class="button-primary" name="upload" value="<?php _e('Upload', 'follow_up_emails'); ?>" />
    </p>

    <?php do_action('fue_settings_subscribers'); ?>

    <p class="submit">
        <input type="submit" name="save" value="<?php _e('Save Settings', 'follow_up_emails'); ?>" class="button-primary" />
    </p>

</form>