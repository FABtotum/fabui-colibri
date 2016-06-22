<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="row">
	<div class="col-sm-3 hidden-xs hidden-sm"></div>
	<div class="col-sm-6">
		<div class="well no-padding">
			<form action="<?php echo site_url('login/doNewAccount'); ?>" method="POST" id="register-form" class="smart-form client-form">
				<header><i class="fa fa-play fa-rotate-90"></i> Register new account</header>
				<fieldset>
					<section>
						<label class="input"> <i class="icon-append fa fa-user"></i>
							<input type="email" name="email" placeholder="Email">
							<b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i> Needed to enter the website</b></label>
					</section>
					<section>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="password" placeholder="Password" id="password">
							<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> Don't forget your password</b> </label>
					</section>
					<section>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="passwordConfirm" placeholder="Confirm password">
							<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> Don't forget your password</b> </label>
					</section>
				</fieldset>
				<fieldset>
					<div class="row">
						<section class="col col-6">
							<label class="input"> 
								<input type="text" placeholder="First name" name="first_name">
							</label>
						</section>
						<section class="col col-6">
							<label class="input"> 
								<input type="text" placeholder="Last name" name="last_name">
							</label>
						</section>
					</div>
					<section>
						<label class="checkbox">
							<input type="checkbox" name="terms" id="terms">
							<i></i>I agree with the <a href="#" data-toggle="modal" data-target="#myModal"> Terms and Conditions </a>
						</label>
					</section>
				</fieldset>
				<footer>
					<button type="submit" class="btn btn-primary">Register</button>
				</footer>
			</form>
		</div>
	</div>
	<div class="col-sm-3 hidden-xs hidden-sm"></div>
</div>
<!-- TERMS & CONDITIONS MODAL -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Terms & Conditions</h4>
			</div>
				<div class="modal-body custom-scroll terms-body">
					<div>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam non justo nec orci bibendum eleifend id eget ex. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Curabitur scelerisque luctus orci. Aliquam nec dolor sed nunc placerat aliquam. Suspendisse condimentum convallis leo eu convallis. Phasellus nec tempus elit, a luctus metus. Proin vel bibendum urna, in mattis turpis. Integer varius eget justo at luctus. Vestibulum consequat arcu dolor, quis pretium dolor porttitor id. Praesent sed arcu ante. Cras sed purus ornare, varius dui a, commodo justo.

Proin sagittis convallis tortor, non dictum leo sodales sed. Aliquam erat volutpat. Nunc nec interdum est. Donec nec pharetra nibh, ac sodales elit. Donec nec ultricies elit. Mauris sit amet elementum sem, sed volutpat elit. Praesent facilisis turpis aliquet, congue mi at, scelerisque tellus. Proin egestas nisl vel iaculis egestas. Pellentesque accumsan id leo tincidunt volutpat. Proin in felis magna. Mauris elit turpis, porttitor ut mollis eget, malesuada fringilla quam. Fusce sit amet ante metus. Integer quis nunc vitae est consectetur convallis nec non nulla. Fusce cursus blandit odio et porttitor. Mauris dictum, nunc quis dapibus egestas, enim ante tempus felis, non suscipit ligula augue sed nisl. Vivamus non lacinia dolor.

Nam sit amet mauris purus. Cras malesuada lacus vel molestie pretium. Suspendisse nec maximus dolor. Integer ex lacus, mattis eget ullamcorper quis, cursus nec augue. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Phasellus gravida bibendum felis, quis venenatis lectus condimentum id. Maecenas rutrum malesuada erat. Curabitur sed turpis ac libero sodales aliquet. Phasellus rhoncus vel arcu ac aliquet. Proin rhoncus nisl arcu. Donec consequat diam eu orci elementum, ut dapibus leo tempus.

Mauris posuere massa et lectus malesuada, in varius enim volutpat. Vivamus pharetra, odio eget consequat vestibulum, orci metus rutrum felis, non sollicitudin neque dolor at mauris. Praesent molestie tristique augue ut ultrices. Maecenas ut metus ut tellus accumsan varius vitae id sem. Fusce elementum velit justo, sit amet sagittis elit lacinia eget. Suspendisse mattis porta lorem id malesuada. Phasellus sed libero efficitur, semper est at, hendrerit sem. Aliquam semper magna neque, congue mollis magna venenatis et. Maecenas iaculis non sapien non dictum. Etiam convallis, libero vitae pulvinar sodales, orci erat aliquet purus, ut condimentum dui erat ut risus. Morbi in urna bibendum, laoreet urna quis, viverra metus. Curabitur quis eros id diam molestie faucibus. Etiam tempor lobortis fringilla.

Donec lacus velit, lobortis vel elementum et, commodo quis mauris. Vivamus efficitur, urna a rhoncus commodo, mauris ante accumsan lacus, in imperdiet urna felis eget purus. Integer semper augue eu lorem lobortis, ac pulvinar ex maximus. Pellentesque fermentum eleifend cursus. Cras gravida dignissim nulla, quis dictum mauris cursus in. Aliquam est nulla, suscipit sed posuere porttitor, feugiat nec ex. Nulla scelerisque sed turpis sed aliquam. Nunc lectus lorem, tempor at pretium ac, eleifend nec nibh. Maecenas hendrerit malesuada ex, at condimentum lorem ultricies vel. Phasellus elementum sapien eget diam venenatis, nec lobortis nisl dictum. Nulla dignissim condimentum lorem, at interdum mi ultricies quis. Sed sem risus, dignissim sit amet molestie a, bibendum mollis nulla.</p>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">
						Cancel
					</button>
					<button type="button" class="btn btn-primary" id="i-agree">
						<i class="fa fa-check"></i> I Agree
					</button>
					
					<button type="button" class="btn btn-danger pull-left" id="print">
						<i class="fa fa-print"></i> Print
					</button>
				</div>
			</div>
		</div>
	</div>
