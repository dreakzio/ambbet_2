<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">รายงานผลประกอบการ</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-content">
				<div class="card-body">
					<div class="row mx-auto" style="width: 98%">
						<div class="col-12 col-sm-7 pr-1 pl-1">
							<h4 class="card-title"><i class="fa fa-bar-chart-o"></i>&nbsp;รายวัน</h4>
							<hr>
							<div class="row">
								<div class="col-12 col-sm-7 pr-1 pl-1">
									<div class="input-group mb-1">
										<div class="input-daterange input-group " id="datepicker">
											<div class="input-group-prepend">
												<span class="input-group-text" id="basic-addon1">วันที่ (จาก)</span>
											</div>
											<input type="text" class="input-sm form-control"  id="date_start_report" name="start" />
											<div class="input-group-prepend">
												<span class="input-group-text" id="basic-addon1">วันที่ (ถึง)</span>
											</div>
											<input type="text" class="input-sm form-control"  id="date_end_report" name="end" />
										</div>
									</div>
								</div>
								<div class="col-12 col-sm-5 mx-auto">
									<div class="form-group">
										<button type="button" id="btn-search" name="button" class="btn bg-gradient-primary waves-effect waves-light"><span class="text-silver">ค้นหา</span></button>
										<button id="btn_export_excel" type="button" class="btn bg-gradient-warning waves-effect waves-light"> &nbsp;&nbsp; ออกรายงาน Excel</button>
									</div>
								</div>
							</div>
							<canvas id="myChartPick"></canvas>
						</div>
						<div class="col-12 col-sm-5 pr-1 pl-1">
							<h4 class="card-title"><i class="fa fa-bar-chart-o"></i>&nbsp;รายเดือน</h4>
							<hr>
							<div class="row">
								<div class="col-12 col-sm-3 pr-1 pl-1">
									<div class="input-group mb-1">
										<div class="input-daterange input-group " id="datepicker">
											<div class="input-group-prepend">
												<span class="input-group-text" id="basic-addon1">ปี</span>
											</div>
											<input type="text" class="input-sm form-control"  id="year_start_report" name="start" />
										</div>
									</div>
								</div>
								<div class="col-12 col-sm-9">
									<div class="form-group">
										<button type="button" id="btn-search" name="button" class="btn bg-gradient-primary waves-effect waves-light"><span class="text-silver">ค้นหา</span></button>
										<button id="btn_export_year_excel" type="button" class="btn bg-gradient-warning waves-effect waves-light"> &nbsp;&nbsp; ออกรายงาน Excel</button>
									</div>
								</div>
							</div>
							<canvas id="myChartYear"></canvas>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>
<link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8"></script>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js" integrity="sha512-QSkVNOCYLtj73J4hbmVoOV6KVZuMluZlioC+trLpewV8qMjsWqlIQvkn1KGX2StWvPMdWGBqim1xlC8krl1EKQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/xlsx.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script>
	const bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
	const game_code_list = JSON.parse('<?php echo json_encode(game_code_list()) ?>');
</script>
<script src="<?php echo base_url('assets/scripts/report/business_profit.js?').time() ?>"></script>
