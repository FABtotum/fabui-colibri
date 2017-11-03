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
<div class="row">
	<div class="col-sm-12">
		<div class="text-center  update-box">
			<h1 class="tada animated">
				<span class="fabtotum-icon"> <i style="font-size: 200px;" class="fabui-core"></i></span>
			</h1>
			<?php if($internet):?>
			<h3><i class="fa fa-check"></i> <?php echo _("You successfully connected with your FABID account")?></h3>
			<?php else:?>
			<h3><i class="fa fa-warning"></i> <?php echo _("No internet connection")?><br><?php echo _("Connect the FABTotum to internet and try again");?></h3>
			<?php endif;?>
		</div>
	</div>
</div>