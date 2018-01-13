<?php
ob_start();
?>
<script>
var hours = 0;
var h = 0;
var greeting = '';
function startTime() {
	var today = new Date();
	h = today.getHours();
	/*h = h+1;
	if(h == 24) {
		h = 0;
	}*/
	var m = today.getMinutes();
	var s = today.getSeconds();
	var ampm = h >= 12 ? 'pm' : 'am';
	hours = h % 12;
	hours = hours ? hours : 12;
	m = checkTime(m);
	s = checkTime(s);
	/*document.getElementById('clock').innerHTML = hours + ":" + m + ":" + s + ' ' + ampm;*/
	document.getElementById('clock').innerHTML = hours + ":" + m;
	
	var t = setTimeout(startTime, 500);
}
function checkTime(i) {
	if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
	return i;
}
	$(document).ready(function(){
		startTime();
		changeGreeting();
		function changeGreeting() {
			if(h >= 0 && h < 12 && greeting != 'morning') {
				$('.greeting').hide();
				$('.greet-morning').fadeIn("slow");
				greeting = 'morning';
			} else if(h >= 12 && h < 18 && greeting != 'afternoon') {
				$('.greeting').hide();
				$('.greet-afternoon').fadeIn("slow");
				greeting = 'afternoon';
			} else if(h >= 18 && greeting != 'evening') {
				$('.greeting').hide();
				$('.greet-evening').fadeIn("slow");
				greeting = 'evening';
			}
			var to = setTimeout(changeGreeting, 500);
		}
	});
</script>
<?php
$output['head'] = ob_get_contents();
ob_clean();
ob_start();
?>
<div class="row bg-img">
	<div class="col-md-12">
		<div class="wide_col">
			<div class="text-center">
				<h1 id="clock"></h1>
				<h2>Good <span class="greeting greet-morning">morning</span><span class="greeting greet-afternoon">afternoon</span><span class="greeting greet-evening">evening</span>, <?php echo $user->data['first_name']; ?>.</h2>
			</div>
		</div>
	</div>
</div>
<?php
$output['content'] = ob_get_contents();
ob_clean();
?>