(function($, jQuery) {

    jQuery.fn.contentrepository = function ( options ) {
        options = jQuery.extend ({}, jQuery.fn.contentrepository.defaults, options);

        return this.each(function() {
            $(this).click(function() {
                
            });
        });
    }
    jQuery.fn.contentrepository.defaults = {
        rootPath: "/"
    };
    
})(jQuery, jQuery);

