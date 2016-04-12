<script type="text/javascript">
		/* DO NOT REMOVE : GLOBAL FUNCTIONS!*/
		$(document).ready(function() {
			pageSetUp();
			/*init*/
			fabApp.webSocket();
			fabApp.FabActions();
			fabApp.domReadyMisc();
			fabApp.drawBreadCrumb();
			fabApp.isInternetAvailable();
			fabApp.checkUpdates();
			/* launch intervals */
			$.notification_interval = setInterval(fabApp.checkNotifications, $.notification_interval_timer);
			$.safety_interval = setInterval(fabApp.checkSafetyStatus, $.safety_interval_timer);
			$.temperatures_interval = setInterval(fabApp.getTemperatures, $.temperatures_interval_timer);
			/*events handler*/
			window.onbeforeunload = fabApp.checkExit;
			
			fabApp.checkForFirstSetupWizard();
		});
</script>