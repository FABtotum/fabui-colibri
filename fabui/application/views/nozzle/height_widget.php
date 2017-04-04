
<div class="row">
	
	<div class="col-sm-12 alerts-container">
	<?php if($warning): ?>
		<div class="alert alert-info animated  fadeIn" role="alert">
			<i class="fa fa-info-circle"></i> Seems that you still have not done nozzle height calibration for this head.
		</div>
	<?php endif; ?>
	</div>
	
    <div class="col-sm-12">
        
            
            <div class="row margin-top-10 choice">
    
        <div class="col-sm-6">
            <div class="well well-light">
                <h3 class="text-center text-primary"><?php echo _("Assisted calibration");?></h3>
                <h5 class="text-center"><?php echo _("Helps you correct the nozzle height during prints. Each time you swap heads you should re-calibrate.");?></h5>
                <h2 class="text-center"><a data-action='normal' href="javascript:void(0);" class="btn btn-default btn-primary btn-circle  choice-button" id="nozzle-calibrate-normal"><i class="fa fa-chevron-down"></i></a></h2>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="well well-light">
            <h3 class="text-center text-primary"><?php echo _("Fine calibration");?></h3>
            <h5 class="text-center"><?php echo _("Manually edit the override distance to fine tune the nozzle height during prints.");?></h5>
            <h2 class="text-center"><a data-action='fast' href="javascript:void(0);" class="btn btn-default btn-primary btn-circle  choice-button" id="nozzle-calibrate-fast"><i class="fa fa-chevron-down"></i></a></h2>
            </div>
        </div>
    </div>
    
    
    <div class="row margin-top-10 re-choice" style="display: none;">
        <div class="col-sm-12">
            <h2 class="text-center"><a data-action='unload' href="javascript:void(0);" class="btn btn-primary btn-default btn-circle  re-choice-button"><i class="fa fa-chevron-up"></i></a></h2>
        </div>
    </div>
    
    <div class="row margin-top-10 calibration" id="row-normal-1" style="display:none;">
        <div class="col-sm-12">
          <div class="well well-light">
                <div class="row">
                    
                    <div class="col-sm-6 text-center">
                        <img style="max-width: 50%; display: inline;" class="img-responsive" src="/assets/img/controllers/probe/nozzle.png" />
                    </div>
                    
                     <div class="col-sm-6 text-center">
                        <h2><?php echo _("Make sure nozzle is clean and then press OK to continue");?></h2>
                        <button id="probe-calibration-prepare" class="btn btn-primary btn-default ">Ok</button>
                    </div>
          		</div>
          </div>
        </div>
    </div>
    <div class="row margin-top-10 calibration" id="row-normal-2" style="display:none;">
        <div class="col-sm-12">
            <div class="well well-light">
                <div class="row">
                    <div class="col-sm-6 text-center">
                        <img style="max-width: 50%; display: inline;" class="img-responsive" src="/assets/img/controllers/probe/head_calibration.png" />
                    </div>
                    <div class="col-sm-6">
                        <div class="row margin-bottom-20">
                            <div class="col-sm-12">
                                <h4 class="text-center">
                                    <?php echo _("Using the buttons below, raise the bed until a standard piece of copy paper (80 mg) can barely move between the nozzle and the bed.");?>
                                    <br>
                                    <i class="fa fa-warning"></i> <?php echo _("Caution the nozzle is hot!");?>
                                    <br><br>
                                    <?php echo _("When done press Calibrate to finish");?>
                                </h4>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            
                            <div class="smart-form">
                                <fieldset style="background: none; !important">
                                    <div class="row">
                                        <section class="col col-3 text-center">
                                            <label><strong>Z</strong></label>
                                        </section>
                                        <section class="col col-6 text-center">
                                            <label><strong><?php echo _("Step (mm)");?></strong></label>
                                        </section>
                                        <section class="col col-3 text-center">
                                            <label><strong>Z</strong></label>
                                        </section>
                                    </div>
                                    <div class="row">
                                        <section class="col col-3">
                                            <button data-action="+" type="button" title="<?php echo _("Away from nozzle");?>" class="btn  btn-default btn-primary btn-sm btn-block z-action"><i class="fa fa-arrow-down"></i> </button>
                                        </section>
                                        <section class="col col-6">
                                            <label class="input"><input id="z-value" type="text" style="text-align: center;" value="0.1"></label>
                                        </section>
                                        <section class="col col-3">
                                            <button data-action="-" type="button" title="<?php echo _("Closer to nozzle");?>" class="btn btn-primary  btn-default btn-sm btn-block z-action"><i class="fa fa-arrow-up"></i></button>
                                        </section>
                                    </div>
                                </fieldset>  
                            </div>
                        </div>
                        <div class="row text-align-center">
                            <button id="probe-calibration-calibrate" class="btn btn-primary btn-default ">Calibrate</button>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <div class="row margin-top-10 calibration" id="row-normal-3" style="display:none;">
        <div class="col-sm-12">
            
            <div class="row">
                <div class="col-sm-3">
                    <h3 class="text-primary"><?php echo _("Calibration result");?></h3>
                </div>
                <div class="col-sm-9">
                    <button class="btn btn-primary btn-default  pull-right calibrate-again"><?php echo _("Calibrate again");?></button>
                </div>
            </div>
            <div class="row margin-top-10">
                <div class="col-sm-12">
                    <pre id="calibrate-trace"></pre>
                </div>
            </div>
        </div>
    </div>



    <div class="row margin-top-10 calibration" id="row-fast-1" style="display:none;">
        
        <div class="col-sm-12">
            <div class="well well-light">
                <div class="row">
                    <div class="col-sm-6">
                    	<h3 class="text-center text-primary"><?php echo _("Fine calibration");?></h3>
                        <h4 class="text-center">
                          
