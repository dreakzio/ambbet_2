<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ระบบ Logs สมาชิก</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการ Logs สมาชิก</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row mx-auto" style="width: 98%">
						<div class="col-12 col-md-10 pr-1 pl-1">
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
						<div class="col-12 col-md-2 mx-auto">
							<div class="form-group">
								<button type="button" id="btn-search" name="button" class="btn bg-gradient-primary waves-effect waves-light"><span class="text-silver">ค้นหา</span></button>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<div class="table-responsive">
								<table id="table" class="table dataTables_wrapper dt-bootstrap4  table-hover" style="width:100%">
									<thead>
									<tr>
										<th class="text-center">#</th>
										<th class="text-center">ยูส</th>
										<th class="text-center">แก้ไขโดย</th>
										<th class="text-center">วันที่แก้ไข</th>
										<th class="text-center">รายละเอียด</th>
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
<div class="modal fade" data-backdrop="static" id="modal_detail" tabindex="-1" role="dialog"  aria-hidden="true">
	<div class="modal-dialog  modal-lg modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" >รายละเอียด</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="overflow-y: auto;max-height: 1000px">
				<div class="row mt-2">
					<div class="col-12">
						<div class="table-responsive">
							<table id="table_detail" class="table table-bordered dt-bootstrap4  table-hover" style="width:100%">
								<thead>
								<tr class="bg-primary">
									<th class="text-left text-white">ข้อมูล</th>
									<th class="text-center text-white">ก่อน</th>
									<th class="text-center text-white">หลัง</th>
								</tr>
								</thead>
								<tbody >

								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn bg-gradient-primary" data-dismiss="modal">ตกลง</button>
			</div>
		</div>
	</div>
</div>
<link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8"></script>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script>
	const bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
	const role_display_list = JSON.parse('<?php echo json_encode(roleDisplay()); ?>');
	const game_code_list = JSON.parse('<?php echo json_encode(game_code_list()); ?>');
</script>
<script src="<?php echo base_url('assets/scripts/log/account.js?'.time()) ?>"></script>
