<?php
defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
$user =  $this->Account_model->account_find([
		'id' => $_SESSION['user']['id'],
]);
$web_name = $this->Setting_model->setting_find([
		'name' => 'web_name'
]);

$web_logo = $this->Setting_model->setting_find([
	'name' => 'web_logo'
]);


$report_all_day = get_data_report_all_day();
?>
<!DOCTYPE html>

<html class="loading" lang="en" data-textdirection="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
	<title><?php echo isset($web_name) ? strtoupper($web_name['value'])." - BackOffice" : "Dashboard"; ?></title>
	<link rel="apple-touch-icon" href="<?php echo base_url('assets/app-assets/images/ico/apple-icon-120.png') ?>">
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/app-assets/images/ico/favicon.ico') ?>">
	<link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

	<!-- BEGIN: Vendor CSS-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/vendors/css/vendors.min.css') ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/vendors/css/ui/prism.min.css') ?>">
	<!-- END: Vendor CSS-->

	<!-- BEGIN: Theme CSS-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/css/bootstrap.css') ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/css/bootstrap-extended.css') ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/css/colors.css') ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/css/components.css') ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/css/themes/dark-layout.css') ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/css/themes/semi-dark-layout.css') ?>">

	<!-- BEGIN: Page CSS-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/css/core/menu/menu-types/vertical-menu.css') ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/app-assets/css/core/colors/palette-gradient.css') ?>">
	<!-- END: Page CSS-->
	<!-- BEGIN: Custom CSS-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/assets/css/custom.css') ?>">
	<!-- END: Custom CSS-->
	<script src="<?php echo base_url('assets/app-assets/vendors/js/vendors.min.js') ?>"></script>
	<!-- <script src="<?php echo base_url('assets/plugins/jquery/dist/jquery.min.js') ?>"></script> -->
	<script src="<?php echo base_url('assets/plugins/sweetalert2/dist/sweetalert2.all.min.js') ?>"></script>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern 2-columns  navbar-sticky fixed-footer  " data-open="click" data-menu="vertical-menu-modern" data-col="2-columns">
<input type="hidden" id="base_url" value="<?php echo site_url(); ?>"/>
<script src="<?php echo base_url('assets/scripts/main.js') ?>"></script>
<!-- BEGIN: Header-->
<div class="content-overlay"></div>
<div class="header-navbar-shadow"></div>
<nav class="header-navbar navbar-expand-lg navbar navbar-with-menu fixed-top navbar-light navbar-shadow">
	<div class="navbar-wrapper">
		<div class="navbar-container content">
			<div class="navbar-collapse" id="navbar-mobile">
				<div class="mr-auto float-left bookmark-wrapper d-flex align-items-center">
					<ul class="nav navbar-nav">
						<li class="nav-item mobile-menu d-xl-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ficon feather icon-menu"></i></a></li>
					</ul>
					 <ul class="nav navbar-nav">
						<li class="nav-item "><audio volumn="0" id="alert-sound"  preload="auto" autostart="false" autoplay="false" controls="controls"  style="height: 30px; width: 210px;margin-right: 10px ">
								<source src="<?php echo base_url('assets/app-assets/sound/alert.mp3') ?>" type="audio/mp3">
							</audio>
						</li>
					</ul>
					<script>
						function reverseArr(input) {
							var ret = new Array;
							for(var i = input.length-1; i >= 0; i--) {
								ret.push(input[i]);
							}
							return ret;
						}
						document.getElementById("alert-sound").pause()
						var state_loading = false;
					</script>
				</div>
				<ul class="nav navbar-nav float-right">
					<li class="dropdown dropdown-user nav-item"><a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
							<div class="user-nav d-sm-flex d-none"><span class="user-name text-bold-600"><?php echo $user['full_name']; ?></span><span class="user-status"><?php echo $user['username']; ?></span></div><span><i class="fa fa-user fa-2x text-primary"></i></span>
						</a>
						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item" href="<?php echo base_url('../auth/logout') ?>"><i class="feather icon-power"></i> ออกจากระบบ</a>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</nav>


