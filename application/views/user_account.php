<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('profile') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-users"></i>&nbsp;ข้อมูลเข้าสู่ระบบ</span>
	<hr style="margin-top: 15px">
</section>
<div class="row">
	<div class="col-sm-12 col-md-12">
		<br>
		<center>
			<p style="font-size: 22px;">
				<i class="fa fa-user mb-3"></i> ชื่อผู้ใช้งาน
				: <?php echo !empty($_SESSION['user']['member_username']) ? $_SESSION['user']['member_username'] : 'ท่านยังไม่ได้รับยูสเซอร์' ?><br />
				<i class="fa fa-key"></i> รหัสผ่าน :
				<?php echo !empty($_SESSION['user']['member_password']) ? $_SESSION['user']['member_password'] : 'ท่านยังไม่ได้รับยูสเซอร์' ?>
			</p>
			<a class="btn btn-success"
			   href="<?php echo base_url('home/play_game_once/ambbet') ?>"
			   target="_blank"><i class="fa fa-sign-out-alt"></i> เข้าสู่ระบบอัตโนมัติ</a>
		</center>
	</div>
</div>
