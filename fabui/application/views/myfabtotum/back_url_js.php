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
	setTimeout(function(){
		var regex_install = /fabui\/install/g;
		var regex_login   = /fabui\/login/g;
		var openerPathName = window.opener.location.pathname.replace(/([^:])(\/\/+)/g, '$1/').replace(/\/$/, "");
		var fromInstall = false;
		var fromLogin   = false;
		
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
		

		if(fromInstall){
			<?php if($internet):?>
			window.opener.$("#fabid").val("<?php echo $fabid;?>").triggerHandler('change');
			<?php endif; ?>
		}else if(fromLogin){
			<?php if($internet):?>
			window.opener.$("#fabid").val("<?php echo $fabid;?>");
			window.opener.$('body').css("opacity", "0.4");
			window.opener.$("#fabid-login-form").submit();
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