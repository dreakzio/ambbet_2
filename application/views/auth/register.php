<section class="register">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('auth?force=true') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-user-plus"></i>&nbsp;สมัครสมาชิก</span>
	<hr style="margin-top: 15px">
	<div v-if="step == '1'">
	<?php
		$register_verify_otp_status = $this->Setting_model->setting_find([
				'name' => 'register_verify_otp_status'
		]);
	    $register_verify_otp_status = isset($register_verify_otp_status['value']) ? $register_verify_otp_status['value'] : 1;
		$line_login_status = $this->Setting_model->setting_find([
				'name' => 'line_login_status'
		]);
		$line_login_status = isset($line_login_status['value']) ? $line_login_status['value'] : 1;
		$line_login_chk = false;

		//ไม่ต้อง otp เพราะว่า verify ผ่านไลน์แล้ว
		if(isset($_SESSION['line_login_chk']) && $_SESSION['line_login_chk']){
			if($line_login_status == "1"){
				$line_login_chk = true;
			}
			$register_verify_otp_status = 0;
		}
	?>
	<div class="form-register text-center">
		<?php if($line_login_status == "1" && (!$line_login_chk || is_null($line_login_chk))): ?>
		<div class="register-text mt-4">
			<a href="<?php echo base_url('/auth/line_link'.(isset($_GET['ref']) && !empty($_GET['ref']) ? '?ref='.$_GET['ref'] : '')) ?>" class="btn btn-line btn-submit">
				<i class="fa fa-line mr-1"></i>
				<span class="text-silver">สมัครสมาชิกด้วย LINE</span>
			</a>
		</div>
		<div class="row mt-4 mb-4">
			<div class="col-5">
				<hr>
			</div>
			<div class="col-2">
				<h5 class="text-center  text-muted">หรือ</h5>
			</div>
			<div class="col-5">
				<hr>
			</div>
		</div>
		<?php endif; ?>
		<div class="register-text">
			<div class="text-center">
				<span class="text-silver">กรุณากรอกเบอร์มือถือ จำนวน 10 หลัก</span>
			</div>
			<?php if($register_verify_otp_status == 1): ?>
				<div class="text-center">
					<span class="text-silver">เพื่อระบุ OTP ถัดไป</span>
				</div>
			<?php endif; ?>
		</div>

		<div class="mt-4">
			<div class="input-desktop">
				<div class="input-group mb-4">
					<div class="input-group-prepend">
						<span class="input-group-text"><img src="<?php echo base_url('/') ?>assets/images/phone1.png" alt="" width="20"></span>
					</div>
					<input type="tel" class="form-control" id="phone" maxlength="10" name="phone" v-model="form.phone" placeholder="เบอร์มือถือ">
				</div>
			</div>
			<div class="text-center">
				<button type="button" @click.prevent="sendOtp" class="btn btn-custom btn-submit">
					<span class="text-silver">ยืนยัน</span>
				</button>
			</div>
		</div>

	</div>
	</div>
	<div v-else-if="step == '2'">
		<div class="form-register text-center">
			<div class="register-text-2">
				<div class="text-center">
					<div class="text-silver">ระบบได้ส่งรหัสไปทาง SMS เบอร์มือถือท่าน</div>
				</div>
				<div class="text-center">
					<div class="text-silver">กรุณากรอกรหัสที่ได้รับเพื่อยืนยันตน</div>
				</div>
			</div>
			<div class="mt-4">
				<div class="d-flex justify-content-center mb-3">
					<div class="text-silver align-self-center">เบอร์มือถือ : <span>{{form.phone}}</span></div>
				</div>
				<div class="input-group mb-2 mb-lg-2">
					<div class="input-group-prepend">
						<div class="input-group-text input-text-otp"><img src="<?php echo base_url('/') ?>assets/images/lockcode1.png" alt="" width="25"></div>
					</div>
					<input type="text" class="form-control input-otp" id="otp" maxlength="6" name="otp" v-model="form.otp" placeholder="รหัส OTP">
				</div>
				<div class="pl-4 text-left mb-3">
					<div class="text-silver"><a href="javascript:{}" @click.prevent="sendOtp"><img class="mr-1" src="<?php echo base_url('/') ?>assets/images/refresh.png" alt="" width="10"> <small>ส่งรหัสยืนยันใหม่อีกครั้ง</small></a></div>
				</div>
				<div class="text-center">
					<button type="button" @click.prevent="checkOtp" class="btn btn-custom btn-submit">
						<span class="text-silver">ยืนยัน</span>
					</button>
				</div>
			</div>
		</div>
		<div class="text-center">
			<div class="text-silver">หากไม่ได้รับ SMS กรุณาติดต่อเจ้าหน้าที่</div>
		</div>
	</div>
	<div v-else-if="step = '3'">
		<div class="form-register text-center">
			<div class="register-text">
				<div class="text-center">
					<div class="text-silver">เพิ่มบัญชีธนาคาร</div>
				</div>
			</div>
			<div class="mt-4">
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text"><img src="<?php echo base_url('/') ?>assets/images/register2/phone.png" alt="" width="25"></span>
					</div>
					<?php if($line_login_chk): ?>
						<input type="tel" class="form-control" id="phone" maxlength="10"  name="phone" v-model="form.phone" placeholder="เบอร์มือถือ">
					<?php else: ?>
						<input type="tel" class="form-control" id="phone" maxlength="10" readonly name="phone" v-model="form.phone" placeholder="เบอร์มือถือ">
					<?php endif; ?>
				</div>
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text"><img src="<?php echo base_url('/') ?>assets/images/register2/password.png" alt="" width="25"></span>
					</div>
					<input type="password" class="form-control" id="password" maxlength="15" name="password" v-model="form.password" placeholder="รหัสผ่าน">
				</div>

				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text"><i class="fa fa-university"></i></span>
					</div>
					<select class="form-control" @change="chooseBank" v-model="form.bank" name="bank" id="bank">
						<option value="" disabled>กรุณาเลือก</option>
						<?php foreach (getBankListUniqueCode() as $bank_key => $bank_label): ?>
							<option value="<?php echo $bank_key; ?>"><?php echo $bank_label; ?></option>
						<?php  endforeach; ?>
					</select>
				</div>
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text">
						  <img src="<?php echo base_url('/') ?>assets/images/register2/user.png" alt="" width="25">
						</span>
					</div>
					<input type="text" class="form-control" id="full_name" maxlength="100" name="full_name" v-model="form.full_name" placeholder="ระบุชื่อ-นามสกุลบัญชี">
				</div>
				<div class="input-group mb-3">
					<div class="input-group-prepend">
					  <span class="input-group-text">
						<img src="<?php echo base_url('/') ?>assets/images/register2/acccount_number.png" alt="" width="25">
					  </span>
					</div>
					<input type="tel" class="form-control" :readonly="form.bank == '10'" id="bank_number" maxlength="15" name="bank_number" v-model="form.bank_number" placeholder="กรุณาระบุเลขที่บัญชี">
				</div>
				<div class="mb-3">
					<div class="text-center">
						<small class="text-silver">*ข้อมูลบัญชีที่ถูกบัญทึกไปแล้วจะไม่สามารถแก้ไขเองได้</small>
					</div>
					<div class="text-center">
						<small class="text-silver">กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก</small>
					</div>
				</div>

				<div class="text-center">
					<button type="button" @click.prevent="doRegister" class="btn btn-custom btn-submit">
						<span class="text-silver">ยืนยัน</span>
					</button>
				</div>
			</div>
		</div>
	</div>
	<loading :active.sync="pre_loader"
			 :can-cancel="false"
			 :width="80"
			 :height="60"
			 :opacity="0.2"
			 color="#fff"
			 :is-full-page="true"></loading>
</section>
<script>
	 const register_step = "<?php echo isset($_SESSION['register_step']) ? $_SESSION['register_step'] : 1; ?>";
	 const register_data = JSON.parse('<?php echo isset($_SESSION['register_data']) ? json_encode($_SESSION['register_data']) : "null"; ?>');
	 const register_verify_otp_status = '<?php echo $register_verify_otp_status; ?>'
	 // const register_step = 1;
	// const register_data = null;
</script>
<?php if(isset($_GET['ref']) && !empty($_GET['ref'])): ?>
	<script>
		window.localStorage.setItem("register_ref",'<?php echo $_GET['ref']; ?>')
	</script>
<?php endif ?>
<script>
	$(document).ready(function () {
		$(document).on('click','.icon-bank-box img',function(){
			$('.icon-bank-box img').removeClass('active')
			$(this).addClass('active')
		});
	});
</script>
<script type="text/javascript" src="<?php echo base_url('assets/scripts/register.js?'.time()) ?>"></script>
