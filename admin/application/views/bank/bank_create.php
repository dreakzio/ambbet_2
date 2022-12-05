<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบจัดการธนาคาร</h2>
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('bank') ?>">รายการธนาคาร</a>
                </li>
                <li class="breadcrumb-item active">เพิ่มธนาคาร</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="content-body">
      <section class="card">
        <div class="card-content">
          <div class="card-body">
            <form class="form" id="form_create" method="POST" action="<?php echo site_url("bank/bank_create") ?>">
              <h3 class="card-title">ข้อมูลธนาคาร</h3>
              <hr>
              <div class="form-body mt-3">
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ธนาคาร</label>
                    <select class="form-control" id="bank_code" name="bank_code">
                      <option value="">เลือกธนาคาร</option>
                      <option value="01">ธนาคารกรุงเทพ จำกัด (มหาชน)</option>
                      <option value="02">ธนาคารกสิกรไทย จำกัด (มหาชน)</option>
                      <option value="03">ธนาคารกรุงไทย จำกัด (มหาชน)</option>
                      <option value="04">ธนาคารทีเอ็มบีธนชาต จำกัด (มหาชน)</option>
                      <option value="05">ธนาคารไทยพาณิชย์ จำกัด (มหาชน)</option>
                      <option value="06">ธนาคารกรุงศรีอยุธยา จำกัด (มหาชน)</option>
                      <option value="07">ธนาคารออมสิน จำกัด (มหาชน)</option>
                      <option value="09">ธนาคารเพื่อการเกษตรและสหกรณ์การเกษตร จำกัด (มหาชน)</option>
                      <option value="10">ทรูมันนี่วอลเล็ท</option>
                    </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อบัญชี</label>
                      <input type="text" id="account_name" name="account_name" class="form-control"  placeholder="ข้อมูลชื่อบัญชี">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">เลขบัญชี</label>
                      <input type="text" id="bank_number" name="bank_number" class="form-control" placeholder="ข้อมูลเลขบัญชี" maxlength="15">
                    </div>
                  </div>
                </div>
                <div class="row ">

					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label" id="username_label">Username</label>
							<input type="text" id="username" name="username" class="form-control"  placeholder="Username">
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label" id="password_label">Password</label>
							<input type="text" id="password" name="password" class="form-control" placeholder="Password">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">ประเภท API</label>
							<select class="form-control" name="api_type" id="api_type">
								<option value="1" selected>ดึงข้อมูลจาก App ธนาคาร</option>
								<option value="2">ดึงข้อมูลจาก Internet Banking</option>
							</select>
						</div>
					</div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="control-label">เบอร์ PromptPay <font style="color:red;">*ใช้ได้เฉพาะไทยพาณิชย์</font></label>
                      <input type="text" id="promptpay_number" name="promptpay_number" class="form-control" placeholder="เบอร์ PromptPay" maxlength="10">
                    </div>
                  </div>
                  <div class="col-md-1">
                    <div class="form-group">
                      <label class="control-label">สถานะ</label>
                      <select class="form-control" name="promptpay_status" id="promptpay_status">
                        <option value="0">ปิด</option>
                        <option value="1">เปิด</option>
                      </select>
                    </div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">เวลาปิดระบบฝากออโต้ (จาก) รุปแบบตัวอย่าง 00:15</label>
							<input type="text" id="start_time_can_not_deposit" name="start_time_can_not_deposit" class="form-control" placeholder="เวลาปิดระบบฝากออโต้ (จาก)">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">เวลาปิดระบบฝากออโต้ (ถึง) รุปแบบตัวอย่าง 00:30</label>
							<input type="text" id="end_time_can_not_deposit" name="end_time_can_not_deposit" class="form-control" placeholder="เวลาปิดระบบฝากออโต้ (ถึง)">
						</div>
					</div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">สถานะ</label>
                      <select class="form-control" name="status" id="status">
                        <option value="0">ปิด</option>
                        <option value="1">เปิด</option>
                      </select>
                    </div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">บัญชีที่ใช้ถอน</label>
							<select class="form-control" name="status_withdraw" id="status_withdraw">
								<option value="0">ปิด</option>
								<option value="1">เปิด</option>
							</select>
						</div>
					</div>
					<div class="col-md-4"></div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">จำนวนเงินถอนออโต้ได้ไม่เกิน (บาท/ครั้ง)</label>
							<input type="number" id="max_amount_withdraw_auto" name="max_amount_withdraw_auto" class="form-control" placeholder="จำนวนเงินถอนออโต้ได้ไม่เกิน (บาท/ครั้ง)">
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group">
							<label class="control-label">ข้อความแสดงหน้าฝากเงิน (เวลาปิดระบบฝากออโต้)</label>
							<textarea type="text" id="message_can_not_deposit" name="message_can_not_deposit" class="form-control" placeholder="ข้อความแสดงหน้าฝากเงิน (เวลาปิดระบบฝากออโต้)" cols="3"></textarea>
						</div>
					</div>
                </div>
              </div>
				<div id="container_auto_transfer" class="col-12" style="display: none">
					<h3 class="card-title header-form-api mt-2">โยกเงินออกเพื่อเก็บเข้าบัญชี AUTO</h3>
					<div class="form-body form-auto-trasnfer">
						<hr>
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label class="control-label">สถานะโยกเงินออกเพื่อเก็บเข้าบัญชี AUTO</label>
									<select class="form-control" name="auto_transfer" id="auto_transfer">
										<option value="0" selected>ปิด</option>
										<option value="1">เปิด</option>
									</select>
								</div>
							</div>
							<div class="col-md-4 col-sm-4">
								<div class="form-group">
									<label class="control-label">ยอดเงินขั้นต่ำที่จะโยกออก</label>
									<input type="text" oninput="validateInputNumber(this)" id="auto_min_amount_transfer" name="auto_min_amount_transfer" class="form-control"  placeholder="ระบุเฉพาะตัวเลข">
								</div>
							</div>
							<div class="col-md-4 col-sm-4">
								<div class="form-group">
									<label class="control-label">ธนาคารปลายทาง</label>
									<select id="auto_transfer_bank_code" name="auto_transfer_bank_code" class="form-control" style=" width: 100%;">
										<option value="">กรุณาเลือกธนาคารปลายทาง</option>
										<?php foreach ($bank_data_list as $value): ?>
											<option  value='<?php echo $value['bank_code']; ?>'><?php echo $value['bank_name']." : ".$value['code_en'];  ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="col-md-4 col-sm-4">
								<div class="form-group">
									<label class="control-label">เลขบัญชีปลายทาง</label>
									<input type="number" id="auto_transfer_bank_number" name="auto_transfer_bank_number" class="form-control"  placeholder="ระบุเฉพาะตัวเลข">
								</div>
							</div>
							<div class="col-md-4 col-sm-4">
								<div class="form-group">
									<label class="control-label">ชื่อบัญชีปลายทาง</label>
									<input type="text"  id="auto_transfer_bank_acc_name" name="auto_transfer_bank_acc_name" class="form-control"  maxlength="255" placeholder="ข้อมูลชื่อบัญชีปลายทาง">
								</div>
							</div>
						</div>
					</div>

				</div>
				<h3 class="card-title mt-2 header-form-api">API</h3>

			  <div class="form-body form-api">
				  <hr>
					<div class="row ">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label" id="api_token_1_label">Device ID</label>
								<textarea type="text" id="api_token_1" name="api_token_1"   rows="3" class="form-control"  placeholder="Device ID"></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label" id="api_token_2_label">PIN</label>
								<textarea type="text" id="api_token_2" name="api_token_2" rows="3" class="form-control" placeholder="PIN"></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label" id="api_token_3_label">Other</label>
								<textarea type="text" id="api_token_3" name="api_token_3" rows="3" class="form-control" placeholder="Other"></textarea>
							</div>
						</div>
					</div>
				</div>
				<hr />
				<div class="row mb-4">
					<div class="col-md-12">
						<div class="text-right m-b-10">
							<a type="button" href="<?php echo site_url('bank') ?>" class=" btn bg-gradient-warning waves-effect waves-light mr-1"><span><i class="fa fa-arrow-left mr-1"></i></span>ย้อนกลับ</a>
							<button  id="btn_create" type="submit" class=" btn bg-gradient-success waves-effect waves-light"><span><i class="fa fa-save mr-1"></i></span>บันทึก</button>
						</div>
					</div>
				</div>
            </form>
          </div>
        </div>
      </section>
    </div>
  </div>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/scripts/bank/bank_create.js?'.time()) ?>"></script>
