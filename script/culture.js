(function ($) {
  "use strict";

  Drupal.behaviors.culture_overrides = {
    attach: function (context, settings) {
      // Display all filters for big screens.
      if ($(window).width() > 768) {
        //$('a.fieldset-title', context).click();

        $( "fieldset.bef-select-as-checkboxes-fieldset" ).removeClass( "collapsible" );
        $( "fieldset.bef-select-as-checkboxes-fieldset" ).removeClass( "collapsed" );
        $( "fieldset.bef-select-as-checkboxes-fieldset" ).removeClass( "collapse-processed" );
        $('.fieldset-legend > a').replaceWith(function() {
          return $('<div/>', {
              html: this.innerHTML
          });
        });
      }
    }
  };
})(jQuery);

