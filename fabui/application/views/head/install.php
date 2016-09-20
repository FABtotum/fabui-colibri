<style>.jumbotron{padding:20px;} .jumbotron p {font-size: 15px;} </style>
<div class="row">
	<div class="col-sm-12 alerts-container">
<?php if(!isset($units['hardware']['head']) || $units['hardware']['head'] == ''): ?>
		<div class="alert alert-warning animated  fadeIn" role="alert">
			<i class="fa fa-warning"></i><strong>Warning</strong> Seems that you still have not set the head your are using.
		</div>
<?php else: ?>
		<div class="alert alert-info animated  fadeIn" role="alert">
			<i class="fa fa-info-circle"></i> Currently  your <strong>FABtotum Personal Fabricator</strong> is configured to use <strong><?php echo  $heads[$head]['name']; ?></strong>
		</div>
<?php endif; ?>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<div class="well">
			<div class="row margin-bottom-10">
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12">
							<p class="font-md">Make sure you removed the filament, milling bits and any other accessory on the head.<br>See also <a href="<?php echo site_url('maintenance/spool') ?>">spool maintenance</a></p>
						</div>
					</div>	
					<div class="smart-form">
						<fieldset style="background: none !important;">
							<section>
								<label class="label font-md">Please select which head you want to install </label><label class="select"> <?php echo form_dropdown('heads', $heads_list, $head, 'class="input-lg" id="heads"'); ?> <i></i> </label>
							</section>
						</fieldset>
					</div>
					
					<div class="row" style="margin-top:-30px">
						<div class="col-sm-12">
							<div class="smart-form">
								<fieldset style="background: none !important;">
									<div id="description-container">
										<?php if($head != 'head_shape'): ?>
											<div class="jumbotron">
											<p class="margin-bottom-10 "><?php echo $heads[$head]['description'] ?></p>
											<?php if($heads[$head]['link'] != ''): ?>
											<a style="padding: 6px 12px;" target="_blank" href="<?php echo $heads[$head]['link']; ?>" class="btn btn-default ">More details</a>
											</div>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								</fieldset>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 text-center image-container">
					<a target="_blank" href="javascript:void(0);"><img id="head_img" style="width: 50%; display: inline; cursor:default;" class="img-responsive" src="<?php echo '/assets/img/head/'.$head.'.png'; ?>"></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row hidden">
	<?php foreach($heads as $name => $val): ?>
		<div id="<?php echo $name ?>_description">
			<p class="margin-bottom-10"><?php echo $val['description']; ?></p>
			<?php if($val['link'] != ''): ?>
			<a style="padding: 6px 12px;" target="_blank" href="<?php echo $val['link']; ?>" class="btn btn-default ">More details</a>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
