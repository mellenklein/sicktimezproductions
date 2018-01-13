$(document).ready(function() {

	$('.container-sidebar a').click(function() {
		$('.container-sidebar a').removeClass('active');
		$(this).addClass('active');
	});

	var sidebar = $('.container-sidebar');
	var main = $('.container-main');

	var heightAdjust = function() {
		sidebar.css("min-height", $(window).height());
		main.css("min-height", $(window).height());

		var sidebarHeight = sidebar.height();
		var mainHeight = main.height();

		if(sidebarHeight > mainHeight) {
			main.height(sidebarHeight + 90);
			mainHeight = sidebarHeight + 90;
		}
		if(mainHeight > sidebarHeight) {
			sidebar.height(sidebarHeight - 90);
		}
		$('.bg-img:not(.interior)').height(mainHeight - 70);
	};

	heightAdjust();

	$('.toggle-nav').click(function() {
		$('.container-sidebar').toggleClass('active');
		$('.container-main').toggleClass('active');
		$('.off-canvas-overlay').toggleClass('active');
	});

	var closeMenu = function() {
		$('.container-sidebar').removeClass('active');
		$('.container-main').removeClass('active');
		$('.off-canvas-overlay').removeClass('active');
	}

	$('.close-menu').click(function() {
		closeMenu();
	});
	$('.off-canvas-overlay').click(function() {
		closeMenu();
	});


	$('.selectpicker').change(function() {
		console.log('you chamged the select menu to ' + $(this).val());
		window.location='http://www.domain.com/mypage?id=' + this.value;
	});


	// On Resize:

	$(window).resize(function() {
		if($(window).outerWidth() >= 1200) {
			closeMenu();
		}
		heightAdjust();
	});
}); //end of js function
