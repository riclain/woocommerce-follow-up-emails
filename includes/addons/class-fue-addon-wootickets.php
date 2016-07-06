<?php

/**
 * Class FUE_Addon_Wootickets
 */
class FUE_Addon_Wootickets {

    /**
     * class constructor
     */
    public function __construct() {
        if (self::is_installed()) {
            add_filter( 'fue_email_types', array($this, 'register_email_type') );

            // trigger fields
            add_filter( 'fue_email_form_trigger_fields', array($this, 'register_trigger_fields') );

            // email list - import order action link
            add_action( 'fue_import_orders_supported_types', array($this, 'add_import_support') );
            add_filter( 'fue_wc_get_orders_for_email', array($this, 'get_orders_for_email'), 10, 2 );
            add_filter( 'fue_wc_import_insert', array($this, 'modify_insert_send_date'), 10, 2 );

            // saving email
            add_filter( 'fue_save_email_data', array($this, 'apply_ticket_product_id'), 10, 3 );

            add_filter( 'fue_trigger_str', array($this, 'trigger_string'), 10, 2 );
            add_action( 'fue_email_form_scripts', array($this, 'email_form_script') );

            add_action( 'fue_email_variables_list', array($this, 'add_variables') );

            add_action( 'fue_email_form_after_interval', array($this, 'after_interval') );

            add_action( 'fue_before_variable_replacements', array($this, 'register_variable_replacements'), 10, 4 );

            add_action( 'woocommerce_order_status_completed', array($this, 'set_reminders'), 20 );
            add_action( 'init', array($this, 'hook_statuses') );
        }
    }

    /**
     * Check if plugins is active
     * @return bool
     */
    public static function is_installed() {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        if ( is_multisite() ) {
            return is_plugin_active_for_network( 'wootickets/wootickets.php' );
        } else {
            return is_plugin_active( 'wootickets/wootickets.php' );
        }
    }

    /**
     * Register custom email type
     *
     * @param array $types
     * @return array
     */
    public function register_email_type( $types ) {
        $triggers = array(
            'before_tribe_event_starts' => __('before event starts', 'wc_followup_emails'),
            'after_tribe_event_ends'    => __('after event ends', 'wc_followup_emails')
        );

        $statuses = Follow_Up_Emails::instance()->fue_wc->get_order_statuses();

        foreach ( $statuses as $i => $status ) {
            $triggers[ 'ticket_status_'. $status ] = sprintf( __('after ticket status: %s', 'follow_up_emails'), $status );
        }

        $props = array(
            'label'                 => __('WooTickets', 'follow_up_emails'),
            'singular_label'        => __('WooTickets', 'follow_up_emails'),
            'triggers'              => $triggers,
            'durations'             => Follow_Up_Emails::$durations,
            'long_description'      => __('Create emails based upon the event/ticket status for The Events Calendar.<br />Get going faster, and save time, with a starter pack of Follow-ups from Outbound Commerce.', 'follow_up_emails'),
            'short_description'     => __('Create emails based upon the event/ticket status for The Events Calendar.<br />Get going faster, and save time, with a starter pack of Follow-ups from Outbound Commerce.', 'follow_up_emails')
        );
        $types[] = new FUE_Email_Type( 'wootickets', $props );

        return $types;
    }

    /**
     * Trigger string for custom events
     *
     * @param string $string
     * @param FUE_Email $email
     * @return string
     */
    public function trigger_string( $string, $email ) {
        if ( $email->trigger == 'before_tribe_event_starts' || $email->trigger == 'after_tribe_event_ends' ) {
            $type = $email->get_email_type();
            $string = sprintf(
                __('%d %s %s'),
                $email->interval,
                Follow_Up_Emails::get_duration($email->duration),
                $type->get_trigger_name( $email->trigger )
            );
        }
        return $string;
    }

