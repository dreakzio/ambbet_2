<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ระบบโยกเงินออก</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">ทำรายการ</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">จำนวนเงิน</label>
								<input type="number" oninput="validateInputNumber(this)" id="amount" name="amount" class="form-control"  maxlength="20" placeholder="ข้อมูลจำนวนเงิน" style="text-align:right;">
							</div>
						</div>
					</div>
					<div class="row ">
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">ธนาคารต้นทาง</label>
								<select id="bank_id" name="bank_id" class="form-control" style=" width: 100%;">
									<option value="">กรุณาเลือกธนาคารต้นทาง</option>
									<?php foreach ($bank_list as  $value): ?>
										<option data-bank="<?php echo $value['bank_code']; ?>" data-bank-acc-name="<?php echo $value['account_name']; ?>" data-bank-number="<?php echo $value['bank_number']; ?>" value='<?php echo $value['id'] ?>'><?php echo (array_key_exists($value['bank'],getBankList()) ?getBankList()[$value['bank']] : $value['bank'])." ".$value['bank_number']." : ".$value['account_name'] ." : ".$value['bank_name']." : ".$value['bank_name'];  ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">เลขบัญชีต้นทาง</label>
								<input type="number" disabled readonly  id="bank_number" name="bank_number" class="form-control"  maxlength="20" placeholder="ข้อมูลเลขบัญชีต้นทาง">
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">ชื่อบัญชีต้นทาง</label>
								<input type="text" disabled readonly  id="bank_acc_name" name="bank_acc_name" class="form-control"  maxlength="255" placeholder="ข้อมูลชื่อบัญชีต้นทาง">
							</div>
						</div>
					</div>
					<div class="row ">
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">ธนาคารปลายทาง</label>
								<select id="bank_to" name="bank_to" class="form-control" style=" width: 100%;">
									<option value="">กรุณาเลือกธนาคารปลายทาง</option>
									<?php foreach ($bank_code_list as $value): ?>
										<option  value='<?php echo $value['bank_code']; ?>'><?php echo $value['bank_name']." : ".$value['code_en'];  ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">เลขบัญชีปลายทาง</label>
								<input type="number" oninput="validateInputNumber(this)" id="bank_number_to" name="bank_number_to" class="form-control"  maxlength="20" placeholder="ระบุเฉพาะตัวเลข">
							</div>
						</div>
						<div class="col-md-4 col-sm-4">
							<div class="form-group">
								<label class="control-label">ชื่อบัญชีปลายทาง</label>
								<input type="text"  id="bank_acc_name_to" name="bank_acc_name_to" class="form-control"  maxlength="255" placeholder="ข้อมูลชื่อบัญชีปลายทาง">
							</div>
						</div>
					</div>
					<hr />
					<div class="row">
						<div class="col-md-12">
							<div class="text-right m-b-10">
								<button  id="btn_create" type="button" class=" btn bg-gradient-success waves-effect waves-light"><span><i class="fa fa-money mr-1"></i></span>โยกเงินออก</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<div class="content-body">
		<section class="card">
			<div class="card-header">
				<h4 class="card-title">รายการ Logs โยกเงินออก</h4>
			</div>
			<div class="card-content">
				<div class="card-body">
					<div class="row">
						<div class="col-12">
							<div class="table-responsive">
								<table id="table" class="table dataTables_wrapper dt-bootstrap4  table-hover" style="width:100%">
									<thead>
									<tr>
										<th class="text-center">#</th>
										<th class="text-right">จำนวนเงิน</th>
										<th class="text-center">ธนาคารต้นทาง</th>
										<th class="text-center">ธนาคารปลายทาง</th>
										<th class="text-center">สถานะ</th>
										<th class="text-center">รายละเอียด</th>
										<th class="text-center">วันที่</th>
										<th class="text-center">ทำรายการโดย</th>
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
<script>
	const bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
	const bank_code_list = JSON.parse('<?php echo json_encode(getBankListUniqueTextCode()); ?>');
</script>
<script src="<?php echo base_url('assets/scripts/transfer_out/transfer_out.js?'.time()) ?>"></script>
