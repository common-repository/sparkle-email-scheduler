<?php
/**
 * Plugin Name: Sparkle Email Scheduler
 * Description: Send email notification for your potential customers
 * Author: sparklewpthemes
 * Author URI: https://sparklewpthemes.com
 * Requries at least: 4.0
 * Tested up to: 6.3
 * Version: 1.0.3
 * Text Domain: sparkle-email-scheduler-for-ew
 * Domain Path: languages
 * Network: false
 *
 * @package Sparkle Email Scheduler
 * @author sparklewpthemes
 * @category Core
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( !class_exists( 'Sparkle_Email_Scheduler' ) ) {

	class Sparkle_Email_Scheduler{
		 /**
         * Class Constructor
         * @since 1.0.0
         * @access public
         * @return void
         */
        
        protected static $instance  = null;

		function __construct() {
            $this->define_plugin_constants();
            $this->load_plugin_textdomain();
            $this->enqueue_scripts();

            add_action( 'init', array( $this,'init' ) );

			//menu addition for navigation to email templates
			add_action( 'admin_menu', array( $this, 'sparkle_sesn_register_plugin_menu_page' ) );
			//save and edit email templates
			add_action( 'admin_post_sesn_save_email_template', array( $this, 'sparkle_sesn_save_email_template' ) ); //save the options in the wordpress options table.

            //backend ajax calls
            add_action( 'wp_ajax_sparkle_sesn_email_templates_backend_ajax', array( $this, 'sparkle_email_templates_backend_ajax' ) );
            add_action( 'wp_ajax_nopriv_sparkle_sesn_email_templates_backend_ajax', array( $this, 'sparkle_email_templates_backend_ajax' ) );

		}

        function init(){
            add_filter( 'cron_schedules', array( $this, 'sparkle_sesn_set_time_interval_for_cron' ) );
            
            $email_templates = get_option( 'sesn_email_templates' );

            if( empty( $email_templates ) ){ return; }
            
            foreach( $email_templates as $key => $email_template ){
                $scheduled_time = sanitize_text_field( $email_template['scheduled_time'] );
                
                if( $scheduled_time == '' ) { 
                    continue; 
                }

                if ( ! wp_next_scheduled( $scheduled_time.'_sesn_scheduled_events' ) ) {
                    wp_schedule_event( time(), $scheduled_time, $scheduled_time.'_sesn_scheduled_events' );
                }

                //run auto cron daily for license renewal notification and license validity check. 
                add_action( $scheduled_time.'_sesn_scheduled_events', array( $this, 'sparkle_sesn_send_payment_history_emails_using_cron' ) );
            }

        }

        /** 
         * Class instance
         * @return instance of a class
         * @since 1.0.0
         */
        public static function get_instance(){
            if( null === self:: $instance ){
                self:: $instance = new self;
            }

            return self:: $instance;
        }

        /**
         * Check Plugin Dependencies and show admin notice
         * Initialize Plugin Class
         * @return notice or instance  of class
         * @since 1.0.0
         */
        public static function check_plugin_dependency(){
            //Firstly, check if a dependency plugin - Easy Digital Downloads or WooCommerce is active or not.
            $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
            if ( in_array( 'easy-digital-downloads/easy-digital-downloads.php', $active_plugins ) || in_array( 'woocommerce/woocommerce.php', $active_plugins ) ) {
                return Sparkle_Email_Scheduler::get_instance();
            }else{
                add_action( 'admin_notices', array( 'Sparkle_Email_Scheduler', 'admin_notice' ) );
		        return;
            }
        }

        /**
         * Admin Notice
         * @return string
         * @since 1.0.0
         */
        public static function admin_notice() {
            ?>
            <div class="error">
                <p><?php esc_html_e( 'Email Scheduler is enabled but not effective. It requires WooCommerce or Easy Digital Download in order to work.', 'sparkle-email-scheduler-for-ew' ); ?></p>
            </div>
            <?php
        }

        /**
        * Define plugins contants
        * @since 1.0.0
        */
        private function define_plugin_constants(){
            defined( 'SESN_PATH' ) or define( 'SESN_PATH', plugin_dir_path( __FILE__ ) );
            defined( 'SESN_JS_DIR' ) or define( 'SESN_JS_DIR', plugin_dir_url( __FILE__ ) . 'assets/js' );
        }

        /**
         * Loads plugin text domain
         * @since 1.0.0
         */
        private function load_plugin_textdomain(){
            load_plugin_textdomain( 'sparkle-email-scheduler-for-ew', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' ); 
        }

        /**
         * Includes the plugins required scripts - JS and CSS files
         * @since 1.0.0
         */
        private function enqueue_scripts(){
            add_action( 'admin_enqueue_scripts', array( $this, 'register_backend_assets' ) );
        }

        /**
         * Regiser the backend assets required for both
         * @return null
         * @since 1.0.0
         */
        public function register_backend_assets(){
            wp_enqueue_script( 'sesn-email-template-admin', SESN_JS_DIR . '/email-template-admin.js', array( 'jquery' ), '1.0.0' );

            $ajax_nonce = wp_create_nonce( 'sesn-email-template-backend-ajax-nonce' );
            $localize = array(
                'ajax_nonce'    => $ajax_nonce,
            );

            wp_localize_script( 'sesn-email-template-admin', 'sesn_email_template_backend_object', $localize );

        }

        /**
         * Backend Ajax
         * @since 1.0.0
         * @access public
         * @return void
         */
        function sparkle_email_templates_backend_ajax(){
            $nonce = sanitize_text_field( $_POST['_wpnonce'] );
            $created_nonce = 'sesn-email-template-backend-ajax-nonce';
            if ( ! wp_verify_nonce( $nonce, $created_nonce ) ) {
                die( esc_html__( 'Security check', 'sparkle-email-scheduler-for-ew' ) );
            }
            include( SESN_PATH.'includes/email-template/ajax.php' );
            die();
        }

		/**
         * Notification Email sent using Cron
         * @since 1.0.0
         * @access public
         * @return int
         */
		public static function send_sesn_email_notification_cron( $user_email, $template_id, $payment_id, $email_subject, $email_body, $template_email_frequency ){
			$admin_email = get_option( 'admin_email' );

			$headers = "From: $admin_email" . "\r\n" .
						"Reply-To: $admin_email" . "\r\n" .
						'X-Mailer: PHP/' . phpversion().
                        "MIME-Version: 1.0" . "\n".
                        "Content-type:text/html;charset=UTF-8" . "\n";

			$subject = ( isset( $email_subject ) && $email_subject !='' )  ? $email_subject : esc_html__( 'Notice! Your License is Going to Expire Soon. Please Renew in Time.', 'sparkle-email-scheduler-for-ew' );

			$message = (isset($email_body) && $email_body !='') ? $email_body : "Hello User, \n\nDear user,\n\n

            I noticed that you tried to sign up for your Order but didn't finish checking out. If you have any questions, please let me know and I will be more than happy to answer them. \n\n
            
            Don't forget, you can use the coupon code ICAMEBACK to save $5 off. \n\n\n
            
            Thanks";
			
            $payment    = new EDD_Payment( $payment_id );                
            
            $meta_key1 = $template_id.'_sparkle_email_cron_freq_flag';
            $email_freq = $payment->get_meta( $meta_key1, true );
            
            if($email_freq ==''){ $email_freq = 1; }
            
            if( !isset($email_freq) || $email_freq <= $template_email_frequency ){
                $flag = wp_mail( $user_email, $subject, $message, $headers );

                if( $flag ){
                    $meta_value1 = (int)$email_freq + 1;
                    $payment->update_meta( $meta_key1, $meta_value1 );
                }
            }
		}

        /**
         * Notification Email sent using Cron for WooCommerce Orders
         * @since 1.0.0
         * @access public
         * @return int
         */
        public static function send_sesn_woo_email_notification_cron( $user_email, $template_id, $payment_id, $email_subject, $email_body, $template_email_frequency ){
            $admin_email = get_option( 'admin_email' );

            $headers = "From: $admin_email" . "\r\n" .
                        "Reply-To: $admin_email" . "\r\n" .
                        'X-Mailer: PHP/' . phpversion().
                        "MIME-Version: 1.0" . "\n".
                        "Content-type:text/html;charset=UTF-8" . "\n";

            $subject = ( isset( $email_subject ) && $email_subject !='' )  ? $email_subject : esc_html__( 'Notice! Your License is Going to Expire Soon. Please Renew in Time.', 'sparkle-email-scheduler-for-ew' );

            $message = (isset($email_body) && $email_body !='') ? $email_body : "Hello User, \n\nDear user,\n\n

            I noticed that you tried to sign up for your Order but didn't finish checking out. If you have any questions, please let me know and I will be more than happy to answer them. \n\n
            
            Don't forget, you can use the coupon code ICAMEBACK to save $5 off. \n\n\n
            
            Thanks";
            
            
            $order = wc_get_order( $payment_id );                
            
            $meta_key1 = $template_id.'_sparkle_email_cron_freq_flag';
            $email_freq = $order->get_meta( $meta_key1, true );

            if( !isset($email_freq) || $email_freq <= $template_email_frequency ){
                $flag = wp_mail( $user_email, $subject, $message, $headers );

                if( $flag ){
                    //update email sent counter
                    $meta_value1 = (int)$email_freq + 1;
                    $order->update_meta_data( $meta_key1, $meta_value1 );
                    $order->save();
                }
            }
        }

		/**
         * Return email template for EDD
         * @since 1.0.0
         * @access public
         * @return int
         */
		public static function sparkle_sesn_get_customer_emails_for_email_templates( $payment_status, $template_id ){
			$query_args = array( 'status' => esc_attr( $payment_status ) );
			$query 		= new EDD_Payments_Query( $query_args );
			$payments 	= $query->get_payments();
			$emails 	= array();
            $return_array = array();
			
            foreach( $payments as $key => $payment ){ 
                if( !in_array( $payment->payment_meta['email'], $emails ) ){
                    array_push( $emails, $payment->payment_meta['email'] );
                    $return_array[] = array(
                        'template_id' => $template_id, 
                        'payment_id' => $payment->ID,
                        'email' => $payment->payment_meta['email'],    
                    ); 
                }
			}    

			return $return_array;
		}

        /**
         * Return email tempaltes for woocommerce
         * @since 1.0.0
         * @access public
         * @return int
         */
        public static function sparkle_sesn_get_woo_customer_emails_for_email_templates( $payment_status, $template_id ){

            $customer_orders = wc_get_orders( array(
                'limit'    => -1,
                'status'   => sanitize_text_field( $payment_status )
            ) );
                    
            $emails = array();
            $return_array = array();
            
            foreach( $customer_orders as $key => $customer_order ){
                $data  = $customer_order->get_data();
                if( !in_array( $data['billing']['email'], $emails ) ){
                    array_push( $emails, $data['billing']['email'] );
                    $return_array[] = array(
                        'template_id' => $template_id, 
                        'payment_id' => $data['id'],
                        'email' => $data['billing']['email'],    
                    ); 
                }
            }
            return $return_array;
        }

		/**
         * Payment History Email Cron
         * @since 1.0.0
         * @access public
         * @return void
         */
        public static function sparkle_sesn_send_payment_history_emails_using_cron(){
            $email_templates = get_option( 'sesn_email_templates' );
            
            if( empty( $email_templates ) ){ return; }
            
            foreach( $email_templates as $key => $email_template ){
                if( $email_template['scheduled_time'] =='' ){ continue; }
                if( $email_template['payment_status'] =='' ){ continue; }
                
                $email_frequency = isset( $email_template['email_frequency'] ) ? $email_template['email_frequency'] : '1';

                $template_id    = $key;
                $payment_status = $email_template['payment_status'];

                if( $payment_status !=='' ){
                    if( $email_template['email_for'] == 'woo' ){

                        $email_subject  = sanitize_text_field( $email_template['email_subject'] );
                        $email_body     = stripslashes( $email_template['email_body'] );
                        $emails         = Sparkle_Email_Scheduler:: sparkle_sesn_get_woo_customer_emails_for_email_templates( $payment_status, $template_id );
                        
                        if( !empty( $emails ) ){
                            foreach( $emails as $email ){
                                $template_id = $email['template_id'];
                                $payment_id = $email['payment_id'];
                                $email = $email['email'];
                                Sparkle_Email_Scheduler:: send_sesn_woo_email_notification_cron( $email, $template_id, $payment_id, $email_subject, $email_body, $email_frequency );
                            }
                        }
                    }else{
                        $email_subject  = sanitize_text_field( $email_template['email_subject'] );
                        $email_body     = stripslashes( $email_template['email_body'] );
                        $emails = Sparkle_Email_Scheduler:: sparkle_sesn_get_customer_emails_for_email_templates( $payment_status, $template_id );

                        if( !empty( $emails ) ){
                            foreach( $emails as $email ){
                                $template_id = $email['template_id'];
                                $payment_id = $email['payment_id'];
                                $email = $email['email'];
                                Sparkle_Email_Scheduler:: send_sesn_email_notification_cron( $email, $template_id, $payment_id, $email_subject, $email_body, $email_frequency );
                            }
                        }
                    }
                }
            }
            return;
        }

		/**
         * Time Interval for Cron
         * @since 1.0.0
         * @access public
         * @return void
         */
        function sparkle_sesn_set_time_interval_for_cron( $schedules ) {

            $schedules['every_30_mins'] = array(
                'interval' => 60*30,
                'display' => esc_html__( 'Every 30 minutes' ),
            );

            $schedules['every_6_hours'] = array(
                'interval' => 60*60*6,
                'display' => esc_html__( 'Every 6 Hours' ),
            );

            $schedules['every_12_hours'] = array(
                'interval' => 60*60*12,
                'display' => esc_html__( 'Every 12 Hours' ),
            );

            $schedules['daily'] = array(
                'interval' => 60*60*24,
                'display' => esc_html__( 'Daily' ),
            );
            $schedules['every_2_days'] = array(
                'interval' => 60*60*24*2,
                'display' => esc_html__( 'Every 2 Days' ),
            );

            $schedules['every_3_days'] = array(
                'interval' => 60*60*24*3,
                'display' => esc_html__( 'Every 3 Days' ),
            );

            $schedules['weekly'] = array(
                'interval' => 60*60*24*7,
                'display' => esc_html__( 'Weekly' ),
            );

            return $schedules;
        }

		/**
         * Register Plugin Menu
         * @since 1.0.0
         * @access public
         * @return void
         */
        function sparkle_sesn_register_plugin_menu_page(){
            add_menu_page( 'Sparkle Email Scheduler', 'Email Scheduler', 'manage_options', 'sparkle-email-scheduler', array( $this, 'sparkle_sesn_email_templates_callback' ), 'dashicons-welcome-widgets-menus', 26 );
        }

        /**
         * Email Templates Callabck
         * @since 1.0.0
         * @access public
         * @return void
         */
        function sparkle_sesn_email_templates_callback(){
            include( SESN_PATH . 'includes/email-template/email-templates.php' );
        }

        /**
         * Save Email Templates
         * @since 1.0.0
         * @access public
         * @return void
         */
        function sparkle_sesn_save_email_template(){
            if ( isset( $_POST[ 'sesn_add_nonce_save_email_template' ] ) && isset( $_POST[ 'submit' ] ) && wp_verify_nonce( $_POST[ 'sesn_add_nonce_save_email_template' ], 'sesn_nonce_save_email_template' ) ) {
                $_POST = stripslashes_deep( $_POST );
                $email_subject  = sanitize_text_field( $_POST['sesn_email_template']['email_subject'] );
                $email_for      = sanitize_text_field( $_POST['sesn_email_template']['email_for'] );
                $payment_status = isset( $_POST['sesn_email_template']['payment_status'] ) ? sanitize_text_field( $_POST['sesn_email_template']['payment_status']) : "";
                $email_frequency = isset( $_POST['sesn_email_template']['email_frequency'] ) ? $_POST['sesn_email_template']['email_frequency'] : '1';
                $scheduled_time = isset( $_POST['sesn_email_template']['scheduled_time']) ? sanitize_text_field( $_POST['sesn_email_template']['scheduled_time']  ) : '';
                $email_body     = stripslashes( $_POST['sesn_email_template']['email_body'] );

                $email_templates = get_option( 'sesn_email_templates' );
                if( !empty( $email_templates ) ){
                    $email_templates = $email_templates;
                }else{
                    $email_templates = array();
                }

                if( isset( $_POST['_action'] ) && $_POST['_action'] === 'edit' ){
                    $key_id = sanitize_text_field( $_POST['rowid'] );
                    $email_templates[$key_id] = array(
                                                         'email_subject'    => sanitize_text_field( $email_subject ),
                                                         'email_for'        => sanitize_text_field( $email_for ),
                                                         'payment_status'   => sanitize_text_field( $payment_status ),
                                                         'email_frequency' => sanitize_text_field( $email_frequency ),
                                                         'scheduled_time'   => sanitize_text_field( $scheduled_time ),
                                                         'email_body'       => stripslashes( $email_body ),
                                                );  
                }else{
                    $array_to_push = array(
                                         'email_subject'    => sanitize_text_field( $email_subject ),
                                         'email_for'        => sanitize_text_field( $email_for ),
                                         'payment_status'   => sanitize_text_field( $payment_status ),
                                         'email_frequency'  =>  sanitize_text_field( $email_frequency ),
                                         'scheduled_time'   => sanitize_text_field( $scheduled_time ),
                                         'email_body'       => stripslashes( $email_body ),   
                                    );

                    array_push( $email_templates, $array_to_push );
                }

                // The option already exists, so we just need to update it.
                $status = update_option( 'sesn_email_templates', $email_templates );

                if ( $status == TRUE ) {
                    wp_redirect( admin_url() . 'admin.php?page=sparkle-email-scheduler&message=1' );
                } else {
                    wp_redirect( admin_url() . 'admin.php?page=sparkle-email-scheduler&message=2' );
                }
                exit;

            } else {
                die( 'No script kiddies please!' );
            }
        }
	}
}
add_action( 'plugins_loaded', array ( 'Sparkle_Email_Scheduler', 'check_plugin_dependency' ), 0 );