<!DOCTYPE HTML>
<?php
$web_setting = [];
$web_setting['web_title'] = $this->Setting_model->setting_find([
		'name' => 'web_title'
]);
$web_setting['web_description'] = $this->Setting_model->setting_find([
		'name' => 'web_description'
]);
$web_setting['web_keyword'] = $this->Setting_model->setting_find([
		'name' => 'web_keyword'
]);
$web_setting['telephone_number'] = $this->Setting_model->setting_find([
		'name' => 'telephone_number'
]);
$web_setting['telephone_number'] = $this->Setting_model->setting_find([
		'name' => 'telephone_number'
]);
$web_setting['line_id'] = $this->Setting_model->setting_find([
		'name' => 'line_id'
]);
$web_setting['line_url'] = $this->Setting_model->setting_find([
		'name' => 'line_url'
]);
$web_setting['web_logo'] = $this->Setting_model->setting_find([
		'name' => 'web_logo'
]);
$web_setting['web_logo_cover'] = $this->Setting_model->setting_find([
		'name' => 'web_logo_cover'
]);
$web_setting['web_logo_cover_m'] = $this->Setting_model->setting_find([
		'name' => 'web_logo_cover_m'
]);
$web_setting['web_name'] = $this->Setting_model->setting_find([
		'name' => 'web_name'
]);
$web_setting['ref_percent'] = $this->Setting_model->setting_find([
		'name' => 'ref_percent'
]);
$web_setting['ref_turn'] = $this->Setting_model->setting_find([
		'name' => 'ref_turn'
]);
$web_setting['theme_color_1'] = $this->Setting_model->setting_find([
		'name' => 'theme_color_1'
]);
$web_setting['feature_bonus_aff_turnover_and_winlose'] = $this->Feature_status_model->setting_find([
		'name' => 'bonus_aff_turnover_and_winlose'
]);
$web_setting['feature_bonus_return_balance_winlose'] = $this->Feature_status_model->setting_find([
		'name' => 'bonus_return_balance_winlose'
]);
$web_setting['feature_bonus_aff_turnover_and_winlose_step2'] = $this->Feature_status_model->setting_find([
		'name' => 'bonus_aff_turnover_and_winlose_step2'
]);
$web_setting['feature_wheel'] = $this->Feature_status_model->setting_find([
		'name' => 'wheel'
]);
$web_setting['ref_return_balance_status'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_status'
]);
$web_setting['ref_return_balance_percent'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_percent'
]);
$web_setting['ref_return_balance_turn'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_turn'
]);
$web_setting['ref_return_balance_rank1_percent'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_rank1_percent'
]);
$web_setting['ref_return_balance_rank2_percent'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_rank2_percent'
]);
$web_setting['ref_return_balance_rank3_percent'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_rank3_percent'
]);
$web_setting['ref_return_balance_rank1_turn'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_rank1_turn'
]);
$web_setting['ref_return_balance_rank2_turn'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_rank2_turn'
]);
$web_setting['ref_return_balance_rank3_turn'] = $this->Setting_model->setting_find([
		'name' => 'ref_return_balance_rank3_turn'
]);
$web_setting['wheel_point_for_spin'] = $this->Setting_model->setting_find([
		'name' => 'wheel_point_for_spin'
]);
$web_setting['wheel_amount_per_point'] = $this->Setting_model->setting_find([
		'name' => 'wheel_amount_per_point'
]);
$web_setting['login_turn'] = $this->Setting_model->setting_find([
		'name' => 'login_turn'
]);
$web_setting['login_status'] = $this->Setting_model->setting_find([
		'name' => 'login_status'
]);
$web_setting['login_point'] = $this->Setting_model->setting_find([
		'name' => 'login_point'
]);
$web_setting['slot_status'] = $this->Setting_model->setting_find([
		'name' => 'slot_status'
]);
$web_setting['casino_status'] = $this->Setting_model->setting_find([
		'name' => 'casino_status'
]);
$web_setting['football_status'] = $this->Setting_model->setting_find([
		'name' => 'football_status'
]);
$web_setting['lotto_status'] = $this->Setting_model->setting_find([
		'name' => 'lotto_status'
]);
error_reporting(0);
?>
<html lang="en">
<head>
	<?php if(isset($page_title)): ?>
		<title><?php echo isset($web_setting['web_title']['value']) ? $page_title." - ".$web_setting['web_title']['value']: $page_title." - ".'AKOKBET - ศูนย์รวมคาสิโนอันดับ 1 ของไทย การันตีความมั่นคง'?></title>
	<?php else: ?>
		<title><?php echo isset($web_setting['web_title']['value']) ? $web_setting['web_title']['value']:'AKOKBET - ศูนย์รวมคาสิโนอันดับ 1 ของไทย การันตีความมั่นคง'?></title>
	<?php endif; ?>
	<meta name="description" content="<?php echo isset($web_setting['web_description']['value'])? $web_setting['web_description']['value']:''?>">
	<meta name="keyword" content="<?php echo isset($web_setting['web_keyword']['value'])? $web_setting['web_keyword']['value']:''?>">
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<link rel="stylesheet" href="<?php echo base_url('/'); ?>assets/css/css-bootstrap.min.css" />
	<style>
		:root {
			--base-color-main: <?php echo isset($web_setting['theme_color_1']['value'])? $web_setting['theme_color_1']['value']:'rgb(252, 19, 19)'; ?>;
			--base-color-main-border: <?php echo isset($web_setting['theme_color_2']['value'])? $web_setting['theme_color_2']['value']:'rgb(217, 19, 19)'; ?>;
		}
		a.forgot{
			color: var(--base-color-main) !important;;
		}
		.btn-outline-red:hover{
			background-color: var(--base-color-main) !important;
			color: white !important;
		}

		.play-button,.fix-nav-bottom-play,.btn-red{
			background-color: var(--base-color-main) !important;
			border-color: var(--base-color-main-border) !important;
		}
		.btn-outline-red{
			color: var(--base-color-main) !important;
			border-color: var(--base-color-main-border) !important;
		}
		.btn-success{
			background-color: var(--base-color-main) !important;
			border-color: var(--base-color-main-border) !important;
		 }
		.btn-green{
			background-color: #118F33 !important;
			border-color: #067924 !important;
		}
	</style>
	<link rel="stylesheet" href="<?php echo base_url('/'); ?>assets/css/css-style.css" />
	<link rel="stylesheet" href="<?php echo base_url('/'); ?>assets/css/css-style-dashboardv5.css" />
	<link rel="stylesheet" href="<?php echo base_url('/'); ?>assets/css/css-animate.css" />
	<link rel="stylesheet" href="<?php echo base_url('/'); ?>assets/css/css-hover.css" />
	<link rel="stylesheet" href="<?php echo base_url('/'); ?>assets/css/thbank-thbanklogos.css" />
	<link rel="stylesheet" href="<?php echo base_url('/'); ?>assets/css/thbank-thbanklogos-colors.css" />
	<link href="<?php echo base_url('/'); ?>assets/favicons/img_member-icon.png" rel="shortcut icon" type="image/x-icon" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" />
	<link href="https://fonts.googleapis.com/css?family=Kanit&amp;display=swap" rel="stylesheet" />
	<script src="<?php echo base_url("assets/plugins/jquery/dist/jquery.min.js") ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
	<script src="<?php echo base_url('assets/plugins/sweetalert2/dist/sweetalert2.all.min.js') ?>"></script>
	<script src="<?php echo base_url('/'); ?>assets/js/9611130-js-particles.js"></script>
	<link rel="stylesheet" href="<?php echo base_url('/'); ?>assets/css/custom.css?=<?php echo date('Y-m-d') ?>">

	<style>

	</style>
