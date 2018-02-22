<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="row">
	<div class="col-sm-12">
		<a href="javascript:void(0);" class="btn btn-default sync"><i class="fas fa-sync"></i> <?php echo _("Sync");?></a>
		<a href="projects/add" class="btn btn-default pull-right"><?php echo _("Crate project");?></a>
	</div>
</div>
<div class="row" id="projects-container"></div>
<div class="row">
	<div class="col-sm-12 text-center">
		<button data-attribute-limit="<?php echo $default_limit; ?>" data-attribute-offset="<?php echo $default_offset; ?>" id="load-more-button" class="btn btn-default"><?php echo _("Load more");?></button>
	</div>
</div>
