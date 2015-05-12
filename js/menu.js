// Javascript related to the admin menu(s).
(function ($) {
  Drupal.behaviors.bootstrapAdminMenu = {
    attach: function (context) {
      $(window).load(function() {

        // Add the class "open" to parent items when clicked.
        // Display is then customised using css rules.
        // @see _menu.scss
        $(".menu.primary li.expanded").click(function(e) {
          e.preventDefault();
          $(this).toggleClass('open');
        });
        // Stop event propagation for sub-menu items so that the parent item is
        // not collpased when an item of its sub-menu is clicked.
        $(".menu.primary li ul li").click(function(e) {
          e.stopPropagation();
        });

      });
    }
  }
})(jQuery);
