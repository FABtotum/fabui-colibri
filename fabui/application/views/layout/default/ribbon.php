<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<!-- RIBBON -->
<div id="ribbon">
	<span class="ribbon-button-alignment" id="ribbon-left-buttons"> 
		<span id="lock"    class="btn btn-ribbon" data-action="lockPage"     data-title="Lock"    rel="tooltip" data-placement="bottom" data-original-title="<?php echo _("Lock screen");?>"><i class="fa fa-lock"></i></span>
		<span id="refresh" class="btn btn-ribbon" data-action="resetWidgets" data-title="refresh" rel="tooltip" data-placement="bottom" data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset all your widget settings." data-html="true" data-reset-msg="Would you like to RESET all your saved widgets and clear LocalStorage?"><i class="fa fa-refresh"></i></span>
		<?php if(isset($this->session->user['settings']['fabid']['logged_in']) && $this->session->user['settings']['fabid']['logged_in'] == true):?>
			<span id="ribbon-fabid-button" class="btn btn-ribbon" rel="tooltip" data-placement="bottom" data-html="true" data-title="<i class='text-success fa fa-check'></i> <?php echo _("You are connected with your FABID account")." (".$this->session->user['settings']['fabid']["email"].")";?>" ><i class="fa fa-check text-success"></i> <?php echo _("FABID connected");?></span>
		<?php else: ?>
			<span id="ribbon-fabid-button" class="btn btn-ribbon" rel="tooltip" data-placement="bottom" data-title="<?php echo _("Connect with your FABID account");?>" data-action="fabidLogin"><?php echo _("Connect with FABID");?></span>
		<?php endif; ?>
	</span>
	<!-- breadcrumb -->
	<ol class="breadcrumb">
		<!-- This is auto generated -->
	</ol>
</div>
<!-- END RIBBON --> 