jQuery(document).ready(function($) {
    jQuery('#guest-post-form').submit(function(e) {
        e.preventDefault();

        jQuery('.all-class').remove();
        jQuery('.gp-input').removeClass('error');

        var title = jQuery('#gp_title').val().trim();
        var content = jQuery('#gp_content').val().trim();
        var author = jQuery('#gp_author').val().trim();
        var email = jQuery('#gp_email').val().trim();
        var hasError = false;

        if (title === '') {
            jQuery('#gp_title').after("<div class='all-class'>Title field is required.</div>");
            jQuery('#gp_title').addClass('error');
            hasError = true;
        }

        if (content === '') {
            jQuery('#gp_content').after("<div class='all-class'>Content field is required.</div>");
            jQuery('#gp_content').addClass('error');
            hasError = true;
        }

        if (author === '') {
            jQuery('#gp_author').after("<div class='all-class'>Author Name field is required.</div>");
            jQuery('#gp_author').addClass('error');
            hasError = true;
        }

        if (email === '') {
            jQuery('#gp_email').after("<div class='all-class'>Email field is required.</div>");
            jQuery('#gp_email').addClass('error');
            hasError = true;
        } else if (!isValidEmail(email)) {
            jQuery('#gp_email').after("<div class='all-class'>Enter a valid email address.</div>");
            $('#gp_email').addClass('error');
            hasError = true;
        }

        if (hasError) {
            jQuery('.submit-wrap').after("<div class='all-class new-error-msg button-under-msg'>One or more fields have an error. Please check and try again.</div>");
        } else {
            this.submit();
        }
    });

    function isValidEmail(email) {
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }
});
