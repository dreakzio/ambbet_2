<header class="header" id="header_menu">
    <div class="navbar">
        <div class="container d-flex justify-content-between">
            <div class="col-2 p-0">
                <div class="top-nav">
                    <ul>
                        <li>
                            <a href="<?php echo base_url('dashboard'); ?>"><i class="fa fa-home mb-2"></i>
								<p><?php echo $this->lang->line('main'); ?></p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-8 text-center">
                <a href="<?php echo base_url('dashboard'); ?>"><img class="header-logo"
                        src="<?php echo isset($web_setting['web_logo']) ? $web_setting['web_logo']['value'] : base_url()."assets/images/main_logo.png"; ?>"
                        alt="" /></a>
            </div>
            <?php if(isset($_SESSION['user'])): ?>
            <div class="col-2 p-0">
                <div class="top-nav">
                    <ul>
                        <li>
                            <a href="javascript: {}" @click.prevent="logout"><i class="fa fa-sign-out-alt mb-2"></i>
                                <p><?php echo $this->lang->line('logout'); ?></p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php else: ?>
            <div class="col-2 p-0">
                <div class="top-nav">
                    <ul>
                        <li>
                            <a style="" href="<?php echo base_url('register') ?>"><i class="fa fa-user-plus mb-2"></i>
                                <p><?php echo $this->lang->line('register'); ?></p>
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <?php endif; ?>

        </div>
        <div class="nav-mobile" style="position:absolute; right:25px">
				<a href="#" @click.prevent="PickLanguageThai">
					<img src="<?php echo base_url(); ?>assets/images/flag_thai.png" width="20"
						height="20">
				</a>
				<a href="#" @click.prevent="PickLanguageEnglish">
					<img src="<?php echo base_url(); ?>assets/images/flag_english.png" width="20"
						height="20">
				</a>
                <a href="#" @click.prevent="PickLanguageLao">
					<img src="<?php echo base_url(); ?>assets/images/flag_lao.png" width="20"
						height="20">
				</a>
                <span>Test Push/Pull</span>
        </div>
    </div>
    <script src="<?php echo base_url('assets/scripts/header_menu.js?').date('Y-m-d-h-i') ?>"></script>
</header>
