<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<?php if(isset($wlanInfo['ip_address']) && $wlanInfo['ip_address'] != ''): ?>
<div class="row wifi-details">
	<div class="col-sm-12">
		<div class="well">
			<dl class="dl-horizontal">
			  <dt>Connectedo to </dt>
			  <dd><?php echo $wlanInfo['ssid'] ?></dd>
			  <!-- -->
			  <dt>IP Address </dt>
			  <dd><a href="<?php echo $wlanInfo['ip_address'] ?>"><?php echo $wlanInfo['ip_address'] ?></a></dd>
			  <!-- -->
			  <dt>Mac Address </dt>
			  <dd><?php echo $wlanInfo['mac_address'] ?></dd>
			  <!-- -->
			  <dt>AP Mac Address </dt>
			  <dd><?php echo $wlanInfo['ap_mac_address'] ?></dd>
			  <!-- -->
			  <dt>Bit rate</dt>
			  <dd><?php echo $wlanInfo['bitrate'] ?></dd>
			   <!-- -->
			  <dt>Frequency</dt>
			  <dd><?php echo $wlanInfo['frequency'] ?></dd>
			  <!-- -->
			  <dt>IEEE</dt>
			  <dd><?php echo $wlanInfo['ieee'] ?></dd>
			</dl>
		</div>
	</div>
</div>
<?php endif; ?>
<div class="row">
	<div class="col-sm-12 table-container">
	</div>
</div>
<!-- PASSWORD MODAL -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-lock"></i> <span id="passwordModalTitle"></span> <i class="fa fa-wifi"></i></h4>
			</div>
			<div class="modal-body custom-scroll " id="progressModalBody">
				<form class="smart-form" id="passwordModalForm">
					<fieldset>
						<section>
							<label class="input"> <i class="icon-prepend fa fa-lock"></i>
								<input type="password" class="input-password" placeholder="insert password" id="wifiPassword" name="wifiPassword">
							</label>
						</section>
						<section>
							<label class="checkbox">
								<input type="checkbox" class="show-password"> <i></i> Show password
							</label>
						</section>
					</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="modalConnectButton"><i class="fa fa-check"></i> Connect </button>
			</div>
		</div>
	</div>
</div>
<!-- HIDDEN WIFI MODAL -->
<div class="modal fade" id="hiddenWifiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-user-secret"></i> Hidden WiFi <i class="fa fa-wifi"></i></h4>
			</div>
			<div class="modal-body custom-scroll " id="progressModalBody">
				<form class="smart-form" id="hiddenWifiForm">
					<fieldset>
						<section>
							<label class="input"> <i class="icon-prepend fa fa-user-secret"></i>
								<input type="text" placeholder="Wifi ESSID" id="hiddenWifiName" name="hiddenWifiName">
							</label>
						</section>
						<section>
							<label class="input"> <i class="icon-prepend fa fa-lock"></i>
								<input type="password" class="input-password" placeholder="insert password" id="hiddenWifiPassword" name="hiddenWifiPassword">
							</label>
						</section>
						<section>
							<label class="checkbox">
								<input type="checkbox" class="show-password"> <i></i> Show password
							</label>
						</section>
					</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="hiddenModalConnectButton"><i class="fa fa-check"></i> Connect </button>
			</div>
		</div>
	</div>
</div>