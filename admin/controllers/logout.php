<?php
$user->logout();
header('location:'.$config->get('admin_url'));
?>