(function ($) {
  "use strict";

  Drupal.behaviors.culture_overrides = {
    attach: function (context, settings) {
      // Display all filters for big screens.
      if ($(window).width() > 768) {
        $('a.fieldset-title', context).click();
      }
    }
  };
})(jQuery);

