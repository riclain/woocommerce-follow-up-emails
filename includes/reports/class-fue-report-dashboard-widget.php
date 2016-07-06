<?php

/**
 * Class FUE_Report_Dashboard_Widget
 */
class FUE_Report_Dashboard_Widget {

    public static function display() {
        $wpdb = Follow_Up_Emails::instance()->wpdb;

        $stats = array(
            'total_emails_sent'         => 0,
            'emails_sent_today'         => 0,
            'emails_scheduled_total'    => 0
        );

        $today  = date( 'Y-m-d', current_time('timestamp') );
        $from   = $today .' 00:00:00';
        $to     = $today .' 23:59:59';

        $stats['total_emails_sent'] = FUE_Reports::count_emails_sent();

        $stats['emails_sent_today'] = FUE_Reports::count_emails_sent( array( $from, $to ) );

        $stats['emails_scheduled_total'] = $wpdb->get_var(
            "SELECT COUNT(*)
            FROM {$wpdb->prefix}followup_email_orders o, {$wpdb->posts} p
            WHERE o.is_sent = 0
            AND o.email_id = p.ID"
        );

        $stats['emails_sent_today_pct'] = 0;
        $stats['emails_scheduled_pct']  = 0;
        $total = $stats['emails_sent_today'] + $stats['emails_scheduled_total'];

        if ( $total > 0 ) {
            $stats['emails_sent_today_pct'] = round( ( $stats['emails_sent_today'] /  $total ) * 100, 2 );
            $stats['emails_scheduled_pct']  = 100 - $stats['emails_sent_today_pct'];
        } elseif ( $stats['emails_sent_today'] > 0 && $stats['emails_scheduled_total'] == 0 ) {
            $stats['emails_sent_today_pct'] = 100;
        }

        $stats['total_opened']       = FUE_Reports::count_opened_emails();
        $stats['total_clicks']       = FUE_Reports::count_total_email_clicks();
        $stats['total_bounces']      = FUE_Reports::count_total_bounces();
        $stats['open_pct']           = 0;
        $stats['click_pct']          = 0;
        $stats['bounce_pct']         = 0;

        if ( $stats['total_emails_sent'] > 0 ) {
            $stats['open_pct']   = round( ($stats['total_opened'] / $stats['total_emails_sent']) * 100 );
            $stats['click_pct']  = round( ($stats['total_clicks'] / $stats['total_emails_sent']) * 100 );
            $stats['bounce_pct'] = round( ($stats['total_bounces'] / $stats['total_emails_sent']) * 100 );
        }

        $stats['device_desktop']     = FUE_Reports::count_by_device_type( 'desktop' );
        $stats['device_mobile']      = FUE_Reports::count_by_device_type( 'mobile' );
        $stats['device_web']         = FUE_Reports::count_by_device_type( 'webmail' );
        $stats['device_unknown']     = FUE_Reports::count_by_device_type( '' );

        include FUE_TEMPLATES_DIR .'/dashboard-widget.php';
    }

}