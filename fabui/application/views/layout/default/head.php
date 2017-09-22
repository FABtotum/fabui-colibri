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
<title> .: FABUI :. </title>
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
<link rel="shortcut icon" href="/assets/img/favicon/favicon.ico" type="image/x-icon">
<link rel="icon"          href="/assets/img/favicon/favicon.ico" type="image/x-icon">
<!-- HEADERD JAVASCRIPTS -->
<script src="/assets/js/libs/jquery-2.1.1.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script src="/assets/js/libs/jquery-ui-1.10.3.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php echo $this->cssInline; ?>
<!--  TRANSLATIONS -->
<?php if(isset($translations)) echo $translations; ?>
<!-- END TRANSLATIONS -->
<?php echo $this->jsInLineTop; ?>