<?php echo _("If the print first layer is too high or too close to the bed, use this function to finely calibrate the distance from the nozzle and the bed during 3D-prints. Usually 0.05mm increments are enough to make a difference.");?>
                            
                        </h4>
                    </div>
                    <div class="col-sm-6">
                    
                        <div class="row">
                            <div class="smart-form">
                                <fieldset style="background: none; !important">
                                    <div class="row">
                                        <section class="col col-3 text-center">
                                            <label><strong><?php echo _("Closer");?></strong></label>
                                        </section>
                                        <section class="col col-6 text-center">
                                            <label><?php echo _('<strong>Distance override (<span id="nozzle-offset"></span> mm)</strong>');?></label>
                                        </section>
                                        <section class="col col-3 text-center">
                                            <label><strong><?php echo _("Further");?></strong></label>
                                        </section>
                                    </div>
                                    <div class="row">
                                        <section class="col col-3">
                                            <button data-action="-" type="button" class="btn btn-primary btn-default btn-sm btn-block change-over"><i class="fa fa-minus"></i> </button>
                                        </section>
                                        <section class="col col-6">
                                            <label class="input"><input max="2" min="-2" id="over" type="text" style="text-align: center;" readonly="true" value="0"></label>
                                        </section>
                                        <section class="col col-3">
                                            <button data-action="+" type="button" class="btn btn-primary btn-default btn-sm btn-block change-over"><i class="fa fa-plus"></i></button>
                                        </section>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="row text-align-center">
                            <button id="probe-calibration-save" class="btn btn-primary btn-default ">Save</button>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <div class="row margin-top-10 calibration" id="row-fast-2" style="display:none;">
        
        <div class="col-sm-12">
            
            <div class="row">
                <div class="col-sm-2">
                    <h3 class="text-primary"><?php echo _("Calibration result");?></h3>
                </div>
                <div class="col-sm-10">
                    <button  class="btn btn-primary btn-default pull-right calibrate-again"><?php echo _("Calibrate again");?></button>
                </div>
            </div>
            <div class="row margin-top-10">
                <div class="col-sm-12">
                    <pre id="over-calibrate-trace" style="height: 150px;"></pre>
                </div>
            </div>
        </div>
        
    </div>

    </div>
</div>
