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
	106 : '<?php echo _("Y min Endstop hit: Move the carriage to the center or check Settings > Hardware > Custom Settings >Invert X Endstop Logic"); ?>',
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
 * FAB APP TEXT
 */
var app_text = {
	0  : '<?php echo _("Hi")?>',
	1  : '<?php echo _("Cancel")?>',
	2  : '<?php echo _("Go") ?>',
	3  : '<?php echo _("Shutdown") ?>',
	4  : '<?php echo _("Restart") ?>',
	5  : '<?php echo _("Logout") ?>',
	6  : '<?php echo _("No") ?>',
	7  : '<?php echo _("Yes") ?>',
	8  : '<?php echo _("Resetting controller") ?>',
	9  : '<?php echo _("Aborting all operations") ?>',
	10 : '<?php echo _("Reloading page") ?>',
	11 : '<?php echo _("Restart in progress") ?>',
	12 : '<?php echo _("Please wait") ?>',
	13 : '<?php echo _("You will be redirect to login page") ?>',
	14 : '<?php echo _("Shutdown in progress") ?>',
	15 : '<?php echo _("Now you can switch off the power") ?>',
	16 : '<?php echo _("You have attempted to leave this page. The Fabtotum Personal Fabricator is still working. Are you sure you want to reload this page?") ?>',
	17 : '<?php echo _("Front panel has been opened") ?>',
	18 : '<?php echo _("Ok") ?>',
	19 : '<?php echo _("Ignore") ?>',
	20 : '<?php echo _("Install head") ?>',
	21 : '<?php echo _("Press OK to continue or Ignore to disable this warning") ?>',
	22 : '<?php echo _("Tasks") ?>',
	23 : '<?php echo _("Oops.. An error occurred") ?>',
	24 : '<?php echo _("You will be redirect to recovery page") ?>',
	25 : '<?php echo _("Wifi connected") ?>',
	26 : '<?php echo _("Internet available") ?>',
	27 : '<?php echo _("Installing head") ?>',
	28 : '<?php echo _("Before proceed make sure the head is properly locked in place") ?>'
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
