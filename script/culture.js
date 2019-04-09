(function ($) {
  "use strict";

  Drupal.behaviors.culture_overrides = {
    attach: function (context) {
      // Display all filters for big screens.
      if ($(window).width() > 768) {
        $( "fieldset.bef-select-as-checkboxes-fieldset" ).removeClass( "collapsible collapsed collapse-processed" );
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

  /**
   * Toggle opening hours
   */
  function toggle_opening_hours() {
    // Create toggle link
    $('.js-opening-hours-toggle-element').each(function () {
      var
        $this = $(this),
        text = [];

      if ($this.attr('data-extended-title')) {
        $('th', this).slice(1).each(function () {
          text.push($(this).text());
        });
      } else {
        text.push(Drupal.t('Opening hours'));
      }

      $('<a />', {
        'class' : 'opening-hours-toggle js-opening-hours-toggle js-collapsed collapsed',
        'href' : Drupal.t('#toggle-opening-hours'),
        'text' : text.join(', ')
      }).insertBefore(this);
    });

    // Set variables
    var element = $('.js-opening-hours-toggle');

    // Attach click
    element.on('click touchstart', function(event) {
      // Store clicked element for later use
      var element = this;

      // Toggle
      $(this).next('.js-opening-hours-toggle-element').slideToggle('fast', function() {
        // Toggle class
        $(element)
          .toggleClass('js-collapsed js-expanded collapsed')

          // Remove focus from link
          .blur();
      });

      // Prevent default (href)
      event.preventDefault();
    });

    // Expand opening hours on library pages.
    if (Drupal.settings.ding_ddbasic_opening_hours && Drupal.settings.ding_ddbasic_opening_hours.expand_on_library) {
      element.triggerHandler('click');
    }
  }

  // When ready start the magic.
  $(document).ready(function () {
    // Toggle opening hours.
    toggle_opening_hours();

    // Expand block when accesed from "Opening hours" widget.
    var hash = window.location.hash;
    if (hash === '#toggle-opening-hours') {
      var element = $('a.js-opening-hours-toggle');
      if (!$(element).hasClass('js-expanded')) {
        $(element).toggleClass('js-collapsed js-expanded');
        $('.js-opening-hours-toggle-element').css('display','block');
      }
    }
  });
})(jQuery);

