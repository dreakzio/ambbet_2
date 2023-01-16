<header class="header" id="header_menu">
    <div class="navbar">
        <div class="container">
            <div class="row align-items-center justify-content-around d-flex">
                <div class="col-7 col-md-7 m-0">
                    <a href="<?php echo base_url('dashboard'); ?>"><img class="header-logo"
                            src="<?php echo isset($web_setting['web_logo']) ? $web_setting['web_logo']['value'] : base_url()."assets/images/main_logo.png"; ?>"
                            alt="" /></a>
                </div>
                <?php if(isset($_SESSION['user'])): ?>
                <div class="col-2 d-flex justify-content-center text-center ">
                    <button class="btn btn-lang_logout btn-sm" type="button" href="javascript: {}"
                        @click.prevent="logout">
                        <span>Logout</span>
                    </button>
                </div>
                <div class="col-3 ">
                    <button class="btn btn-lang btn-sm dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span>
                            <?php echo ($this->session->userdata('language') == NULL ? "thailand" : $this->session->userdata('language')); ?>
                        </span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="#" @click.prevent="PickLanguageThai" style="min-width:inherit;">
                            <img src="<?php echo base_url(); ?>assets/images/flag_thai.png" width="20" height="20"> TH
                        </a>
                        <a class="dropdown-item" href="#" @click.prevent="PickLanguageLao" style="min-width:inherit;">
                            <img src="<?php echo base_url(); ?>assets/images/flag_lao.png" width="20" height="20">
                            LAO </a>
                        <a class="dropdown-item" href="#" @click.prevent="PickLanguageEnglish"
                            style="min-width:inherit;"> <img src="<?php echo base_url(); ?>assets/images/flag_english.png"
                                width="20" height="20"> EN </a>
                    </div>

                </div>
                <?php else: ?>
                <div class="col-2 col-md-3 d-flex justify-content-center text-center justify-content-md-end">
                    <button class="btn btn-lang_register btn-sm" type="button" onclick="location.href='<?php echo base_url();?>register'">
                        <span>Register</span>
                    </button>
                </div>
                <div class="col-3 col-md-2">
                    <button class="btn btn-lang btn-sm dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span>
                            <?php echo ($this->session->userdata('language') == NULL ? "thailand" : $this->session->userdata('language')); ?>
                        </span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="#" @click.prevent="PickLanguageThai" style="min-width:inherit;">
                            <img src="<?php echo base_url(); ?>assets/images/flag_thai.png" width="20" height="20"> TH
                        </a>
                        <a class="dropdown-item" href="#" @click.prevent="PickLanguageLao" style="min-width:inherit;">
                            <img src="<?php echo base_url(); ?>assets/images/flag_lao.png" width="20" height="20">
                            LAO </a>
                        <a class="dropdown-item" href="#" @click.prevent="PickLanguageEnglish"
                            style="min-width:inherit;"> <img src="<?php echo base_url(); ?>assets/images/flag_english.png"
                                width="20" height="20"> EN </a>
                    </div>

                </div>

            </div>

            <?php endif; ?>

        </div>
        <!-- <div class="language_lay">
            <button class="btn btn-lang btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span> <?php echo ($this->session->userdata('language') == NULL ? "thailand" : $this->session->userdata('language')); ?> </span>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="#" @click.prevent="PickLanguageThai" style="min-width:inherit;"> <img src="<?php echo base_url(); ?>assets/images/flag_thai.png" width="20"height="20"> TH </a>
                <a class="dropdown-item" href="#" @click.prevent="PickLanguageLao" style="min-width:inherit;"> <img src="<?php echo base_url(); ?>assets/images/flag_english.png" width="20"height="20"> LAO </a>
                <a class="dropdown-item" href="#" @click.prevent="PickLanguageEnglish" style="min-width:inherit;"> <img src="<?php echo base_url(); ?>assets/images/flag_lao.png" width="20"height="20"> EN </a>
            </div>
        </div> -->
    </div>

    <script src="<?php echo base_url('assets/scripts/header_menu.js?').date('Y-m-d') ?>"></script>
</header>