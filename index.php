<?php

header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
session_start();

# control $_SESSION inactivity time
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
	// last request was more than 30 minates ago
	@session_destroy(); // destroy session data in storage
	session_unset(); // unset $_SESSION variable for the runtime
}

$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
if($_GET['func'] == 'logout') session_unset();

# Includes ----------------------------------
include 'inc/config.php';
include $conf_include_path .'comm.php';
include $conf_include_path .'connect.php';
include $conf_include_path .'oops_comm.php';

if(!$_GET['lang'] && !$_SESSION['misc']['lang']) $_GET['lang'] = $conf_default_lang;
if($_GET['lang']) $_SESSION['misc']['lang'] = $_GET['lang'];
include $conf_include_path .'translation.php';
date_default_timezone_set($conf_timezone);

# Sanitize get and post ----------------------------------
sanitize_input();

# Logout user ----------------------------------
if($_GET['func'] == 'logout') {
	# store record of user logging out?
	session_unset(); session_destroy();
	jump_to($conf_main_page);
	exit();
}

# Get user info ----------------------------------
if(!isset($_SESSION['login']['user_id']))
	$_SESSION['login']['user_id'] = $conf_generic_user_id;
# Create objects for this page. ----------------------------------
if(!isset($ob_user))
	$ob_user = new user($_SESSION['login']['user_id'], $_SESSION['login']['name']);

$now = new date_time('now');
# Manage modules ----------------------------------
if(!$_GET['mod']) $_GET['mod'] = $conf_default_mod;
	refresh_users_modules(true);

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="LCGaste Ltd" />
	<meta name="keywords" content="LCGaste Rocaya ersmsk miltonrooms rooms milton keynes" />
	<meta name="author" content="humans.txt">
    
	<link rel="icon" href="../../favicon.ico">

    <title>::: Lcgaste.com :::</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/main.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="http://www.lcgaste.com">Lcgaste.com</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" action="<?= $conf_main_page; ?>?action=login" method="post" name="login_form">
		<div class="input-group">
		  <input type="text" class="form-control" placeholder="Username" aria-describedby="usrname" name="user" id="user">
		  <span class="input-group-addon" id="usrname">@</span>
		</div>
		<div class="input-group">
		  <input type="text" class="form-control" placeholder="Password" aria-describedby="pasapalabra" name="pass" id="pass">
		  <span class="input-group-addon" id="pasapalabra">
		  	<span class="glyphicon glyphicon-lock" aria-hidden="true"></span>
		  </span>
		</div>
            <button type="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </nav>
    <div class="jumbotron">
      <div class="container">
        <h1>LCGaste ltd.</h1>
        <p>IT consultancy and web development.</p>
       <!-- <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more &raquo;</a></p>-->
      </div>
    </div>
    <div class="container">
      <div class="row">
	    <div class="websites">
		<div class="col-lg-4">
		  <img class="img-circle" src="<?= $conf_images_path; ?>rocaya_capture.png" alt="www.rocaya.com" height="140" width="140">
		  <h2>rocaya.com</h2>
		  <p>Rock climbing, crags, routes, blog and photos about climbing</p>
		  <p><a class="btn btn-default" href="http://www.rocaya.com" role="button">Enter &raquo;</a></p>
		</div>
		<div class="col-lg-4">
		  <img class="img-circle" src="<?= $conf_images_path; ?>MiltonCapture.JPG" alt="www.miltonrooms.com" height="140" width="140">
		  <h2>MiltonRooms</h2>
		  <p>Flexible and affordable accommodation in Milton Keynes in a private residence.</p>
		  <p><a class="btn btn-default" href="http://www.miltonrooms.com" role="button" target="_blank">Enter &raquo;</a></p>
		</div>
		<div class="col-lg-4">
		  <img class="img-circle" src="<?= $conf_images_path; ?>ersmsk.png" alt="www.ersmsk.com" height="140" width="140">
		  <h2>ERSMSK.com</h2>
		  <p>ERS Global is an international project finance and energy consulting company, specialising in the delivery of engineering projects, energy resources consulting, procurement of project financing and government liaison to effect projects..</p>
		  <p><a class="btn btn-default" href="http://www.lcgaste.com/ersmsk" role="button">Enter &raquo;</a></p>
		</div>
        </div><!-- websites -->
	  </div><!-- row -->
	<!--  
	<div class="container">
		<div class="labs">
			<div class="row">
				<div class="col-md-4">
					pi pic
				</div>
				<div class="col-md-8">
					<h3>Raspberry PI</h3>
					<p>Some experiments with Raspberry PI: Web server, Current Cost logger</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-8">
					<h3>Sports Club manager</h3>
					<p>Our Sports Club manager allows to manage bookings, users, payments, schedules, classes, etc.</p>
				</div>
				<div class="col-md-4">
					padel pic
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					arduino pic
				</div>
				<div class="col-md-8">
					<h3>Arduino</h3>
					<p>Arduino stuff we're working on</p>
				</div>
			</div>
		</div>
	</div>
      <hr>
      -->
      <footer>
        <p>&copy; Lcgaste ltd. <?= date('Y'); ?></p>
      </footer>
    </div> <!-- /container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="<?= $conf_include_path; ?>js/bootstrap.min.js"></script>
    <script language="javascript" src="<?= $conf_include_path; ?>comm.js"></script>
    <script language="javascript" src="<?= $conf_include_path; ?>ajax.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>-->
  </body>
</html>
