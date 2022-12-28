<?php
if (isset($_SESSION['user'])) {
	redirect('dashboard');
}
$line_login_status = $this->Setting_model->setting_find([
		'name' => 'line_login_status'
]);
$line_login_status = isset($line_login_status['value']) ? $line_login_status['value'] : 1;
$line_login_chk = false;

//ไม่ต้อง otp เพราะว่า verify ผ่านไลน์แล้ว
if(isset($_SESSION['line_login_chk']) && $_SESSION['line_login_chk']){
	if($line_login_status == "1"){
		$line_login_chk = true;
	}
}
?>
<section class="login" id="loginModal" @keyup.enter="doLogin()">
	<loading :active.sync="pre_loader"
			 :can-cancel="false"
			 :width="80"
			 :height="60"
			 :opacity="0.2"
			 color="#fff"
			 :is-full-page="true"></loading>
	<?php if($line_login_status == "1" && $line_login_chk): ?>
		<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-sign-in-alt"></i> <?php echo $this->lang->line('login_by_line'); ?> </span>
	<?php else: ?>
		<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-sign-in-alt"></i> <?php echo $this->lang->line('login'); ?> </span>
		<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('register') ?>" class="float-right btn btn-outline-red btn-md">
			<i class="fa fa-user-plus"></i>  <?php echo $this->lang->line('register'); ?> </a>
	<?php endif; ?>
	<hr style="margin-top: 15px" />

	<div id="login"> ทดสอบ git action main
		<?php if($line_login_status == "1" && (!$line_login_chk || is_null($line_login_chk))): ?>
			<div class="form-group">
				<div class="register-text mt-4 text-center">
					<a href="<?php echo base_url('/auth/line_link') ?>" class="btn btn-line btn-submit">
						<i class="fa fa-line mr-1"></i>
						<span class="text-silver"> <?php echo $this->lang->line('login_by_line'); ?> </span>
					</a>
				</div>
				<div class="row mt-4 mb-4">
					<div class="col-5">
						<hr>
					</div>
					<div class="col-2">
						<h5 class="text-center text-muted">หรือ</h5>
					</div>
					<div class="col-5">
						<hr>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label> <?php echo $this->lang->line('tel'); ?> </label>
				<input placeholder="ชื่อผู้ใช้ (เบอร์โทรศัพท์)" required="required" ame="username" id="username" ref="username" v-model="form.username" type="tel"
					   class="form-control form-control-lg" value="" />
			</div>
			<div class="form-group">
				<a href="<?php echo $web_setting['line_url']['value']; ?>" class="float-right forgot"> <?php echo $this->lang->line('forget_password'); ?> </a>
				<label> <?php echo $this->lang->line('password'); ?> </label>
				<input placeholder="รหัสผ่าน" type="password" required="required" name="password" id="password" ref="password" v-model="form.password"
					   class="form-control form-control-lg" value="" />
			</div>
			<div class="form-group">
				<button type="button" @click.prevent="doLogin"  class="btn-red btn-lg btn-block" style="font-size: 24px">
					<i class="fa fa-sign-in-alt"></i>  <?php echo $this->lang->line('login'); ?> 
				</button>
			</div>
		<?php elseif($line_login_status == "1" && $line_login_chk): ?>
			<div class="form-group">
				<div id="btn-form-line-login">
					<div class="register-text mt-4 text-center">
						<a href="<?php echo base_url('/auth/register'.(isset($_SESSION['line_login_aff']) ? '?ref='.$_SESSION['line_login_aff'] : '')) ?>" class="btn btn-line btn-submit">
							<i class="fa fa-line mr-1"></i>
							<span class="text-silver"> <?php echo $this->lang->line('login_by_new_data'); ?></span>
						</a>
					</div>
					<div class="row mt-4 mb-4">
						<div class="col-5">
							<hr>
						</div>
						<div class="col-2">
							<h5 class="text-center text-muted">หรือ</h5>
						</div>
						<div class="col-5">
							<hr>
						</div>
					</div>
					<div class="register-text mt-4 text-center">
						<a href="#" onclick="$('#form-line-login').show();$('#btn-form-line-login').hide();" class="btn btn-line btn-submit">
							<i class="fa fa-line mr-1"></i>
							<span class="text-silver"><?php echo $this->lang->line('login_by_old_data'); ?></span>
						</a>
					</div>
				</div>
				<div id="form-line-login" style="display: none">
					<div class="form-group">
						<label><?php echo $this->lang->line('tel'); ?></label>
						<input placeholder="ชื่อผู้ใช้ (เบอร์โทรศัพท์)" required="required" ame="username" id="username" ref="username" v-model="form.username" type="tel"
							   class="form-control form-control-lg" value="" />
					</div>
					<div class="form-group">
						<a href="<?php echo $web_setting['line_url']['value']; ?>" class="float-right forgot"><?php echo $this->lang->line('forget_password'); ?></a>
						<label><?php echo $this->lang->line('password'); ?></label>
						<input placeholder="รหัสผ่าน" type="password" required="required" name="password" id="password" ref="password" v-model="form.password"
							   class="form-control form-control-lg" value="" />
					</div>
					<div class="form-group">
						<button type="button" @click.prevent="doLogin"  class="btn-red btn-lg btn-block" style="font-size: 24px">
							<i class="fa fa-sign-in-alt"></i> <?php echo $this->lang->line('login'); ?>
						</button>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="form-group">
				<label><?php echo $this->lang->line('tel'); ?></label>
				<input placeholder="ชื่อผู้ใช้ (เบอร์โทรศัพท์)" required="required" ame="username" id="username" ref="username" v-model="form.username" type="tel"
					   class="form-control form-control-lg" value="" />
			</div>
			<div class="form-group">
				<a href="<?php echo $web_setting['line_url']['value']; ?>" class="float-right forgot"><?php echo $this->lang->line('forget_password'); ?></a>
				<label><?php echo $this->lang->line('password'); ?></label>
				<input placeholder="รหัสผ่าน" type="password" required="required" name="password" id="password" ref="password" v-model="form.password"
					   class="form-control form-control-lg" value="" />
			</div>
			<div class="form-group">
				<button type="button" @click.prevent="doLogin"  class="btn-red btn-lg btn-block" style="font-size: 24px">
					<i class="fa fa-sign-in-alt"></i> <?php echo $this->lang->line('login'); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</section>
<script>
	let login_username = "<?php echo isset($_SESSION['user_register'])?$_SESSION['user_register']['username']:'' ?>";
	let login_password = "<?php echo isset($_SESSION['user_register'])?$_SESSION['user_register']['password']:'' ?>";
</script>
<script type="text/javascript" src="<?php echo base_url('assets/scripts/login.js?'.time()) ?>"></script>
<?php if(isset($error_message) && !empty($error_message)): ?>
	<script>
		<?php if(isset($redirect_domain) && !empty($redirect_domain)): ?>
		Swal.fire({
			type: "error",
			// title: 'แจ้งเตือน',
			html: '<?php echo $error_message; ?>',
			confirmButtonText: 'ตกลง',
			confirmButtonColor: 'red',
		}).then(function() {
			window.location = '<?php echo $redirect_domain; ?>';
		});
		<?php else: ?>
		sweetAlert2('error', '<?php echo $error_message; ?>');
		<?php endif; ?>
	</script>
<?php endif; ?>
