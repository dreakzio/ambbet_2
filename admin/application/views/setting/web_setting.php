<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<script src="<?php echo base_url('assets/plugins/jscolor-2.3.3/jscolor.js') ?>"></script>
<script>
	/*jscolor.presets.default = {
		format:'rgb'
	};*/
</script>
<div class="content-wrapper">
	<div class="content-header row">
		<div class="content-header-left col-md-9 col-12 mb-2">
			<div class="row breadcrumbs-top">
				<div class="col-12">
					<h2 class="content-header-title float-left mb-0">ตั้งค่าระบบทั่วไป</h2>
				</div>
			</div>
		</div>
	</div>
		<form class="form" id="form_create" method="POST" action="<?php echo site_url("setting/web_setting_update") ?>" enctype="multipart/form-data">
		<div class="content-body">
			<section class="card">
				<div class="card-content">
					<div class="card-body">
						<h3 class="card-title">ข้อมูลระบบทั่วไป</h3>
						<hr>
						<div class="form-body mt-3">
							<div class="row ">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">ชื่อเว็ป</label>
										<?php $web_name = $this->Setting_model->setting_find([
												'name' => 'web_name'
										]); ?>
										<input type="text" id="line" name="web_setting[web_name]" class="form-control" value="<?php echo $web_name['value'] ?>" placeholder="ชื่อเว็ป">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">Line ID</label>
										<?php $line_id = $this->Setting_model->setting_find([
												'name' => 'line_id'
										]); ?>
										<input type="text" id="line" name="web_setting[line_id]" class="form-control" value="<?php echo $line_id['value'] ?>" placeholder="ข้อมูล Line ID">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">Line Url</label>
										<?php $line_url = $this->Setting_model->setting_find([
												'name' => 'line_url'
										]); ?>
										<input type="text" id="line_url" name="web_setting[line_url]" class="form-control" value="<?php echo $line_url['value'] ?>" placeholder="ข้อมูล Line Url">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">เบอร์โทรศัพท์</label>
										<?php $phone = $this->Setting_model->setting_find([
												'name' => 'telephone_number'
										]); ?>
										<input type="text" id="line_url" name="web_setting[telephone_number]" class="form-control" value="<?php echo $phone['value'] ?>" placeholder="ข้อมูลเบอร์โทรศัพท์">
									</div>
								</div>
							</div>
							<div class="row ">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">Web Title</label>
										<?php $web_title = $this->Setting_model->setting_find([
												'name' => 'web_title'
										]); ?>
										<input type="text" id="title" name="web_setting[web_title]" class="form-control" value="<?php echo $web_title['value'] ?>" placeholder="ข้อมูล Web Title" >
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?php $web_description = $this->Setting_model->setting_find([
												'name' => 'web_description'
										]); ?>
										<label class="control-label">Web description</label>
										<input type="text" id="web_description" name="web_setting[web_description]" class="form-control" value="<?php echo $web_description['value'] ?>" placeholder="ข้อมูล Web description">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?php $web_keyword = $this->Setting_model->setting_find([
												'name' => 'web_keyword'
										]); ?>
										<label class="control-label">Web keyword</label>
										<input type="text" id="web_keyword" name="web_setting[web_keyword]" class="form-control" value="<?php echo $web_keyword['value'] ?>" placeholder="ข้อมูล Web keyword">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?php $clear_turn = $this->Setting_model->setting_find([
												'name' => 'clear_turn'
										]); ?>
										<label class="control-label">Clear Turn</label>
										<input type="text" oninput="validateInputNumber(this)" id="clear_turn" name="web_setting[clear_turn]" class="form-control" value="<?php echo $clear_turn['value'] ?>" placeholder="ข้อมูล Web description">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?php $turn_type = $this->Setting_model->setting_find([
												'name' => 'turn_type'
										]); ?>
										<label class="control-label">ประเภทการคิด Turn</label>
										<select class="form-control" name="web_setting[turn_type]" id="turn_type">
											<option value="1">จาก Turn ที่มีการเล่น</option>
											<option value="2" <?php if ($turn_type['value']==2): ?>
												selected
											<?php endif; ?>>จาก Turn เครดิต</option>
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?php $withdraw_min_amount = $this->Setting_model->setting_find([
												'name' => 'withdraw_min_amount'
										]); ?>
										<label class="control-label">ยอดถอนเงินขั้นต่ำ (บาท)</label>
										<input type="text" oninput="validateInputNumber(this)" id="withdraw_min_amount" name="web_setting[withdraw_min_amount]" class="form-control" value="<?php echo $withdraw_min_amount['value'] === "" || is_null($withdraw_min_amount['value']) ? 0 : $withdraw_min_amount['value'] ?>" placeholder="กรอกเฉพาะตัวเลขเท่านั้น">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?php $deposit_min_amount_for_disable_auto = $this->Setting_model->setting_find([
												'name' => 'deposit_min_amount_for_disable_auto'
										]); ?>
										<label class="control-label">ยอดฝากเงินขั้นต่ำ (บาท) ที่ปิดไม่ให้บอททำงานเติม Auto</label>
										<input type="text" oninput="validateInputNumber(this)" id="deposit_min_amount_for_disable_auto" name="web_setting[deposit_min_amount_for_disable_auto]" class="form-control" value="<?php echo $deposit_min_amount_for_disable_auto['value'] === "" || is_null($deposit_min_amount_for_disable_auto['value']) ? 0 : $deposit_min_amount_for_disable_auto['value'] ?>" placeholder="กรอกเฉพาะตัวเลขเท่านั้น">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">สถานะยืนยัน OTP (หน้าสมัครสมาชิก)</label>
										<?php $register_verify_otp_status = $this->Setting_model->setting_find([
												'name' => 'register_verify_otp_status'
										]); ?>
										<select class="form-control" name="web_setting[register_verify_otp_status]" id="register_verify_otp_status">
											<option value="0">ปิดใช้งาน</option>
											<option value="1" <?php if ($register_verify_otp_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิดใช้งาน</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">สถานะ Line notify</label>
										<?php $line_notify_status = $this->Setting_model->setting_find([
												'name' => 'line_notify_status'
										]); ?>
										<select class="form-control" name="web_setting[line_notify_status]" id="line_notify_status">
											<option value="0">ปิดใช้งาน</option>
											<option value="1" <?php if ($line_notify_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิดใช้งาน</option>
										</select>
									</div>
								</div>
								<div class="col-md-8">
									<div class="form-group <?php echo $line_notify_status['value'] == "0" || empty($line_notify_status['value']) ? 'd-none' : '' ?>">
										<label class="control-label">Line notify token</label>
										<?php $line_notify_token = $this->Setting_model->setting_find([
												'name' => 'line_notify_token'
										]); ?>
										<textarea type="text" rows="2" id="line_notify_token" name="web_setting[line_notify_token]" class="form-control"  placeholder="ข้อมูล Line notify token"><?php echo $line_notify_token['value'] ?></textarea>
									</div>
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">สถานะ Line notify Log API</label>
										<?php $line_notify_log_api_status = $this->Setting_model->setting_find([
												'name' => 'line_notify_log_api_status'
										]); ?>
										<select class="form-control" name="web_setting[line_notify_log_api_status]" id="line_notify_log_api_status">
											<option value="0">ปิดใช้งาน</option>
											<option value="1" <?php if ($line_notify_log_api_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิดใช้งาน</option>
										</select>
									</div>
								</div>
								<div class="col-md-8">
									<div class="form-group <?php echo $line_notify_log_api_status['value'] == "0" || empty($line_notify_log_api_status['value']) ? 'd-none' : '' ?>">
										<label class="control-label">Line notify token Log API</label>
										<?php $line_notify_log_api_token = $this->Setting_model->setting_find([
												'name' => 'line_notify_log_api_token'
										]); ?>
										<textarea type="text" rows="2" id="line_notify_log_api_token" name="web_setting[line_notify_log_api_token]" class="form-control"  placeholder="ข้อมูล Line notify Log API token"><?php echo $line_notify_log_api_token['value'] ?></textarea>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">สถานะ Line Login</label>
										<?php $line_login_status = $this->Setting_model->setting_find([
												'name' => 'line_login_status'
										]); ?>
										<select class="form-control" name="web_setting[line_login_status]" id="line_login_status">
											<option value="0">ปิดใช้งาน</option>
											<option value="1" <?php if ($line_login_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิดใช้งาน</option>
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group <?php echo $line_login_status['value'] == "0" || empty($line_login_status['value']) ? 'd-none' : '' ?>">
										<label class="control-label">Line Login Client ID</label>
										<?php $line_login_client_id = $this->Setting_model->setting_find([
												'name' => 'line_login_client_id'
										]); ?>
										<textarea type="text" rows="2" id="line_login_client_id" name="web_setting[line_login_client_id]" class="form-control"  placeholder="ข้อมูล Line Login Client ID"><?php echo $line_login_client_id['value'] ?></textarea>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group <?php echo $line_login_status['value'] == "0" || empty($line_login_status['value']) ? 'd-none' : '' ?>">
										<label class="control-label">Line Login Client Secret</label>
										<?php $line_login_client_secret = $this->Setting_model->setting_find([
												'name' => 'line_login_client_secret'
										]); ?>
										<textarea type="text" rows="2" id="line_login_client_secret" name="web_setting[line_login_client_secret]" class="form-control"  placeholder="ข้อมูล Line Login Client Secret"><?php echo $line_login_client_secret['value'] ?></textarea>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group <?php echo $line_login_status['value'] == "0" || empty($line_login_status['value']) ? 'd-none' : '' ?>">
										<label class="control-label">Line Login Callback URL</label>
										<?php $line_login_callback = $this->Setting_model->setting_find([
												'name' => 'line_login_callback'
										]); ?>
										<textarea type="text" rows="2" id="line_login_callback" name="web_setting[line_login_callback]" class="form-control"  placeholder="ข้อมูล Line Login Callback URL"><?php echo $line_login_callback['value'] ?></textarea>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">สถานะ Google 2FA</label>
										<?php $gg_2fa_status = $this->Setting_model->setting_find([
												'name' => 'gg_2fa_status'
										]); ?>
										<select class="form-control" name="web_setting[gg_2fa_status]" id="gg_2fa_status">
											<option value="0">ปิดใช้งาน</option>
											<option value="1" <?php if ($gg_2fa_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิดใช้งาน</option>
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group <?php echo $gg_2fa_status['value'] == "0" || empty($gg_2fa_status['value']) ? 'd-none' : '' ?>">
										<label class="control-label">Google 2FA Secret Code</label>
										<?php
										$gg_2fa_secret = $this->Setting_model->setting_find([
												'name' => 'gg_2fa_secret'
										]);
											try{
												$gg2fa_secret = decrypt(base64_decode($gg_2fa_secret['value']),$this->config->item('secret_key_salt'));
												if($gg2fa_secret === FALSE){
													$this->db->update_batch('web_setting', [[
															'name' => 'gg_2fa_secret',
															'value' => base64_encode(encrypt($gg_2fa_secret['value'],$this->config->item('secret_key_salt')))
													]], 'name');
												}
											}catch (Exception $ex){
												$this->db->update_batch('web_setting', [[
														'name' => 'gg_2fa_secret',
														'value' => base64_encode(encrypt($gg_2fa_secret['value'],$this->config->item('secret_key_salt')))
												]], 'name');
												$gg2fa_secret = $gg_2fa_secret['value'];
											}
										?>
										<div class="mx-auto text-center mt-2 mb-2">
											<img id="img_2fa_qrcode"  src="<?php echo $this->google_authenticator_librarie->getQRCodeGoogleUrl($web_name['value'], $gg2fa_secret); ?>" class="img-fluid" alt="">
										</div>
										<div class="input-group">
											<textarea type="text" rows="1" id="gg_2fa_secret" name="web_setting[gg_2fa_secret]" class="form-control" disabled  placeholder="ข้อมูล Google 2FA Secret Code"><?php echo $gg2fa_secret; ?></textarea>
											<div class="input-group-append">
												<input type="hidden" name="web_setting[gg_2fa_gen]" id="gg_2fa_gen" value="N"/>
												<button type="button" id="btn_copy_gg_2fa_secret" class=" btn bg-gradient-warning waves-effect waves-light"><i class="fa fa-clipboard"></i>&nbsp;คัดลอก</button>
												<button type="button" id="btn_gen_gg_2fa_secret" class=" btn bg-gradient-success waves-effect waves-light"><i class="fa fa-refresh"></i>&nbsp;สร้างใหม่</button>
											</div>
										</div>

									</div>
								</div>
							</div>
							<div class="row" style="display:none;">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">SMS API Username</label>
										<?php $sms_api_username = $this->Setting_model->setting_find([
												'name' => 'sms_api_username'
										]); ?>
										<input type="text" id="sms_api_username" name="web_setting[sms_api_username]" class="form-control" value="<?php echo $sms_api_username['value'] ?>" placeholder="ข้อมูล SMS API Username">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">SMS API Password</label>
										<?php $sms_api_password = $this->Setting_model->setting_find([
												'name' => 'sms_api_password'
										]); ?>
										<input type="text" id="sms_api_password" name="web_setting[sms_api_password]" class="form-control" value="<?php echo $sms_api_password['value'] ?>" placeholder="ข้อมูล SMS API Password">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>


		<!-- start of finance
		<div class="content-body">
			<section class="card">
				<div class="card-content">
					<div class="card-body">
						<h3 class="card-title">ข้อมูลการเงิน</h3>
						<hr>
						<div class="form-body mt-3">
							<div class="row ">
								<div class="col-md-4">
										<?php $withdraw_auto_status = $this->Setting_model->setting_find([
														'name' => 'withdraw_auto_status'
										  ]); ?>
											<div class="form-group">
												<label class="control-label">เปิดการถอนออโต้</label>
												<select class="form-control" name="web_setting[withdraw_auto_status]" id="withdraw_auto_status">
													<option value="0">ปิด (ใช้การตรวจสอบ)</option>
													<option value="1" <?php if ($withdraw_auto_status['value']==1): ?>
														selected
													<?php endif; ?>>เปิดการถอนออโต้</option>
												</select>
											</div>
								 </div>
								<div class="col-md-4">
									<div class="form-group">
										<?php $withdraw_min_amount = $this->Setting_model->setting_find([
												'name' => 'withdraw_min_amount'
										]); ?>
										<label class="control-label">ยอดถอนเงินขั้นต่ำ (บาท)</label>
										<input type="text" oninput="validateInputNumber(this)" id="withdraw_min_amount" name="web_setting[withdraw_min_amount]" class="form-control" value="<?php echo $withdraw_min_amount['value'] === "" || is_null($withdraw_min_amount['value']) ? 0 : $withdraw_min_amount['value'] ?>" placeholder="กรอกเฉพาะตัวเลขเท่านั้น">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?php $deposit_min_amount_for_disable_auto = $this->Setting_model->setting_find([
												'name' => 'deposit_min_amount_for_disable_auto'
										]); ?>
										<label class="control-label">ยอดฝากเงินขั้นต่ำ (บาท) ที่ปิดไม่ให้บอททำงานเติม Auto</label>
										<input type="text" oninput="validateInputNumber(this)" id="deposit_min_amount_for_disable_auto" name="web_setting[deposit_min_amount_for_disable_auto]" class="form-control" value="<?php echo $deposit_min_amount_for_disable_auto['value'] === "" || is_null($deposit_min_amount_for_disable_auto['value']) ? 0 : $deposit_min_amount_for_disable_auto['value'] ?>" placeholder="กรอกเฉพาะตัวเลขเท่านั้น">
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</section>
		</div>
	-->



		<!-- end of finance -->

		<div class="content-body">
			<section class="card">
				<div class="card-content">
					<div class="card-body">
						<h3 class="card-title">ตั้งค่า "เปิด/ปิด" ห้องเดิมพัน</h3>
						<hr>
						<div class="form-body mt-3">
							<div class="row ">
								<div class="col-md-3">
									<div class="form-group">
										<label class="control-label">สล็อต</label>
										<?php $slot_status = $this->Setting_model->setting_find([
												'name' => 'slot_status'
										]); ?>
										<select class="form-control" name="web_setting[slot_status]" id="slot_status">
											<option value="0">ปิด</option>
											<option value="1" <?php if ($slot_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิด</option>
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="control-label">คาสิโน</label>
										<?php $casino_status = $this->Setting_model->setting_find([
												'name' => 'casino_status'
										]); ?>
										<select class="form-control" name="web_setting[casino_status]" id="casino_status">
											<option value="0">ปิด</option>
											<option value="1" <?php if ($casino_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิด</option>
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="control-label">ฟุตบอล</label>
										<?php $football_status = $this->Setting_model->setting_find([
												'name' => 'football_status'
										]); ?>
										<select class="form-control" name="web_setting[football_status]" id="football_status">
											<option value="0">ปิด</option>
											<option value="1" <?php if ($football_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิด</option>
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="control-label">หวย</label>
										<?php $lotto_status = $this->Setting_model->setting_find([
												'name' => 'lotto_status'
										]); ?>
										<select class="form-control" name="web_setting[lotto_status]" id="lotto_status">
											<option value="0">ปิด</option>
											<option value="1" <?php if ($lotto_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิด</option>
										</select>
									</div>
								</div>
								<div class="col-md-4"></div>
							</div>
						</div>

					</div>
				</div>
			</section>
		</div>

		<div class="content-body">
			<section class="card">
				<div class="card-content">
					<div class="card-body">
						<h3 class="card-title">ข้อมูลออโต้สร้างยูสเซอร์</h3>
						<hr>
						<div class="form-body mt-3">
							<div class="row ">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">รูปแบบ</label>
										<?php $auto_create_member = $this->Setting_model->setting_find([
												'name' => 'auto_create_member'
										]); ?>
										<select class="form-control" name="web_setting[auto_create_member]" id="auto_create_member">
											<option value="0">หลังฝากเงินเข้าระบบครั้งแรก</option>
											<option value="1" <?php if ($auto_create_member['value']==1): ?>
												selected
											<?php endif; ?>>หลังสมัครสมาชิกเสร็จ</option>
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group <?php echo $auto_create_member['value'] == "0" || empty($auto_create_member['value']) ? '' : 'd-none' ?>">
										<label class="control-label">จำนวนเงินฝากครั้งแรก</label>
										<?php $auto_create_member_deposit_amount = $this->Setting_model->setting_find([
												'name' => 'auto_create_member_deposit_amount'
										]); ?>
										<input type="text" id="auto_create_member_deposit_amount" oninput="validateInputNumber(this)"  name="web_setting[auto_create_member_deposit_amount]" class="form-control" value="<?php echo $auto_create_member_deposit_amount['value'] ?>"  placeholder="กรอกเฉพาะตัวเลขเท่านั้น">
									</div>
								</div>
								<div class="col-md-4"></div>
								<!-- START input จำกัดการถอน ครั้ง/คน/วัน  -->
								<!-- <div class="col-md-4">
									<div class="form-group">
										<label class="control-label">จำกัดการถอน ครั้ง/คน/วัน <span class="badge badge-warning">WARNING!!</span></label>
										<?php $limit_withdraw_per_day = $this->Setting_model->setting_find([
												'name' => 'limit_withdraw_per_day'
										]); ?>
										<input type="text" id="limit_withdraw_per_day" oninput="validateInputNumber(this)"  name="web_setting[limit_withdraw_per_day]" class="form-control" value="<?php echo $limit_withdraw_per_day['value'] ?>"  placeholder="กรอกเฉพาะตัวเลขเท่านั้น">
									</div>
								</div> -->
								<!-- END input จำกัดการถอน ครั้ง/คน/วัน  -->

								<!-- START input จำกัดจำนวนเงินที่ถอนได้ สูงสุง/วัน/คน  -->
								<!-- <div class="col-md-4">
									<div class="form-group">
										<label class="control-label">จำกัดจำนวนเงินที่ถอนได้ สูงสุง/วัน/คน <span class="badge badge-warning">WARNING!!</span></label>
										<?php $limit_max_withdraw_per_day = $this->Setting_model->setting_find([
												'name' => 'limit_max_withdraw_per_day'
										]); ?>
										<input type="text" id="limit_max_withdraw_per_day" oninput="validateInputNumber(this)"  name="web_setting[limit_max_withdraw_per_day]" class="form-control" value="<?php echo $limit_max_withdraw_per_day['value'] ?>"  placeholder="กรอกเฉพาะตัวเลขเท่านั้น">
									</div>
								</div> -->
								<!-- END input จำกัดจำนวนเงินที่ถอนได้ สูงสุง/วัน/คน  -->
							</div>
						</div>

					</div>
				</div>
			</section>
		</div>
		<?php
		$feature_bonus_aff_turnover_and_winlose = $this->Feature_status_model->setting_find([
				'name' => 'bonus_aff_turnover_and_winlose'
		]);
		$feature_bonus_aff_turnover_and_winlose_step2 = $this->Feature_status_model->setting_find([
				'name' => 'bonus_aff_turnover_and_winlose_step2'
		]);
		?>
		<?php if($feature_bonus_aff_turnover_and_winlose!="" && $feature_bonus_aff_turnover_and_winlose['value'] == "1"): ?>
			<div class="content-body">
				<section class="card">
					<div class="card-content">
						<div class="card-body">
							<?php $ref_bonus_type = $this->Setting_model->setting_find([
									'name' => 'ref_bonus_type'
							]); ?>
							<h3 class="card-title">ข้อมูลการให้โบนัสแนะนำเพื่อน</h3>
							<hr>
							<div class="form-body mt-3">
								<div class="row ">
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">ประเภทการให้โบนัส</label>
											<select class="form-control" name="web_setting[ref_bonus_type]" id="ref_bonus_type">
												<option value="0">ยอดเล่นเทิร์นโอเวอร์</option>
												<option value="1" <?php if ($ref_bonus_type['value']==1): ?>
													selected
												<?php endif; ?>>ยอดเล่นเสีย</option>
											</select>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">Percent จากยอดเล่น</label>
											<?php $ref_percent = $this->Setting_model->setting_find([
													'name' => 'ref_percent'
											]); ?>
											<input type="text" id="ref_percent" name="web_setting[ref_percent]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_percent['value'] ?>" placeholder="ข้อมูล Percent">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">Turn ที่ต้องคูณ</label>
											<?php $ref_turn = $this->Setting_model->setting_find([
													'name' => 'ref_turn'
											]); ?>
											<input type="text" id="ref_turn" name="web_setting[ref_turn]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_turn['value'] ?>"  placeholder="ข้อมูล Turn">
										</div>
									</div>
									<?php if($feature_bonus_aff_turnover_and_winlose_step2!="" && $feature_bonus_aff_turnover_and_winlose_step2['value'] == "1"): ?>
										<div class="col-12">
											<hr>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label class="control-label">สถานะโบนัสขั้น 2</label>
												<?php $ref_step2_status = $this->Setting_model->setting_find([
														'name' => 'ref_step2_status'
												]); ?>
												<select class="form-control" name="web_setting[ref_step2_status]" id="ref_step2_status">
													<option value="0">ปิดใช้งาน</option>
													<option value="1" <?php if ($ref_step2_status['value']==1): ?>
														selected
													<?php endif; ?>>เปิดใช้งาน</option>
												</select>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group <?php echo $ref_step2_status['value'] == "0" || empty($ref_step2_status['value']) ? 'd-none' : '' ?>">
												<label class="control-label">Percent จากยอดเล่น</label>
												<?php $ref_step2_percent = $this->Setting_model->setting_find([
														'name' => 'ref_step2_percent'
												]); ?>
												<input type="text" id="ref_step2_percent" name="web_setting[ref_step2_percent]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_step2_percent['value'] ?>" placeholder="ข้อมูล Percent">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group <?php echo $ref_step2_status['value'] == "0" || empty($ref_step2_status['value']) ? 'd-none' : '' ?>">
												<label class="control-label">Turn ที่ต้องคูณ</label>
												<?php $ref_step2_turn = $this->Setting_model->setting_find([
														'name' => 'ref_step2_turn'
												]); ?>
												<input type="text" id="ref_step2_turn" name="web_setting[ref_step2_turn]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_step2_turn['value'] ?>"  placeholder="ข้อมูล Turn">
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>
		<?php endif; ?>
		<?php
		$feature_bonus_return_balance_winlose = $this->Feature_status_model->setting_find([
				'name' => 'bonus_return_balance_winlose'
		]);
		?>
		<?php if($feature_bonus_return_balance_winlose!="" && $feature_bonus_return_balance_winlose['value'] == "1"): ?>
			<div class="content-body">
				<section class="card">
					<div class="card-content">
						<div class="card-body">
							<?php $ref_bonus_type = $this->Setting_model->setting_find([
									'name' => 'ref_bonus_type'
							]); ?>
							<h3 class="card-title">ข้อมูลการให้โบนัสคืนยอดเสียให้ตัวเอง</h3>
							<hr>
							<div class="form-body mt-3">
								<div class="row ">
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">เปิด/ปิด ระบบรับโบนัส auto</label>
											<?php $deposit_with_bonus_auto = $this->Setting_model->setting_find([
													'name' => 'deposit_with_bonus_auto'
											]); ?>
											<select class="form-control" name="web_setting[deposit_with_bonus_auto]" id="deposit_with_bonus_auto">
												<option value="0">ปิดใช้งาน</option>
												<option value="1" <?php if ($deposit_with_bonus_auto['value']==1): ?>
													selected
												<?php endif; ?>>เปิดใช้งาน</option>
											</select>
										</div>
									</div>

									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">สถานะ</label>
											<?php $ref_return_balance_status = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_status'
											]); ?>
											<select class="form-control" name="web_setting[ref_return_balance_status]" id="ref_return_balance_status">
												<option value="0">ปิดใช้งาน</option>
												<option value="1" <?php if ($ref_return_balance_status['value']==1): ?>
													selected
												<?php endif; ?>>เปิดใช้งาน</option>
											</select>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4">
										<div class="form-group <?php echo $ref_return_balance_status['value'] == "0" || empty($ref_return_balance_status['value']) ? 'd-none' : '' ?>">
											<label class="control-label">Percent จากยอดเล่นเสีย (ค่าเริ่มต้น)</label>
											<?php $ref_return_balance_percent = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_percent'
											]); ?>
											<input type="text" id="ref_return_balance_percent" name="web_setting[ref_return_balance_percent]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_percent['value'] ?>" placeholder="ข้อมูล Percent">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group <?php echo $ref_return_balance_status['value'] == "0" || empty($ref_return_balance_status['value']) ? 'd-none' : '' ?>">
											<label class="control-label">Turn ที่ต้องคูณ (ค่าเริ่มต้น)</label>
											<?php $ref_return_balance_turn = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_turn'
											]); ?>
											<input type="text" id="ref_return_balance_turn" name="web_setting[ref_return_balance_turn]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_turn['value'] ?>"  placeholder="ข้อมูล Turn">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group <?php echo $ref_return_balance_status['value'] == "0" || empty($ref_return_balance_status['value']) ? 'd-none' : '' ?>">
											<label class="control-label">คืนยอดเสียสูงสุดได้ไม่เกิน (เครดิต) (ค่าเริ่มต้น)</label>
											<?php $ref_return_balance_max = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_max'
											]); ?>
											<input type="text" id="ref_return_balance_max" name="web_setting[ref_return_balance_max]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_max['value'] ?>"  placeholder="ข้อมูล คืนยอดเสียสูงสุดได้ไม่เกิน (เครดิต)">
										</div>
									</div>
								</div>
								<hr>
								<div class="row <?php echo $ref_return_balance_status['value'] == "0" || empty($ref_return_balance_status['value']) ? 'd-none' : '' ?>" id="ref_return_balance_rank1_container">

									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">ชื่อ</label>
											<input type="text" id="ref_return_balance_rank1" readonly disabled class="form-control" value="Rank Member">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">Percent จากยอดเล่นเสีย</label>
											<?php $ref_return_balance_rank1_percent = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank1_percent'
											]); ?>
											<input type="text" id="ref_return_balance_rank1_percent" name="web_setting[ref_return_balance_rank1_percent]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank1_percent['value'] ?>" placeholder="ข้อมูล Percent">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">Turn ที่ต้องคูณ</label>
											<?php $ref_return_balance_rank1_turn = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank1_turn'
											]); ?>
											<input type="text" id="ref_return_balance_rank1_turn" name="web_setting[ref_return_balance_rank1_turn]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank1_turn['value'] ?>"  placeholder="ข้อมูล Turn">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">จำนวนยอดฝากรวมขั้นต่ำที่จะผ่านการขึ้น Rank (บาท)</label>
											<?php $ref_return_balance_rank1_deposit_min = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank1_deposit_min'
											]); ?>
											<input type="text" id="ref_return_balance_rank1_deposit_min" name="web_setting[ref_return_balance_rank1_deposit_min]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank1_deposit_min['value'] ?>"  placeholder="ข้อมูล จำนวนยอดฝากรวมขั้นต่ำที่จะผ่านการขึ้น Rank (บาท)">
											<label><strong class="text-danger">*ต้องทำ Turn ให้ครบตามจำนวนยอดฝาก+โบนัสของวันนั้นๆ</strong></label>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">คืนยอดเสียสูงสุดได้ไม่เกิน (เครดิต)</label>
											<?php $ref_return_balance_rank1_max = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank1_max'
											]); ?>
											<input type="text" id="ref_return_balance_rank1_max" name="web_setting[ref_return_balance_rank1_max]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank1_max['value'] ?>"  placeholder="ข้อมูล คืนยอดเสียสูงสุดได้ไม่เกิน (เครดิต)">
										</div>
									</div>
								</div>

								<div class="row <?php echo $ref_return_balance_status['value'] == "0" || empty($ref_return_balance_status['value']) ? 'd-none' : '' ?>" id="ref_return_balance_rank2_container">
									<hr>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">ชื่อ</label>
											<input type="text" id="ref_return_balance_rank2" readonly disabled class="form-control" value="Rank Silver">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">Percent จากยอดเล่นเสีย</label>
											<?php $ref_return_balance_rank2_percent = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank2_percent'
											]); ?>
											<input type="text" id="ref_return_balance_rank2_percent" name="web_setting[ref_return_balance_rank2_percent]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank2_percent['value'] ?>" placeholder="ข้อมูล Percent">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">Turn ที่ต้องคูณ</label>
											<?php $ref_return_balance_rank2_turn = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank2_turn'
											]); ?>
											<input type="text" id="ref_return_balance_rank2_turn" name="web_setting[ref_return_balance_rank2_turn]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank2_turn['value'] ?>"  placeholder="ข้อมูล Turn">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">จำนวนยอดฝากรวมขั้นต่ำที่จะผ่านการขึ้น Rank (บาท)</label>
											<?php $ref_return_balance_rank2_deposit_min = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank2_deposit_min'
											]); ?>
											<input type="text" id="ref_return_balance_rank2_deposit_min" name="web_setting[ref_return_balance_rank2_deposit_min]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank2_deposit_min['value'] ?>"  placeholder="ข้อมูล จำนวนยอดฝากรวมขั้นต่ำที่จะผ่านการขึ้น Rank (บาท)">
											<label><strong class="text-danger">*ต้องทำ Turn ให้ครบตามจำนวนยอดฝาก+โบนัสของวันนั้นๆ</strong></label>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">คืนยอดเสียสูงสุดได้ไม่เกิน (เครดิต)</label>
											<?php $ref_return_balance_rank2_max = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank2_max'
											]); ?>
											<input type="text" id="ref_return_balance_rank2_max" name="web_setting[ref_return_balance_rank2_max]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank2_max['value'] ?>"  placeholder="ข้อมูล คืนยอดเสียสูงสุดได้ไม่เกิน (เครดิต)">
										</div>
									</div>
								</div>

								<div class="row <?php echo $ref_return_balance_status['value'] == "0" || empty($ref_return_balance_status['value']) ? 'd-none' : '' ?>" id="ref_return_balance_rank3_container">
									<hr>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">ชื่อ</label>
											<input type="text" id="ref_return_balance_rank3" readonly disabled class="form-control" value="Rank Gold">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">Percent จากยอดเล่นเสีย</label>
											<?php $ref_return_balance_rank3_percent = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank3_percent'
											]); ?>
											<input type="text" id="ref_return_balance_rank3_percent" name="web_setting[ref_return_balance_rank3_percent]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank3_percent['value'] ?>" placeholder="ข้อมูล Percent">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">Turn ที่ต้องคูณ</label>
											<?php $ref_return_balance_rank3_turn = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank3_turn'
											]); ?>
											<input type="text" id="ref_return_balance_rank3_turn" name="web_setting[ref_return_balance_rank3_turn]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank3_turn['value'] ?>"  placeholder="ข้อมูล Turn">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">จำนวนยอดฝากรวมขั้นต่ำที่จะผ่านการขึ้น Rank (บาท)</label>
											<?php $ref_return_balance_rank3_deposit_min = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank3_deposit_min'
											]); ?>
											<input type="text" id="ref_return_balance_rank3_deposit_min" name="web_setting[ref_return_balance_rank3_deposit_min]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank3_deposit_min['value'] ?>"  placeholder="ข้อมูล จำนวนยอดฝากรวมขั้นต่ำที่จะผ่านการขึ้น Rank (บาท)">
											<label><strong class="text-danger">*ต้องทำ Turn ให้ครบตามจำนวนยอดฝาก+โบนัสของวันนั้นๆ</strong></label>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label">คืนยอดเสียสูงสุดได้ไม่เกิน (เครดิต)</label>
											<?php $ref_return_balance_rank3_max = $this->Setting_model->setting_find([
													'name' => 'ref_return_balance_rank3_max'
											]); ?>
											<input type="text" id="ref_return_balance_rank3_max" name="web_setting[ref_return_balance_rank3_max]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $ref_return_balance_rank3_max['value'] ?>"  placeholder="ข้อมูล คืนยอดเสียสูงสุดได้ไม่เกิน (เครดิต)">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>
		<?php endif; ?>
		<div class="content-body">
			<section class="card">
				<div class="card-content">
					<div class="card-body">
						<h3 class="card-title">ข้อมูลการให้โบนัสกิจกรรมเช็คอิน</h3>
						<hr>
						<div class="form-body mt-3">
							<div class="row ">
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label">สถานะ</label>
										<?php $login_status = $this->Setting_model->setting_find([
												'name' => 'login_status'
										]); ?>
										<select class="form-control" name="web_setting[login_status]" id="login_status">
											<option value="0">ปิดใช้งาน</option>
											<option value="1" <?php if ($login_status['value']==1): ?>
												selected
											<?php endif; ?>>เปิดใช้งาน</option>
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group <?php echo $login_status['value'] == "0" || empty($login_status['value']) ? 'd-none' : '' ?>">
										<label class="control-label">แต้มที่ได้จากการเข้าสู่ระบบ/วัน</label>
										<?php $login_point = $this->Setting_model->setting_find([
												'name' => 'login_point'
										]); ?>
										<input type="text" id="login_point" name="web_setting[login_point]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $login_point['value'] ?>"  placeholder="ข้อมูล แต้มที่ได้จากการเข้าสู่ระบบ/วัน">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group <?php echo $login_status['value'] == "0" || empty($login_status['value']) ? 'd-none' : '' ?>">
										<label class="control-label">Turn ที่ต้องคูณ</label>
										<?php $login_turn = $this->Setting_model->setting_find([
												'name' => 'login_turn'
										]); ?>
										<input type="text" id="login_turn" name="web_setting[login_turn]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $login_turn['value'] ?>"  placeholder="ข้อมูล Turn">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php
		$feature_wheel = $this->Feature_status_model->setting_find([
				'name' => 'wheel'
		]);
		?>
		<?php if($feature_wheel!="" && $feature_wheel['value'] == "1"): ?>
			<div class="content-body">
				<section class="card">
					<div class="card-content">
						<div class="card-body">
							<h3 class="card-title">ข้อมูลวงล้อ</h3>
							<hr>
							<div class="form-body mt-3">
								<div class="row ">
									<div class="col-md-6">
										<div class="form-group">
											<label class="control-label">จำนวนเงินฝาก (บาท/1 เหรียญ)</label>
											<?php $wheel_amount_per_point = $this->Setting_model->setting_find([
													'name' => 'wheel_amount_per_point'
											]); ?>
											<input type="text" id="wheel_amount_per_point" name="web_setting[wheel_amount_per_point]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $wheel_amount_per_point['value'] ?>" placeholder="ข้อมูล จำนวนเงินฝาก (บาท/1 เหรียญ)">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="control-label">จำนวนเหรียญที่ใช้ในการสุ่ม (เหรียญ/1 ครั้ง)</label>
											<?php $wheel_point_for_spin = $this->Setting_model->setting_find([
													'name' => 'wheel_point_for_spin'
											]); ?>
											<input type="text" id="wheel_point_for_spin" name="web_setting[wheel_point_for_spin]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $wheel_point_for_spin['value'] ?>" placeholder="ข้อมูล จำนวนเงินฝาก (บาท/1 เหรียญ)">
										</div>
									</div>
									<div class="col-12">
										<hr>
									</div>
									<?php for($i=1;$i<=10;$i++): ?>
										<!--<div class="col-md-2">
											<div class="form-group">
												<label class="control-label">สถานะ (<?php /*echo $i; */?>)</label>
												<?php /*$wheel_status = $this->Setting_model->setting_find([
														'name' => 'wheel_status_'.$i
												]); */?>
												<select class="form-control" name="web_setting[wheel_status_<?php /*echo $i; */?>]" id="wheel_status_<?php /*echo $i; */?>">
													<option value="0">ปิดใช้งาน</option>
													<option value="1" <?php /*if ($wheel_status['value']==1): */?>
														selected
													<?php /*endif; */?>>เปิดใช้งาน</option>
												</select>
											</div>
										</div>-->
										<div class="col-md-3">
											<div class="form-group">
												<label class="control-label">ชื่อ (<?php echo $i; ?>)</label>
												<?php $wheel_name = $this->Setting_model->setting_find([
														'name' => 'wheel_name_'.$i
												]); ?>
												<input type="text" id="wheel_name_<?php echo $i; ?>" name="web_setting[wheel_name_<?php echo $i; ?>]"  class="form-control" value="<?php echo $wheel_name['value'] ?>" placeholder="ข้อมูล ชื่อ">
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label class="control-label">% การสุ่มได้ (<?php echo $i; ?>)</label>
												<?php $wheel_percent = $this->Setting_model->setting_find([
														'name' => 'wheel_percent_'.$i
												]); ?>
												<input type="text" id="wheel_percent_<?php echo $i; ?>" name="web_setting[wheel_percent_<?php echo $i; ?>]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $wheel_percent['value'] ?>" placeholder="ข้อมูล % การสุ่มได้">
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label class="control-label">เครดิตที่ได้ (<?php echo $i; ?>)</label>
												<?php $wheel_credit = $this->Setting_model->setting_find([
														'name' => 'wheel_credit_'.$i
												]); ?>
												<input type="text" id="wheel_credit_<?php echo $i; ?>" name="web_setting[wheel_credit_<?php echo $i; ?>]" oninput="validateInputNumber(this)" class="form-control" value="<?php echo $wheel_credit['value'] ?>" placeholder="ข้อมูล เครดิตที่ได้">
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label class="control-label">โทนสี (<?php echo $i; ?>)</label>
												<?php $wheel_color = $this->Setting_model->setting_find([
														'name' => 'wheel_color_'.$i
												]); ?>
												<input data-jscolor="{format:'hex'}"  id="wheel_color_<?php echo $i; ?>" name="web_setting[wheel_color_<?php echo $i; ?>]" class="form-control" value="<?php echo $wheel_color['value'] ?>"/>
											</div>
										</div>
									<?php endfor; ?>
								</div>
							</div>

						</div>
					</div>
				</section>
			</div>
		<?php endif; ?>
		<div class="content-body">
			<section class="card">
				<div class="card-content">
					<div class="card-body">
						<h3 class="card-title">ข้อมูลโทนสี & รูปภาพ & เสียงการแจ้งเตือน</h3>
						<hr>
						<div class="form-body mt-3">
							<div class="row ">
								<div class="col-md-3">
									<div class="form-group">
										<label class="control-label">โทนสีหลัก</label>
										<?php $theme_color_1 = $this->Setting_model->setting_find([
												'name' => 'theme_color_1'
										]); ?>
										<input data-jscolor="{format:'rgb'}"  id="theme_color_1" name="web_setting[theme_color_1]" class="form-control" value="<?php echo $theme_color_1['value'] ?>"/>
										<small class="mb-1 mt-1 text-muted">ค่าเริ่มต้น : rgb(252, 19, 19)</small>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="control-label">โทนสีหลัก (Border)</label>
										<?php $theme_color_2 = $this->Setting_model->setting_find([
												'name' => 'theme_color_2'
										]); ?>
										<input data-jscolor="{format:'rgb'}"  id="theme_color_2" name="web_setting[theme_color_2]" class="form-control" value="<?php echo $theme_color_2['value'] ?>"/>
										<small class="mb-1 mt-1 text-muted">ค่าเริ่มต้น : rgb(217, 19, 19)</small>
									</div>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-md-3">
									<?php $web_logo = $this->Setting_model->setting_find([
											'name' => 'web_logo'
									]); ?>
									<label >รูปภาพโลโก้ (800x190)</label>
									<img class="img-fluid" src="<?php echo $web_logo != "" ? $web_logo['value'] : base_url('assets/images/not-found.png'); ?>" alt="">
									<input type="file" id="image"  name="web_logo" class="dropify" data-height="200" data-allowed-file-extensions="jpg png"/>
								</div>
								<div class="col-md-3">
									<?php $web_logo_cover = $this->Setting_model->setting_find([
											'name' => 'web_logo_cover'
									]); ?>
									<label >รูปภาพ Cover Desktop (1980x440)</label>
									<img class="img-fluid" src="<?php echo $web_logo_cover != "" ? $web_logo_cover['value'] : base_url('assets/images/not-found.png'); ?>" alt="">
									<input type="file" id="image"  name="web_logo_cover" class="dropify" data-height="200" data-allowed-file-extensions="jpg png"/>
								</div>
								<div class="col-md-3">
									<?php $web_logo_cover_m = $this->Setting_model->setting_find([
											'name' => 'web_logo_cover_m'
									]); ?>
									<label >รูปภาพ Cover Mobile (1000x375)</label>
									<img class="img-fluid" src="<?php echo $web_logo_cover_m != "" ? $web_logo_cover_m['value'] : base_url('assets/images/not-found.png'); ?>" alt="">
									<input type="file" id="image"  name="web_logo_cover_m" class="dropify" data-height="200" data-allowed-file-extensions="jpg png"/>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-md-3">
									<?php $web_logo = $this->Setting_model->setting_find([
											'name' => 'web_sound'
									]); ?>
									<label >เสียงการแจ้งเตือน (เฉพาะไฟล์ mp3 เท่านั้น)</label>
									<input type="file" id="web_sound_alert" class="dropify" name="web_sound_alert"  accept = ".mp3"/>
								</div>
							</div>
							<hr>
					</div>
				</div>
			</section>
		</div>
		<hr />
		<div class="row mb-4">
			<div class="col-md-12">
				<div class="text-right m-b-10">
					<a type="button" href="<?php echo site_url('/') ?>" class=" btn bg-gradient-warning waves-effect waves-light mr-1"><span><i class="fa fa-arrow-left mr-1"></i></span>ย้อนกลับ</a>
					<button  id="btn_update" type="submit" class=" btn bg-gradient-success waves-effect waves-light"><span><i class="fa fa-save mr-1"></i></span>แก้ไข</button>
				</div>
			</div>
		</div>
	</form>
</div>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/setting/web_setting.js?'.time()) ?>"></script>
