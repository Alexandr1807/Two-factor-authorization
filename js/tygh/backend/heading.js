(function (_, $) {
  // Navbar submenu Horizontal position
  $(_.doc).on('mouseenter', '.navbar-right li, .subnav .nav-pills:not(.mobile-visible) li', function () {
    var adminContentWidth = $('.admin-content .navbar-admin-top').width(),
        $dropdownMenu = $(this).children('.dropdown-menu');

    if ($dropdownMenu.length) {
      var elmXPosition = $dropdownMenu.offset().left + $dropdownMenu.width(),
          addedСlass = $(this).hasClass('dropdown-submenu') ? 'dropdown-menu-to-right' : 'pull-right';

      if (_.language_direction === 'ltr' && elmXPosition > adminContentWidth || _.language_direction === 'rtl' && elmLeftPosition < 0) {
        $dropdownMenu.addClass(addedСlass);
      }
    }
  });
  $(_.doc).on('mouseleave', '.navbar-right li, .subnav .nav-pills:not(.mobile-visible) li', function () {
    var $self = $(this),
        toggleClass = $self.is('.dropdown-top-menu-item, .notifications-center__opener-wrapper') ? 'dropdown-menu-to-right' : 'dropdown-menu-to-right pull-right';
    $self.children('.dropdown-menu').removeClass(toggleClass);
  }); // Navbar submenu Vertical position

  $(_.doc).on('mouseenter', '.navbar-right li.dropdown-submenu', function () {
    var $self = $(this),
        $dropdownMenu = $self.children('.dropdown-menu');

    if ($dropdownMenu.length) {
      var elmOffset = 5;
      var elmHeight = $dropdownMenu.outerHeight(),
          elmYPosition = $dropdownMenu.offset().top + elmHeight,
          bottomPanelHeight = $('#bp_bottom_panel').outerHeight() || 0,
          adminContentHeight = $(window).height() - bottomPanelHeight;

      if (elmYPosition > adminContentHeight) {
        var top = -elmHeight + elmOffset + $self.height();
        $dropdownMenu.css('top', top + 'px');
      }
    }
  });
  $(_.doc).on('mouseleave', '.navbar-right li.dropdown-submenu', function () {
    $(this).children('.dropdown-menu').css('top', '');
  });
})(Tygh, Tygh.$);