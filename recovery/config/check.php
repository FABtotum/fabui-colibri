<?php

ob_implicit_flush();
ob_end_flush();

?><!DOCTYPE html>
<html lang="en-us">
	<head>
		<meta charset="utf-8">
		<meta name="author" content="FABteam">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="HandheldFriendly" content="true">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<title>FAB UI beta</title>
		<link rel="shortcut icon" href="/assets/img/favicon/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/assets/img/favicon/favicon.ico" type="image/x-icon">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/smartadmin-production-plugins.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/smartadmin-production.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/smartadmin-skins.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/demo.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/font-fabtotum.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/js/plugin/magnific-popup/magnific-popup.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/fonts.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/fabtotum_style.css">
		<style>

			.table {
				font-size:13px !important;
			}
			
			.danger {
				font-weight: bolder !important;
			}
		
			#main {
				margin-left: 0px !important;
			}
			kbd#console { display:block; white-space:pre-line; font-weight:600; min-height:490px; }
		</style>
		<script src="/assets/js/libs/jquery-2.1.1.min.js"></script>
		<script src="/assets/js/libs/jquery-ui-1.10.3.min.js"></script>
	</head>
	<body>
		<header id="header">
			<div id="logo-group">
				<span id="logo"><img src="/assets/img/logo-0.png"></span>
			</div>
		</header>
		<div id="main" role="main">
			<div id="ribbon">
				<ol class="breadcrumb">
					<li><a href="/recovery/index.php">Recovery</a></li>
					<li><a href="/recovery/config">Config</a></li>
					<li>Check</li>
				</ol>
			</div>
			<div id="content">
				<kbd id="console" class="col-xs-12 font-md">Checking FABui environment configuration...
				<?php $code = 0;
					foreach (array(
						'python check-python.py',
						'php check-php.php'
					) as $script)
					{
						system ($script, $ret);
						if ($ret > $code)
							$code = $ret;
						if ($ret >= 3)
							break;
					}
					switch ($code)
					{
						case 0:
							echo "\nAll tests passed with no errors\n";
							break;
						case 1:
							echo "\nTest passed with some warnings\n";
							break;
						case 2:
							echo "\nTest failed because of some errors\n";
							break;
						case 3: default:
							echo "\nTest halted due to an unrecoverable failure\n";
					}
				?>
				</kbd>
			</div>
		</div>
	</body>
</html>
