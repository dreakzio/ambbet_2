<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ระบบ ตำแหน่งการใช้งาน</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการ ตำแหน่งการใช้งาน</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row" style=" margin-bottom: 40px;">
						<div class="col-12 text-right">
							<a href="<?php echo site_url('role/role_form_create'); ?>" class="btn bg-gradient-primary"><span><i class="fa fa-plus mr-1"></i></span>เพิ่มตำแหน่ง</a>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<div class="table-responsive">
								<table id="table" class="table dataTables_wrapper dt-bootstrap4  table-hover" style="width:100%">
									<thead>
									<tr>
										<th class="text-center">#</th>
										<th class="text-center">ตำแหน่ง</th>
										<th class="text-center">Level</th>
										<th class="text-center">เมนูที่สามารถเข้าได้</th>
										<th class="text-center">ตำแหน่งภายใต้ที่จัดการได้</th>
										<th class="text-center">สถานะ</th>
										<th class="text-center">จัดการข้อมูล</th>
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
<div class="modal fade" id="modal_detail" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="max-height: 600px;overflow-y: auto">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn bg-gradient-primary" data-dismiss="modal">ตกลง</button>
			</div>
		</div>
	</div>
</div>
<script>
	const role_display = JSON.parse('<?php echo json_encode(roleDisplay()); ?>');
	const role_user = '<?php echo roleMember(); ?>';
	const role_superadmin = '<?php echo roleSuperAdmin(); ?>';
</script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/role/role.js') ?>"></script>
