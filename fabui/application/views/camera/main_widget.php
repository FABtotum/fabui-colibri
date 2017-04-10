<div class="row">
	<div class="col-sm-12">
		<div class="well">
			<div class="row">

	<div class="col-sm-7 margin-bottom-10">

		<div class="row margin-bottom-10">
			<div class="col-sm-12">
				<img id="raspi_picture" class="img-responsive" src="<?php echo site_url('cam/getPicture') ?>" />
				<div id="result"></div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-8 margin-bottom-10">
				
				<div class="btn-group btn-group-justified">
					<div class="btn-group">
						<button type="button" data-attribue-direction="up" class="btn btn-default btn-sm directions "> Y <i class="fa fa-arrow-up"></i></button>
					</div>
					<div class="btn-group">
						<button type="button" class="btn btn-default btn-sm" id="take_photo"><i class="fa fa-camera"></i> <span class="hidden-mobile hidden-tablet"><?php echo _("Take a picture");?></span></button>
					</div>
					<div class="btn-group">
						<button type="button" data-attribue-direction="down" class="btn btn-default btn-sm directions "> Y <i class="fa fa-arrow-down"></i></button>
					</div>
				</div>

			</div>

			<div class="col-sm-4 ">
				<div class="btn-group btn-group-justified">
					<a  href="cam/downloadPicture" id="download_photo" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <span class="hidden-mobile hidden-tablet"> <?php echo _("Download");?> </span></a>
				</div>
			</div>
		</div>

	</div>

	<div class="col-sm-5">

		<ul id="widget-cam-tab" class="nav nav-tabs bordered">
			<li class="active">
				<a href="#tab-photo" data-toggle="tab"><i class="fa fa-camera"></i> <?php echo _("Photo");?></a>
			</li>
			<li>
				<a href="#tab-settings" data-toggle="tab"><i class="fa fa-cogs"></i> <?php echo _("Settings");?></a>
			</li>
		</ul>

		<div class="tab-content padding-10">
			<div class="tab-pane fade in active" id="tab-photo">

				<div class="row">

					<div class="col-sm-12">

						<div class="smart-form">
							<fieldset>
								<section class="col col-6">
									<label class="label"><?php echo _("Type");?></label>
									<label class="select">
										<?php echo form_dropdown('encoding', $params['encoding'], $settings['encoding'], 'id="encoding"'); ?><i></i> </label>
								</section>
								<section class="col col-6">
									<label class="label"><?php echo _("Size");?></label>
									<label class="select">
										<?php echo form_dropdown('size', $params['size'], $settings['width'].'x'.$settings['height'], 'id="size"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("ISO");?></label>
									<label class="select">
										<?php echo form_dropdown('iso', $params['ISO'], $settings['ISO'], 'id="iso"'); ?><i></i> </label>
								</section>
								<section class="col col-6">
									<label class="label"><?php echo _("Quality");?> %</label>
									<label class="select">
										<?php echo form_dropdown('quality', $params['quality'], $settings['quality'], 'id="quality"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("Effect");?></label>
									<label class="select">
										<?php echo form_dropdown('imxfx', $params['imxfx'], $settings['imxfx'], 'id="imxfx" class="form-control"'); ?><i></i> </label>
								</section>
								
								<section class="col col-6">
									<label class="label"><?php echo _("Flip");?></label>
									<label class="select">
										<?php echo form_dropdown('flip', $params['flip'], $settings['flip'], 'id="flip" class="form-control"'); ?><i></i> </label>
								</section>
								
							</fieldset>

						</div>

					</div>
				</div>

			</div>

			<div class="tab-pane fade in" id="tab-settings">

				<div class="row">
					<div class="col-sm-12">
						<div class="smart-form">
							<fieldset>
								<section class="col col-6">
									<label class="label"><?php echo _("Brightness");?></label>
									<label class="select">
										<?php echo form_dropdown('brightness', $params['brightness'], $settings['brightness'], 'id="brightness"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("Contrast");?></label>
									<label class="select">
										<?php echo form_dropdown('contrast', $params['contrast'], $settings['contrast'], 'id="contrast"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("Sharpness");?></label>
									<label class="select">
										<?php echo form_dropdown('sharpness', $params['contrast'], $settings['sharpness'], 'id="sharpness"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("Saturation");?></label>
									<label class="select">
										<?php echo form_dropdown('saturation', $params['contrast'], $settings['saturation'], 'id="saturation"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("AWB");?></label>
									<label class="select">
										<?php echo form_dropdown('awb', $params['awb'], $settings['awb'], 'id="awb"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("EV Comp.");?></label>
									<label class="select">
										<?php echo form_dropdown('ev_comp', $params['ev_comp'], $settings['ev'], 'id="ev_comp"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("Exposure");?></label>
									<label class="select">
										<?php echo form_dropdown('exposure', $params['exposure'], $settings['exposure'], 'id="exposure"'); ?><i></i> </label>
								</section>

								<section class="col col-6">
									<label class="label"><?php echo _("Rotation");?></label>
									<label class="select">
										<?php echo form_dropdown('rotation', $params['rotation'], $settings['rotation'], 'id="rotation"'); ?><i></i> </label>
								</section>
								
								<section class="col col-6">
									<label class="label"><?php echo _("Metering");?></label>
									<label class="select">
										<?php echo form_dropdown('metering', $params['metering'], $settings['metering'], 'id="metering"'); ?><i></i> </label>
								</section>

							</fieldset>

						</div>
					</div>
				</div>

			</div>

		</div>
		
		<p>
			<button type="button" class="btn btn-default btn-block set-default"><?php echo _("Default All");?></button>
		</p>

	</div>

			</div>
		</div>
	</div>
</div>
