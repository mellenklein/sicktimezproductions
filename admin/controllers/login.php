<?php
if($user->id){
	header('location:'.$config->get('admin_url'));
	exit();
}

// Process site login
if(isset($_POST['username'])){
	$user = new Admin($_POST['username'], $_POST['password']);
	if(!$user->id){
		$_SESSION['msg_type'] = 'fail';
		$_SESSION['msg_text'] = 'Invalid username or password.';
	}
	header('location:'.$config->get('admin_url'));
	exit();
}

// Output body HTML
ob_start();
?>
<div class="row">
	<div class="col-sm-12">
		<div class="bg-img" id="login">
			<div class="overlay">
				<div class="login-box">
					<div class="login-inner">
						<a href="http://www.foxfuelcreative.com" target="_blank"><img src="images/FoxDen-logo-square.png" alt="Welcome to the FoxDen"></a>
						<form action="<?php echo $config->get('admin_url'); ?>login" method="post">
							<label class="show-for-ie" for="username">Username</label>
							<input class="in_text" type="text" name="username" id="username" placeholder="Username" value="" />
							<label class="show-for-ie" for="password">Password</label>
							<input class="in_text" type="password" name="password" id="password" placeholder="Password" value="" />
							<input class="btn primary-btn" type="submit" value="Login" />
						</form>
						<!--
						<p>Don't have an account? <a href="#">Request access.</a></p>
						<p><a href="#">Forgot password?</a></p>
						-->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
$output['content'] = ob_get_contents();
ob_clean();
?>
