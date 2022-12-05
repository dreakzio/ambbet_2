<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css')?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2/dist/css/select2.css') ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2-theme/dist/select2-bootstrap4.css') ?>">
<link href="<?php echo base_url('assets/plugins/clockpicker/dist/bootstrap-clockpicker.min.css')?>" rel="stylesheet">
<style>
	.form-control:disabled, .form-control[readonly] {
		background-color: #FFF;
		opacity : 1;
	}
</style>
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ระบบจัดการเครดิต (รอฝาก)</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการเครดิต (รอฝาก)</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row mx-auto" style="width: 98%">
						<div class="col-12 col-md-8 pr-1 pl-1">
							<div class="input-group mb-1">
								<div class="input-daterange input-group " id="datepicker">
									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">วันที่สลิป (จาก)</span>
									</div>
									<input type="text" class="input-sm form-control"  id="date_start_report" name="start" />
									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">วันที่สลิป (ถึง)</span>
									</div>
									<input type="text" class="input-sm form-control"  id="date_end_report" name="end" />
								</div>
							</div>
						</div>
						<div class="col-12 col-md-4 mx-auto">
							<div class="form-group">
								<button type="button" id="btn-search" name="button" class="btn bg-gradient-primary waves-effect waves-light"><span class="text-silver">ค้นหา</span></button>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<div class="table-responsive">
								<!-- table-bordered -->
								<table id="table" class="table dataTables_wrapper dt-bootstrap4  table-hover" style="width:100%">
									<thead>
									<tr>
										<th class="text-center">ธนาคาร</th>
										<th class="text-center">เลขบัญชี</th>
										<th class="text-center">ชื่อบัญชี</th>
										<th class="text-center">วัน-เวลาสลิป</th>
										<th class="text-right">จำนวน</th>
										<th class="text-center">รายละเอียด</th>
										<th class="text-center">ปรับเครดิตให้กับยูส</th>
									</tr>
									</thead>
									<tbody>


									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>
<select style="display: none" id="dummy-user-select">
	<?php foreach ($user_select as $key => $value): ?>
		<option value="<?php echo $value['id']; ?>"><?php echo getBankList()[$value['bank']]; ?> <?php echo $value['bank_number']; ?> <?php echo $value['bank_name']; ?> : <?php echo $value['username']; ?> (<?php echo $value['account_agent_username'] != "" && $value['account_agent_username']  != null ? $value['account_agent_username'] : 'ยังไม่ได้รับยูส'; ?>)</option>
	<?php endforeach; ?>
</select>
<link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8"></script>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/clockpicker/dist/bootstrap-clockpicker.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/bootstrap-datepicker/dist/locales/bootstrap-datepicker.th.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/i18n/th.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script>
	const bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
</script>
<script src="<?php echo base_url('assets/scripts/creditwait/credit.js?').time() ?>"></script>
