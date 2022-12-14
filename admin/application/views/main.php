<?php
defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(0);
$user =  $this->Account_model->account_find([
		'id' => $_SESSION['user']['id'],
]);
$total_online  = $this->Account_model->get_total_online_user();
$web_name = $this->Setting_model->setting_find([
		'name' => 'web_name'
]);

$web_logo = $this->Setting_model->setting_find([
	'name' => 'web_logo'
]);

$web_sound_alert = $this->Setting_model->setting_find([
	'name' => 'web_sound_alert'
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
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo $web_logo['value'];?>">
	<!-- <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/app-assets/images/ico/favicon.ico') ?>"> oldcode -->
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
								<source src="<?php echo $web_sound_alert['value'] == "0" ? base_url('assets/app-assets/sound/alert.mp3') :  $web_sound_alert['value'] ?>" type="audio/mp3">
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
						let withdraw_notify_interval  = setInterval(function(){
							if(!state_loading){
								state_loading = true;
								$.ajax({
									url: BaseURL + "/withdraw/withdraw_list_no_paginate_page?draw=1&search[value]=&start=0&length=25",
									method: "GET",
									dataType: 'json',
									success: function(response) {
										var wd_recordsTotal = window.localStorage.getItem("wd_recordsTotal");
										if(wd_recordsTotal!=null && wd_recordsTotal != "" && typeof(wd_recordsTotal) != "undefined" && parseInt(response.recordsTotal) > parseInt(wd_recordsTotal)){
											window.localStorage.setItem("wd_recordsTotal",parseInt(response.recordsTotal));
											var chk_play_sound = false;
											var data = reverseArr(response.data);
											for(var i=0;i<data.length;i++){
												value = data[i];
												var wd_maxId = window.localStorage.getItem("wd_maxId");
												wd_maxId = wd_maxId!=null && wd_maxId != "" && typeof(wd_maxId) != "undefined" ? parseInt(wd_maxId) : 0;
												if(parseInt(value.id) > parseInt(wd_maxId) && value.status == '0'){
													chk_play_sound = true;
													window.localStorage.setItem("wd_maxId",parseInt(value.id));
													let Toast = Swal.mixin({
														toast: true,
														position: 'top-end',
														showConfirmButton: false,
														timer: 5000,
														onClose: (toast) => {
															state_loading = false;
														}
													});
													Toast.fire({
														type: 'warning',
														title: 'แจ้งถอนเงิน ยูสเซอร์ : '+value.username+' จำนวน => '+value.amount+' บาท'
													});
													if(chk_play_sound){
														state_loading = false;
														setTimeout(function(){
															document.getElementById("alert-sound").pause()
															document.getElementById("alert-sound").currentTime = 0
															document.getElementById("alert-sound").volumn = 100
															document.getElementById("alert-sound").play()
														},1500)
													}
													break;
												}

											}
											if(!chk_play_sound){
												state_loading = false;
											}

										}else{
											window.localStorage.setItem("wd_recordsTotal",parseInt(response.recordsTotal));
											var chk_play_sound = false;
											var data = reverseArr(response.data);

											for(var i=0;i<data.length;i++) {
												value = data[i];
												var wd_maxId = window.localStorage.getItem("wd_maxId");
												wd_maxId = wd_maxId!=null && wd_maxId != "" && typeof(wd_maxId) != "undefined" ? parseInt(wd_maxId) : 0;
												if(parseInt(value.id) > parseInt(wd_maxId) && value.status == '0'){
													chk_play_sound = true;
													window.localStorage.setItem("wd_maxId",parseInt(value.id));
													let Toast = Swal.mixin({
														toast: true,
														position: 'top-end',
														showConfirmButton: false,
														timer: 5000,
														onClose: (toast) => {
															state_loading = false;
														}
													});
													Toast.fire({
														type: 'warning',
														title: 'แจ้งถอนเงิน ยูสเซอร์ : '+value.username+' จำนวน => '+value.amount+' บาท'
													});
													if(chk_play_sound){
														state_loading = false;
														setTimeout(function(){
															document.getElementById("alert-sound").pause()
															document.getElementById("alert-sound").currentTime = 0
															document.getElementById("alert-sound").volumn = 100
															document.getElementById("alert-sound").play()
														},1500)
													}
													break;
												}

											}
											if(!chk_play_sound){
												state_loading = false;
											}
										}

									},
									error: function() {
										state_loading = false;
									}
								});
							}

						},30000);
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
			<li class=" nav-item"><a href="<?php echo site_url() ?>"><i class="feather icon-home primary"></i><span class="menu-title" data-i18n="Dashboard">Dashboard</span></a>
			</li>
			<li class="<?php if ($this->uri->segment(1)=="gamestatus"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('gamestatus') ?>"><i class="feather icon-crosshair danger"></i><span class="menu-title" >สถานะเกมส์</span></a></li>
			<li class=" navigation-header"><span style="color:blue;font-weight:bold">ระบบสมาชิก</span></li>
			<li class="<?php if ($this->uri->segment(1)=="user"&&$this->uri->segment(2)==""): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('user') ?>"  ><i class="feather icon-users success"></i><span class="menu-title" >สมาชิก</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="user_suspend"&&$this->uri->segment(2)==""): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('user_suspend') ?>"  ><i class="feather icon-user-x danger"></i><span class="menu-title" >สมาชิกที่ถูกระงับ</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="agent"): ?>
                  active
                <?php endif; ?> nav-item"><a href="<?php echo site_url('agent') ?>"  ><i class="feather icon-users info "></i><span class="menu-title" >พันธมิตร</span></a></li>

			<li class="<?php if ($this->uri->segment(1)=="ref"&&$this->uri->segment(2)==""): ?>
									active
								<?php endif; ?> nav-item "><a href="<?php echo site_url('ref') ?>"><i class="feather icon-user-plus warning"></i><span class="menu-title" >แนะนำเพื่อน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="ref"&&$this->uri->segment(2)=="bonus"): ?>
									active
								<?php endif; ?> nav-item "><a href="<?php echo site_url('ref/bonus') ?>"><i class="feather icon-dollar-sign warning"></i><span class="menu-title" >โบนัสแนะนำเพื่อน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="bonus"&&$this->uri->segment(2)=="returnbalance"): ?>
									active
								<?php endif; ?> nav-item "><a href="<?php echo site_url('bonus/returnbalance') ?>"><i class="feather icon-dollar-sign warning"></i><span class="menu-title" >โบนัสคืนยอดเสีย</span></a></li>
			<li class=" navigation-header"><span style="color:blue;font-weight:bold">รายงาน</span></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="business_profit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/business_profit') ?>"><i class="fa fa-bar-chart success"></i><span class="menu-title" >ผลประกอบการ</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="member_register_sum_deposit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/member_register_sum_deposit') ?>"><i class="fa fa-bar-chart success"></i><span class="menu-title" >ยอดฝากรวมรายวัน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="member_not_deposit_less_than_7"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/member_not_deposit_less_than_7') ?>"><i class="fa fa-bar-chart danger"></i><span class="menu-title" >ไม่ได้ฝากมากกว่า 7 วัน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="add_credit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/add_credit') ?>"><i class="fa fa-bar-chart info"></i><span class="menu-title" >ยอดเติมเครดิต</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="add_bonus"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/add_bonus') ?>"><i class="fa fa-bar-chart warning"></i><span class="menu-title" >การรับโบนัส</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="report"&&$this->uri->segment(2)=="add_promotion"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('report/add_promotion') ?>"><i class="fa fa-bar-chart primary"></i><span class="menu-title" >การรับโปรโมชั่น</span></a></li>
			<li class=" navigation-header"><span style="color:blue;font-weight:bold">ระบบธุรกรรม</span></li>
			<li class="<?php if ($this->uri->segment(1)=="statement"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('statement') ?>"><i class="fa fa-list-alt primary"></i><span class="menu-title" >รายการเดินบัญชี</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="deposit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('deposit') ?>"><i class="fa fa-usd warning"></i><span class="menu-title" >เครดิต</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="creditwait"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('creditwait') ?>"><i class="feather icon-plus warning"></i><span class="menu-title" >เครดิต (รอฝาก)</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="credit"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('credit') ?>"><i class="feather icon-plus primary"></i><span class="menu-title" >ฝากเงิน</span></a></li>
			<!-- icon-minus-circle -->
			<li class=" nav-item <?php if ($this->uri->segment(1)=="withdraw"): ?>
									active
								<?php endif; ?>"><a href="<?php echo site_url('withdraw') ?>"><i class="feather icon-minus danger"></i><span class="menu-title" >ถอนเงิน</span></a></li>
			<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == roleSuperAdmin()): ?>
				<li class=" nav-item <?php if ($this->uri->segment(1)=="TransferOut"): ?>
									active
								<?php endif; ?>"><a href="<?php echo site_url('TransferOut') ?>"><i class="fa fa-money warning"></i><span class="menu-title" >โยกเงินออก</span></a></li>
			<?php endif; ?>
			<li class=" navigation-header"><span style="color:blue;font-weight:bold">ระบบ Logs</span></li>
			<li class="<?php if ($this->uri->segment(1)=="LogDepositWithdraw"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogDepositWithdraw') ?>"><i class="fa fa-history success"></i><span class="menu-title" >ฝาก-ถอน</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogAccount"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogAccount') ?>"><i class="fa fa-history primary"></i><span class="menu-title" >สมาชิก</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogReturnBalance"): ?>
								active
							<?php endif; ?> nav-item"><a href="<?php echo site_url('LogReturnBalance') ?>"><i class="fa fa-history danger"></i><span class="menu-title" >คืนยอดเสีย</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogSms"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogSms') ?>"><i class="fa fa-comment primary"></i><span class="menu-title" >SMS</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogPage"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogPage') ?>"><i class="fa fa-history info"></i><span class="menu-title" >เปิดหน้าเว็ป</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogLineNotify"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogLineNotify') ?>"><i class="fa fa-bell success"></i><span class="menu-title" >Line notify</span></a></li>
			<li class="<?php if ($this->uri->segment(1)=="LogWheel"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogWheel') ?>"><i class="fa fa-history warning"></i><span class="menu-title" >วงล้อ</span></a></li>
			<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == roleSuperAdmin()): ?>
				<li class="<?php if ($this->uri->segment(1)=="LogTransferOut"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('LogTransferOut') ?>"><i class="fa fa-money danger"></i><span class="menu-title" >โยกเงินออก</span></a></li>
			<?php endif; ?>
			<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == roleSuperAdmin()): ?>
				<li class=" navigation-header"><span style="color:blue;font-weight:bold">ตั้งค่าระบบ</span></li>
				<li class="<?php if ($this->uri->segment(1)=="promotion"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('promotion') ?>"><i class="fa fa-clone info"></i><span class="menu-title" >โปรโมชั่น</span></a></li>
				<li class="<?php if ($this->uri->segment(1)=="news"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('news') ?>"><i class="fa fa-newspaper-o info"></i><span class="menu-title" >ประกาศ</span></a></li>
				<li class="<?php if ($this->uri->segment(1)=="setting"): ?>
                  active
                <?php endif; ?> nav-item"><a href="<?php echo site_url('setting/web_setting') ?>"><i class="fa fa-cog primary"></i><span class="menu-title" >ตั้งค่าเว็บ</span></a></li>
				<li class="<?php if ($this->uri->segment(1)=="bank"): ?>
									active
								<?php endif; ?> nav-item"><a href="<?php echo site_url('bank') ?>"><i class="fa fa-clone info"></i><span class="menu-title" >ตั้งค่าธนาคาร</span></a></li>
			<?php endif; ?>
			<!-- <li class=" nav-item"><a href="#"><i class="feather icon-zap"></i><span class="menu-title" data-i18n="Starter kit">Starter kit</span></a>
				<ul class="menu-content">
					<li><a href="sk-layout-2-columns.html"><i></i><span class="menu-item" data-i18n="2 columns">2 columns</span></a>
					</li>
					<li><a href="sk-layout-fixed-navbar.html"><i></i><span class="menu-item" data-i18n="Fixed navbar">Fixed navbar</span></a>
					</li>
					<li><a href="sk-layout-floating-navbar.html"><i></i><span class="menu-item" data-i18n="Floating navbar">Floating navbar</span></a>
					</li>
					<li class="active"><a href="sk-layout-fixed.html"><i></i><span class="menu-item" data-i18n="Fixed layout">Fixed layout</span></a>
					</li>
				</ul>
			</li> -->
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
			</div>
			<div class="content-body">
				<!-- Dashboard Analytics Start -->
				<!-- แถบแรก -->
				<div class="row">
					<div class="col-lg-12 col-md-6 col-12">
						<div class="card bg-analytics">
							<div class="card-header d-flex justify-content-between align-items-end">
								<h4 class="mb-0 danger" >ผลประกอบการวันนี้</h4>
							</div>
							<div class="card-content">
								<div class="card-body px-0 pb-0">
									<div id="goal-overview-chart" class="mt-75"></div>
									<div class="row text-center mx-0">
										<div class="col-4 border-top border-right d-flex align-items-between flex-column py-1">
											<i class="feather icon-credit-card text-info font-medium-5"></i>
											<p class="mb-50 text-info">ยอดฝากวันนี้</p>
											<p class="text-bold-700 mb-50" id="txt_report_all_day_deposit"><?php if ($report_all_day['deposit'] == null){ echo " 0";}else{echo number_format($report_all_day['deposit'],2); }?> ฿</p>
										</div>
										<div class="col-4 border-top border-right d-flex align-items-between flex-column py-1">
											<i class="feather icon-credit-card text-danger font-medium-5"></i>
											<p class="mb-50 text-danger">ยอดถอนวันนี้</p>
											<p class="text-bold-700 mb-50" id="txt_report_all_day_withdraw"><?php if ($report_all_day['withdraw'] == null){ echo " 0";}else{echo number_format($report_all_day['withdraw'],2); }?> ฿</p>
										</div>
										<div class="col-4 border-top d-flex align-items-between flex-column py-1">
											<i class="feather icon-credit-card text-success font-medium-5"></i>
											<p class="mb-50 text-success">กำไรสุทธิวันนี้</p>
											<p class="text-bold-700 mb-50 " id="txt_report_all_day_total"><?php  echo number_format($report_all_day['total'],2) ?> ฿</p>
											<!-- <?php
											$showDivFlag=false;
											$profit=500;
											$deprofit=400;
											if($profit > $deprofit){
												$showDivFlag=true;
											}else{
												$showDivFlag=false;
											}
											?>
											<span><i class="feather icon-arrow-up text-danger"></i><?php echo $showDivFlag ?></span>
											<span><i class="feather icon-arrow-up text-success"></i><?php echo $showDivFlag ?></span> -->
										</div>
										<div class="col-4 border-top d-flex align-items-between flex-column py-1">
											<p class="mb-50">สมาชิกวันนี้</p>
											<p class="text-bold-700 mb-50" id="txt_report_all_day_member"><?php if ($report_all_day['member'] == null){ echo " 0";}else{echo $report_all_day['member']; }?></p>
										</div>
										<div class="col-4 border-top d-flex align-items-between flex-column py-1">
											<p class="mb-50">รายการฝากวันนี้</p>
											<p class="text-bold-700 mb-50" id="txt_report_all_day_deposit_count"><?php if ($report_all_day['deposit_count'] == null){ echo " 0";}else{echo $report_all_day['deposit_count']; }?></p>
										</div>
										<div class="col-4 border-top d-flex align-items-between flex-column py-1">
											<p class="mb-50">รายการถอนวันนี้</p>
											<p class="text-bold-700 mb-50" id="txt_report_all_day_withdraw_count"><?php if ($report_all_day['withdraw_count'] == null){ echo " 0";}else{echo $report_all_day['withdraw_count']; }?></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-6 col-md-4 col-sm-12">
						<div class="card text-center">
								<div class="card-header d-flex justify-content-between align-items-end">
									<h4 class="mb-0 danger">สมาชิกทั้งหมด</h4>
								</div>
								<hr>
							<div class="card-content">
								<div class="card-body row text-center mx-0">
									<div class="col-xl-6 col-md-4 col-sm-12 align-items-between flex-column">
										<div class="avatar bg-rgba-info p-50 m-0 mb-1">
											<div class="avatar-content spinner-grow text-success" >
												<i class="feather icon-users text-info font-medium-5"></i>
											</div>
										</div>
										<h2 class="text-bold-700" id="txt_report_all_day_member_total"><?php echo $total_online; ?></h2>
										<p class="mb-0 line-ellipsis ">จำนวนสมาชิกออนไลน์</p>
									</div>
									<div class="col-xl-6 col-md-4 col-sm-12 align-items-between flex-column">
										<div class="avatar bg-rgba-info p-50 m-0 mb-1">
											<div class="avatar-content">
												<i class="feather icon-users text-info font-medium-5"></i>
											</div>
										</div>
										<h2 class="text-bold-700" id="txt_report_all_day_member_total"><?php echo $report_all_day['member_total']; ?></h2>
										<p class="mb-0 line-ellipsis">จำนวนสมาชิกทั้งหมด</p>
									</div>
								</div>
							</div>
						</div>
					</div>


					<div class="col-lg-6 col-md-12 col-12 col-sm-12">
						<div class="card">
							<div class="card-header d-flex justify-content-between align-items-end">
								<h4 class="mb-0 danger">ธนาคาร</h4>
							</div>
							<div class="card-content">
								<div class="table-responsive mt-1">
									<table id="tableBank" class="table table-hover-animation mb-0">
										<thead>
										<tr>
											<th>ธนาคาร</th>
											<th>ชื่อบัญชี</th>
											<th>เลขบัญชี</th>
											<th>ประเภท</th>
											<th>ยอดคงเหลือ</th>
										</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
									<script>
										var loading_bank = false;
										function getTableBank(){
											if(!loading_bank){
												loading_bank = true;
												$.ajax({
													url: BaseURL + "/bank/bank_list?&security=1&group_by=bank_number&status=1",
													method: "GET",
													dataType: 'json',
													success: function(response) {
														loading_bank = false;
														var base_url = '<?php echo base_url('assets/images') ?>';
														if(response.result.length > 0){
															$("#tableBank > tbody").empty();
														}else{
															$("#tableBank > tbody").empty();
															$("#tableBank > tbody").append("<tr colspan='4'>ไม่มีข้อมูล</tr>");
														}
														var bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
														$.each(response.result,function(i,value){

															if(value.status == '1' || value.status == '2'){

																var url_img_bank = "";
																if (value.bank_code ==1 || value.bank_code =='01') {
																	url_img_bank =  base_url+"/bank/1.png";
																}
																else if (value.bank_code ==2 || value.bank_code =='02') {
																	url_img_bank =  base_url+"/bank/2.png";
																}
																else if (value.bank_code ==3 || value.bank_code =='03') {
																	url_img_bank =  base_url+"/bank/3.png";
																}
																else if (value.bank_code ==4 || value.bank_code =='04') {
																	url_img_bank =  base_url+"/bank/5.png";
																}
																else if (value.bank_code ==5 || value.bank_code =='05') {
																	url_img_bank =  base_url+"/bank/6.png";
																}
																else if (value.bank_code ==6 || value.bank_code =='06') {
																	url_img_bank =  base_url+"/bank/4.png";
																}
																else if (value.bank_code ==7 || value.bank_code =='07') {
																	url_img_bank =  base_url+"/bank/7.png";
																}else if (value.bank_code ==8 || value.bank_code =='08') {
																	url_img_bank =  base_url+"/bank/9.png";
																}else if (value.bank_code ==9 || value.bank_code =='09') {
																	url_img_bank =  base_url+"/bank/baac.png";
																}
																else if (value.bank_code ==10 || value.bank_code =='10') {
																		url_img_bank =  base_url+"/bank/10.png";
																}
																else {
																	url_img_bank = base_url+"/bank/not-found.png";
																}
																var type_bank = '';
																if (value.status == '1') {
																	 type_bank = 'AUTO';
																} else if(value.status == '2') {
																	type_bank = 'SLIP';
																}
																var parts = parseFloat(value.balance).toFixed(2).split(".");
																var num = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																		(parts[1] ? "." + parts[1] : "");
																url_img_bank = '<ul class="list-unstyled users-list m-0  d-flex align-items-center">\n' +
																		'<li data-toggle="tooltip" data-popup="tooltip-custom" data-placement="bottom" data-original-title="'+(typeof(bank_list[value.bank_code]) != "undefined" ? bank_list[value.bank_code] : "-")+'" class="avatar pull-up m-0">' +
																		'<img class="media-object rounded" src="'+url_img_bank+'" alt="Avatar" height="30" width="30"></li></ul>';
																$("#tableBank > tbody").append("<tr>" +
																		"<td>"+url_img_bank+"</td>" +
																		"<td>"+value.account_name+"</td>" +
																		"<td>"+value.bank_number+"</td>" +
																		"<td>"+type_bank+"</td>" +
																		"<td>"+num+"</td>" +
																		"</tr>" +
																		"");
															}
														})
													},
													error: function() {
														loading_bank = false;
													}
												});
											}
										}
										getTableBank();
										setInterval(function(){
											getTableBank();
										},15000);
									</script>
								</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					var loading_report_all_day =false;
					function getTableReportAllDay(){
						if(!loading_report_all_day){
							loading_report_all_day = true;
							$.ajax({
								url: BaseURL + "/home/report_summary_all_day",
								method: "GET",
								dataType: 'json',
								success: function(response) {
									loading_report_all_day = false;
									var data = response.result;
									if(data.deposit){
										var parts = parseFloat(data.deposit).toFixed(2).split(".");
										var num = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
												(parts[1] ? "." + parts[1] : "");
										$("#txt_report_all_day_deposit").text(num+" ฿");
									}
									if(data.withdraw){
										var parts = parseFloat(data.withdraw).toFixed(2).split(".");
										var num = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
												(parts[1] ? "." + parts[1] : "");
										$("#txt_report_all_day_withdraw").text(num+" ฿");
									}
									if(data.total){
										var parts = parseFloat(data.total).toFixed(2).split(".");
										var num = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
												(parts[1] ? "." + parts[1] : "");
										$("#txt_report_all_day_total").text(num+" ฿");
									}
									if(data.member){
										$("#txt_report_all_day_member").text(data.member);
									}
									if(data.deposit_count){
										$("#txt_report_all_day_deposit_count").text(data.deposit_count);
									}
									if(data.withdraw_count){
										$("#txt_report_all_day_withdraw_count").text(data.withdraw_count);
									}
									if(data.member_total){
										$("#txt_report_all_day_member_total").text(data.member_total);
									}
								},
								error: function() {
									loading_report_all_day = false;
								}
							});
						}
					}
					setInterval(function(){
						getTableReportAllDay();
					},100000);
				</script>
				<!-- สิ้นสุดแถบแรก -->
				<div class="row">
					<div class="col-12 col-md-6">
						<div class="card">
							<div class="card">
								<div class="card-header">
									<h4 class="mb-0 danger">รายการถอน</h4>
								</div>
								<div class="card-content">
									<div class="table-responsive mt-1">
										<table id="table-history-withdraw" class="table table-hover-animation mb-0">
											<thead>
											<tr>
												<th>ชื่อ</th>
												<th>สถานะ</th>
												<th>ธนาคาร</th>
												<th>เลขบัญชี</th>
												<th>สั่งถอนเมื่อ</th>
												<th class="text-right">จำนวนเงิน</th>
											</tr>
											</thead>
											<tbody>
											</tbody>
											<script>
												var loading_history_withdraw =false;
												var bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
												function getTableHistoryWithdraw(){
													if(!loading_history_withdraw){
														loading_history_withdraw = true;
														$.ajax({
															url: BaseURL + "/home/report_history_withdraw?start=0&length=5",
															method: "GET",
															dataType: 'json',
															success: function(response) {
																loading_history_withdraw = false;
																var base_url = '<?php echo base_url('assets/images') ?>';
																if(response.result.length > 0){
																	$("#table-history-withdraw > tbody").empty();
																}else{
																	$("#table-history-withdraw > tbody").empty();
																	$("#table-history-withdraw > tbody").append("<tr colspan='6'>ไม่มีข้อมูล</tr>");
																}
																$.each(response.result,function(i,value){
																	var url_img_bank = "";
																	var status = "";
																	if (value.bank ==1 || value.bank =='01') {
																		url_img_bank =  base_url+"/bank/1.png";
																	}
																	else if (value.bank ==2 || value.bank =='02') {
																		url_img_bank =  base_url+"/bank/2.png";
																	}
																	else if (value.bank ==3 || value.bank =='03') {
																		url_img_bank =  base_url+"/bank/3.png";
																	}
																	else if (value.bank ==4 || value.bank =='04') {
																		url_img_bank =  base_url+"/bank/5.png";
																	}
																	else if (value.bank ==5 || value.bank =='05') {
																		url_img_bank =  base_url+"/bank/6.png";
																	}
																	else if (value.bank ==6 || value.bank =='06') {
																		url_img_bank =  base_url+"/bank/4.png";
																	}
																	else if (value.bank ==7 || value.bank =='07') {
																		url_img_bank =  base_url+"/bank/7.png";
																	}else if (value.bank ==8 || value.bank =='08') {
																		url_img_bank =  base_url+"/bank/9.png";
																	}else if (value.bank ==9 || value.bank =='09') {
																		url_img_bank =  base_url+"/bank/baac.png";
																	}
																	else if (value.bank ==10 || value.bank =='10') {
																		url_img_bank =  base_url+"/bank/10.png";
																	}
																	else {
																		url_img_bank = base_url+"/bank/not-found.png";
																	}
																	if(value.status == 0){
																		status = "<div class='spinner-grow text-warning' role='status'>\n" +
																				"      <span class='sr-only'>Loading...</span>\n" +
																				"</div>รออนุมัติ";
																	}else if(value.status == 1){
																		status = "<div class='spinner-grow text-success' role='status'>\n" +
																				"      <span class='sr-only'>Loading...</span>\n" +
																				"</div>อนุมัติ (ถอนออโต้)";
																	}else if(value.status == 2){
																		status = "<div class='spinner-grow text-danger' role='status'>\n" +
																				"      <span class='sr-only'>Loading...</span>\n" +
																				"</div>ไม่อนุมัติ";
																	}else if(value.status == 3){
																		status = "<div class='spinner-grow text-success' role='status'>\n" +
																				"      <span class='sr-only'>Loading...</span>\n" +
																				"</div>อนุมัติ (ถอนมือ)";
																	}else if(value.status == 4){
																		status = "<div class='spinner-grow text-warning' role='status'>\n" +
																				"      <span class='sr-only'>Loading...</span>\n" +
																				"</div>ดำเนินการถอนออโต้";
																	}
																	var parts = parseFloat(value.amount).toFixed(2).split(".");
																	var num = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																			(parts[1] ? "." + parts[1] : "");
																	url_img_bank = '<ul class="list-unstyled users-list m-0  d-flex align-items-center">\n' +
																			'<li data-toggle="tooltip" data-popup="tooltip-custom" data-placement="bottom" data-original-title="'+(typeof(bank_list[value.bank]) != "undefined" ? bank_list[value.bank] : "-")+'" class="avatar pull-up m-0">' +
																			'<img class="media-object rounded" src="'+url_img_bank+'" alt="Avatar" height="30" width="30"></li></ul>';
																	$("#table-history-withdraw > tbody").append("<tr>" +
																			"<td>"+value.full_name+"</td>" +
																			"<td>"+status+"</td>" +
																			"<td>"+url_img_bank+"</td>" +
																			"<td>"+value.bank_number+"</td>" +
																			"<td>"+value.created_at+"</td>" +
																			"<td class='text-right'>"+num+"</td>" +
																			"</tr>" +
																			"");

																})
																$("body").tooltip({
																	selector: '[data-toggle="tooltip"]'
																});
																$('.tooltip.fade.show').each(function(){
																	if($('[aria-describedby="'+$(this).attr('id')+'"]').length == 0){
																		$(this).remove();
																	}
																});
															},
															error: function() {
																loading_history_withdraw = false;
															}
														});
													}
												}
												getTableHistoryWithdraw();
												setInterval(function(){
													getTableHistoryWithdraw();
												},100000);
											</script>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="card">
							<div class="card">
								<div class="card-header">
									<h4 class="mb-0 danger">รายการฝาก</h4>
								</div>
								<div class="card-content">
									<div class="table-responsive mt-1">
										<table id="table-history-deposit" class="table table-hover-animation mb-0">
											<thead>
											<tr>
												<th>ชื่อ</th>
												<th>ธนาคาร</th>
												<th>เลขบัญชี</th>
												<th>ฝากเมื่อ</th>
												<th class="text-right">จำนวนเงิน</th>
											</tr>
											</thead>
											<tbody>
											</tbody>
											<script>
												var loading_history_deposit =false;
												var bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
												function getTableHistoryDeposit(){
													if(!loading_history_deposit){
														loading_history_deposit = true;
														$.ajax({
															url: BaseURL + "/home/report_history_deposit?start=0&length=5",
															method: "GET",
															dataType: 'json',
															success: function(response) {
																loading_history_deposit = false;
																var base_url = '<?php echo base_url('assets/images') ?>';
																if(response.result.length > 0){
																	$("#table-history-deposit > tbody").empty();
																}else{
																	$("#table-history-deposit > tbody").empty();
																	$("#table-history-deposit > tbody").append("<tr colspan='5'>ไม่มีข้อมูล</tr>");
																}
																$.each(response.result,function(i,value){
																	var url_img_bank = "";
																	if (value.bank ==1 || value.bank =='01') {
																		url_img_bank =  base_url+"/bank/1.png";
																	}
																	else if (value.bank ==2 || value.bank =='02') {
																		url_img_bank =  base_url+"/bank/2.png";
																	}
																	else if (value.bank ==3 || value.bank =='03') {
																		url_img_bank =  base_url+"/bank/3.png";
																	}
																	else if (value.bank ==4 || value.bank =='04') {
																		url_img_bank =  base_url+"/bank/5.png";
																	}
																	else if (value.bank ==5 || value.bank =='05') {
																		url_img_bank =  base_url+"/bank/6.png";
																	}
																	else if (value.bank ==6 || value.bank =='06') {
																		url_img_bank =  base_url+"/bank/4.png";
																	}
																	else if (value.bank ==7 || value.bank =='07') {
																		url_img_bank =  base_url+"/bank/7.png";
																	}else if (value.bank ==8 || value.bank =='08') {
																		url_img_bank =  base_url+"/bank/9.png";
																	}else if (value.bank ==9 || value.bank =='09') {
																		url_img_bank =  base_url+"/bank/baac.png";
																	}
																	else if (value.bank ==10 || value.bank =='10') {
																		url_img_bank =  base_url+"/bank/10.png";
																	}
																	else {
																		url_img_bank = base_url+"/bank/not-found.png";
																	}
																	var parts = parseFloat(value.amount).toFixed(2).split(".");
																	var num = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																			(parts[1] ? "." + parts[1] : "");
																	url_img_bank = '<ul class="list-unstyled users-list m-0  d-flex align-items-center">\n' +
																			'<li data-toggle="tooltip" data-popup="tooltip-custom" data-placement="bottom" data-original-title="'+(typeof(bank_list[value.bank]) != "undefined" ? bank_list[value.bank] : "-")+'" class="avatar pull-up m-0">' +
																			'<img class="media-object rounded" src="'+url_img_bank+'" alt="Avatar" height="30" width="30"></li></ul>';
																	$("#table-history-deposit > tbody").append("<tr>" +
																			"<td>"+value.full_name+"</td>" +
																			"<td>"+url_img_bank+"</td>" +
																			"<td>"+value.bank_number+"</td>" +
																			"<td>"+value.created_at+"</td>" +
																			"<td class='text-right'>"+num+"</td>" +
																			"</tr>" +
																			"");

																})
																$("body").tooltip({
																	selector: '[data-toggle="tooltip"]'
																});
																$('.tooltip.fade.show').each(function(){
																	if($('[aria-describedby="'+$(this).attr('id')+'"]').length == 0){
																		$(this).remove();
																	}
																});
															},
															error: function() {
																loading_history_deposit = false;
															}
														});
													}
												}
												getTableHistoryDeposit();
												setInterval(function(){
													getTableHistoryDeposit();
												},100000);
											</script>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-12 col-md-6">
						<div class="card">
							<div class="card">
								<div class="card-header">
									<h4 class="mb-0 danger">สรุปรายวัน</h4>
								</div>
								<div class="card-content">
									<div class="table-responsive mt-1">
										<table id="table-summary-per-day" class="table table-hover-animation mb-0">
											<thead>
											<tr>
												<th>วันที่</th>
												<th class="text-right">ยอดฝาก</th>
												<th class="text-right">ยอดถอน</th>
												<th class="text-right">กำไรสุทธิ</th>
											</tr>
											</thead>
											<tbody>
											</tbody>
											<script>
												var loading_summary_per_day =false;
												function getTableSummaryPerDay(){
													if(!loading_summary_per_day){
														loading_summary_per_day = true;
														$.ajax({
															url: BaseURL + "/home/report_summary_per_day",
															method: "GET",
															dataType: 'json',
															success: function(response) {
																loading_summary_per_day = false;
																if(response.result.length > 0){
																	$("#table-summary-per-day > tbody").empty();
																}else{
																	$("#table-summary-per-day > tbody").empty();
																	$("#table-summary-per-day > tbody").append("<tr colspan='4'>ไม่มีข้อมูล</tr>");
																}
																$.each(response.result,function(i,value){
																	var parts_deposit = parseFloat(value.deposit).toFixed(2).split(".");
																	var deposit = parts_deposit[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																			(parts_deposit[1] ? "." + parts_deposit[1] : "");
																	var parts_withdraw = parseFloat(value.withdraw).toFixed(2).split(".");
																	var withdraw = parts_withdraw[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																			(parts_withdraw[1] ? "." + parts_withdraw[1] : "");
																	var parts_total = parseFloat(value.total).toFixed(2).split(".");
																	var total = parts_total[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																			(parts_total[1] ? "." + parts_total[1] : "");
																	$("#table-summary-per-day > tbody").append("<tr>" +
																			"<td>"+value.day+"</td>" +
																			"<td class='text-right'>"+deposit+"</td>" +
																			"<td  class='text-right'>"+withdraw+"</td>" +
																			"<td  class='text-right'>"+total+"</td>" +
																			"</tr>" +
																			"");

																})
															},
															error: function() {
																loading_summary_per_day = false;
															}
														});
													}
												}
												getTableSummaryPerDay();
												setInterval(function(){
													getTableSummaryPerDay();
												},100000);
											</script>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="card">
							<div class="card">
								<div class="card-header">
									<h4 class="mb-0 danger">สรุปรายเดือน</h4>
								</div>
								<div class="card-content">
									<div class="table-responsive mt-1">
										<table id="table-summary-per-month" class="table table-hover-animation mb-0">
											<thead>
											<tr>
												<th>เดือน</th>
												<th class="text-right">ยอดฝาก</th>
												<th class="text-right">ยอดถอน</th>
												<th class="text-right">กำไรสุทธิ</th>
											</tr>
											</thead>
											<tbody>
											</tbody>
											<script>
												var loading_summary_per_month =false;
												function getTableSummaryPerMonth(){
													if(!loading_summary_per_month){
														loading_summary_per_month = true;
														$.ajax({
															url: BaseURL + "/home/report_summary_per_month",
															method: "GET",
															dataType: 'json',
															success: function(response) {
																loading_summary_per_month = false;
																if(response.result.length > 0){
																	$("#table-summary-per-month > tbody").empty();
																}else{
																	$("#table-summary-per-month > tbody").empty();
																	$("#table-summary-per-month > tbody").append("<tr colspan='4'>ไม่มีข้อมูล</tr>");
																}
																$.each(response.result,function(i,value){
																	var parts_deposit = parseFloat(value.deposit).toFixed(2).split(".");
																	var deposit = parts_deposit[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																			(parts_deposit[1] ? "." + parts_deposit[1] : "");
																	var parts_withdraw = parseFloat(value.withdraw).toFixed(2).split(".");
																	var withdraw = parts_withdraw[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																			(parts_withdraw[1] ? "." + parts_withdraw[1] : "");
																	var parts_total = parseFloat(value.total).toFixed(2).split(".");
																	var total = parts_total[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																			(parts_total[1] ? "." + parts_total[1] : "");
																	$("#table-summary-per-month > tbody").append("<tr>" +
																			"<td>"+value.month+"</td>" +
																			"<td class='text-right'>"+deposit+"</td>" +
																			"<td  class='text-right'>"+withdraw+"</td>" +
																			"<td  class='text-right'>"+total+"</td>" +
																			"</tr>" +
																			"");

																})
															},
															error: function() {
																loading_summary_per_month = false;
															}
														});
													}
												}
												getTableSummaryPerMonth();
												setInterval(function(){
													getTableSummaryPerMonth();
												},100000);
											</script>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Dashboard Analytics end -->

			</div>
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
<?php if ($this->session->flashdata('toast')): ?>
	<script>
		const Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 2500
		});
		Toast.fire({
			type: 'success',
			title: '<?php echo $this->session->flashdata('toast'); ?>'
		});
	</script>
<?php elseif ($this->session->flashdata('warning')): ?>
	<script>
		const Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 2500
		});
		Toast.fire({
			type: 'warning',
			title: '<?php echo $this->session->flashdata('warning'); ?>'
		});
	</script>
<?php elseif ($this->session->flashdata('error')): ?>
	<script>
		const Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 2500
		});
		Toast.fire({
			type: 'error',
			title: '<?php echo $this->session->flashdata('error'); ?>'
		});
	</script>
<?php endif; ?>

</body>

</html>
