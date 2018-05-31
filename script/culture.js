(function ($) {
  "use strict";

  Drupal.behaviors.culture_overrides = {
    attach: function (context, settings) {
      // Display all filters for big screens.
      if ($(window).width() > 768) {
        $( "fieldset.bef-select-as-checkboxes-fieldset" ).removeClass( "collapsible" );
        $( "fieldset.bef-select-as-checkboxes-fieldset" ).removeClass( "collapsed" );
        $( "fieldset.bef-select-as-checkboxes-fieldset" ).removeClass( "collapse-processed" );
        $('.fieldset-legend > a').replaceWith(function() {
          return $('<div/>', {
              html: this.innerHTML
          });
        });
      }

      if ($('section.navigation-wrapper', context).length) {
        $('.site-header', context).css({"height": "175px"});
      }
    }
  };
})(jQuery);

