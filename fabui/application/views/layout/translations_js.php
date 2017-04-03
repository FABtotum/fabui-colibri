<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Translastions for javascript text
 * 
 */
?>
<script type="text/javascript">
/**
 *  Emergency Errors Description
 */
var emergency_descriptions = {
	100 : '<?php echo _("General Safety Lockdown"); ?>',
	101 : '<?php echo _("Printer stopped due to errors"); ?>',
	102 : '<?php echo _("Front panel is open, cannot continue"); ?>',
	103 : '<?php echo _("Head not properly aligned or absent"); ?>',
	104 : '<?php echo _("Extruder Temperature critical, shutting down"); ?>',
	105 : '<?php echo _("Bed Temperature critical, shutting down"); ?>',
	106 : '<?php echo _("X max Endstop hit: Move the carriage to the center or check Settings > Hardware > Custom Settings >Invert X Endstop Logic"); ?>',
	107 : '<?php echo _("X min Endstop hit: Move the carriage to the center or check Settings > Hardware > Custom Settings >Invert X Endstop Logic"); ?>',
	108 : '<?php echo _("Y max Endstop hit: Move the carriage to the center and reset"); ?>',
	109 : '<?php echo _("Y min Endstop hit: Move the carriage to the center and reset"); ?>',
	110 : '<?php echo _("The FABtotum has been idling for more than 10 minutes. Temperatures and Motors have been turned off"); ?>',
	120 : '<?php echo _("Both Y Endstops hit at the same time"); ?>',
	121 : '<?php echo _("Both Z Endstops hit at the same time"); ?>',
	122 : '<?php echo _("Ambient temperature is less then 15&deg;C. Cannot continue"); ?>',
	123 : '<?php echo _("Cannot extrude filament: the nozzle temperature is too low"); ?>',
	124 : '<?php echo _("Cannot extrude so much filament!"); ?>'
}

/**
 * GETTEXT
 */
var gettext_data = <?php echo getJsonTranslation(); ?>;
function _(msgid)
{
	if(gettext_data.hasOwnProperty(msgid))
	{
		var msgstr = gettext_data[msgid];
		if(msgstr[1] == "")
			return msgid;
		return msgstr[1];
	}
	return msgid;
}
</script>
