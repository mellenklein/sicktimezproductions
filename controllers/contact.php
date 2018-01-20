<?php
$page_title = 'Seattle';

$img_dir = '/images/';
$hero_image = 'contact-hero.jpg';
$hero_text = '';


ob_start();
?>
<!-- Black contact form starts here: -->
<div class="black-bg" id="contact-section">
	<div class="row">
		<div class="large-12 columns small-centered">
			<div class="row">
				<div class="large-8 columns small-centered">
					<div id="messageShell" style="display:none;">
						<h4>Thank You!</h4>
						<p class="text-center">Your message has been sent.</p>
					</div>

					<form action="." method="post" id="contactForm" class="text-center" name="contactForm" novalidate="novalidate">
						<label class="show-for-ie9" for="name">Name</label>
						<input id="contactName" required type="text" name="name" value="" placeholder="Name" />
						<label class="show-for-ie9" for="email">Email</label>
						<input id="contactEmail" type="text" name="email" placeholder="Email" required>
						<label class="show-for-ie9" for="message">Message</label>
						<textarea id="contactMessage" type="text" name="message" placeholder="Message" required></textarea>
						<div class="new-btn">
							<button type="submit" class="button primary" id="contactBtn" name="contactBtn">Send</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- end of Black contact form -->



<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
?>

	<script src="/plugins/selectric/jquery.selectric.js"></script>
	<script type="text/javascript">

		$(document).ready(function(){

			// Initialize Select Menu special styles (disable for mobile):
			// var contactAnimationOne = false;
      //
		  // // Scroll magic initialize:
		  // var controller = new ScrollMagic.Controller();
      //
			// // Scene 1:
		  // var sceneContact = new ScrollMagic.Scene({
		  //     triggerElement: "#contact-section",
		  //     duration: 400,
		  //     offset: 0
		  //   })
		  //   .addTo(controller)
      //
		  // .on("progress", function(e) {
		  //   $('#contact-section form').addClass('move');
		  //   $('#contact-section').addClass('move');
		  //   //the number from top youve scrolled
		  //   if (e.progress.toFixed(3) >= 0 && contactAnimationOne == false) {
		  //     $('#contact-section form').addClass('move');
			// 		$('#contact-section').addClass('move');
		  //     contactAnimationOne = true;
		  //   }
		  // });

			$('#contactBtn').click(function(e){
			  e.preventDefault();
			  $('#contactForm').submit();
			 });

			 $('#contactForm').submit(function(e) {
		     e.preventDefault();
		   }).validate({
				rules: {
					email: {
						required: true,
						email: true
					}
				},
				submitHandler: function(form) {

					var formData = $('#contactForm').serialize();
					formData += '&action=contact-form';
					$.ajax({
						 type: 'POST',
						 url: './ajax.seattle.php',
						 data: formData,
						 success: function(data){
							 if (data.status == 'error') {
								$('#errorShell').html('Oops! An error occurred while processing your answers.');
								$('#messageShell h3').hide();
								$('#messageShell p').html('There was a problem processing your message. Please try again.').css('color', 'red');
								$('#messageShell').show();
							} else {
								//successful
								$('#contactForm').slideUp(500);
								$('#messageShell').slideDown(500);
							}
						 }
					 });
					$('#contactForm').slideUp();
					$('#messageShell').slideDown();
				}
			});


		}); //end of document.ready

</script>


<?php
$output['footer_js'] = ob_get_contents();
ob_clean();
?>
