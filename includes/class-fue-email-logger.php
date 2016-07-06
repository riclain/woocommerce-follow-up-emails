<?php

/**
 * FUE_Email_Logger Class
 *
 * Class that logs all changes made to a Follow-Up Email
 */

class FUE_Email_Logger {

    public static function log( $email_id, $message ) {
        $user = wp_get_current_user();
        $data = apply_filters( 'fue_email_log_data', array(
            'comment_post_ID'       => $email_id,
            'comment_author'        => $user->display_name,
            'comment_author_email'  => $user->user_email,
            'comment_content'       => $message,
            'comment_type'          => 'email_history',
            'user_id'               => $user->ID,
            'comment_approved'      => 1
        ) );
        wp_insert_comment( $data );
    }

    public static function log_changes( $email_id, $prev_email ) {
        $new_email  = new FUE_Email( $email_id );
        $props      = get_object_vars( $new_email );
        $log        = array();

        $excludes = apply_filters( 'fue_logger_excluded_props', array(
            'post', 'edit_lock', 'prev_name', 'prev_subject', 'prev_message', 'prev_status', 'prev_type'
        ) );
        foreach ( $props as $prop => $value ) {

            if ( in_array( $prop, $excludes ) ) {
                continue;
            }

            if ( in_array( $prop, array( 'name', 'subject', 'message', 'status', 'type' ) ) ) {
                $prev_prop = 'prev_'. $prop;

                if ( isset( $prev_email->$prev_prop ) ) {
                    $prev_email->$prop = $prev_email->$prev_prop;
                }
            } elseif ( $prop == 'conditions' && !empty( $value ) ) {
                $conditions = $value;
                $value      = '';

                foreach ( $conditions as $condition ) {
                    foreach ( $condition as $key => $condition_value ) {
                        if ( is_array( $condition_value ) ) {
                            $condition_value = implode( ',', $condition_value );
                        }
                        $value .= $key .': '. $condition_value .'; ';
                    }
                }

                if ( !empty( $prev_email->$prop ) ) {
                    $prev_conditions = $prev_email->$prop;
                    $prev_email->$prop = '';

                    foreach ( $prev_conditions as $condition ) {
                        foreach ( $condition as $key => $condition_value ) {
                            if ( is_array( $condition_value ) ) {
                                $condition_value = implode( ',', $condition_value );
                            }
                            $prev_email->$prop .= $key .': '. $condition_value .'; ';
                        }
                    }
                }

            } elseif ( $prop == 'meta' ) {

                $meta   = $value;
                $value  = '';
                $old_meta   = $prev_email->meta;
                $changed_meta = array();

                foreach ( $meta as $meta_key => $meta_value ) {
                    if ( !isset( $old_meta[ $meta_key ] ) ) {
                        $changed_meta[ $meta_key ] = array( '', $meta_value );
                    } elseif ( $old_meta[ $meta_key ] != $meta_value ) {
                        $changed_meta[ $meta_key ] = array( $old_meta[ $meta_key ], $meta_value );
                    }
                }

                foreach ( $changed_meta as $meta_key => $changes ) {
                    $prop = 'meta['. $meta_key .']';
                    $log[ $prop ] = array( $changes[0], $changes[1] );
                }

                continue;
            }

            if ( !isset( $prev_email->$prop ) ) {
                $log[ $prop ] = array( '', $new_email->$prop );
            } elseif ( $prev_email->$prop != $value ) {
                $log[ $prop ] = array( $prev_email->$prop, $value );
            }
        }

        $log = apply_filters( 'fue_logger_log_props', $log, $email_id, $prev_email );

        if ( !empty( $log ) ) {
            $message = __('Email attributes updated: %s', 'follow_up_emails');
            $list = '';

            foreach ( $log as $property => $changes ) {
                if ( empty( $changes[0] ) ) {
                    $changes[0] = '-';
                }

                if ( empty( $changes[1] ) ) {
                    $changes[1] = '-';
                }

                $list .= '<p><strong>'. $property .'</strong> from <em>'. $changes[0] .'</em> to <em>'. $changes[1] .'</em></p>';
            }

            $message = sprintf( $message, $list );
            self::log( $email_id, $message );
        }

    }

    /**
     * Exclude follow-up emails history from queries and RSS
     *
     * @param array $clauses
     * @return array
     */
    public static function exclude_fue_comments( $clauses ) {
        global $wpdb, $typenow;

        $tab = ( empty( $_GET['tab'] ) ) ? '' : $_GET['tab'];

        if ( is_admin() && $tab == 'history' ) {
            return $clauses; // Don't hide when viewing email history in admin
        }

        if ( ! $clauses['join'] ) {
            $clauses['join'] = '';
        }

        if ( ! strstr( $clauses['join'], "JOIN $wpdb->posts" ) ) {
            $clauses['join'] .= " LEFT JOIN $wpdb->posts ON comment_post_ID = $wpdb->posts.ID ";
        }

        if ( $clauses['where'] ) {
            $clauses['where'] .= ' AND ';
        }

        $clauses['where'] .= " $wpdb->posts.post_type <> 'follow_up_email' ";

        return $clauses;
    }

    /**
     * Exclude fue comments from queries and RSS
     *
     * @param string $join
     * @return string
     */
    public static function exclude_fue_comments_from_feed_join( $join ) {
        global $wpdb;

        if ( ! strstr( $join, $wpdb->posts ) ) {
            $join = " LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID ";
        }

        return $join;
    }

    /**
     * Exclude fue comments from queries and RSS
     *
     * @param string $where
     * @return string
     */
    public static function exclude_fue_comments_from_feed_where( $where ) {
        global $wpdb;

        if ( $where ) {
            $where .= ' AND ';
        }

        $where .= " $wpdb->posts.post_type <> 'follow_up_email' ";

        return $where;
    }

}