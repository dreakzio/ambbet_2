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
					<h2 class="content-header-title float-left mb-0">ระบบจัดการฝากเงิน</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<form method="post" id="upload_form" align="center" enctype="multipart/form-data">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">ทำรายการ</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row ">
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">Username</label>
								<select id="username" name="username" class="form-control" style=" width: 100%;">
									<option value="">เลือก Username</option>
									<?php /*foreach ($user as $key => $value): */?><!--
										<option value='<?php /*echo json_encode($value) */?>'><?php /*echo $value['username'] */?></option>
									--><?php /*endforeach; */?>

								</select>
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">เครดิตรอฝาก</label>
								<input type="text" id="credit_before" name="credit_before" class="form-control"  placeholder="ข้อมูลเครดิตคงเหลือ" readonly style="text-align:right;">
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">ประเภท</label>
								<select id="type" name="type" class="form-control" >
									<option value="1">เพิ่ม</option>
									<option value="2">ลด</option>
								</select>
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">จำนวนเงิน</label>
								<input type="text" id="process" name="process" class="form-control"  maxlength="20" placeholder="ข้อมูลจำนวนเงิน" style="text-align:right;">
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">เครดิตหลังทำรายการ</label>
								<input type="text" id="credit_after" name="credit_after" class="form-control"   placeholder="ข้อมูลเครดิตหลังทำรายการ" readonly style="text-align:right;">
							</div>
						</div>
						<div class="col-md-4 col-sm-4" id="div_transaction">
							<div class="form-group">
								<label class="control-label">บันทึกลง Transaction</label>
								<select id="transaction" name="transaction" class="form-control" >
									<option value="0">ไม่</option>
									<option value="1">ใช่</option>
								</select>
							</div>
						</div>
						<div class="col-md-4 col-sm-4" id="div_upload_slip">
							<div class="form-group">
								<label class="control-label">upload slip</label>
								<input type="file" id="image_file" name="image_file" size="20" class="form-control"  onchange="return fileValidation()"/>
								<div id="imagePreview" class="imagePreview"></div>
							</div>
						</div>
					</div>
					<div class="row" id="div_date" style=" display: none;">
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">วันที่</label>
								<input type="text" name="date" id="date" class="form-control" placeholder="วัน/เดือน/ปี" >
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">เวลา</label>
								<input type="text" name="time" id="time" class="form-control clockpicker" placeholder="เวลา" value="<?php echo date('H:i').":00" ?>" data-placement="bottom" data-align="bottom" data-autoclose="true">
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="jumbotron p-1">
								<p class="mb-0">เงื่อนไข/ข้อชี้แจ้ง "บันทึกลง Transaction" เป็น "ใช่"</p>

								<hr class="mt-1 mb-1">
								<p class="mb-0"><b class="text-danger">** เฉพาะบัญชีรับฝากเราที่เป็น SCB เท่านั้น **</b></p>
								<ul class="list-unstyled mt-1">
									<li>-&nbsp;&nbsp;ในช่อง <b><u>"วันที่"</u></b> หากเวลา ณ ตอนนั้นอยู่ในช่วง <b class="text-danger">23:00 - 23:59</b> ให้ทำการบวกวัน <u><b>เพิ่ม 1 วัน</b></u></li>
									<li>-&nbsp;&nbsp;ตัวอย่างเช่น จะเติมแมนนวลในช่วงวันที่-เวลา => <b class="text-danger"><?php echo date('Y-m-d') ?> 23:15</b> ให้กรอกวันที่-เวลาเป็น => <b class="text-danger"><?php echo date('Y-m-d', strtotime('+1 days')) ?> 23:15</b></li>
									<li>- หากนอกเหนือจากเวลา 23:00 - 23:59 <u><b>ไม่ต้องเพิ่มวัน</b></u></li>
								</ul>
								<hr class="mt-1 mb-1">
								<p class="mb-0"><b class="text-danger">** เฉพาะบัญชีรับฝากเราที่เป็นธนาคารอื่นๆ (ไม่ใช่ SCB) **</b></p>
								<ul class="list-unstyled mt-1">
									<li>-&nbsp;&nbsp;<b>ไม่ต้องเพิ่มวัน</b> ให้กรอกวันที่-เวลาตามสลิปจริง</u></li>
								</ul>
							</div>
						</div>
					</div>
					<hr />
					<div class="row">
						<div class="col-md-12">
							<div class="text-right m-b-10">
								<button  id="btn_create" type="submit" class=" btn bg-gradient-success waves-effect waves-light"><span><i class="fa fa-save mr-1"></i></span>บันทึก</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		</form>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการฝากเงิน</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row mx-auto" style="width: 98%">
						<div class="col-12 col-md-8 pr-1 pl-1">
							<div class="input-group mb-1">
								<div class="input-daterange input-group " id="datepicker">
									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">วันที่ (จาก)</span>
									</div>
									<input type="text" class="input-sm form-control"  id="date_start_report" name="start" />
									<input type="text" class="input-sm form-control"  id="time_start_report" name="time_start" />
									<div class="input-group-prepend">
										<span class="input-group-text" id="basic-addon1">วันที่ (ถึง)</span>
									</div>
									<input type="text" class="input-sm form-control"  id="date_end_report" name="end" />
									<input type="text" class="input-sm form-control"  id="time_end_report" name="time_nd" />
								</div>
							</div>
						</div>
						<div class="col-12 col-md-4 mx-auto">
							<div class="form-group">
								<button type="button" id="btn-search" name="button" class="btn bg-gradient-primary waves-effect waves-light"><span class="text-silver">ค้นหา</span></button>
								<button id="btn_export_excel" type="button" class="btn bg-gradient-warning waves-effect waves-light"> &nbsp;&nbsp; ออกรายงาน Excel</button>
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
										<th class="text-center" width="60px">แก้ไขให้</th>
										<th class="text-center">ยอดก่อนหน้านี้</th>
										<th class="text-center">ประเภท</th>
										<th class="text-center">จำนวน</th>
										<th class="text-center">รวม</th>
										<th class="text-center">ทำรายการโดย</th>
										<th class="text-center" >วัน-เวลาสลิป</th>
										<th class="text-center" >วันที่สร้าง</th>
										<th class="text-center" >สลิป</th>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js" ></script>
<link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8"></script>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/xlsx.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/clockpicker/dist/bootstrap-clockpicker.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/bootstrap-datepicker/dist/locales/bootstrap-datepicker.th.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/i18n/th.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/credit/credit.js?').time() ?>"></script>
<script type="text/javascript">
	var bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
</script>
<style>
	.imagePreview{
		max-height: 600px;
	}
	.imagePreview img{
		max-height: 600px;
		max-width: 300px;
	}
</style>
