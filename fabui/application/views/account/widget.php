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
	<div class="col-sm-12">
		<form class="smart-form" id="user-form" action="<?php echo site_url('user') ?>" method="post">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label"><?php echo _("First name"); ?></label>
						<label class="input">
							<i class="icon-prepend fa fa-user"></i>
							<input type="text" placeholder="<?php echo _("First name"); ?>" name="first_name" id="first_name" value="<?php echo $this->session->user['first_name'] ?>">
						</label>
					</section>
					<section class="col col-6">
						<label class="label"><?php echo _("Last name"); ?></label>
						<label class="input">
							<i class="icon-prepend fa fa-user"></i>
							<input type="text" placeholder="<?php echo _("Last name"); ?>" name="last_name" id="last_name" value="<?php echo $this->session->user['last_name'] ?>">
						</label>
					</section>
				</div>
				<section >
					<label class="label"><?php echo _("Email")?></label>
					<label class="input">
						<i class="icon-prepend fa fa fa-envelope-o"></i>
						<input type="text" name="email" id="email" value="<?php echo $this->session->user['email'] ?>">
					</label>
				</section>
				<section>
					<label class="label"><?php echo _("Language")?></label>
					<label class="select">
						<?php echo langauges_menu('form-control', 'settings-language', 'id="settings-language"');?> <i></i>
					</label>
				</section>
			</fieldset>
		</form>
	</div>
</div>