<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ระบบ เมนู (หมวดหมู่)</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการ เมนู (หมวดหมู่)</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row" style=" margin-bottom: 40px;">
						<div class="col-12 text-right">
							<a href="<?php echo site_url('menu/category_form_create'); ?>" class="btn bg-gradient-primary"><span><i class="fa fa-plus mr-1"></i></span>เพิ่มหมวดหมู่</a>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<div class="table-responsive">
								<table id="table" class="table dataTables_wrapper dt-bootstrap4  table-hover" style="width:100%">
									<thead>
									<tr>
										<th class="text-center">#</th>
										<th class="text-center">ชื่อ</th>
										<th class="text-left">รายละเอียด</th>
										<th class="text-center">Icon Class</th>
										<th class="text-center">สถานะ</th>
										<th class="text-center">เรียงลำดับ</th>
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
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/group_menu/group_menu.js') ?>"></script>