</head>
<body>
<input type="hidden" id="base_url" value="<?php echo site_url(); ?>"/>
<script src="<?php echo base_url('assets/plugins/vue.min.js?'.date('Y-m-d')) ?>" ></script>
<!-- Lastly add this package -->
<script src="<?php echo base_url('assets/plugins/accounting-js.js?'.date('Y-m-d')) ?>"></script>
<script src="<?php echo base_url('assets/plugins/vue-numeric.min.js?'.date('Y-m-d')) ?>"></script>
<script src="<?php echo base_url('assets/plugins/qs.min.js?'.date('Y-m-d')) ?>"></script>
<script src="<?php echo base_url('assets/plugins/vue-loading-overlay.min.js?'.date('Y-m-d')) ?>"></script>
<!-- Init the plugin and component-->
<script src="<?php echo base_url('assets/plugins/axios.min.js?'.date('Y-m-d')) ?>"></script>
<script src="<?php echo base_url('assets/scripts/main.js?'.date('Y-m-d')) ?>"></script>
<div>
	<div>

		<?php
		if (isset($header_menu)) {
			$this->load->view($header_menu,['web_setting'=>$web_setting]);
		}

		if (isset($middle_bar)) {
			$this->load->view($middle_bar,['back_btn'=>isset($back_btn) ? $back_btn : false,'back_url'=>isset($back_url) ? $back_url : base_url(),'web_setting'=>$web_setting]);
		}
		?>
		<div style="margin-top: 90px">
			<div id="app">
				<main role="main">
					<div class="container content">
						<div id="<?php echo str_replace("/","_",$page); ?>">
							<script>
								let page_id = '<?php echo str_replace("/","_",$page); ?>';
							</script>
							ทดสอบ
							<?php
							$this->load->view($page,['web_setting'=>$web_setting,'page_id'=>str_replace("/","_",$page)]);
							?>
						</div>
					</div>
				</main>
			</div>
		</div>
	</div>
	<?php
	if (isset($footer_menu)) {
		$this->load->view($footer_menu,['web_setting'=>$web_setting,'class_extend'=> $page == 'dashboard' ? 'd-none d-sm-block' : '']);
	}
	?>
	<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
	<script type="text/javascript" src="<?php echo base_url('assets/scripts/custom.js?'.date('Y-m-d')) ?>"></script>
	<?php if(isset($_SESSION['line_login_error_msg']) && !empty($_SESSION['line_login_error_msg'])): ?>
		<?php
			$line_login_error_msg = $_SESSION['line_login_error_msg'];
			unset($_SESSION['line_login_error_msg']);
		?>
		<script type="text/javascript">
			sweetAlert2('error', '<?php echo $line_login_error_msg; ?>');
		</script>
	<?php endif; ?>
</div>
</body>
