<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); // Exit if accessed directly.

if( isset( $_GET['action'] ) && $_GET['action'] == 'add-new' ){
    //Get the settings from options table
    $sesn_email_subject    = esc_html__( 'Use discount code to get 20% discount during your checkout.', 'sparkle-email-scheduler-for-ew' );
    $sesn_renew_notice     = '';
    $payment_status        = '';
    $scheduled_time        = '';
    $email_frequency       = '1';
    $sesn_email_body       = esc_html__( 'Dear user,
        This email inform to you that please complete your uncomplete checkout and get 20% discount using the below coupon code during checkout.
    Coupon Code: specialoff20

    Thank you.', 'sparkle-email-scheduler-for-ew' );
}else if( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ){
    $key_id                 = intval( $_GET['id'] );
    $email_templates        = get_option( 'sesn_email_templates' );
    $email_template         = $email_templates[$key_id];
    $email_for              = isset( $email_template['email_for'] ) ? esc_html( $email_template['email_for'] ) : '';
    $sesn_email_subject     = esc_html( $email_template['email_subject'] );
    $payment_status         = esc_html( $email_template['payment_status'] );
    $email_frequency        = isset( $email_template['email_frequency'] ) ? intval( $email_template['email_frequency'] ) : '1';
    $scheduled_time         = isset( $email_template['scheduled_time'] ) ? esc_html( $email_template['scheduled_time'] ) : '';
    $sesn_email_body        = stripslashes( $email_template['email_body'] );
}

if( isset( $_GET['action'] ) && ( $_GET['action'] == 'add-new' || $_GET['action'] == 'edit' ) ){
    ?>
    <div class="wrap sesn-edd-renewal-notices-page">
        <h2><?php esc_html_e( 'Email Templates Settings', 'sparkle-email-scheduler-for-ew' ); ?></h2>

        <form method="post" action="<?php echo admin_url() . 'admin-post.php' ?>">
            <input type="hidden" name="action" value="sesn_save_email_template" />
            <?php if( $_GET['action'] === 'edit' ){ ?>
                <input type="hidden" name='_action' value="edit" />
                <input type='hidden' name='rowid' value='<?php echo intval( $key_id ); ?>' />
                <?php
            }
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th>
                        <label for="sesn_email_subject"><?php esc_html_e( 'Email Subject', 'sparkle-email-scheduler-for-ew' ); ?></label>
                    </th>
                    <td>
                        <div class="sesn-edd-email-template-subject">
                            <input type="text" id="sesn_email_subject" class='sesn-email-template-subject' name="sesn_email_template[email_subject]" value="<?php echo esc_html( $sesn_email_subject ); ?>" />
                            <div class="sesn-notice" role="alert">
                                <?php esc_html_e( 'The subject of the email template to be send in email.','sparkle-email-scheduler-for-ew'); ?>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr valign="top">
                    <th>
                        <div class="sesn-edd-renewal-notices">
                            <label for="sesn_renew_notice"><?php esc_html_e( 'Email For','sparkle-email-scheduler-for-ew' ); ?></label>
                        </div>
                    </th>
                    <td>
                        <div class="sesn-edd-renewal-notices">
                            <select name="sesn_email_template[email_for]" id="sesn_email_for">
                                <option value="edd" <?php if( isset( $email_for ) && $email_for === 'edd'){ echo "selected='selected'"; } ?>><?php esc_html_e( "Easy Digital Downloads(EDD)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="woo" <?php if( isset( $email_for ) && $email_for === 'woo'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "WooCommerce", "sparkle-email-scheduler-for-ew" ); ?></option>
                            </select>
                            <div class="sesn-notice" role="alert">
                                <?php esc_html_e( 'Please select the plugin name that you are using this notification for.', 'sparkle-email-scheduler-for-ew' ); ?>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr valign="top">
                    <th>
                        <div class="sesn-edd-renewal-notices">
                            <label for="sesn_renew_notice"><?php esc_html_e( 'Payment Status','sparkle-email-scheduler-for-ew' ); ?></label>
                        </div>
                    </th>
                    <td>
                        <div class="sesn-edd-renewal-notices">
                            <select name="sesn_email_template[payment_status]" id="sesn_payment_status">
                                <option value=""><?php esc_html_e( "Don't send", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="complete" <?php if($payment_status === 'complete'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Complete(EDD)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="pending" <?php if($payment_status === 'pending'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Pending(both)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="processing" <?php if($payment_status === 'processing'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Processing(both)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="refunded" <?php if($payment_status === 'refunded'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Refunded(both)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="revoked" <?php if($payment_status === 'revoked'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Revoked(EDD)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="failed" <?php if($payment_status === 'failed'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Failed(both)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="abandoned" <?php if($payment_status === 'abandoned'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Abandoned(EDD)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="preapproval" <?php if($payment_status === 'preapproval'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Preapproval(EDD)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="cancelled" <?php if($payment_status === 'cancelled'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Cancelled(both)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="completed" <?php if($payment_status === 'completed'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Completed(Woo)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                <option value="on-hold" <?php if($payment_status === 'on-hold'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "On Hold(Woo)", "sparkle-email-scheduler-for-ew" ); ?></option>
                                
                            </select>
                            <div class="sesn-notice" role="alert">
                                <?php esc_html_e( 'Please select the payment status to send the email notification.', 'sparkle-email-scheduler-for-ew' ); ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <tr valign="top">
                <th>
                    <label for="sesn_email_subject"><?php esc_html_e( 'Email Frequency', 'sparkle-email-scheduler-for-ew' ); ?></label>
                </th>
                <td>
                    <div class="sesn-edd-email-template-subject">
                        <input type="text" id="sesn_email_frequency" class='sesn-email-frequency' name="sesn_email_template[email_frequency]" value="<?php echo esc_html( $email_frequency ); ?>" />
                        <div class="sesn-notice" role="alert">
                            <?php esc_html_e( "Please set the number of frequency to send an email for each payment's customer email.",'sparkle-email-scheduler-for-ew'); ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>
                    <div class="sesn-edd-renewal-notices">
                        <label for="sesn_renew_notice"><?php esc_html_e( 'Schedule Email','sparkle-email-scheduler-for-ew' ); ?></label>
                    </div>
                </th>
                <td>
                    <div class="sesn-edd-renewal-notices">
                        <select name="sesn_email_template[scheduled_time]" id="wedl_renew_notice">
                            <option value=""><?php esc_html_e("Don't send", "sparkle-email-scheduler-for-ew"); ?></option>
                            <option value="every_30_mins" <?php if( $scheduled_time === 'every_30_mins' ){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Every 30 Minutes From Now", "sparkle-email-scheduler-for-ew" ); ?></option>
                            <option value="every_6_hours" <?php if( $scheduled_time === 'every_6_hours' ){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Every Six Hours From Now", "sparkle-email-scheduler-for-ew" ); ?></option>
                            <option value="every_12_hours" <?php if($scheduled_time === 'every_12_hours'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Every Twelve Hours From Now", "sparkle-email-scheduler-for-ew" ); ?></option>
                            <option value="daily" <?php if($scheduled_time === 'daily'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Daily", "sparkle-email-scheduler-for-ew" ); ?></option>
                            <option value="every_2_days" <?php if($scheduled_time === 'every_2_days'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Every 2 days From Now", "sparkle-email-scheduler-for-ew" ); ?></option>
                            <option value="every_3_days" <?php if($scheduled_time === 'every_3_days'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Every 3 days From Now", "sparkle-email-scheduler-for-ew" ); ?></option>
                            <option value="weekly" <?php if($scheduled_time === 'weekly'){ echo "selected='selected'"; } ?> ><?php esc_html_e( "Weekly", "sparkle-email-scheduler-for-ew" ); ?></option>
                        </select>
                        <div class="wedl-notice" role="alert">
                            <?php esc_html_e( 'Please set the option when to send the renewal notification', 'sparkle-email-scheduler-for-ew' ); ?>
                        </div>
                    </div>
                </td>
            </tr>
                <tr valign="top">
                    <th>
                        <div class="sesn-edd-renewal-notices">
                            <label for="sesn_edd_renew_notice_message"><?php esc_html_e('Email Body','sparkle-email-scheduler-for-ew'); ?></label>
                        </div>
                    </th>
                    <td>
                        <?php add_thickbox(); ?>
                        <div class="sesn-edd-renewal-notices"><?php
                            $content    = stripslashes( $sesn_email_body );
                            $editor_id  = 'sesn-email-template-email-body';
                            $settings   = array(
                                'textarea_name' => 'sesn_email_template[email_body]',
                                'textarea_rows' => 12,
                                'editor_class'  => 'sesn-meta-renew-notification-email-body',
                                'tinymce' => false
                            );
                            wp_editor( $content, $editor_id, $settings );
                            ?>
                            
                        </div>

                        <button type="button" class='button' id="sesn-email-preview-button" style="margin-top: 10px;"><?php esc_html_e( 'Preview Mail', 'sparkle-email-scheduler-for-ew' ); ?></button>

                        <div id="sesn-inline-popup-content" style="display:none; margin-top:15px;"></div>
                        
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php wp_nonce_field( 'sesn_nonce_save_email_template', 'sesn_add_nonce_save_email_template' ); ?>
                        <?php submit_button(); ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}else{ ?>
    <table class='wp-list-table widefat striped table-view-list sesn-email-templates' style="margin-top:20px; margin-right: 20px;">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Email Subject', 'sparkle-email-scheduler-for-ew' ); ?></th>
            <th><?php esc_html_e( 'Email For', 'sparkle-email-scheduler-for-ew' ); ?></th>
            <th><?php esc_html_e( 'Schedule Email', 'sparkle-email-scheduler-for-ew' ); ?></th>
            <th><?php esc_html_e( 'Payment Status', 'sparkle-email-scheduler-for-ew' ); ?></th>
            <th><?php esc_html_e( 'Actions', 'sparkle-email-scheduler-for-ew' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sesn_email_templates = get_option( 'sesn_email_templates' );

        if( !empty( $sesn_email_templates ) ){
            foreach( $sesn_email_templates as $key => $email_template ){
                ?>
                <tr>
                    <td><?php echo esc_html( $email_template['email_subject'] ); ?></td>
                    <td><?php echo esc_html( $email_template['email_for'] ); ?></td>
                    <td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $email_template['scheduled_time'] ) ) ); ?></td>
                    <td><?php echo esc_html( ucfirst( $email_template['payment_status'] ) ); ?></td>
                    <td>
                        <?php
                        $base_url = admin_url( 'admin.php?page=sparkle-email-scheduler' );
                        $edit_redirect = add_query_arg( array( 'action' => 'edit', 'id' => $key ), $base_url );
                        ?>
                        <a href='<?php echo esc_url_raw( $edit_redirect ); ?>' title="<?php esc_attr_e( 'Edit Email Template', 'sparkle-email-scheduler-for-ew' ); ?>"><?php esc_html_e( 'Edit', 'sparkle-email-scheduler-for-ew' ); ?></a> | <a href='javascript:void(0);' class='copy-email-template-keyid' data-key_id="<?php echo intval( $key ); ?>" data-confirm="<?php esc_attr_e( 'Are you sure you want to copy this email template?', 'sparkle-email-scheduler-for-ew' ); ?>" ><?php esc_html_e( 'Copy', 'sparkle-email-scheduler-for-ew' ); ?></a> | <a href='javascript:void(0);' class='delete-email-template-keyid' data-key_id="<?php echo intval( $key ); ?>" data-confirm="<?php esc_attr_e( 'Are you sure you want to delete this email template?', 'sparkle-email-scheduler-for-ew' ); ?>"><?php esc_html_e( 'Delete', 'sparkle-email-scheduler-for-ew' ); ?></a>
                        <span class="spinner"></span>
                    </td>
                </tr>
                <?php
            }
        }else{ ?>
            <tr><td><?php esc_html_e( "No email templates found. Create One here.", "sparkle-email-scheduler-for-ew" ); ?></td></tr><?php
        } ?>
        </tbody>
    </table>
    <?php
    $base_url = admin_url( 'admin.php?page=sparkle-email-scheduler' );
    $redirect = add_query_arg( array( 'action' => 'add-new' ), $base_url );
    ?>
    <a href="<?php echo esc_url_raw( $redirect ); ?>" class='button button-secondary add-email-templates'><?php esc_html_e( 'Add Email Template', 'sparkle-email-scheduler-for-ew' ); ?></a>
    <?php
}