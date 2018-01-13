<?php

$page_title = 'Contact';
$jq_validate = true;

ob_start();
/* Custom css or other header links/includes go here. */
?>

<?php
$output['head'] = ob_get_contents();
ob_clean();

ob_start();
/* Main page content goes here */
?>

<?php echo show_message('callout'); ?>

<!-- Background-image and text: -->
<div class="page-hero" style="background-image:url(/images/quiz-page-hero.jpg)">
	<div class="columns medium-12 text-center">
		<div class="center-wrap">
			<h1 class="text-center"><?php echo $page_title; ?></h1>
		</div>
	</div>
</div> <!-- end of Background-image and text -->

<div class="row tb-pad-60 form-wrap">
	<div class="columns large-8 small-10 small-centered">
		<div id="contact">
			<form action="." method="post" id="contact-tep-form">
				<input type="hidden" name="action" value="contact-email">
				<div class="">
					<label for="name">Your Name*</label>
					<input type="text" name="name" id="name" required value="" />
				</div>
				<div class="">
					<label for="email">Your Email*</label>
					<input type="text" name="email" id="email" required value="" />
				</div>
				<div class="">
					<label for="message">Your Message*</label>
					<textarea name="message" id="message" rows="8" cols="40" required></textarea>
				</div>
				<div class="">
					<p class="text-right req">*Required fields</p>
				</div>
				<div class="row tb-pad-30">
					<div class="columns medium-12 submit-btn">
						<button type="submit" class="button submit-btn">Submit</button>
					</div>
				</div>
			</form>
		</div>
		<div id="contact-success" style="display: none;">
			<h4 class="text-center">Thank You!</h4>
			<p class="text-center">Your message has been sent.</p>
		</div>
	</div>
</div>




<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
/* Custom JavaScript goes here. */
?>
<script>
$('document').ready(function() {
	$('#contact-tep-form').validate({
		rules: {
			email: {
				email: true
			}
		},
		submitHandler: function(form) {
			$.ajax({
				type: "POST",
				url: "<?php $config->get('url'); ?>ajax",
				data: $('#contact-tep-form').serialize(),
				success: function(data){
					$('#contact').hide();
					$('#contact-success').show();
				},
				dataType: "json"
			});
		}
	});
});
</script>

<?php
$output['footer_js'] = ob_get_contents();
ob_clean();
?>
