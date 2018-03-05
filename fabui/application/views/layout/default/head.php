<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<!-- IE11 -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!-- WEB MANIFEST -->
<link rel="manifest" href="/assets/manifest.json">
<title><?php echo getHostName(); ?> - </title>
<?php foreach($this->meta_tags as $name => $value): ?>
<meta name="<?php echo $name ?>" content="<?php echo $value; ?>">
<?php endforeach; ?>
<meta charset="UTF-8">
<!-- Basic Styles -->
<?php if(ENVIRONMENT == 'production' && file_exists(FCPATH.'/assets/css/mandatory.css')): ?>
	<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/mandatory.css?v=<?php echo FABUI_VERSION ?>">
<?php else:?>
	<?php foreach($this->css_mandatory as $css):?>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $css ?>?v=<?php echo FABUI_VERSION ?>">
	<?php endforeach;?>
<?php endif?>
<!-- PAGE RELATED CSS FILES -->
<?php echo cssFilesInclusion($this->css); ?>
<!-- FAVICONS -->
<?php if(isset($this->session->user['settings']['image']['url']) && $this->session->user['settings']['image']['url'] != ''):?>
	<link rel="shortcut icon" href="<?php echo $this->session->user['settings']['image']['url'];?>" type="image/x-icon">
    <link rel="icon"          href="<?php echo $this->session->user['settings']['image']['url'];?>" type="image/x-icon">
<?php else:?>
    <link rel="shortcut icon" href="/assets/img/favicon/favicon.png" type="image/x-icon">
    <link rel="icon"          href="/assets/img/favicon/favicon.png" type="image/x-icon">
<?php endif;?>
<!-- HEADERD JAVASCRIPTS -->
<script src="/assets/js/libs/jquery-3.2.1.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script src="/assets/js/libs/jquery-ui.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php echo $this->cssInline; ?>
<!--  TRANSLATIONS -->
<?php if(isset($translations)) echo $translations; ?>
<!-- END TRANSLATIONS -->
<?php echo $this->jsInLineTop; ?>
<script type="text/javascript">
	var page_title_prefix = $(document).find("title").text();
</script>
<noscript>
    <style type="text/css">
        #main, #header, #left-panel {display:none !important;}
    </style>
    <div class="alert alert-danger animeted fadeIn alert-block text-center" style="margin-top: 10em;">
		<h1><?php echo _("We are sorry, but FABUI doesn't work properly without JavaScript enabled.<br>Please enable JavaScript and reload the page");?></h1>
	</div>
</noscript>