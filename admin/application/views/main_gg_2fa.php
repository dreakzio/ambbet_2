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
					<ul hidden class="nav navbar-nav">
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
							<div class="user-nav d-sm-flex d-none"><span class="user-name text-bold-600"><?php echo $user['full_name']; ?></span><span class="user-status"><?php echo roleDisplay()[$user['role']]; ?> | <?php echo $user['username']; ?></span></div><span><i class="fa fa-user fa-2x text-primary"></i></span>
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
			<?php
			$CI =& get_instance();
			$CI->load->library('Menu_service');
			$group_menu_list = $CI->menu_service->get_menu_list(null,null,true);
			?>
			<?php foreach ($group_menu_list as $group_id => $group_menu): ?>
				<?php if(isset($group_menu['menu_list']) && count($group_menu['menu_list']) > 0): ?>
					<li class=" navigation-header"><span class="<?php echo $group_menu['icon_class']; ?>"><?php echo $group_menu['name']; ?></span></li>
					<?php foreach ($group_menu['menu_list'] as $menu_index => $menu): ?>
						<?php
						$chk_active = false;
						$chk_active_sub_id = null;
						$chk_has_sub = false;
						$chk_has_sub_open = false;
						$uri_segment_cnt = count($this->uri->segment_array());
						if(empty($menu['menu_url']) && $menu['menu_have_child'] == "1"){
							$chk_has_sub = true;
							if(isset($menu['node_menu_list']) && is_array($menu['node_menu_list']) && count($menu['node_menu_list']) > 0){
								foreach ($menu['node_menu_list'] as $node_menu){

									$node_menu_segment_url = explode('/',$node_menu['url']);
									$node_menu_segment_url_cnt = count($node_menu_segment_url) ;
									if(
										!$chk_has_sub_open &&
										$uri_segment_cnt >= 1 && $uri_segment_cnt == $node_menu_segment_url_cnt
									){
										$cnt_match_segment_all = 0;
										for ($i=1;$i<=$uri_segment_cnt;$i++){
											if(
												!empty($this->uri->segment($i)) && (
													strtolower($node_menu_segment_url[$i-1])  == strtolower($this->uri->segment($i))
												)
											){
												$cnt_match_segment_all += 1;
											}
										}
										if($cnt_match_segment_all == $uri_segment_cnt){
											$chk_has_sub_open = true;
											$chk_active_sub_id = $node_menu['id'];
										}
									}else if(
										!$chk_has_sub_open &&
										$uri_segment_cnt >= 2 &&
										!empty($this->uri->segment(2)) &&
										($uri_segment_cnt >= 2 && strtolower(explode("_", $this->uri->segment(2))[0]) == strtolower($node_menu_segment_url[1]))
									){
										$chk_has_sub_open = true;
										$chk_active_sub_id = $node_menu['id'];
									}
								}
							}
						}else{
							$menu_segment_url = explode('/',$menu['menu_url']);
							$menu_segment_url_cnt = count($menu_segment_url) ;
							if(
								$uri_segment_cnt >= 1 && $uri_segment_cnt == $menu_segment_url_cnt
							){
								$cnt_match_segment_all = 0;
								for ($i=1;$i<=$uri_segment_cnt;$i++){
									if(!empty($this->uri->segment($i)) && strtolower($menu_segment_url[$i-1])  == strtolower($this->uri->segment($i))){
										$cnt_match_segment_all += 1;
									}
								}
								if($cnt_match_segment_all == $uri_segment_cnt){
									$chk_active = true;
								}
							}else if($menu['menu_url'] == 'home' && empty($this->uri->segment(1))){
								$chk_active = true;
							}else if(
								$uri_segment_cnt >= 2 &&
								!empty($this->uri->segment(2)) &&
								(
									strtolower(explode("_", $this->uri->segment(2))[0]) == strtolower($menu_segment_url[1]) ||
									strpos(strtolower($menu_segment_url[0]),strtolower(explode("_", $this->uri->segment(2))[0])) !== FALSE
								)
							){
								$chk_active = true;
							}
						}

						?>
						<li class="nav-item <?php echo $chk_has_sub ? 'has-sub'.($chk_has_sub_open ? ' open' : '') : ($chk_active ? 'active' : '') ?>">
							<a href="<?php echo site_url($menu['menu_url']) ?>"><i class="<?php echo $menu['menu_icon_class']; ?>"></i><span class="menu-title" ><?php echo $menu['menu_name']; ?></span></a>
							<?php if(isset($menu['node_menu_list']) && is_array($menu['node_menu_list']) && count($menu['node_menu_list']) > 0): ?>
								<ul class="menu-content" style="">
									<?php foreach ($menu['node_menu_list'] as $node_menu): ?>
										<li class="nav-item <?php echo $chk_active_sub_id == $node_menu['id'] ? 'active' : ''; ?>"><a href="<?php echo site_url($node_menu['url']) ?>">
												<i class="<?php echo empty($node_menu['icon_class']) ? 'feather icon-circle' : $node_menu['icon_class']; ?>"></i>
												<span class="menu-item"><?php echo $node_menu['name']; ?></span></a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endforeach; ?>
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
