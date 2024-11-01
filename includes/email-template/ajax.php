<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if( $_POST['_action'] === 'copy_email_template_by_keyid' ){
	$key_id 			 = intval( $_POST['key_id'] );
	$email_templates 	 = get_option( 'sesn_email_templates' );
    $array_fetched 		 = $email_templates[$key_id];
    
    array_push( $email_templates, $array_fetched );
    
    $status = update_option( 'sesn_email_templates', $email_templates );
    
    return true;

}else if( $_POST['_action'] === 'delete_email_template_by_keyid' ){
	
	$key_id = intval( $_POST['key_id'] );

	$email_templates = get_option( 'sesn_email_templates' );

	unset( $email_templates[$key_id] );

   	$status = update_option( 'sesn_email_templates', $email_templates );
    return true;
}