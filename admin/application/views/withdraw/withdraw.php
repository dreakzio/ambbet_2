<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2/dist/css/select2.css') ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2-theme/dist/select2-bootstrap4.css') ?>">

  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบจัดการถอนเงิน</h2>
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
					  <div class="row ">
						  <div class="col-md-3 col-sm-3">
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
						  <div class="col-md-3 col-sm-3">
							  <div class="form-group">
								  <label class="control-label">จำนวนเงิน</label>
								  <input type="text" id="amount" name="amount" class="form-control"  maxlength="20" placeholder="ข้อมูลจำนวนเงิน" style="text-align:right;">
							  </div>
						  </div>
						  <div class="col-md-3 col-sm-3">
							  <div class="form-group">
								  <label class="control-label">บันทึกลง Transaction</label>
								  <select name="transaction" id="transaction" class="form-control">
									  <option value="Y">ใช่</option>
									  <option value="N">ไม่</option>
								  </select>
							  </div>
						  </div>
						  <div class="col-md-3 col-sm-3">
							  <div class="jumbotron p-1">
								  <p class="mb-0">เงื่อนไข/ข้อชี้แจ้ง "บันทึกลง Transaction"</p>
								  <hr class="mt-1 mb-1">
								  <ul class="list-unstyled mt-1">
									  <li>-&nbsp;&nbsp;เลือก "ใช่" ระบบจะบันทึกลงรายการถอนเงินและลดเครดิต Credit ลูกค้า</li>
									  <li>-&nbsp;&nbsp;เลือก "ไม่" ระบบจะลดเครดิต Credit ลูกค้าอย่างเดียวเท่านั้น</li>
								  </ul>
							  </div>
						  </div>
					  </div>
					  <hr />
					  <div class="row">
						  <div class="col-md-12">
							  <div class="text-right m-b-10">
								  <button  id="btn_create" type="button" class=" btn bg-gradient-success waves-effect waves-light"><span><i class="fa fa-save mr-1"></i></span>บันทึก</button>
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
          <h4 class="card-title">รายการถอนเงิน</h4>
        </div>
        <div class="card-content">
          <div class="card-body">
			  <div class="row mx-auto" style="width: 98%">
				  <div class="col-12 col-md-6 pr-1 pl-1">
					  <div class="input-group mb-1">
						  <div class="input-daterange input-group " id="datepicker">
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">วันที่ถอน (จาก)</span>
							  </div>
							  <input type="text" class="input-sm form-control"  id="date_start_report" name="start" />
							  <input type="text" class="input-sm form-control"  id="time_start_report" name="time_start" />
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">วันที่ถอน (ถึง)</span>
							  </div>
							  <input type="text" class="input-sm form-control"  id="date_end_report" name="end" />
							  <input type="text" class="input-sm form-control"  id="time_end_report" name="time_nd" />
						  </div>
					  </div>
				  </div>
				  <div class="col-md-3 col-sm-3 mx-auto">
					  <div class="form-group">
						  <div class="input-group mb-3">
							  <div class="input-group-prepend">
									  <span class="input-group-text" id="basic-addon1">สถานะ</span>
							  </div>
							  <select id="status" name="status" class="form-control">
								  <option value="" selected>ทั้งหมด</option>
								  <option value="0">รอตรวจสอบ</option>
								  <option value="1">ถอนออโต้</option>
								  <option value="3">ถอนมือ</option>
								  <option value="4">ดำเนินการถอนออโต้</option>
								  <option value="2">ไม่อนุมัติ</option>
							  </select>
						  </div>
					  </div>
				  </div>
				  <div class="col-12 col-md-3 mx-auto">
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

                        <th class="text-center">Username</th>
                        <!-- <th class="text-center">Username (member)</th> -->
                        <th class="text-center">ธนาคาร</th>
						<th class="text-center">ชื่อ-นามสกุล</th>
                        <th class="text-center">เลขบัญชี</th>
                        <th class="text-center">ถอนเมื่อ</th>
                        <th class="text-center">ดำเนินการโดย</th>
                        <th class="text-right">จำนวนเงิน</th>
						<th class="text-center">หมายเหตุ</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">QRCode</th>
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
<div class="modal fade" id="modal_qrcode" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">QRCode</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row mt-2">
					<div class="col-12 text-center">
						<img class="img-fluid img-rounded img-qrcode" src=""/>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn bg-gradient-primary" data-dismiss="modal">ตกลง</button>
			</div>
		</div>
	</div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js" ></script>
  <link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
  <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
  <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8"></script>
  <script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/xlsx.full.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/select2/dist/js/select2.full.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/select2/dist/js/i18n/th.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script>
	const bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
</script>
  <script src="<?php echo base_url('assets/scripts/withdraw/withdraw.js?').time() ?>"></script>
