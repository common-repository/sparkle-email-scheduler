/**
 * Sparkle Email Scheduler
 * @author Sparkle WP Themes
 * Backend JQuery 
 * @since 1.0.0
 */
(function($) {
    "use strict";
    jQuery(document).ready(function($) {

        //email template notifications
        var $email_template_wrapper = $('.sesn-email-templates');
        $email_template_wrapper.on('click', '.copy-email-template-keyid', function() {
            var $this = $(this),
                $key_id = $this.data('key_id');

            if (confirm($this.data('confirm'))) {
                // ajax call
                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'sparkle_sesn_email_templates_backend_ajax',
                        _action: 'copy_email_template_by_keyid',
                        key_id: $key_id,
                        _wpnonce: sesn_email_template_backend_object.ajax_nonce
                    },
                    beforeSend: function() {
                        $this.closest('td').find('.spinner').addClass('is-active');
                    },
                    success: function(response) {
                        $this.closest('td').find('.spinner').removeClass('is-active');
                        location.reload(true);
                    }
                });
            }
        });

        $email_template_wrapper.on('click', '.delete-email-template-keyid', function() {
            var $this = $(this),
                $key_id = $this.data('key_id');
            if (confirm($this.data('confirm'))) {
                // ajax call
                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'sparkle_sesn_email_templates_backend_ajax',
                        _action: 'delete_email_template_by_keyid',
                        key_id: $key_id,
                        _wpnonce: sesn_email_template_backend_object.ajax_nonce
                    },
                    beforeSend: function() {
                        $this.closest('td').find('.spinner').addClass('is-active');
                    },
                    success: function(response) {
                        $this.closest('td').find('.spinner').removeClass('is-active');
                        location.reload(true);
                    }
                });
            }
        });


        // email preview content render
        $('#sesn-email-preview-button').click(function() {

            $('#sesn-inline-popup-content').html($('#sesn-email-template-email-body').val());

            $('#sesn-inline-popup-content').toggle();

        })

    });
})(jQuery);