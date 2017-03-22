<style>.jumbotron{padding:20px;} .jumbotron p {font-size: 15px;} </style>

<div class="row">
	<div class="col-sm-12">
		<div class="well well-light no-border">
			<div class="row margin-bottom-10">
				<div class="col-sm-6">
					<div class="smart-form">
						<fieldset style="background: none !important;">
							<label class="label font-md">Please select which head you want to install </label>
							<section>
                                <label class="select"> <?php echo form_dropdown('heads', $heads_list, $head, 'class="input-lg" id="heads"'); ?> <i></i> </label>
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
											<a style="padding: 6px 12px;" target="_blank" href="<?php echo $heads[$head]['link']; ?>" class="btn btn-default no-ajax">More details</a>
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
