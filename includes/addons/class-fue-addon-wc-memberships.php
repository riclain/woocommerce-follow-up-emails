<?php

/**
 * Class FUE_Addon_WC_Memberships
 */
class FUE_Addon_WC_Memberships {

    /**
     * class constructor
     */
    public function __construct() {

        // manual emails
        add_action( 'fue_manual_types', array($this, 'manual_types') );
        add_action( 'fue_manual_type_actions', array($this, 'manual_type_actions') );
        add_filter( 'fue_manual_email_recipients', array($this, 'manual_email_recipients'), 10, 2 );
        add_action( 'fue_manual_js', array($this, 'manual_form_script') );

    }

    /**
     * Check if the WC Memberships plugin is installed and active
     * @return bool
     */
    public static function is_installed() {
        return function_exists('init_woocommerce_memberships');
    }

    /**
     * Memberships option for manual emails
     */
    public function manual_types() {
        ?><option value="wc_memberships"><?php _e('Membership Plan Members', 'follow_up_emails'); ?></option><?php
    }

    /**
     * Fields to show if WC Memberships is selected
     */
    public function manual_type_actions() {
        $plans = wc_memberships_get_membership_plans();
        ?>
        <div class="send-type-wc-memberships send-type-div">
            <select id="membership_plan" name="membership_plan" class="select2" style="width: 400px;">
                <?php foreach ( $plans as $plan ): ?>
                    <option value="<?php echo $plan->id; ?>"><?php esc_attr_e( $plan->name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php
    }

    /**
     * Get all members of the selected plan
     *
     * @param array $recipients
     * @param array $post
     *
     * @return array
     */
    public function manual_email_recipients( $recipients, $post ) {
        global $wpdb;

        if ( $post['send_type'] == 'wc_memberships' ) {
            $plan       = wc_memberships_get_membership_plan( $post['membership_plan'] );

            if ( !$plan ) {
                return $recipients;
            }

            $members    = $plan->get_memberships();

            foreach ( $members as $member ) {
                $user   = new WP_User( $member->user_id );
                $name   = fue_get_user_full_name( $user->ID );
                $key    = $user->ID .'|'. $user->user_email .'|'. $name;
                $recipients[$key] = array( $user->ID, $user->user_email, $name );
            }
        }

        return $recipients;
    }

    /**
     * Javascript for manual emails
     */
    public function manual_form_script() {
    ?>
        jQuery("#send_type").change(function() {
            if ( jQuery(this).val() == "wc_memberships" ) {
                jQuery(".send-type-wc-memberships").show();
            } else {
                jQuery(".send-type-wc-memberships").hide();
            }
        }).change();
    <?php
    }

}

if ( FUE_Addon_WC_Memberships::is_installed() )
    new FUE_Addon_WC_Memberships();