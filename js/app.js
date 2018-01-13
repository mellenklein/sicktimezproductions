$(document).foundation();

$(document).ready(function() {
  if ($(window).width() < 1024) {
    $('.is-drilldown').css({
      'min-height': '100%',
      'max-width': '100%',
    });
  }

  function parallaxBG() {
    var element = $('.hero');
    var scrolled = $(window).scrollTop();
    if (scrolled < 0) {
      scrolled = 0;
    }
    element.css({
      'background-position': 'center top ' + Math.round(-1 * (scrolled * 0.35) - 5) + 'px'
    });
  }

  function changeNav() {
    var scrolled = $(window).scrollTop();
    if (scrolled > 30) {
      console.log('time to turn pink');
      $('.top-bar').addClass('scrolled');
      $('.nav-divider').show();
    } else {
      $('.top-bar').removeClass('scrolled');
      $('.nav-divider').hide();
    }

  }

  $(window).scroll(function() {
    parallaxBG();
    changeNav();
  });



  $('.menu.drilldown li').not('.is-drilldown-submenu-parent').click(function() {
    $('#responsive-menu').css('display', 'none');
  });

  $('.submenu.is-drilldown-submenu li a').not('.js-drilldown-back a').click(function() {
    $('#responsive-menu').css('display', 'none');
  });


  $('#cta-btn').click(function(e) {
    e.preventDefault();
    $('.static').slideUp('slow');
    $('.onclick').slideDown('slow');
  });
  $('.close-btn').click(function(e) {
    e.preventDefault();
    $('.onclick').slideUp('slow');
    $('.static').slideDown('slow');
  });


}); //end of document.ready
