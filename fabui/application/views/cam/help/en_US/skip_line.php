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
	<div class="col-sm-12">
		<p>Skip line setting controls how many horizontal laser lines will be
			skipped.</p>
		<p>
			Two parameters control this behaviour. <strong>Group size</strong>
			defining the number of lines to be handled as a group and <strong>pattern</strong>
			which controls which line in the group should be burned or skipped.
		</p>
		<p>
			<strong>Pattern</strong> parameter is a comma separated list of
			element numbers that should be burned. Ex. <strong>0,1</strong> or <strong>0,1,5</strong>
			or just <strong>0</strong>
		</p>
		<p>
			If you don't want to skip any lines then set <strong>Group size</strong>
			to 1 and <strong>pattern</strong> to 0.
		</p>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<h5>Example</h5>
		<p>Here we have three examples of Group_size and pattern
			configurations. The right images show 8 lines numbered from 0 to 7
			and the left images show which lines are skipped.</p>
		<p>Group size makes the line numbers repat in a group over and over
			again. For a group of three it would be 0,1,2,0,1,2... and for a
			group of four it would be 0,1,2,3,0,1,2...</p>
		<p>Therefore with the pattern parameter you can select which line
			numbers should be burned simply but enumerating them.</p>
	</div>
</div>
<div class="row">
	<div class="col-sm-4 text-center">

		<span class="font-xs margin-bottom-10">Group_size = 2, Pattern = 0</span>
		<img class="img-responsive margin-bottom-10"
			src="<?php echo base_url('/assets/img/cam/skip_2_0.png');?>">
		<p>Every other line is skipped.</p>
	</div>

	<div class="col-sm-4 text-center">


		<span class="font-xs margin-bottom-10">Group_size = 3, Pattern = 0,1</span>
		<img class="img-responsive margin-bottom-10"
			src="<?php echo base_url('/assets/img/cam/skip_3_01.png');?>">
		<p>In a group of 3 the third line is skipped.</p>

	</div>

	<div class="col-sm-4 text-center">

		<span class="font-xs margin-bottom-10">Group_size = 4, Pattern = 0,1</span>
		<img class="img-responsive margin-bottom-10"
			src="<?php echo base_url('/assets/img/cam/skip_4_01.png');?>">
		<p>In a group of 4 the last two lines are skipped.</p>

	</div>
</div>