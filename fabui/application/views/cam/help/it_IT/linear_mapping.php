<?php
/**
 *
 * @author FABteam
 * @version 0.0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */
?>
<div class="row">
	<div class="col-sm-6 text-center">
		<img class="img-responsive margin-bottom-10"
			src="<?php echo base_url('/assets/img/cam/linear_mapping_graph.png');?>" />
	</div>
	<div class="col-sm-6">
		<p>Linear mapping adjusts PWM or Feedrate value depending on the
			individual pixel color value. As the image is converted to gray scale
			possible color range is 0-255.</p>
		<p>Four values control how the mapping will be done.</p>
		<p>
			<strong>Input-min</strong> and <strong>Input-max</strong> parameters
			define the input color range.
		</p>
		<p>
			Any color value smaller then <strong>Input-min</strong> will result
			in zero output. This means that you can control which will be the
			lightest color value that will activate the laser.
		</p>
		<p>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
			With <strong>Output-min</strong> and <strong>Output-max</strong>
			parameters you can control the minimum and maximum values for PWM or
			Feedrate.
		</p>
		<p>
			Any color value greater then <strong>Output-max</strong> will be
			reduced to <strong>Output-max</strong>. Using this parameter you can
			control the maximum PWM or Feedrate used to burn the image.
		</p>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
	
		<h5>Example</h5>
		<p>Let's set the PWM parameters to:</p>
		<p>
			<strong>Input-min:</strong> 50, <strong>Input-max:</strong> 200, <strong>Output-min:</strong>
			150, <strong>Output-max:</strong> 180
		</p>
		<p>This configuration will activate the laser only for color values
			greater then 50 and will start with PWM value 150. The maximum PWM
			value used will be 180. Any color value greater then 200 will be
			mapped to PWM value 180.</p>
		<p>
			A setup like this will give inverted values as <strong>white</strong>
			color has the value of 255 and <strong>black</strong> has the value
			0. To get the right colors you will need to check the <strong>Invert
				color values</strong> checkbox.
		</p>

	</div>
</div>