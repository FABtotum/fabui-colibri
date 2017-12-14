<?php 


?>
<h3>Hi, <?php echo $user['first_name'];?></h3>
<p><?php echo pyformat( _('We\'ve generated a URL to reset your password. If you did not request to reset your password or if you\'ve changed your mind, simply ignore this email and nothing will happen.<br><br>You can reset your password by clicking the following URL:<br><a href="{0}">{0}</a><br><br>If clicking the URL above does not work, copy and paste the URL into a browser window. The URL will only be valid for a limited time and will expire.'), array($complete_url) ) ?>;</p>
