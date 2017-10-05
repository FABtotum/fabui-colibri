<?php
/**
 * 
 * @author Krios Mane
 * @author Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<div class="row fuelux">
	<div class="col-sm-12">
	<?php echo $wizard?>
	</div>
</div>
<div class="row hidden" id="product-container">
	<div class="col-xs-12 col-sm-12 col-md-12">
		<h5 id="slider-title"><?php echo _("Get more filaments");?></h5>
		<div class="carousel carousel-showmanymoveone slide " id="itemslider">
			<div class="carousel-inner"><!-- PRODUCTS HERE --></div>
			<div id="slider-control">
				<a class="left carousel-control" href="#itemslider"  data-slide="prev"><img src="/assets/img/arrow_left.png" alt="<?php echo _("Left");?>" class="img-responsive"></a> 
				<a class="right carousel-control" href="#itemslider" data-slide="next"><img src="/assets/img/arrow_right.png" alt="<?php echo _("Right");?>" class="img-responsive"></a>
			</div>
			
		</div>
	</div>
</div>