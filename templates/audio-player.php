<div id="audio-player" class="audio-player-wrapper">
	<div class="audio-player-image">
		<span class="audio-player-song-name"></span>
	</div>

	<div class="player paused">
		<a id="closeBtn">×</a>
		<div class="album">
			<?php foreach($songs as $s): ?>
				<div class="art-test"><img class="album-art" src="/assets/images/songs/<?php echo $s['featured_image']; ?>" alt="<?php echo $s['album']; ?>" /></div>
			<?php endforeach;	 ?>
			<div class="cover">
				<div class="art-container" id="currArt">
					<?php foreach($songs as $s): ?>
						<img src="/assets/images/songs/<?php echo $s['featured_image']; ?>" alt="<?php echo $s['album']; ?>" />
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="info">
			<div class="time">
				<span class="current-time">0:00</span>
				<span class="progress"><span></span></span>
				<span class="duration">0:00</span>
			</div>
			<div class="actions">
				<button class="button rw">
					<div class="arrow"></div>
					<div class="arrow"></div>
				</button>
				<button class="button play-pause">
					<div class="arrow"></div>
				</button>
				<button class="button ff">
					<div class="arrow"></div>
					<div class="arrow"></div>
				</button>
			</div>

			<div class="track-info tb-pad-30">
				<?php foreach ($songs as $s): ?>
					<div class="song">
						<h1><?php echo $s['name']; ?></h1>
						<h2><?php echo $s['artist']; ?></h2>
					</div>
				<?php endforeach; ?>

				<div class="song-container" id="currSong">

				</div>
			</div>
		</div>

		<?php foreach ($songs as $s): ?>
			<audio preload src="/assets/uploads/songs/<?php echo $s['file']; ?>"></audio>
		<?php endforeach; ?>
	</div>
</div>
