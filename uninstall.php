<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
delete_option( 'venobox_options' );
delete_option( 'venobox_activation_date' );
delete_option( 'venobox_review_notice_dismissed' );