<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
	<div class="navbar-header">
		<ul class="nav navbar-nav flex-row">

			<img onclick="location.href = '<?php echo site_url();?>';" width="200" height="50" src="<?php echo $web_logo['value'];?>" />
			<li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i class="feather icon-x d-block d-xl-none font-medium-4 primary toggle-icon"></i><i class="toggle-icon feather icon-disc font-medium-4 d-none d-xl-block primary" data-ticon="icon-disc"></i></a></li>
		</ul>
	</div>
	<div class="shadow-bottom"></div>
	<div class="main-menu-content">

		<ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
			<li class=" nav-item"><a href="<?php echo site_url() ?>"><i class="feather icon-home"></i><span class="menu-title" data-i18n="Dashboard">Dashboard</span></a>
			</li>
			<li class="<?php if ($this->uri->segment(1)=="gamestatus"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('gamestatus') ?>"><i class="feather icon-crosshair"></i><span class="menu-title" >สถานะเกมส์</span></a></li>
			<li class=" navigation-header"><span>ระบบสมาชิก</span></li>
			<li class="<?php if ($this->uri->segment(1)=="user"&&$this->uri->segment(2)==""): ?>
									active
			<?php endif; ?> nav-item"><a href="<?php echo site_url('user') ?>"  ><i class="feather icon-users"></i><span class="menu-title" >สมาชิก</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="user_suspend"&&$this->uri->segment(2)==""): ?>
									active
			<?php endif; ?> nav-item"><a href="<?php echo site_url('user_suspend') ?>"  ><i class="feather icon-user-x"></i><span class="menu-title" >สมาชิกที่ถูกระงับ</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="agent"): ?>
                  active
                <?php endif; ?> nav-item"><a href="<?php echo site_url('agent') ?>"  ><i class="feather icon-users"></i><span class="menu-title" >พันธมิตร</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="transfer_marketing"): ?>
                  active
                <?php endif; ?> nav-item"><a href="<?php echo site_url('transfer_marketing') ?>"  ><i class="feather icon-users info "></i><span class="menu-title" >โยกสมาชิกการตลาด</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="ref"&&$this->uri->segment(2)==""): ?>
									active
								<?php endif; ?> nav-item "><a href="<?php echo site_url('ref') ?>"><i class="feather icon-mail"></i><span class="menu-title" >แนะนำเพื่อน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="ref"&&$this->uri->segment(2)=="bonus"): ?>
									active
								<?php endif; ?> nav-item "><a href="<?php echo site_url('ref/bonus') ?>"><i class="feather icon-mail"></i><span class="menu-title" >โบนัสแนะนำเพื่อน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="bonus"&&$this->uri->segment(2)=="returnbalance"): ?>
									active
								<?php endif; ?> nav-item "><a href="<?php echo site_url('bonus/returnbalance') ?>"><i class="feather icon-mail"></i><span class="menu-title" >โบนัสคืนยอดเสีย</span></a></li>
			<li class=" navigation-header"><span>รายงาน</span></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="business_profit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/business_profit') ?>"><i class="fa fa-bar-chart"></i><span class="menu-title" >ผลประกอบการ</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="member_register_sum_deposit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/member_register_sum_deposit') ?>"><i class="fa fa-bar-chart"></i><span class="menu-title" >ยอดฝากรวมรายวัน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="member_not_deposit_less_than_7"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/member_not_deposit_less_than_7') ?>"><i class="fa fa-bar-chart"></i><span class="menu-title" >ไม่ได้ฝากมากกว่า 7 วัน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="add_credit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/add_credit') ?>"><i class="fa fa-bar-chart"></i><span class="menu-title" >ยอดเติมเครดิต</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="add_bonus"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/add_bonus') ?>"><i class="fa fa-bar-chart"></i><span class="menu-title" >การรับโบนัส</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="add_promotion"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/add_promotion') ?>"><i class="fa fa-bar-chart"></i><span class="menu-title" >การรับโปรโมชั่น</span></a></li>
			<li class=" navigation-header"><span>ระบบธุรกรรม</span></li>
			<li class="<?php if ($this->uri->segment(1)=="statement"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('statement') ?>"><i class="fa fa-list-alt primary"></i><span class="menu-title" >รายการเดินบัญชี</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="deposit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('deposit') ?>"><i class="fa fa-usd"></i><span class="menu-title" >ฝากเงิน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="creditwait"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('creditwait') ?>"><i class="feather icon-plus"></i><span class="menu-title" >เครดิต (รอฝาก)</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="credit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('credit') ?>"><i class="feather icon-plus"></i><span class="menu-title" >เครดิต</span></a></li>
			<!-- icon-minus-circle -->
			<li class=" nav-item <?php if ($this->uri->segment(1)=="withdraw"): ?>
									active
								<?php endif; ?>"><a href="<?php echo site_url('withdraw') ?>"><i class="feather icon-minus"></i><span class="menu-title" >ถอนเงิน</span></a></li>
			<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == roleSuperAdmin()): ?>
				<li class=" nav-item <?php if ($this->uri->segment(1)=="TransferOut"): ?>
									active
								<?php endif; ?>"><a href="<?php echo site_url('TransferOut') ?>"><i class="fa fa-money"></i><span class="menu-title" >โยกเงินออก</span></a></li>
			<?php endif; ?>
			<li class=" navigation-header"><span>ระบบ Logs</span></li>
			<li class="<?php if ($this->uri->segment(1)=="LogDepositWithdraw"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogDepositWithdraw') ?>"><i class="fa fa-history"></i><span class="menu-title" >ฝาก-ถอน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogAccount"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogAccount') ?>"><i class="fa fa-history"></i><span class="menu-title" >สมาชิก</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogReturnBalance"): ?>
								active
							<?php endif; ?> nav-item"><a href="<?php echo site_url('LogReturnBalance') ?>"><i class="fa fa-history"></i><span class="menu-title" >คืนยอดเสีย</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogSms"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogSms') ?>"><i class="fa fa-comment"></i><span class="menu-title" >SMS</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogPage"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogPage') ?>"><i class="fa fa-history"></i><span class="menu-title" >เปิดหน้าเว็ป</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogLineNotify"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogLineNotify') ?>"><i class="fa fa-history"></i><span class="menu-title" >Line notify</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogWheel"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogWheel') ?>"><i class="fa fa-history"></i><span class="menu-title" >วงล้อ</span></a></li>
			<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == roleSuperAdmin()): ?>
				<li class="<?php if ($this->uri->segment(1)=="LogTransferOut"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogTransferOut') ?>"><i class="fa fa-money"></i><span class="menu-title" >โยกเงินออก</span></a></li>
			<?php endif; ?>
			<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == roleSuperAdmin()): ?>
				<li class=" navigation-header"><span>ตั้งค่าระบบ</span></li>
				<li class="<?php if ($this->uri->segment(1)=="promotion"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('promotion') ?>"><i class="fa fa-clone"></i><span class="menu-title" >โปรโมชั่น</span></a></li>
				<li class="<?php if ($this->uri->segment(1)=="news"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('news') ?>"><i class="fa fa-newspaper-o"></i><span class="menu-title" >ประกาศ</span></a></li>
				<li class="<?php if ($this->uri->segment(1)=="setting"): ?>
                  active
                <?php endif; ?> nav-item"><a href="<?php echo site_url('setting/web_setting') ?>"><i class="fa fa-cog"></i><span class="menu-title" >ตั้งค่าเว็บ</span></a></li>
				<li class="<?php if ($this->uri->segment(1)=="bank"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('bank') ?>"><i class="fa fa-clone"></i><span class="menu-title" >ตั้งค่าธนาคาร</span></a></li>
			<?php endif; ?>
		</ul>
	</div>
