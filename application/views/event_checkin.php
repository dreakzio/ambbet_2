<section class="register">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('event') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fas fa-check-circle mb-2"></i>&nbsp;กิจกรรมเช็คอิน</span>
	<hr style="margin-top: 15px">
	<div class="text-center pb-0">
		<h5 class="mt-3 text-dark">ท่านจะได้รับ <?php echo isset($web_setting["login_status"]) && $web_setting["login_status"]["value"] == "1" && is_numeric($web_setting['login_point']['value'] ) ? number_format($web_setting['login_point']['value'],2) : "0.00"; ?> แต้ม / วัน ฟรีเพียงแค่เข้าสู่ระบบหรือเข้าใช้งานเว็ปเท่านั้น
		</h5>
	</div>
	<div class="container" >
		<div class="mb-2 p-0">
			<div class="d-flex justify-content-center profile-box-top">
				<div class="p-0 align-self-center text-center" >
					<div class="user-name  text-silver text-center mb-1">
						<p class="mb-0 text-success"><strong>วันที่เข้าใช้งานเว็ปล่าสุด : <?php echo !is_null($user['login_process_job_date']) && !empty($user['login_process_job_date']) ? date('Y-m-d',strtotime($user['login_process_job_date'])) : date('Y-m-d'); ?></strong></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<section class="affiliate mb-3">
		<div class="affiliate-box"  style="background: var(--base-color-main) !important">
			<div class="affiliate-amount-box text-center"><small>แต้มสะสม</small>
				<p class="amount">
					<vue-numeric :class="'text-silver'" id="commission_wallet" :read-only="true" empty-value="0.00" output-type="String" v-bind:precision="2" v-bind:value="commission || 0" separator=","></vue-numeric>
				</p>
			</div>
			<center>
				<div class="form-group">
					<button :disabled="pre_loader" @click.prevent="transferToMainWallet()" type="button" class="btn btn-light btn-lg btn-block" style="font-size: 20px">
						<i class="fas fa-paper-plane"></i> โยกเข้ากระเป๋าเงิน
					</button>
				</div>
			</center>
		</div>

	</section>
</section>
<loading :active.sync="pre_loader"
		 :can-cancel="false"
		 :width="80"
		 :height="60"
		 :opacity="0.2"
		 color="#fff"
		 :is-full-page="true"></loading>
<script>
	const login_turn = "<?php echo isset($web_setting['login_turn']) && !empty($web_setting['login_turn']['value']) ? $web_setting['login_turn']['value'] : '0'?>";
</script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/qrcode/qrcode.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/event_checkin.js?').time() ?>"></script>



