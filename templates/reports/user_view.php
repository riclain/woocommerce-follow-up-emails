<?php
/**
 * @var array $reports
 * @var Wpdb $wpdb
 */

if ( empty($reports) ) {
    $heading = sprintf(__('Report for %s', 'wc_folloup_emails'), $email);
} else {
    $report = $reports[0];
    $heading = sprintf(__('Report for %s (%s)', 'wc_folloup_emails'), $report->customer_name, $report->email_address);
}

if ( $user_id ):
?>
<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
    <a href="<?php echo get_edit_user_link( $user_id ); ?>" class="nav-tab"><?php _e('Personal Options', 'follow_up_emails'); ?></a>
    <a href="#" class="nav-tab nav-tab-active"><?php _e('Customer Data', 'follow_up_emails'); ?></a>
</h2>
<?php endif; ?>

<div id="fue_user_report">
    <div class="col-left">
	    <div class="wrap">
        <h3><?php echo $heading; ?></h3>
        <table class="widefat fixed striped posts">
            <thead>
            <tr>
                <th scope="col" id="email_name" class="manage-column column-email_name" style=""><?php _e('Email', 'follow_up_emails'); ?></th>
                <th scope="col" id="trigger" class="manage-column column-trigger" style=""><?php _e('Trigger', 'follow_up_emails'); ?></th>
                <th scope="col" id="opened" class="manage-column column-opened"><?php _e('Opened', 'follow_up_emails'); ?></th>
                <th scope="col" id="clicked" class="manage-column column-clicked"><?php _e('Clicked', 'follow_up_emails'); ?></th>
                <th scope="col" id="date_sent" class="manage-column column-date_sent" style=""><?php _e('Date Sent', 'follow_up_emails'); ?></th>
                <th scope="col" id="order" class="manage-column column-order" style="">&nbsp;</th>
            </tr>
            </thead>
            <tbody id="the_list">
            <?php
            if ( empty($reports) ):
                ?>
                <tr scope="row">
                    <th colspan="6"><?php _e('No reports available', 'follow_up_emails'); ?></th>
                </tr>
            <?php
            else:
                foreach ($reports as $report):
                    $opens = FUE_Reports::count_opened_emails( array('email_order_id' => $report->email_order_id) );
                    $clicks= FUE_Reports::count_total_email_clicks( array('email_order_id' => $report->email_order_id) );
                    ?>
                    <tr scope="row">
                        <td class="post-title column-title"><?php echo $report->email_name; ?></td>
                        <td><?php echo $report->email_trigger; ?></td>
                        <td><?php echo ($opens > 0) ? __('<span class="dashicons dashicons-visibility" style="color:#7AD03A;"></span> Yes', 'follow_up_emails') : __('<span class="dashicons dashicons-hidden" style="color:#EEE;"></span> No', 'follow_up_emails'); ?></td>
                        <td><?php echo ($clicks > 0) ? __('<span class="dashicons dashicons-carrot" style="color:#7AD03A;"></span> Yes', 'follow_up_emails') : __('<span class="dashicons dashicons-carrot" style="color:#EEE;"></span> No', 'follow_up_emails'); ?></td>
                        <td><?php echo date( get_option('date_format') .' '. get_option('time_format') , strtotime($report->date_sent)); ?></td>
                        <td>
                            <?php
                            if ($report->order_id != 0) {
                                echo '<a class="button" href="post.php?post='. $report->order_id .'&action=edit">View Order</a>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                <?php
                endforeach;
            endif;
            ?>
            </tbody>
        </table>
	    </div>

        <h3><?php _e('Scheduled Emails', 'follow_up_emails'); ?></h3>

        <table class="widefat fixed striped posts">
            <thead>
            <tr>
                <th scope="col" id="order" class="manage-column column-product" style="" width="60"><?php _e('Order', 'wc_folloup_emails'); ?></th>
                <th scope="col" id="email_name" class="manage-column column-email_name" style=""><?php _e('Email', 'wc_folloup_emails'); ?></th>
                <th scope="col" id="status" class="manage-column column-status" style=""><?php _e('Status', 'follow_up_emails'); ?></th>
                <th scope="col" id="date_sent" class="manage-column column-date_sent" style="" width="180"><?php _e('Scheduled', 'follow_up_emails'); ?></th>
            </tr>
            </thead>
            <tbody id="the_list">
            <?php if ( empty($queue) ): ?>
                <tr>
                    <td colspan="4"><?php _e('No emails scheduled', 'follow_up_emails'); ?></td>
                </tr>
            <?php
            else:
                $email_rows     = array();
                $date_format    = get_option('date_format') .' '. get_option('time_format');
                foreach ( $queue as $row ):
                    $item = new FUE_Sending_Queue_Item( $row->id );

                    if (! isset($email_rows[$row->email_id]) ) {
                        $email_row = new FUE_Email( $row->email_id );
                        $email_rows[$row->email_id] = $email_row;
                    }

                    $email_name = $email_rows[$row->email_id]->name;
                    $email = $email_rows[$row->email_id];

                    if (! $email->exists() ) {
                        continue;
                    }

                    ?>
                    <tr>
                        <td>
                            <?php
                            if ( $row->order_id > 0 && ($order = WC_FUE_Compatibility::wc_get_order($row->order_id)) ) {
                                echo '<a href="post.php?post='. $row->order_id .'&action=edit">'. $order->get_order_number() .'</a>';
                            } else {
                                echo '-';
                            }

                            if ( $row->product_id > 0 ) {
                                echo ' for <a href="post.php?post='. $row->product_id .'&action=edit">'. get_the_title($row->product_id) .'</a>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            printf(
                                __('<a href="%s">#%d %s</a><br/><small>(%s)</small>', 'follow_up_emails'),
                                admin_url('post.php?post='. $item->email_id .'&action=edit'),
                                $item->email_id,
                                $email->name,
                                $email->get_trigger_string()
                            );
                            ?>
                        </td>
                        <td class="status">
                            <?php
                            if ( $row->status == 1 ) {
                                echo __('Queued', 'follow_up_emails');
                                echo '<br/><small><a href="#" class="queue-toggle" data-status="queued" data-id="'. $row->id .'">'. __('Do not send', 'follow_up_emails') .'</a></small>';
                            } else {
                                echo __('Suspended', 'follow_up_emails');
                                echo '<br/><small><a href="#" class="queue-toggle" data-status="paused" data-id="'. $row->id .'">'. __('Re-enable', 'follow_up_emails') .'</a></small>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo date( $date_format, $row->send_on ); ?>
                        </td>
                    </tr>
                <?php
                endforeach;
            endif;
            ?>
            </tbody>
        </table>

        <h3><?php _e('Unsubscribes', 'follow_up_emails'); ?></h3>

        <table class="widefat fixed striped posts">
            <thead>
            <tr>
                <th scope="col" id="exclude_user_email" class="manage-column column-user_email" style=""><?php _e('Email', 'follow_up_emails'); ?></th>
                <th scope="col" id="exclude_order" class="manage-column column-order" style=""><?php _e('Order', 'wc_folloup_emails'); ?></th>
                <th scope="col" id="exclude_date_added" class="manage-column column-date_added" style=""><?php _e('Date Added', 'follow_up_emails'); ?></th>
            </tr>
            </thead>
            <tbody id="the_list">
            <?php if ( empty($excludes) ): ?>
                <tr>
                    <td colspan="3"><?php _e('No unsubscribes found', 'follow_up_emails'); ?></td>
                </tr>
            <?php
            else:
                $date_format    = get_option('date_format') .' '. get_option('time_format');
                foreach ( $excludes as $row ):
                    $order_str = ( empty( $row->order_id ) ) ? __('All emails', 'follow_up_emails') : apply_filters( 'woocommerce_order_number', $row->order_id );
                    ?>
                    <tr>
                        <td><?php echo $row->email; ?></td>
                        <td><?php echo $order_str; ?></td>
                        <td>
                            <?php echo date( $date_format, strtotime($row->date_added) ); ?>
                        </td>
                    </tr>
                <?php
                endforeach;
            endif;
            ?>
            </tbody>
        </table>

        <h3><?php _e('Order History', 'follow_up_emails'); ?></h3>

        <table class="widefat fixed striped posts">
            <thead>
            <tr>
                <th scope="col" class="manage-column" style=""><?php _e( 'Order (status)', 'follow_up_emails' ); ?></th>
                <th scope="col" class="manage-column" style=""><?php _e( 'Date', 'follow_up_emails' ); ?></th>
                <th scope="col" class="manage-column" style=""><?php _e( 'Total', 'follow_up_emails' ); ?></th>
                <th scope="col" class="manage-column" style=""></th>
            </tr>
            </thead>
            <tbody>
            <?php
            // Setup important variables
            $lifetime_total = 0;
            $count          = 1;

            if ( $user_id ) {
                $args = array(
                    'numberposts' => -1,
                    'meta_key'    => '_customer_user',
                    'meta_value'  => absint( $user_id ),
                    'post_type'   => 'shop_order',
                    'post_status' => function_exists( 'wc_get_order_statuses' ) ? array_keys( wc_get_order_statuses() ) : array( 'publish' ),
                    'order'       => 'ASC',
                );
            } else {
                $args = array(
                    'numberposts' => -1,
                    'meta_key'    => '_billing_email',
                    'meta_value'  => $email,
                    'post_type'   => 'shop_order',
                    'post_status' => function_exists( 'wc_get_order_statuses' ) ? array_keys( wc_get_order_statuses() ) : array( 'publish' ),
                    'order'       => 'ASC',
                );
            }

            $orders = get_posts( $args );

            if ( ! empty( $orders ) ) {
                foreach ( $orders as $key => $purchase ) {
                    $purchase_order = new WC_Order( $purchase );

                    // If order isn't cancelled, refunded, failed or pending, include its total
                    if ( in_array( $purchase->post_status, array( 'wc-completed', 'wc-processing', 'wc-on-hold' ) ) ) {
                        $lifetime_total += $purchase_order->order_total;
                    }
                    ?>
                    <tr>
                        <td>
                            <?php
                            $url = admin_url('post.php?post='. $purchase_order->id .'&action=edit');
                            printf( __('<a href="%s">%s</a> <br /> (%s)', 'follow_up_emails' ), $url, $purchase_order->get_order_number(), $purchase_order->status );
                            ?>
                        </td>
                        <td><?php echo date( get_option('date_format') .' '. get_option('time_format') , strtotime( $purchase_order->order_date ) ); ?></td>
                        <td><?php echo $purchase_order->get_formatted_order_total(); ?></td>
                        <td>
	                        <?php
                            if ($report->order_id != 0) {
                                printf( __('<a class="button" href="%s">View Order</a>', 'follow_up_emails' ), $url );
                            } else {
                                echo '-';
                            }
                            ?>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>

        <p><?php printf( __( '<strong>Lifetime Value:</strong> %s', 'follow_up_emails' ), '<span style="color:#7EB03B; font-size:1.2em; font-weight:bold;">' . woocommerce_price( $lifetime_total ) . '</span>' ); ?></p>

    </div>
    <div class="col-right">
        <div class="postbox" id="fue_customer_followups">
            <h3 class="handle"><?php _e('Schedule Emails', 'follow_up_emails'); ?></h3>
            <div class="inside">
                <p id="schedule_email_error"></p>
                <p id="schedule_email_success"></p>
                <p>
                    <label for="email"><?php _e('Select Email', 'follow_up_emails'); ?></label>
                    <br/>
                    <select id="email" class="full">
                        <option value=""></option>
                        <?php
                        $emails = fue_get_emails( 'manual', FUE_Email::STATUS_ACTIVE );

                        foreach ( $emails as $email ):
                            ?>
                            <option value="<?php echo $email->id; ?>"><?php echo $email->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label for="send_schedule"><?php _e('Send', 'follow_up_emails'); ?></label>
                    <select id="send_schedule">
                        <option value="now"><?php _e('now', 'follow_up_emails'); ?></option>
                        <option value="later"><?php _e('later', 'follow_up_emails'); ?></option>
                    </select>
                </p>
                <p class="send-later">
                    <input type="text" id="send_date" class="datepicker" placeholder="mm/dd/yyyy" />

                    <?php _e('at', 'follow_up_emails'); ?>
                    <select id="send_time_hour">
                        <?php
                        for ( $x = 1; $x <= 12; $x++ ):
                            $y = ($x >= 10) ? $x : '0'. $x;
                        ?>
                        <option value="<?php echo $x; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    :
                    <select id="send_time_minute">
                        <?php for ( $x = 0; $x < 60; $x+=5 ):
                            $y = ($x >= 10) ? $x : '0'. $x;
                        ?>
                            <option value="<?php echo $x; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    <select id="send_time_ampm">
                        <option value="am"><?php _e('AM', 'follow_up_emails'); ?></option>
                        <option value="pm"><?php _e('PM', 'follow_up_emails'); ?></option>
                    </select>
                </p>
                <p class="send-again-p">
                    <label>
                        <input type="checkbox" id="send_again" />
                        <?php _e('Send again', 'follow_up_emails'); ?>
                    </label>

                    <span class="send-again">
                        <?php _e('in', 'follow_up_emails'); ?>
                        <input type="number" min="1" id="send_again_value" />
                        <select id="send_again_interval">
                            <option value="minutes"><?php _e('minutes', 'follow_up_emails'); ?></option>
                            <option value="hours"><?php _e('hours', 'follow_up_emails'); ?></option>
                            <option value="days"><?php _e('days', 'follow_up_emails'); ?></option>
                            <option value="weeks"><?php _e('weeks', 'follow_up_emails'); ?></option>
                            <option value="months"><?php _e('months', 'follow_up_emails'); ?></option>
                            <option value="years"><?php _e('years', 'follow_up_emails'); ?></option>
                        </select>
                    </span>
                </p>
                <p class="separated">
                    <a class="schedule-email button-primary" href="#"><?php _e('Schedule Email', 'follow_up_emails'); ?></a>
                </p>
            </div>
        </div>

        <div class="postbox" id="fue_customer_reminders">
            <h3 class="handle"><?php _e('Reminders', 'follow_up_emails'); ?></h3>
            <div class="inside">
                <ul class="customer-reminders">
                    <?php if ( empty( $reminders ) ): ?>
                        <li><?php _e('There are no reminders yet', 'follow_up_emails'); ?></li>
                    <?php
                    else:
                        $date_format    = get_option( 'date_format' );
                        $time_format    = get_option( 'time_format' );
                        foreach( $reminders as $reminder ):
                            $author     = new WP_User( $reminder->meta['author'] );
                            $assignee   = new WP_User( $reminder->meta['assignee'] );
                            $date   = date( $date_format, $reminder->send_on );
                            $time   = date( $time_format, $reminder->send_on );

                            if ( $assignee->ID == $author->ID ) {
                                $meta = sprintf( __('added by %s', 'follow_up_emails'), $author->display_name );
                            } else {
                                $meta = sprintf( __('assigned to %s by %s', 'follow_up_emails'), $assignee->display_name, $author->display_name );
                            }
                            ?>
                            <li class="reminder" rel="<?php echo esc_attr( $reminder->id ); ?>">
                                <div class="reminder-content">
                                    <p>
                                        <?php printf( __('Reminder set for %s at %s', 'follow_up_emails'), $date, $time ); ?>
                                    </p>
                                    <?php if ( !empty( $reminder->meta['note'] ) ): ?>
                                        <pre><?php echo wp_kses_post( $reminder->meta['note'] ); ?></pre>
                                    <?php endif; ?>
                                </div>
                                <p class="meta">
                                    <?php echo $meta; ?>
                                    <a class="delete_reminder" href="#"><?php _e('Delete', 'follow_up_emails'); ?></a>
                                </p>
                            </li>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
                <div class="add-reminder">
                    <h4><?php _e('Add Reminder', 'follow_up_emails'); ?></h4>

                    <p>
                        <textarea id="reminder_note" placeholder="<?php _e('Reminder notes', 'follow_up_emails'); ?>"></textarea>
                    </p>

                    <p>
                        <label>
                            <input type="checkbox" id="assign_reminder" />
                            <?php _e( 'Assign reminder', 'follow_up_emails' ); ?>
                        </label>
                    </p>

                    <p id="assignee_block">
                        <input
                            type="hidden"
                            data-allow_clear="true"
                            value=""
                            data-selected=""
                            data-placeholder="<?php _e('Search for a user...', 'follow_up_emails'); ?>"
                            name="assignee"
                            id="assignee"
                            class="user-search-select"
                            tabindex="-1"
                            title=""
                            >
                    </p>

                    <p class="separated">
                        <?php _e('Send in', 'follow_up_emails'); ?>
                        <input type="number" min="1" step="1" id="reminder_interval_days" value="1" size="3" />
                        <?php _e('day(s)', 'follow_up_emails'); ?>

                        <a class="set_interval_reminder button" href="#"><?php _e('Set Reminder', 'follow_up_emails'); ?></a>
                    </p>
                    
                    <p class="separated">
                        <?php _e('Send on', 'follow_up_emails'); ?>
                        <br/>
                        <input type="text" id="reminder_date" value="" class="datepicker" />
                        @
                        <select id="reminder_hour">
                            <option value="01">01</option>
                            <option value="02">02</option>
                            <option value="03">03</option>
                            <option value="04">04</option>
                            <option value="05">05</option>
                            <option value="06">06</option>
                            <option value="07">07</option>
                            <option value="08">08</option>
                            <option value="09">09</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                        <select id="reminder_minute">
                            <option value="00">00</option>
                            <option value="05">05</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="30">30</option>
                            <option value="35">35</option>
                            <option value="40">40</option>
                            <option value="45">45</option>
                            <option value="50">50</option>
                            <option value="55">55</option>
                        </select>
                        <select id="reminder_ampm">
                            <option value="am"><?php _e('AM', 'follow_up_emails'); ?></option>
                            <option value="pm"><?php _e('PM', 'follow_up_emails'); ?></option>
                        </select>

                        <a class="set_date_reminder button" href="#"><?php _e('Set Reminder', 'follow_up_emails'); ?></a>
                    </p>
                    <br class="clear"/>
                </div>

            </div>
        </div>

        <div class="postbox" id="fue_customer_notes">
            <h3 class="handle"><?php _e('Customer Notes', 'follow_up_emails'); ?></h3>
            <div class="inside">
                <ul class="customer-notes">
                    <?php if ( empty( $notes ) ): ?>
                    <li><?php _e('There are no notes yet', 'follow_up_emails'); ?></li>
                    <?php
                    else:
                        $datetime_format = get_option( 'date_format' ) .' '. get_option( 'time_format' );
                        foreach( $notes as $note ):
                            $author = new WP_User( $note->author_id );
                            $pretty_date = date_i18n( $datetime_format, strtotime( $note->date_added ) );
                    ?>
                    <li class="note" rel="<?php echo esc_attr( $note->id ); ?>">
                        <div class="note-content">
                            <p><?php echo wp_kses_post( $note->note ); ?></p>
                        </div>
                        <p class="meta">
                            <?php printf( 'added by %s on <abbr title="%s" class="exact-date">%s</abbr>', $author->display_name, $note->date_added, $pretty_date ); ?>
                            <a class="delete_note" href="#"><?php _e('Delete note', 'follow_up_emails'); ?></a>
                        </p>
                    </li>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
                <div class="add-note">
                    <h4><?php _e('Add Note', 'follow_up_emails'); ?></h4>

                    <p>
                        <textarea rows="5" cols="20" class="input-text" id="add_customer_note" name="customer_note" type="text"></textarea>
                    </p>

                    <p>
                        <input type="hidden" id="customer_id" value="<?php echo esc_attr( $customer->id ); ?>" />
                        <input type="hidden" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
                        <a class="add_note button" href="#"><?php _e('Add', 'follow_up_emails'); ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>