<style>
#Display-items {
    background-color: #000 width: 100%;
    display: flex;
}

.nav-other-button {
    width: 100%;
}

.other-list-new {
    width: 100%;
    padding: 20px;
}

@media only screen and (max-width: 480px) {
    * {}

    .other-list-new {
        padding: 10px
    }

    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    #Display-items {
        display: flex;
        flex-direction: column;
    }
}
</style>

<section class="bonus text-left">
    <a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
        <i class="fa fa-backward"></i> <?php echo $this->lang->line('back'); ?></a>
    <span class="mb-4 mt-1" style="font-size: 25px"><i class="fas fa-award"></i>&nbsp;<?php echo $this->lang->line('event'); ?></span>
    <hr style="margin-top: 15px">
</section>

<!-- start old code -->
<!-- <div class="row mx-auto">
    <div class="col-12 col-sm-6 mb-3 Casino pl-1 pr-1">
        <div class="card">
            <div class="card-body p-0">
                <div class="" style="width:100%;">
                    <a href="<?php echo base_url('event/checkin') ?>">
                        <img style="width: 100%" src="<?php echo base_url('/assets/images/event.png'); ?>"
                            class="img-fluid" alt="กิจกรรมเช็คอิน">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div> -->
<!-- end old code -->

<section class="navigation  ">
    <div class="nav-other-button p-1 " id="Display-items">
        <div class="other-list other-list-new"><a href="<?php echo base_url('profile') ?>"
                class="btn-dark-tri hvr-buzz-out"><i class="fas fa-donate mb-2"></i>
                <p><?php echo $this->lang->line('event_return_loss'); ?></p>
            </a></div>
        <div class="other-list other-list-new"><a href="<?php echo base_url('play_wheel') ?>"
                class="btn-dark-tri hvr-buzz-out"><i class="fas fa-life-ring mb-2"></i>
                <p id="test"><?php echo $this->lang->line('event_wheel'); ?></p>
            </a></div>
        <div class="other-list other-list-new"><a href="<?php echo base_url('event/checkin') ?>"
                class="btn-dark-tri hvr-buzz-out"><i class="far fa-calendar-check mb-2"></i>
                <p><?php echo $this->lang->line('event_checkin'); ?></p>
            </a></div>
        <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
</section>