    /**
     * JS for the email form
     */
    public function email_form_script() {
        wp_enqueue_script( 'fue-form-the-events-calendar', FUE_TEMPLATES_URL .'/js/email-form-the-events-calendar.js' );
    }

    /**
     * Add ticket selector to the Trigger tab
     *
     * @param FUE_Email $email
     */
    public function register_trigger_fields( $email ) {
        if ( $email->type == 'wootickets' || $email->type == 'twitter' ) {
            $wootickets_type = (empty($email->meta['wootickets_type'])) ? 'all' : $email->meta['wootickets_type'];
            $categories = get_terms( 'product_cat', array('hide_empty' => false) );

            if ( !empty( $email->product_id ) ) {
                $wootickets_type = 'products';
            }

            include FUE_TEMPLATES_DIR .'/email-form/the-events-calendar/event-selector.php';
        }
    }

    /**
     * Declare order importing support for wootickets emails
     * @param array $types
     * @return array
     */
    public function add_import_support( $types ) {
        $types[] = 'wootickets';

        return $types;
    }

    /**
     * Get orders that match the $email's criteria
     * @param array     $order_ids Matching Order IDs
     * @param FUE_Email $email
     * @return array
     */
    public function get_orders_for_email( $order_ids, $email ) {
        if ( $email->type != 'wootickets' ) {
            return $order_ids;
        }

        $wpdb = Follow_Up_Emails::instance()->wpdb;

        if ( $email->product_id ) {
            $order_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT order_id
                FROM {$wpdb->prefix}followup_order_items
                WHERE product_id = %d
                OR variation_id = %d",
                $email->product_id,
                $email->product_id
            ));
        } elseif ( $email->category_id ) {
            $order_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT order_id
                FROM {$wpdb->prefix}followup_order_categories
                WHERE category_id = %d",
                $email->category_id
            ));
        } else {
            $ticket_ids = $wpdb->get_col(
                "SELECT DISTINCT post_id
                FROM {$wpdb->postmeta}
                WHERE meta_key = '_tribe_wooticket_for_event'
                AND meta_value > 0"
            );

            if ( !empty( $ticket_ids ) ) {
                $ids_csv = implode( ',', array_map( 'absint', $ticket_ids ) );

                $order_ids = $wpdb->get_col(
                    "SELECT DISTINCT order_id
                    FROM {$wpdb->prefix}followup_order_items
                    WHERE product_id IN ( {$ids_csv} )
                    OR variation_id IN ( {$ids_csv} )"
                );
            }
        }

        if ( empty( $order_ids ) ) {
            return array();
        }

        return array( $email->id => $order_ids );
    }

    /**
     * Change the send date of the email relative to the event's start and end dates
     * @param array $insert
     * @param FUE_Email $email
     * @return array
     */
    public function modify_insert_send_date( $insert, $email ) {
        if ( $email->type != 'wootickets' ) {
            return $insert;
        }

        $now        = current_time('timestamp');
        $ticket_id  = $this->get_ticket_id_from_order( $insert['order_id'], $email );

        if ( !$ticket_id ) {
            return $insert;
        }

        // if $item is a ticket, load the event where the ticket is attached to
        $event_id = get_post_meta( $ticket_id, '_tribe_wooticket_for_event', true );

        $interval   = (int)$email->interval_num;
        $add        = FUE_Sending_Scheduler::get_time_to_add( $interval, $email->interval_duration );

        if ( $email->trigger == 'before_tribe_event_starts' ) {
            $start = get_post_meta( $event_id, '_EventStartDate', true );

            if ( empty($start) ) {
                return $insert;
            }
            $start = strtotime($start);

            // check if a limit is in place
            $email_meta = maybe_unserialize( $email->meta );
            if ( isset($email_meta['tribe_limit'], $email_meta['tribe_limit_days']) && !empty($email_meta['tribe_limit_days']) ) {
                $days = ($start - $now) / 86400;

                if ( $days <= $email_meta['tribe_limit_days'] ) {
                    // $days is within limit - skip
                    return false;
                }
            }

            $send_on    = $start - $add;

            // if send_on is in the past, do not queue it
            if ( $now > $send_on ) {
                return false;
            }

            $insert['send_on'] = $send_on;
        } else {
            $end        = get_post_meta( $event_id, '_EventEndDate', true );

            if ( empty($end) ) {
                return $insert;
            }

            $end        = strtotime($end);
            $send_on    = $end + $add;

            // if send_on is in the past, do not queue it
            if ( $now > $send_on ) {
                return false;
            }

            $insert['send_on'] = $send_on;
        }

        return $insert;
    }

    /**
     * Pull the ticket ID from the order that triggered the FUE_Email
     *
     * @param int $order_id
     * @param FUE_Email $email
     * @return int
     */
    public function get_ticket_id_from_order( $order_id, $email ) {
        $ticket_id = 0;

        if ( $email->product_id ) {
            $ticket_id = $email->product_id;
        } else {
            $order = WC_FUE_Compatibility::wc_get_order( $order_id );
            $items = $order->get_items();

            foreach ( $items as $item ) {
                $event_id = get_post_meta( $item['product_id'], '_tribe_wooticket_for_event', true );

                if ( empty( $event_id ) ) {
                    continue;
                }

                if ( $email->category_id ) {
                    // get the first ticket product that matches the category id
                    $categories = wp_get_object_terms( $item['product_id'], 'product_cat' );

                    if ( !is_wp_error( $categories ) ) {
                        foreach ( $categories as $category ) {
                            if ( $category->term_id == $email->category_id ) {
                                $ticket_id = $item['product_id'];
                                break 2;
                            }
                        }
                    }
                } else {
                    // get the first ticket product
                    $ticket_id = $item['product_id'];
                    break;
                }
            }
        }

        return apply_filters( 'fue_get_ticket_id_from_order', $ticket_id, $order_id, $email );
    }

    /**
     * Apply the value of 'ticket_product_id' to the 'product_id' field
     *
     * @param array     $data
     * @param int       $post_id
     * @param WP_Post   $post
     * @return array $data
     */
    public function apply_ticket_product_id( $data, $post_id, $post ) {
        if ( $data['type'] == 'wootickets' || $data['type'] == 'twitter' ) {

            if ( $_POST['meta']['wootickets_type'] == 'all' ) {
                $data['product_id']     = 0;
                $data['category_id']    = 0;
            } else {
                if ( !empty( $_POST['ticket_product_id'] ) ) {
                    $data['product_id']     = $_POST['ticket_product_id'];
                    $data['category_id']    = 0;
                } elseif ( !empty( $_POST['ticket_category_id'] ) ) {
                    $data['category_id']    = $_POST['ticket_category_id'];
                    $data['product_id']     = 0;
                }
            }

        }

        return $data;
    }

    /**
     * Available email variables
     */
    public function add_variables( $email ) {
        global $woocommerce;
        
        if ( $email->type == 'wootickets' ):
        ?>
        <li class="var hideable var_events_calendar var_event_name"><strong>{event_name}</strong> <img class="help_tip" title="<?php _e('The name of the event', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <li class="var hideable var_events_calendar var_event_link"><strong>{event_link}</strong> <img class="help_tip" title="<?php _e('The name of the event with a link to the event page', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <li class="var hideable var_events_calendar var_event_url"><strong>{event_url}</strong> <img class="help_tip" title="<?php _e('The URL of the event', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <li class="var hideable var_events_calendar var_event_location"><strong>{event_location}</strong> <img class="help_tip" title="<?php _e('The name and address of the venue', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <li class="var hideable var_events_calendar var_event_organizer"><strong>{event_organizer}</strong> <img class="help_tip" title="<?php _e('The name of the event organizer', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <li class="var hideable var_events_calendar var_event_start_datetime"><strong>{event_start_datetime}</strong> <img class="help_tip" title="<?php _e('The start date/time of the event', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <li class="var hideable var_events_calendar var_event_end_datetime"><strong>{event_end_datetime}</strong> <img class="help_tip" title="<?php _e('The end date/time of the event', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <li class="var hideable var_events_calendar var_event_ticket_name"><strong>{ticket_name}</strong> <img class="help_tip" title="<?php _e('The name of the ticket', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <li class="var hideable var_events_calendar var_event_ticket_description"><strong>{ticket_description}</strong> <img class="help_tip" title="<?php _e('The description of the ticket', 'wc_followup_emails'); ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" width="16" height="16" /></li>
        <?php
        endif;
    }

    /**
     * Addition option for ticket emails
     * @param array $defaults
     */
    public function after_interval( $defaults ) {
        if ( $defaults['type'] != 'wootickets') {
            return;
        }

        $days = (isset($defaults['meta']['tribe_limit_days']) ) ? $defaults['meta']['tribe_limit_days'] : '';
        ?>
        <div class="field tribe_limit_tr">
            <label for="meta_tribe_limit">
                <input type="checkbox" name="meta[tribe_limit]" id="meta_tribe_limit" value="yes" <?php if (isset($defaults['meta']['tribe_limit']) && $defaults['meta']['tribe_limit'] == 'yes') echo 'checked'; ?> style="vertical-align: baseline;" />
                <?php printf( __('Do not send email if a customer books a ticket %s days before the event starts.', 'wc_followup_emails'), '<input type="text" name="meta[tribe_limit_days]" size="2" value="'. $days .'" placeholder="5" />'); ?>
            </label>
        </div>
        <?php
    }

    /**
     * Register subscription variables to be replaced
     *
     * @param FUE_Sending_Email_Variables   $var
     * @param array                 $email_data
     * @param FUE_Email             $email
     * @param object                $queue_item
     */
    public function register_variable_replacements( $var, $email_data, $email, $queue_item ) {
        $variables = array(
            'event_name', 'event_start_datetime', 'event_end_datetime', 'event_link', 'event_url',
            'event_location', 'event_organizer', 'ticket_name', 'ticket_description'
        );

        // use test data if the test flag is set
        if ( isset( $email_data['test'] ) && $email_data['test'] ) {
            $variables = $this->add_test_variable_replacements( $variables, $email_data, $email );
        } else {
            $variables = $this->add_variable_replacements( $variables, $email_data, $queue_item, $email );
        }

        $var->register( $variables );
    }

    /**
     * Scan through the keys of $variables and apply the replacement if one is found
     * @param array     $variables
     * @param array     $email_data
     * @param object    $queue_item
     * @param FUE_Email $email
     * @return array
     */
    protected function add_variable_replacements( $variables, $email_data, $queue_item, $email ) {
        $ticket_id = $queue_item->product_id;

        if (! $ticket_id )
            return $variables;

        $event_id       = get_post_meta( $ticket_id, '_tribe_wooticket_for_event', true );
        $woo_tickets    = TribeWooTickets::get_instance();
        $ticket         = $woo_tickets->get_ticket( $event_id, $ticket_id );

        // Ticket Vars
        $ticket_name    = $ticket->name;
        $ticket_desc    = $ticket->description;

        // Event Vars
        $event_name     = get_the_title( $event_id );
        $event_link     = '<a href="'. get_permalink( $event_id ) .'">'. $event_name .'</a>';
        $event_url      = get_permalink( $event_id );
        $event_location = '';
        $event_org      = '';
        $event_start    = '';
        $event_end      = '';

        $venue_id = get_post_meta( $event_id, '_EventVenueID', true );

        if (! empty($venue_id) ) {
            $venue_name     = get_post_meta( $venue_id, '_VenueVenue', true );
            $venue_address  = get_post_meta( $venue_id, '_VenueAddress', true );
            $venue_city     = get_post_meta( $venue_id, '_VenueCity', true );
            $venue_country  = get_post_meta( $venue_id, '_VenueCountry', true );
            $venue_state    = get_post_meta( $venue_id, '_VenueStateProvince', true );
            $venue_zip      = get_post_meta( $venue_id, '_VenueZip', true );

            $event_location = sprintf( '<b>%s</b><br/>%s<br/>%s, %s<br/>%s %s', $venue_name, $venue_address, $venue_city, $venue_state, $venue_country, $venue_zip );
        }

        $org_id = get_post_meta( $event_id, '_EventOrganizerID', true );

        if (! empty($org_id) ) {
            $event_org = get_post_meta( $org_id, '_OrganizerOrganizer', true );
        }

        $start_stamp    = strtotime( get_post_meta( $event_id, '_EventStartDate', true ) );
        if ( $start_stamp ) {
            $event_start    = date( get_option('date_format') .' '. get_option('time_format'), $start_stamp );
        }

        $end_stamp      = strtotime( get_post_meta( $event_id, '_EventEndDate', true ) );
        if ( $end_stamp ) {
            $event_end    = date( get_option('date_format') .' '. get_option('time_format'), $end_stamp );
        }

        $variables['event_name']            = $event_name;
        $variables['event_start_datetime']  = $event_start;
        $variables['event_end_datetime']    = $event_end;
        $variables['event_link']            = $event_link;
        $variables['event_url']             = $event_url;
        $variables['event_location']        = $event_location;
        $variables['event_organizer']       = $event_org;
        $variables['ticket_name']           = $ticket_name;
        $variables['ticket_description']    = $ticket_desc;

        return $variables;
    }

    /**
     * Add variable replacements for test emails
     *
     * @param array     $variables
     * @param array     $email_data
     * @param FUE_Email $email
     *
     * @return array
     */
    protected  function add_test_variable_replacements( $variables, $email_data, $email ) {
        $now            = current_time('timestamp');
        $event_name     = 'Event Name';
        $event_start    = date( get_option('date_format') .' '. get_option('time_format'), $now + 86400 );
        $event_end      = date( get_option('date_format') .' '. get_option('time_format'), $now + (86400*2) );
        $event_link     = '<a href="'. site_url() .'">Event Name</a>';
        $event_url      = site_url();
        $event_location = 'The Venue';
        $event_org      = 'Event Organizer';
        $ticket_name    = 'Ticket A Upper Box B';
        $ticket_desc    = 'The ticket\'s description';

        $variables['event_name']            = $event_name;
        $variables['event_start_datetime']  = $event_start;
        $variables['event_end_datetime']    = $event_end;
        $variables['event_link']            = $event_link;
        $variables['event_url']             = $event_url;
        $variables['event_location']        = $event_location;
        $variables['event_organizer']       = $event_org;
        $variables['ticket_name']           = $ticket_name;
        $variables['ticket_description']    = $ticket_desc;

        return $variables;
    }

    /**
     * Queue emails after an order is marked as completed
     * @param int $order_id
     */
    public function set_reminders( $order_id ) {
        $queued = array();

        // load reminder emails
        $emails = fue_get_emails( array('wootickets', 'twitter'), FUE_Email::STATUS_ACTIVE, array(
            'meta_query' => array(
                array(
                    'key'       => '_interval_type',
                    'value'     => array( 'before_tribe_event_starts', 'after_tribe_event_ends' ),
                    'compare'   => 'IN'
                )
            )
        ) );

        $tickets = array();

        if ( empty($emails) ) return;

        $has_tickets = get_post_meta( $order_id, '_tribe_has_tickets', true );

        $order  = WC_FUE_Compatibility::wc_get_order( $order_id );
        $items  = $order->get_items();

        foreach ( $items as $item ) {
            $ticket_id = (isset($item['id'])) ? $item['id'] : $item['product_id'];

            // if $item is a ticket, load the event where the ticket is attached to
            $event_id = get_post_meta( $ticket_id, '_tribe_wooticket_for_event', true );

            if (! $event_id ) continue;

            if (! in_array($ticket_id, $tickets) ) {
                $tickets[] = $ticket_id;
            }
        }

        $now = current_time('timestamp');
        foreach ( $emails as $email ) {
            $interval   = (int)$email->interval_num;
            $add        = FUE_Sending_Scheduler::get_time_to_add( $interval, $email->interval_duration );

            foreach ( $tickets as $ticket_id ) {

                // if this email is for a specific ticket, make sure the IDs match
                if ( !empty($email->product_id) && $email->product_id != $ticket_id ) {
                    continue;
                }

                // check for category matching
                if ( !empty( $email->category_id ) ) {
                    $ticket_terms       = get_the_terms( $ticket_id, 'product_cat' );
                    $product_categories = array();

                    if ( $ticket_terms && !is_wp_error( $ticket_terms ) ) {
                        foreach ( $ticket_terms as $ticket_term ) {
                            $product_categories[ $ticket_term->term_id ] = $ticket_term->name;
                        }
                    }

                    if ( !array_key_exists( $email->category_id, $product_categories ) ) {
                        continue;
                    }
                }

                $event_id = get_post_meta( $ticket_id, '_tribe_wooticket_for_event', true );

                if ( $email->interval_type == 'before_tribe_event_starts' ) {
                    $start = get_post_meta( $event_id, '_EventStartDate', true );

                    if ( empty($start) ) continue;
                    $start = strtotime($start);

                    // check if a limit is in place
                    $email_meta = maybe_unserialize( $email->meta );
                    if ( isset($email_meta['tribe_limit'], $email_meta['tribe_limit_days']) && !empty($email_meta['tribe_limit_days']) ) {
                        $days = ($start - $now) / 86400;

                        if ( $days <= $email_meta['tribe_limit_days'] ) {
                            // $days is within limit - skip
                            continue;
                        }
                    }

                    $send_on    = $start - $add;

                    // if send_on is in the past, do not queue it
                    if ( $now > $send_on ) continue;
                } else {
                    $end        = get_post_meta( $event_id, '_EventEndDate', true );

                    if ( empty($end) ) continue;

                    $end        = strtotime($end);
                    $send_on    = $end + $add;

                    // if send_on is in the past, do not queue it
                    if ( $now > $send_on ) continue;
                }

                $insert = array(
                    'user_id'       => $order->user_id,
                    'order_id'      => $order_id,
                    'product_id'    => $ticket_id,
                    'email_id'      => $email->id,
                    'send_on'       => $send_on
                );
                if ( !is_wp_error( FUE_Sending_Scheduler::queue_email( $insert, $email ) ) ) {
                    $queued[] = $insert;
                }
            }
        }

        Follow_Up_Emails::instance()->fue_wc->wc_scheduler->add_order_notes_to_queued_emails( $queued );
    }

    /**
     * Register order statuses to trigger follow-up emails
     */
    public function hook_statuses() {
        $statuses = Follow_Up_Emails::instance()->fue_wc->get_order_statuses();

        foreach ( $statuses as $status ) {
            add_action('woocommerce_order_status_'. $status, array($this, 'ticket_status_updated'), 100);
        }

    }

    /**
     * Queue matching wootickets emails
     * @param int $order_id
     */
    public function ticket_status_updated( $order_id ) {
        $order          = WC_FUE_Compatibility::wc_get_order( $order_id );
        $queued         = array();
        $triggers       = array( 'ticket_status_' . $order->get_status() );

        $args = array(
            'meta_query'    => array(
                array(
                    'key'       => '_interval_type',
                    'value'     => $triggers,
                    'compare'   => 'IN'
                )
            )
        );

        $emails = fue_get_emails( array('wootickets', 'twitter'), FUE_Email::STATUS_ACTIVE, $args );

        $tickets = array();

        if ( empty($emails) ) {
            return;
        }

        $items = $order->get_items();

        foreach ( $items as $item ) {
            $ticket_id = (isset($item['id'])) ? $item['id'] : $item['product_id'];

            // if $item is a ticket, load the event where the ticket is attached to
            $event_id = get_post_meta( $ticket_id, '_tribe_wooticket_for_event', true );

            if (! $event_id ) {
                continue;
            }

            if (! in_array($ticket_id, $tickets) ) {
                $tickets[] = $ticket_id;
            }
        }

        $now = current_time('timestamp');
        foreach ( $emails as $email ) {
            $interval   = (int)$email->interval_num;
            $add        = FUE_Sending_Scheduler::get_time_to_add( $interval, $email->interval_duration );

            foreach ( $tickets as $ticket_id ) {

                // if this email is for a specific ticket, make sure the IDs match
                if ( !empty($email->product_id) && $email->product_id != $ticket_id ) {
                    continue;
                }

                // check for category matching
                if ( !empty( $email->category_id ) ) {
                    $ticket_terms       = get_the_terms( $ticket_id, 'product_cat' );
                    $product_categories = array();

                    if ( $ticket_terms && !is_wp_error( $ticket_terms ) ) {
                        foreach ( $ticket_terms as $ticket_term ) {
                            $product_categories[ $ticket_term->term_id ] = $ticket_term->name;
                        }
                    }

                    if ( !array_key_exists( $email->category_id, $product_categories ) ) {
                        continue;
                    }
                }

                $insert = array(
                    'user_id'       => $order->user_id,
                    'order_id'      => $order_id,
                    'product_id'    => $ticket_id,
                    'email_id'      => $email->id
                );
                if ( !is_wp_error( FUE_Sending_Scheduler::queue_email( $insert, $email ) ) ) {
                    $queued[] = $insert;
                }
            }
        }

        Follow_Up_Emails::instance()->fue_wc->wc_scheduler->add_order_notes_to_queued_emails( $queued );

    }

    /**
     * Get ticket products in the given order
     *
     * @param int $order_id
     * @return array
     */
    protected function get_tickets_from_order( $order_id ) {
        $wpdb = Follow_Up_Emails::instance()->wpdb;

        $ticket_ids = array();
        $products = $wpdb->get_results( $wpdb->prepare(
            "SELECT product_id, variation_id
            FROM {$wpdb->prefix}followup_order_items
            WHERE order_id = %d",
            $order_id
        ), ARRAY_A );

        foreach ( $products as $product ) {
            if ( get_post_meta( $product['product_id'], '_tribe_wooticket_for_event', true ) ) {
                $ticket_ids[] = $product['product_id'];
            }

            if ( $product['variation_id'] && get_post_meta( $product['variation_id'], '_tribe_wooticket_for_event', true ) ) {
                $ticket_ids[] = $product['variation_id'];
            }
        }

        return array_unique( $ticket_ids );
    }

    /**
     * Get an array of Category IDs included in the given $order
     * @param int|WC_Order  $order
     * @return array
     */
    protected function get_categories_from_order( $order ) {
        $wpdb = Follow_Up_Emails::instance()->wpdb;

        if ( is_numeric( $order ) ) {
            $order = WC_FUE_Compatibility::wc_get_order( $order );
        }

        if ( 1 != get_post_meta( $order->id, '_fue_recorded', true ) ) {
            FUE_Addon_Woocommerce::record_order( $order );
        }

        $category_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT category_id
            FROM {$wpdb->prefix}followup_order_categories
            WHERE order_id = %d",
            $order->id
        ) );

        return array_unique( $category_ids );

    }

}

$GLOBALS['fue_wootickets'] = new FUE_Addon_Wootickets();
