<?php
$page_title = $page->data['title'];
$banner_image = $page->data['banner_image'];

ob_start();
/* Custom css or other header links/includes go here. */
?>

<?php
$output['head'] = ob_get_contents();
ob_clean();
ob_start();
/* Main page content goes here */
?>
<?php include($thinBannerURL); ?>

<!-- end of Hero for interior pages -->
<div class="spacer"></div>

<!-- Main content: -->
<div class="totalContent">
	<?php echo $page->data['content']; ?>
</div>
<!-- end of main CMS content -->

<?php if($url_segments[0] == 'testing'): ?>
	<!-- Part of template for "About" page only: -->

	<!-- Photo grid starts here: -->
	<!-- <div class="grid about-page">
		<div class="grid-item grid-item--height3" style="background-image:url('/images/about-photo01.jpg')">&nbsp;</div>
		<div class="grid-item grid-item--height4 grid-item--width2" style="background-image:url('/images/about-photo02.jpg')">&nbsp;</div>
		<div class="grid-item grid-item--height4" style="background-image:url('/images/about-photo03.jpg')">&nbsp;</div>
		<div class="grid-item grid-item--height4" style="background-image:url('/images/about-photo04.jpg')">&nbsp;</div>
		<div class="grid-item grid-item--height4 grid-item--width2" style="background-image:url('/images/about-photo05.jpg')">&nbsp;</div>
	</div> -->
	<!-- end of photo grid -->

	<!-- end of template for "About" page only -->
<?php endif; ?>

<?php if($url_segments[0] == 'about'): ?>
	<!-- Part of template for "About" page only: -->
	<!-- end of template for "About" page only -->
<?php endif; ?>




<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
/* Custom JavaScript goes here. */
?>
<script type="text/javascript">


$(document).ready(function() {
	//------ Drawing green and purple lines: ---------//
	function drawLines () {
		if($('img').hasClass('purple-line') || $('img').hasClass('green-line')) {

			$('img.purple-line').after('<div class="purple-line"></div>').removeClass('purple-line');

			$('img.green-line').after('<div class="green-line"></div>').removeClass('green-line');
		}
	};
	drawLines();

	setTimeout(function() {
		//------ Calculating purple and green line positions: ---------//
		var purpImgHeight = $('.purple-line').prev('img').height();
		var greenImgHeight = $('.green-line').prev('img').height();
		var purpLine = $('.purple-line').prev('img').next('.purple-line');
		var greenLine = $('.green-line').prev('img').next('.green-line');

		function calculateLinePosition (imageHeight, coloredLine) {
			if(imageHeight > 0) {
				coloredLine.css('marginTop', '-'+ ((imageHeight/2) + 90) + 'px');
				coloredLine.addClass('show-for-medium');
			} else {

			}
		};
		calculateLinePosition(purpImgHeight, purpLine);
		calculateLinePosition(greenImgHeight, greenLine);

		//---------- Calculating left image position: -------//
		var marginLeft = $('.main-content').css("marginLeft");
		$('.left-image').css('marginLeft', '-' + marginLeft);

		//---------- Calculating right image position: ------//
		var marginRight = parseInt($('.main-content').css("marginRight").replace('px', ''));
		var pushRight = $('.main-content').width() - ($('.right-image').width()) + marginRight;
		$('.right-image').css('marginLeft', pushRight);
	}, 200);

	$(window).resize(function() {
		setTimeout(function() {
			var marginLeft = $('.main-content').css("marginLeft");
			$('.left-image').css('marginLeft', '-' + marginLeft);

			var marginRight = parseInt($('.main-content').css("marginRight").replace('px', ''));
			var rightMargin = $('.main-content').width() - ($('.right-image').width()) + marginRight;
			$('.right-image').css('marginLeft', rightMargin);
		}, 200);
	});
});
</script>
<?php
$output['footer_js'] = ob_get_contents();
ob_clean();
?>
