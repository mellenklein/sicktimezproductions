<?php
$page_title = 'Contact';
$banner_image = 'header-mic.jpg';

$songs_db = new Model('songs');
$songs = $songs_db->get_items(array('order' => 'sort', 'where' => 'active = 1'));

if(isset($url_segments[1])) {
	$songs_db->load($url_segments[1], 'slug');
}

ob_start();
?>
<?php include($thinBannerURL); ?>

	<!-- contact form starts here: -->
	<div class="black-bg" id="contact-section">
		<div class="row">
			<div class="large-12 columns small-centered tb-pad-60">
				<div class="row">
					<div class="large-8 columns small-centered">
						<div id="messageShell" style="display:none;">
							<h4>Thank You!</h4>
							<p class="text-center">Your message has been sent.</p>
						</div>
						<h2 class="tb-pad-60 text-center">Hit Me Up</h2>
						<form action="." method="post" id="contactForm" class="text-center" name="contactForm" novalidate="novalidate">
							<div class="tb-pad-15">
								<label class="show-for-ie9" for="name">Name</label>
								<input id="contactName" required type="text" name="name" value="" placeholder="Name" />
							</div>
							<div class="tb-pad-15">
								<label class="show-for-ie9" for="email">Email</label>
								<input id="contactEmail" type="text" name="email" placeholder="Email" required>
							</div>
							<div class="tb-pad-15">
								<label class="show-for-ie9" for="message">Message</label>
								<textarea id="contactMessage" type="text" name="message" placeholder="Message" required></textarea>
							</div>
							<div class="tb-pad-30">
								<button type="submit" class="button primary large" id="contactBtn">Send</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end of contact form -->

<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
?>
<script src="/plugins/validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function(){

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
				 url: './ajax.contact.php',
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
