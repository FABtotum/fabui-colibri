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
	setTimeout(function(){


		var regex_install = /fabui\/install/g;
		var regex_login   = /fabui\/login/g;
		var openerPathName = window.opener.location.pathname;
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
			window.opener.$("#fabid").val("<?php echo $fabid;?>").triggerHandler('change');
		}else if(fromLogin){
			window.opener.$("#fabid").val("<?php echo $fabid;?>");
			window.opener.$('body').css("opacity", "0.4");
			window.opener.$("#fabid-login-form").submit();
		}
		else{
			window.opener.location.reload(true);
		}
		window.close();
	}, 1000);
</script>