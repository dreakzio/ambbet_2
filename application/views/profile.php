<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-user"></i>&nbsp;บัญชีผู้ใช้</span>
	<hr style="margin-top: 15px">
</section>
<section class="">
	<section class="bonus">
		<div class="row">
			<div class="col-md-12">
				<div class="d-flex justify-content-start p-3">
					<div class="p-2 d-none d-sm-block">
						<img src="<?php echo base_url() ?>assets/images/profile-user.png" style="width: 80px" />
					</div>
					<div class="p-2 flex-grow-1 align-self-center">
						<h4 class="d-block text-center mb-3 d-sm-none">
							<img src="<?php echo base_url() ?>assets/images/profile-user.png" style="width: 80px" />
						</h4>
						<h4>ชื่อ-นามสกุล : <?php echo $user['full_name']; ?></h4>
						<h4>รหัสสมาชิก : <?php echo $user['username']; ?></h4>
						<?php if(isset($web_setting['feature_bonus_return_balance_winlose']) && $web_setting['feature_bonus_return_balance_winlose']['value'] == "1"): ?>
							<h4 class="mb-0">โบนัสคืนยอดเสีย :
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" name="radioReturnBalance" v-on:change="changeReturnBalance" id="radioReturnBalance1" value="0" v-model="is_active_return_balance">
									<label class="form-check-label text-dark" for="radioReturnBalance1">
										ปิด
									</label>
								</div>
								<div class="form-check mb-2 form-check-inline">
									<input class="form-check-input" type="radio" name="radioReturnBalance" v-on:change="changeReturnBalance" id="radioReturnBalance2" value="1" v-model="is_active_return_balance">
									<label class="form-check-label text-dark" for="radioReturnBalance2">
										เปิด
									</label>
								</div>
							</h4>
						<?php
							$ref_return_balance_turn_cal =  0;
							$ref_return_balance_percent_cal =  0;
							if(isset($web_setting['ref_return_balance_status']) && $web_setting['ref_return_balance_status']['value'] == "1"){
								if(isset($_SESSION['user']['rank']) && !is_null($_SESSION['user']['rank'])){
									if($_SESSION['user']['rank'] =="1"){
										$ref_return_balance_turn_cal = isset($web_setting['ref_return_balance_rank1_turn']) && $web_setting['ref_return_balance_rank1_turn']['value'] ? $web_setting['ref_return_balance_rank1_turn']['value']  : 0;
										$ref_return_balance_percent_cal = isset($web_setting['ref_return_balance_rank1_percent']) && $web_setting['ref_return_balance_rank1_percent']['value'] ? $web_setting['ref_return_balance_rank1_percent']['value']  : 0;
									}else if($_SESSION['user']['rank'] =="2"){
										$ref_return_balance_turn_cal = isset($web_setting['ref_return_balance_rank2_turn']) && $web_setting['ref_return_balance_rank2_turn']['value'] ? $web_setting['ref_return_balance_rank2_turn']['value']  : 0;
										$ref_return_balance_percent_cal = isset($web_setting['ref_return_balance_rank2_percent']) && $web_setting['ref_return_balance_rank2_percent']['value'] ? $web_setting['ref_return_balance_rank2_percent']['value']  : 0;
									}else if($_SESSION['user']['rank'] =="3"){
										$ref_return_balance_turn_cal = isset($web_setting['ref_return_balance_rank3_turn']) && $web_setting['ref_return_balance_rank3_turn']['value'] ? $web_setting['ref_return_balance_rank3_turn']['value']  : 0;
										$ref_return_balance_percent_cal = isset($web_setting['ref_return_balance_rank3_percent']) && $web_setting['ref_return_balance_rank3_percent']['value'] ? $web_setting['ref_return_balance_rank3_percent']['value']  : 0;
									}else{
										$ref_return_balance_turn_cal = isset($web_setting['ref_return_balance_rank1_turn']) && $web_setting['ref_return_balance_rank1_turn']['value'] ? $web_setting['ref_return_balance_rank1_turn']['value']  : 0;
										$ref_return_balance_percent_cal = isset($web_setting['ref_return_balance_rank1_percent']) && $web_setting['ref_return_balance_rank1_percent']['value'] ? $web_setting['ref_return_balance_rank1_percent']['value']  : 0;
									}
								}else{
									$ref_return_balance_turn_cal = isset($web_setting['ref_return_balance_turn']) && $web_setting['ref_return_balance_turn']['value'] ? $web_setting['ref_return_balance_turn']['value']  : 0;
									$ref_return_balance_percent_cal = isset($web_setting['ref_return_balance_percent']) && $web_setting['ref_return_balance_percent']['value'] ? $web_setting['ref_return_balance_percent']['value']  : 0;
								}
							}
						?>
							<?php if(isset($_SESSION['user']['rank']) && !is_null($_SESSION['user']['rank'])): ?>
								<?php if($_SESSION['user']['rank'] =="1"): ?>
									<h4 class="d-inline mb-0"><strong>Rank : Member</strong></h4>&nbsp;<img style="width: 50px" src="<?php echo base_url('assets/images/rank-bronze.png'); ?>" class="img-fluid" alt="...">
								<?php elseif($_SESSION['user']['rank'] =="2"): ?>
									<h4 class="d-inline mb-0"><strong>Rank : <span class="text-secondary">Silver</span></strong></h4>&nbsp;<img style="width:  50px" src="<?php echo base_url('assets/images/rank-silver.png'); ?>" class="img-fluid" alt="...">
								<?php elseif($_SESSION['user']['rank'] =="3"): ?>
									<h4 class="d-inline mb-0"><strong>Rank : <span class="text-warning">Gold</span></strong></h4>&nbsp;<img style="width:  50px" src="<?php echo base_url('assets/images/rank-gold.png'); ?>" class="img-fluid" alt="...">
								<?php else: ?>
									<h4 class="d-inline mb-0"><strong>Rank : Member</strong></h4>&nbsp;<img style="width:  50px" src="<?php echo base_url('assets/images/rank-bronze.png'); ?>" class="img-fluid" alt="...">
								<?php endif; ?>
							<?php endif; ?>
							<?php if(isset($web_setting['ref_return_balance_status']) && $web_setting['ref_return_balance_status']['value'] == "1"): ?>
								<p class="text-warning mb-2 mt-0">หากเปิดรับโบนัสจะได้รับเครดิตฟรี <?php echo $ref_return_balance_percent_cal; ?> %
									(ทำเทิร์น <?php echo $ref_return_balance_turn_cal; ?> เท่า) จากยอดเล่นเสียแต่ละวันของท่าน</p>
							<?php else: ?>
								<p class="text-warning mb-2 mt-0">หากเปิดรับโบนัสจะได้รับเครดิตฟรี <?php echo $ref_return_balance_percent_cal; ?> % จากยอดเล่นเสียแต่ละวันของท่าน</p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-md-12">
				<section class="affiliate mb-3">
					<div class="affiliate-box" style="background: var(--base-color-main) !important">
						<div class="affiliate-amount-box text-center"><small>โบนัสคืนยอดเสียคงเหลือ</small>
							<p class="amount">
								<vue-numeric :class="'text-silver'" id="return_balance_wallet" :read-only="true" empty-value="0.00" output-type="String" v-bind:precision="2" v-bind:value="return_balance || 0.00" separator=","></vue-numeric>
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
			</div>
			<div class="col-sm-12 col-md-12">
				<a style="color: #000" href="<?php echo base_url('account/user_bank') ?>" class="menu-list">
					<div class="d-flex d-flex justify-content-between">
						<div class="p-2 flex-grow-1 bd-highlight">
							<i class="fa fa-university"></i> ข้อมูลธนาคาร
						</div>
						<div class="p-2 align-self-center">
							<i class="fas fa-angle-right"></i>
						</div>
					</div>
				</a>
				<a style="color: #000" href="<?php echo base_url('account/user_account') ?>" class="menu-list">
					<div class="d-flex d-flex justify-content-between">
						<div class="p-2 flex-grow-1 bd-highlight">
							<i class="fa fa-users"></i> ข้อมูลเข้าสู่ระบบ
						</div>
						<div class="p-2 align-self-center">
							<i class="fas fa-angle-right"></i>
						</div>
					</div>
				</a>
				<a style="color: #000" href="<?php echo base_url('change-password') ?>" class="menu-list">
					<div class="d-flex d-flex justify-content-between">
						<div class="p-2 flex-grow-1 bd-highlight">
							<i class="fa fa-key"></i> เปลี่ยนรหัสผ่าน
						</div>
						<div class="p-2 align-self-center">
							<i class="fas fa-angle-right"></i>
						</div>
					</div>
				</a>
			</div>
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
	const ref_turn = "<?php echo $ref_return_balance_turn_cal; ?>"
	const is_active_return_balance = '<?php echo $user['is_active_return_balance']; ?>'
</script>
<script src="<?php echo base_url('assets/scripts/profile.js?').time() ?>"></script>
