<?php
$page_title = '';
$banner_image = '';
$banner_image = 'sicktimez_studio.jpg';
$home_hero_content = '<h2>Stoke</h2><h3>Your</h3><h2>Sound</h2>';

$song_db = new Model('songs');
$songs = $song_db->get_items(array('order' => 'sort', 'where' => 'active = 1'));

ob_start();
/* Custom css or other header links/includes go here. */
?>

<?php
$output['head'] = ob_get_contents();
ob_clean();
ob_start();
/* Main page content goes here */
?>

<!-- Hero content starts here: -->
<?php include($thinBannerURL); ?>

<!-- Intro section starts here: -->

<!-- end of Intro section -->


<?php
$output['content'] = ob_get_contents();
ob_clean();
ob_start();
/* Custom JavaScript goes here. */
?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/howler/1.1.28/howler.min.js"></script>
<script type="text/javascript" src="/bower_components/scroll-magic/dist/scrollmagic.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {

		var i = 0;

		var player = $('.player'),
    audio = player.find('audio'),
		art = player.find('.album-art'),
		trackname = player.find('.song'),
    duration = $('.duration'),
    currentTime = $('.current-time'),
    progressBar = $('.progress span'),
    mouseDown = false,
    rewind, showCurrentTime;
		var totalTracks = audio.length;
		console.log('totalTracks: ' +totalTracks);
		console.log(duration);

		function secsToMins(time) {
		  var int = Math.floor(time),
		      mins = Math.floor(int / 60),
		      secs = int % 60,
		      newTime = mins + ':' + ('0' + secs).slice(-2);

		  return newTime;
		}

		function getCurrentTime() {
		  var currentTimeFormatted = secsToMins(audio[i].currentTime),
		      currentTimePercentage = audio[i].currentTime / audio[i].duration * 100;
					var currArt = art[i].currentSrc;
					$('#currArt').html('<img src="'+currArt+'" alt="Tempest Way">');
					var currSong = trackname[i];
					$('#currSong').html(currSong);
					$('#currSong .song').fadeIn();
					var currDuration = secsToMins(audio[i].duration);
					console.log(currDuration);
					$('.duration').text(currDuration);

		  currentTime.text(currentTimeFormatted);
		  progressBar.css('width', currentTimePercentage + '%');

		  if (player.hasClass('playing')) {
		    showCurrentTime = requestAnimationFrame(getCurrentTime);
		  } else {
		    cancelAnimationFrame(showCurrentTime);
		  }
		}

		audio.on('loadedmetadata', function() {
		  var durationFormatted = secsToMins(audio[i].duration);
		  duration.text(durationFormatted);
		}).on('ended', function() {
		  if ($('.repeat').hasClass('active')) {
		    audio[i].currentTime = 0;
		    audio[i].play();
		  } else {
		    player.removeClass('playing').addClass('paused');
		    audio[i].currentTime = 0;
				console.log("The audio has ended");
				playNextTrack();
				player.removeClass('paused').addClass('playing');
				getCurrentTime();
				audio[i].play();

		  }
		});

		//When you click a button:
		$('.player button').on('click', function() {
		  var self = $(this);

		  if (self.hasClass('play-pause') && player.hasClass('paused')) {
		    player.removeClass('paused').addClass('playing');
		    audio[i].play();
				$('.song').hide();
		    getCurrentTime();
		  } else if (self.hasClass('play-pause') && player.hasClass('playing')) {
		    player.removeClass('playing').addClass('paused');
		    audio[i].pause();
		  }

		  if (self.hasClass('shuffle') || self.hasClass('repeat')) {
		    self.toggleClass('active');
		  }
		}).on('mousedown', function() {
		  var self = $(this);

		  if (self.hasClass('rw')) {
		    player.addClass('rwing');
		    rewind = setInterval(function() { audio[i].currentTime -= .3; }, 100);
		  }
		}).on('mouseup', function() {
		  var self = $(this);

			//when you click the "next" btn:
		  if (self.hasClass('ff')) {
				//go to next track:
				playNextTrack();
		  }

		  if (self.hasClass('rw')) {
		    player.removeClass('rwing');
		    clearInterval(rewind);
		  }
		});

		player.on('mousedown mouseup', function() {
		  mouseDown = !mouseDown;
		});

		progressBar.parent().on('click mousemove', function(e) {
		  var self = $(this),
		      totalWidth = self.width(),
		      offsetX = e.offsetX,
		      offsetPercentage = offsetX / totalWidth;

		  if (mouseDown || e.type === 'click') {
		    audio[i].currentTime = audio[i].duration * offsetPercentage;
		    if (player.hasClass('paused')) {
		      progressBar.css('width', offsetPercentage * 100 + '%');
		    }
		  }
		});

		var playNextTrack = function(){
			audio[i].pause();
			$('.song').fadeOut();
			//reset first track, change track, then play next track from start:
			audio[i].currentTime = 0;
			console.log('currentTime: '+ audio[i].currentTime);

			if (i < totalTracks-1) {
				i += 1;
			} else {
				i = 0;
			}
			 getCurrentTime();
			 if(player.hasClass('playing')) {
				 audio[i].play();
			 }
			console.log('track number = '+ i);
		}

		$('#showPlayer').click(function(){
			$(this).fadeOut(100, function(){
				$('#playerGoesHere #audio-player').slideDown(1000);
				$('.hero').addClass('overlay');
			});
		});

		$('#closeBtn').click(function(){
			$('#showPlayer').show();
			$('#playerGoesHere #audio-player').slideUp(1000, function(){
				$('.hero').removeClass('overlay');
			});
		});



	}); //end of custom js
</script>
<?php
$output['footer_js'] = ob_get_contents();
ob_clean();
?>
