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


		var regex = /fabui\/install/g;
		var openerPathName = window.opener.location.pathname;
		var fromInstall = false;
		
		var m = regex.exec(openerPathName);

		console.log(m);
		if(m != null){
			m.forEach((match, groupIndex) => {
		        fromInstall = true;
		    });
		}

		if(fromInstall){
			window.opener.$("#fabid").val("<?php echo $fabid;?>").triggerHandler('change');
		}else{
			window.opener.location.reload(true);
		}
		window.close();
	}, 1000);
</script>