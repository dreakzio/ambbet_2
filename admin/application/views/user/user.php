<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ระบบจัดการสมาชิก</h2>
					<!-- <div class="breadcrumb-wrapper col-12">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="sk-layout-2-columns.html">Home</a>
							</li>
							<li class="breadcrumb-item"><a href="#">Starter Kit</a>
							</li>
							<li class="breadcrumb-item active">Fixed Layout
							</li>
						</ol>
					</div> -->
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการสมาชิก</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row mx-auto" style="width: 98%">
						<div class="col-12 col-md-10 pr-1 pl-1">
							<div class="input-group mb-1">
								<div class="input-daterange input-group " id="datepicker">
									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">วันที่สมัคร (จาก)</span>
									</div>
									<input type="text" class="input-sm form-control" id="date_start_report"
										   name="start" />
									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">วันที่สมัคร (ถึง)</span>
									</div>
									<input type="text" class="input-sm form-control" id="date_end_report" name="end" />
									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">สถานะ</span>
									</div>
									<select name="status" id="status" class="form-control">
										<option value="">ทั้งหมด</option>
										<option value="1">ได้รับยูสเซอร์แล้ว</option>
										<option value="0">ยังไม่ได้รับยูสเซอร์</option>
									</select>

									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">สิทธิ์</span>
									</div>
									<?php
									$canManageRole = canManageRole()[$_SESSION['user']['role']];
									$roleDisplay = roleDisplay();
									?>
									<select name="role" id="role" class="form-control">
										<option value="" selected>ทั้งหมด</option>
										<?php foreach ($canManageRole as $canManage): ?>
											<?php if(array_key_exists($canManage,$roleDisplay)): ?>
												<option value="<?php echo $canManage; ?>">
													<?php echo $roleDisplay[$canManage]; ?></option>
											<?php endif; ?>
										<?php endforeach; ?>
									</select>

								</div>
							</div>
						</div>
						<div class="col-12 col-md-2 mx-auto">
							<div class="form-group">
								<button type="button" id="btn-search" name="button"
										class="btn bg-gradient-primary waves-effect waves-light"><span
										class="text-silver">ค้นหา</span></button>
							</div>
						</div>
					</div>
					<!-- <div class="row" style=" margin-bottom: 40px;">
              <div class="col-12 text-right">
                <a href="<?php echo site_url('member/member_form_create'); ?>" class="btn btn-success"><span><i class="fa fa-plus mr-2"></i></span>เพิ่มผู้ใช้งาน</a>
              </div>
            </div> -->

					<div class="row">
						<div class="col-12">
							<div class="table-responsive">
								<!-- table-bordered -->
								<table id="table" class="table dataTables_wrapper dt-bootstrap4  table-hover"
									   style="width:100%">
									<thead>
									<tr>
										<!-- <th class="text-center" width="15px">#</th> -->
										<th class="text-center" width="60px">เบอร์มือถือ</th>
										<th class="text-center">ชื่อ - นามสกุล</th>
										<th class="text-right">กำไร</th>
										<th class="text-center">ธนาคาร</th>
										<th class="text-center">เลขบัญชี</th>
										<th class="text-center">วันที่สมัคร</th>
										<th class="text-center">กระเป๋า (รอฝาก / Commission / คืนยอดเสีย /
											แต้มเช็คอิน)</th>
										<th class="text-center">Rank / ยอดฝากรวม</th>
										<th class="text-center">User / Password[พนัน]</th>
										<th class="text-center">หมายเหตุ</th>
										<th class="text-center">สิทธิ์</th>
										<th class="text-center">จัดการข้อมูล</th>
										<!-- <th class="text-center">ข้อมูลธุรกรรม</th> -->
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
<div class="modal fade" id="modal_username" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Username / Password</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6" id="username">
						Username :
					</div>
					<div class="col-md-6" id="password">
						Password :
					</div>
					<!--<div class="col-md-6 mt-2 mb-2" id="credit">
					<strong>MAIN WALLET :</strong>
				  </div>-->
				</div>
				<div class="row mt-2">
					<div class="col-12">
						<ul class="list-group" id="others_credit">
						</ul>
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
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8">
</script>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<!-- <script src="<?php echo base_url('assets/app-assets/js/scripts/components.js') ?>"></script> -->
<!-- <script src="<?php echo base_url('assets/app-assets/js/scripts/popover/popover.js') ?>"></script> -->
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script>
	const bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
	const role_display = JSON.parse('<?php echo json_encode($roleDisplay); ?>');
	const game_code_list = JSON.parse('<?php echo json_encode(game_code_list()) ?>');
	const game_code_text_list = JSON.parse('<?php echo json_encode(game_code_text_list()) ?>');
</script>
<script src="<?php echo base_url('assets/scripts/user/user.js?').time() ?>"></script>
