<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ระบบ เมนู (หลัก)</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการ เมนู (หลัก)</h4>
			</div
			<div class="card-content">
				<div class="card-body">
					<div class="row mx-auto" style="width: 98%">
						<div class="col-12 col-md-4 pr-1 pl-1">
							<div class="input-group mb-1">
								<div class="input-daterange input-group " id="datepicker">
									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">หมวดหมู่</span>
									</div>
									<select name="parent_id" id="parent_id" class="form-control">
										<option value="" selected>ทั้งหมด</option>
										<?php foreach ($group_menu_list as $group_menu): ?>
											<option value="<?php echo $group_menu['id']; ?>">
												<?php echo $group_menu['name'].($group_menu['is_deleted'] == '1' ? ' (ปิดใช้งาน)' : ''); ?></option>
										<?php endforeach; ?>
									</select>

								</div>
							</div>
						</div>
						<div class="col-12 col-md-2">
							<div class="form-group">
								<button type="button" id="btn-search" name="button"
										class="btn bg-gradient-primary waves-effect waves-light"><span
											class="text-silver">ค้นหา</span></button>
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
										<th class="text-center">หมวดหมู่</th>
										<th class="text-center">ชื่อ</th>
										<th class="text-left">รายละเอียด</th>
										<th class="text-center">URL</th>
										<th class="text-left">Icon Class</th>
										<th class="text-center">มีเมนูย่อย</th>
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
<script src="<?php echo base_url('assets/scripts/menu/menu.js') ?>"></script>
