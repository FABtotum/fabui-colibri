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
                            <button data-action="+" type="button" title="Away from nozzle" class="btn  btn-default btn-primary btn-sm btn-block z-action"><i class="fa fa-arrow-down"></i> </button>
                        </section>
                        <section class="col col-6">
                            <label class="input"><input id="z-value" type="text" style="text-align: center;" value="0.1"></label>
                        </section>
                        <section class="col col-3">
                            <button data-action="-" type="button" title="Close to nozzle" class="btn btn-primary  btn-default btn-sm btn-block z-action"><i class="fa fa-arrow-up"></i></button>
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
