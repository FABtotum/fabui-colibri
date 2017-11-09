<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<script type="text/javascript">
	var timeout = <?php echo $internet ? 1000 : 5000 ?>;

	var fromInstall = false;
	var fromLogin   = false;

	var regex_install = /fabui\/install/g;
	var regex_login   = /fabui\/login/g;
	var openerPathName = window.opener.location.pathname.replace(/([^:])(\/\/+)/g, '$1/').replace(/\/$/, "");

	var mInstall = regex_install.exec(openerPathName);
	
	if(mInstall != null){
		mInstall.forEach((match, groupIndex) => {
	        fromInstall = true;
	    });
	}

	var mLogin = regex_login.exec(openerPathName);
	
	if(mLogin != null){
		mLogin.forEach((match, groupIndex) => {
			fromLogin = true;
	    });
	}

	<?php if(!$internet): ?>
	 if(fromLogin){
		 $(".update-box").append('<h5><?php echo _("Sign-in with local access and go to network settings");?></h5>');
	 }
	<?php endif; ?>
	
	setTimeout(function(){
			
		if(fromInstall){
			<?php if($internet):?>
			window.opener.$("#fabid").val("<?php echo $fabid;?>").triggerHandler('change');
			<?php endif; ?>
		}else if(fromLogin){
			<?php if($internet):?>
			window.opener.$("#fabid").val("<?php echo $fabid;?>");
			window.opener.$('body').css("opacity", "0.4");
			window.opener.$("#fabid-login-form").submit();
			disableButton(window.opener.$(".btn"));
			<?php endif;?>
		}
		else{
			<?php if($internet):?>
			window.opener.location.reload(true);
			<?php endif;?>
		}
		window.close();
	}, timeout);
</script>