<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ระบบจัดการธนาคาร</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการธนาคาร</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row" style=" margin-bottom: 40px;">
						<div class="col-12 text-right">
							<a href="<?php echo site_url('bank/bank_form_create'); ?>" class="btn bg-gradient-primary"><span><i class="fa fa-plus mr-1"></i></span>เพิ่มธนาคาร</a>
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
										<th class="text-center">ชื่อบัญชี</th>
										<th class="text-center">เลขบัญชี</th>
										<th class="text-center">Username / Password</th>
										<th class="text-center">เวลาปิดระบบฝากออโต้</th>
										<th class="text-center">จำนวนเงินถอนออโต้ได้ไม่เกิน (บาท/ครั้ง)</th>
										<th class="text-center">โยกเงินออกเพื่อเก็บเข้าบัญชี AUTO</th>
										<th class="text-center">สถานะ</th>
										<th class="text-center">บัญชีที่ใช้ถอน</th>

										<th class="text-center" >จัดการข้อมูล</th>
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

<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/bank/bank.js?'.time()) ?>"></script>
