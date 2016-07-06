<div class="main">
    <ul id="fue-donuts">
        <li>
            <div
                id="sent_total_gauge"
                class="circle"
                data-dimension="200"
                data-info="<?php _e('Emails Sent', 'follow_up_emails'); ?>"
                data-text="<?php echo number_format($stats['total_emails_sent']); ?>"
                data-percent="100"
                data-width="2"
                data-fontsize="25"
            />
        </li>
        <li>
            <div
                id="sent_today_gauge"
                class="circle"
                data-dimension="200"
                data-info="<?php _e('Sent Today', 'follow_up_emails'); ?>"
                data-text="<?php echo number_format($stats['emails_sent_today']); ?>"
                data-percent="<?php echo $stats['emails_sent_today_pct']; ?>"
                data-width="2"
                data-fontsize="25"
            />
        </li>
        <li>
            <div
                id="scheduled_emails_gauge"
                class="circle"
                data-dimension="200"
                data-info="<?php _e('Scheduled', 'follow_up_emails'); ?>"
                data-text="<?php echo $stats['emails_scheduled_total']; ?>"
                data-percent="<?php echo $stats['emails_scheduled_pct']; ?>"
                data-width="2"
                data-fontsize="25"
            />
        </li>
        <li>
            <div
                id="opens_gauge"
                class="circle"
                data-dimension="200"
                data-info="<?php _e('Opens', 'follow_up_emails'); ?>"
                data-text="<?php echo number_format($stats['total_opened']); ?>"
                data-percent="<?php echo $stats['open_pct']; ?>"
                data-width="2"
                data-fontsize="25"
                />
        </li>
    </ul>
    <div class="clear"></div>

</div>