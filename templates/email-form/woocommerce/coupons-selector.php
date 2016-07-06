<div class="options_group storewide non-signup hideable <?php do_action('fue_form_coupons_class', $email); ?> coupons_div">

    <p class="form-field">
        <label for="coupon_id"><?php _e('Coupon', 'follow_up_emails'); ?></label>

        <?php
        $selected = !empty( $email->meta['coupon'] ) ? $email->meta['coupon'] : '';
        ?>
        <select id="coupon_id" name="meta[coupon]" class="select2" data-placeholder="<?php _e('All coupons', 'follow_up_emails'); ?>" style="width:400px;">
            <option value=""></option>
            <?php
            $coupons = get_posts(array(
                'post_type'     => 'shop_coupon',
                'nopaging'      => true
            ));

            foreach ( $coupons as $coupon ):
            ?>
                <option value="<?php echo $coupon->ID; ?>" <?php selected( $selected, $coupon->ID ); ?>><?php echo esc_attr( $coupon->post_title ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>

</div>