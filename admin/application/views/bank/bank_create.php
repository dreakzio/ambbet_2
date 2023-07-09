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
                      <option value="11">ธนาคารเกียรตินาคิน จำกัด (มหาชน)</option>
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
							<label class="control-label">ประเภท API</label>
							<select class="form-control" name="api_type" id="api_type">
								<option value="1" selected>ดึงข้อมูลจาก App ธนาคาร</option>
								<option value="2">ดึงข้อมูลจาก Internet Banking</option>
								<option value="3">ดึงข้อมูลจาก Line lift</option>
								<option value="4">ดึงข้อมูลจาก SMS</option>
							</select>
						</div>
					</div>

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

					<div class="col-md-12">
						<div class="form-group">
							<hr>
							<span class="card-title">ข้อมูลการตั้งค่า Auto & อื่นๆ</span>
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
							<label class="control-label">เวลาปิดระบบฝากออโต้ <font style="color:red;">(จาก) รูปแบบตัวอย่าง "00:15"</font></label>
							<input type="text" id="start_time_can_not_deposit" name="start_time_can_not_deposit" class="form-control" placeholder="เวลาปิดระบบฝากออโต้ (จาก)">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">เวลาปิดระบบฝากออโต้ <font style="color:red;">(ถึง) รูปแบบตัวอย่าง "02:15"</font></label>
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
							<label class="control-label">ประเภทบัญชี</label>
							<select class="form-control" name="status_withdraw" id="status_withdraw">
								<option value="0">ฝาก</option>
								<option value="1">ถอน</option>
							</select>
						</div>
					</div>
					<!-- <div class="col-md-4"></div> -->
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">จำนวนเงินถอนออโต้ได้ไม่เกิน (บาท/ครั้ง)</label>
							<input type="number" id="max_amount_withdraw_auto" name="max_amount_withdraw_auto" class="form-control" placeholder="จำนวนเงินถอนออโต้ได้ไม่เกิน (บาท/ครั้ง)">
						</div>
					</div>
					<div class="col-md-8">
						<div class="form-group">
							<label class="control-label">ข้อความแสดงหน้าฝากเงิน <font style="color:red;">(เวลาปิดระบบฝากออโต้)</font></label>
							<textarea type="text" rows="6" id="message_can_not_deposit" name="message_can_not_deposit" class="form-control" placeholder="ข้อความแสดงหน้าฝากเงิน (เวลาปิดระบบฝากออโต้)" cols="3"></textarea>
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
				<div id="container_check_regis" class="col-12" style="display: none">
					<h3 class="card-title header-form-check-regis mt-2">เปิดใช้บัญชีตรวจสอบการสมัคร</h3>
					<div class="form-body form-check-regis">
						<hr>
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label class="control-label">เปิดใช้งานบัญชีนี้ในการตรวจสอบการสมัคร</label>
									<select class="form-control" name="check_regis" id="check_regis">
										<option value="0" selected>ปิด</option>
										<option value="1">เปิด</option>
									</select>
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

				<h3 class="card-title mt-2 header-form-api-scb" style="display: none">Connect SCB APP[option:ต้องต่อกับเบอร์มือถือที่ลงทะเบียน]</h3>

				<div class="form-body form-api-scb" style="display: none">
					<hr>
					<div class="row ">
						<div class="col-md-12">
							<div class="mt-4 text-center">
								<ul class="list-group" id="deviceIdData" style="display: none;width:540px;margin: 0 auto;" >
									<li class="list-group-item border-0 bg-gradient-primary text-white">
										<span class="font-weight-bold">ข้อมูล Device ID</span>
									</li>
								</ul>

								<a href="#" class="getDiviceID btn bg-gradient-warning waves-effect waves-light mr-1">
									<i class="fa fa-save mr-1"></i>
									<span class="text-silver" > เชื่อมต่อ SCB APP  </span>
								</a>
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
<div class="modal fade" id="modal_getDeviceId" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">เชื่อมต่อ SCB APP</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

				<div class="row mt-2">
					<div class="col-12">
						<ul class="list-group" id="others_credit">
							<li class="list-group-item border-0 bg-gradient-primary text-white">
								<span class="font-weight-bold">ข้อมูลบัญชี</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">เลือกประเทศ</span> :
								<span class="pull-right text-warning" style="width: 60%">
									<select class="form-control" id="cardType" name="cardType" required="">
									<option value="" disabled="" selected="">เลือกประเทศ</option>
									<option value="TH"> ไทย </option>
									<option value="MM"> พม่า </option>
									<option value="LA"> ลาว </option>
									<option value="KH"> กัมพูชา </option>
									<option value="VN"> เวียดนาม </option>
								</select>
								</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">เลขบัตรปรชาชน</span> :
								<span class="pull-right text-warning" style="width: 60%">
									<input type="text" class="form-control" id="cardId" placeholder="1460500228031" maxlength="13" minlength="13" required>
								</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">ปี-เดือน-วัน เกิด (ค.ศ.)</span> :
								<span class="pull-right text-warning" style="width: 60%">
									<input type="text" class="form-control" name="dateOfBirth" id="dateOfBirth" placeholder="1995-01-14" required>
								</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">เบอร์โทรศัพท์</span> :
								<span class="pull-right text-warning" style="width: 60%">
									<input name="MobilePhoneNo" type="text" class="form-control" id="MobilePhoneNo" value="" placeholder="08xxxxxxxxx" required>
								</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn bg-gradient-primary" id="getOtpdata" data-dismiss="modal">ตกลง</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="OtpModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Confirm OTP</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

				<div class="row mt-2">
					<div class="col-12">
						<ul class="list-group" id="others_credit">
							<li class="list-group-item border-0 bg-gradient-primary text-white">
								<span class="font-weight-bold">ข้อมูล OTP</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">เบอร์โทร</span> :
								<span class="pull-right text-warning" style="width: 60%">
									<input type="text" class="form-control" id="refphone" name="refphone" placeholder="0999999999" disabled>
								</select>
								</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">Ref</span> :
								<span class="pull-right text-warning" style="width: 60%">
									<input type="text" class="form-control" name="Ref" id="Ref" placeholder="5qref" disabled>
								</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">Otp</span> :
								<span class="pull-right text-warning" style="width: 60%">
									<input type="text" class="form-control" id="Otp" placeholder=""  maxlength="6" minlength="6" required>
								</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">Pin</span> :
								<span class="pull-right text-warning" style="width: 60%">
									<input type="text" class="form-control" name="pin" id="pin" placeholder="" maxlength="6" minlength="6" required>
								</span>
							</li>
							<li class="list-group-item ">
								<span class="font-weight-bold" style="width: 40%">msisdn <a href="http://info-msisdn.scb.co.th:8080/msisdn?date=<?php echo time(); ?>" target="_blank">link</a></span> :
								<span class="pull-right text-warning" style="width: 60%">
									<input name="TagID" type="text" class="form-control" id="TagID" value="" placeholder="EFEB84D78B71C865516B8964F48B6429EC3F2E577585323B82E146531A7DE4203" required>
								</span>
							</li>
						</ul>
						<input type="hidden" class="form-control mt-2" id="tokenUUID" disabled>
						<input type="hidden" class="form-control mt-2" id="Auth" disabled>
						<input type="hidden" class="form-control mt-2" id="deviceId" disabled>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" id="confirm_otp" >ยืนยัน</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/bank/bank_create.js?'.time()) ?>"></script>