</div>
<!-- END: Main Menu-->

<!-- BEGIN: Content-->
<div class="app-content content">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<?php
	if (isset($page)) {
		$this->load->view($page);
	}else {
		?>
		<div class="content-wrapper">
			<div class="content-header row">
				<div class="content-header-left col-md-9 col-12 mb-2">
					<div class="row breadcrumbs-top">
						<div class="col-12">
							<h2 class="content-header-title float-left mb-0">ระบบตรวจสอบ Google 2FA</h2>
						</div>
					</div>
				</div>
			</div>
			<form class="form" id="form_gg_chk" method="POST" action="<?php echo site_url("home/verify_2fa_chk") ?>" enctype="multipart/form-data">

				<div class="content-body">
					<section class="card">
						<div class="card-content">
							<div class="card-body">
								<div class="form-body mt-3">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label">Google 2FA Code <span class="text-danger">(ติดต่อขอรับ Code ได้ที่แอดมินสูงสุด)</span></label>
												<div class="input-group">
													<input type="number" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==6) return false;" maxlength="6" name="gcode" class="form-control"  required placeholder="ข้อมูล Google 2FA Code"/>
													<div class="input-group-append">
														<button type="submit" class=" btn bg-gradient-success waves-effect waves-light"><i class="fa fa-send"></i>&nbsp;ตรวจสอบ</button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
			</form>
		</div>
		<?php
	}
	?>
</div>
<!-- END: Content-->

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<!-- BEGIN: Footer-->
<footer class="footer fixed-footer footer-light">
	<p class="clearfix blue-grey lighten-2 mb-0"><span class="float-md-left d-block d-md-inline-block mt-25">COPYRIGHT &copy; <?php echo date('Y'); ?><a class="text-bold-800 grey darken-2" href="#" target="_blank"><?php echo $web_name['value'];?></a>All rights Reserved</span>
		<button class="btn btn-primary btn-icon scroll-top" type="button"><i class="feather icon-arrow-up"></i></button>
	</p>
</footer>

<script src="<?php echo base_url('assets/app-assets/vendors/js/forms/select/select2.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/app-assets/vendors/js/ui/prism.min.js') ?>"></script>
<script src="<?php echo base_url('assets/app-assets/js/core/app-menu.js') ?>"></script>
<script src="<?php echo base_url('assets/app-assets/js/core/app.js') ?>"></script>
<?php if (isset($_SESSION['verify_2fa_error'])): ?>
	<script>
		const Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 10000
		});
		Toast.fire({
			type: 'error',
			title: '<?php echo $_SESSION['verify_2fa_error']; ?>'
		});
	</script>
	<?php unset($_SESSION['verify_2fa_error']); ?>
<?php endif; ?>

</body>

</html>
