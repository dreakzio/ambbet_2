<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('profile') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> <?php echo $this->lang->line('back'); ?></a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-users"></i>&nbsp;<?php echo $this->lang->line('login_detail'); ?></span>
	<hr style="margin-top: 15px">
</section>
<div class="row">
	<div class="col-sm-12 col-md-12">
		<br>
		<center>
			<p style="font-size: 22px;">
				<i class="fa fa-user mb-3"></i> <?php echo $this->lang->line('username'); ?>
				: <?php echo !empty($_SESSION['user']['member_username']) ? $_SESSION['user']['member_username'] : $this->lang->line('you_have_not_get_account') ?><br />
				<i class="fa fa-key"></i> <?php echo $this->lang->line('password'); ?> :
				<?php echo !empty($_SESSION['user']['member_password']) ? $_SESSION['user']['member_password'] : $this->lang->line('you_have_not_get_account') ?>
			</p>
			<a class="btn btn-success"
			   href="<?php echo base_url('home/play_game_once/ambbet') ?>"
			   target="_blank"><i class="fa fa-sign-out-alt"></i> <?php echo $this->lang->line('auto_login'); ?></a>
		</center>
	</div>
</div>
