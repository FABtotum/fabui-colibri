<?php
/**
 * 
 * @author FABteam
 * @version 0.0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<script type="text/javascript">
	var fields_descriptions = {
		"target_width" : {
			"title" : "<?php echo _("Target width");?>",
			"description" : "<?php echo _("Engraving width in mm.<br> Height is automatically calculated based on image ratio.<br>For DXF files set to 0 to use file units with scale factor 1.0"); ?>"
		},
		"target_height" : {
			"title" : "<?php echo _("Target height"); ?>",
			"description": "<?php echo _("Engraving height in mm.<br>Width is automatically calculated based on image ratio.<br>For DXF files set to 0 to use file units with scale factor 1.0"); ?>"
		},
		"general-dot_size": {
			"title": "<?php echo _("Laser dot size");?>",
			"description": "<?php echo _("If burning lines are visible reduce it untill they are gone"); ?>"
		},
		"pwm-value" : {
			"title": "<?php echo _("PWM Value");?>",
			"description": "<?php echo _("Laser PWM value");?>",
		},
		"invert" : {
			"title" : "<?php echo _("Invert");?>",
			"description": "<?php echo _("Invert image gray colors to invert what is burned")?>"
		},
		"pwm-off_during_travel" : {
			"title": "<?php echo _("Travel moves");?>",
			"description": "<?php echo _("To prevent laser leaving travel trails, turn it off during travel moves.");?>"
		},
		"pwm-in_min" : {
			"title": "",
			"description": ""
		},
		"pwm-in_max" : {
			"title": "",
			"description": ""
		},
		"pwm-out_min" : {
			"title": "",
			"description" : ""
		},
		"pwm-out_max" : {
			"title": "",
			"description" : ""
		}
	};
</script>