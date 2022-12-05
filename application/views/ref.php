<section class="ref text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-handshake"></i>&nbsp;แนะนำเพื่อน</span>
	<hr style="margin-top: 15px">
</section>
<div class="text-center pb-3">
	<?php if(isset($web_setting['feature_bonus_aff_turnover_and_winlose']) && $web_setting['feature_bonus_aff_turnover_and_winlose']['value'] == "1"): ?>
		<?php
		$ref_step2_status = $this->Setting_model->setting_find([
				'name' => 'ref_step2_status'
		]);
		?>
		<?php $ref_bonus_type = $this->Setting_model->setting_find([
				'name' => 'ref_bonus_type'
		]); ?>
		<?php if( (isset($web_setting['feature_bonus_aff_turnover_and_winlose_step2']) && $web_setting['feature_bonus_aff_turnover_and_winlose_step2']['value'] == "1") && $ref_step2_status!="" && $ref_step2_status['value'] == "1"): ?>
			<h5 class="mt-3 text-dark">แนะนำเพื่อนรับ
				<?php
				echo $web_setting['ref_percent']['value'];
				?>
				%<?php echo isset($web_setting['ref_turn']) && $web_setting['ref_turn']['value'] != "" ? " ทำเทิร์น ".$web_setting['ref_turn']['value']." เท่า" : "" ?> (2 ขั้น) จากยอดเล่น<?php echo $ref_bonus_type!="" && $ref_bonus_type['value'] == "1" ? "เสีย" : "เทิร์นโอเวอร์" ?>ของเพื่อน & ยอดเล่น<?php echo $ref_bonus_type!="" && $ref_bonus_type['value'] == "1" ? "เสีย" : "เทิร์นโอเวอร์" ?>คนที่เพื่อนแนะนำ
			</h5>
		<?php else: ?>
			<h5 class="mt-3 text-dark">แนะนำเพื่อนรับ
				<?php
				echo $web_setting['ref_percent']['value'];
				?>
				%<?php echo isset($web_setting['ref_turn']) && $web_setting['ref_turn']['value'] != "" ? " ทำเทิร์น ".$web_setting['ref_turn']['value']." เท่า" : "" ?>  จากยอดเล่น<?php echo $ref_bonus_type!="" && $ref_bonus_type['value'] == "1" ? "เสีย" : "เทิร์นโอเวอร์" ?>ของเพื่อน
			</h5>
		<?php endif; ?>
	<?php endif; ?>
</div>
<section class="affiliate mb-3">
	<div class="affiliate-box">
		<div class="affiliate-amount-box text-center"><small>คอมมิชชั่นคงเหลือ</small>
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
<section>
	<div class="row">
		<div class="col-12 col-md-6 mb-3 mb-md-0">
			<div class="stat-content green">
				<div class="stat-description text-faded"><a
							style="font-size:20px !important;">คอมมิชชั่นที่โยกเเล้ว</a></div>
				<div class="stat-number text-faded mt-2"><a
							style="font-size:30px !important;"><?php echo number_format($count_commission,2); ?></a></div> <a
						class="stat-footer">บาท</a>
			</div>
		</div>
		<div class="col-12 col-md-6">
			<div class="stat-content red">
				<div class="stat-description text-faded"><a
							style="font-size:20px !important;">เเนะนำเเล้ว</a></div>
				<div class="stat-number text-faded mt-2"><a style="font-size:30px !important;"><?php echo number_format($count_ref); ?></a>
				</div> <a class="stat-footer">คน</a>
			</div>
		</div>
	</div>
</section>

<section class="">
	<div class="row">
		<div class="bg-darkred col-12 mb-2">
			<div class="">

				<div class="d-flex justify-content-center">
					<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist" style="width: 100%">
						<li class="nav-item mx-1 mt-2" role="presentation">
							<a class="nav-link text-center active" id="pills-qrcode-tab" data-toggle="pill" href="#pills-qrcode" role="tab" aria-controls="pills-qrcode" aria-selected="true">QR Code</a>
						</li>
						<li class="nav-item mx-1 mt-2" role="presentation">
							<a class="nav-link text-center" id="pills-ref-tab" data-toggle="pill" href="#pills-ref" role="tab" aria-controls="pills-ref" aria-selected="false">ประวัติแนะนำ</a>
						</li>
						<li class="nav-item mx-1 mt-2" role="presentation">
							<a class="nav-link text-center" id="pills-ref-deposit-tab" data-toggle="pill" href="#pills-ref-deposit" role="tab" aria-controls="pills-ref-deposit" aria-selected="false">ประวัติโบนัส</a>
						</li>
					</ul>
				</div>

				<div class="tab-content p-0" id="pills-tabContent">
					<div class="tab-pane fade show active" id="pills-qrcode" role="tabpanel" aria-labelledby="pills-qrcode-tab">
						<div class="container pl-0 pr-0">
							<div class="row mx-auto">
								<div class="col-12 text-center mx-auto">
									<center class="">
										<div id="qrcode"></div>
										<button type="button"  @click.prevent="copyLinkRef('<?php echo base_url('register?ref=').$user['id'] ?>')"  class="btn btn-custom border-0 mt-3" name="button">
											<span  class="text-silver"><i class="fa fa-clipboard"></i>&nbsp;คัดลอกลิงค์</span>
										</button>
									</center>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="pills-ref" role="tabpanel" aria-labelledby="pills-ref-tab">
						<div class="container pl-0 pr-0">
							<div class="row">
								<div class="col-12 mx-auto">
									<div class="table-history mx-lg-auto">
										<table class="table table-striped">
											<thead class="bg-darkred-2">
											<tr class="text-white bg-success">
												<th class="text-center">ยูส</th>
												<th class="text-center">วันเวลา</th>
											</tr>
											</thead>
											<tbody class="bg-white">
											<tr v-for="result in result_refs">
												<th class="text-center">{{result.to_account_username}}</th>
												<td class="text-center">{{result.created_at}}</td>
											</tr>
											<tr v-if="result_refs.length == 0">
												<td colspan="2" class="text-center">ไม่มีข้อมูล</td>
											</tr>
											</tbody>
											<tfoot>
											<tr>
												<td colspan="2" class="text-right text-muted">20 รายการล่าสุด</td>
											</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="pills-ref-deposit" role="tabpanel" aria-labelledby="pills-ref-deposit-tab">
						<div class="container pl-0 pr-0">
							<div class="row">
								<div class="col-12 mx-auto">
									<div class="table-history mx-lg-auto">
										<table class="table table-striped">
											<thead class="bg-darkred-2">
											<tr class="text-white bg-success">
												<th class="text-center">จากยูส</th>
												<th class="text-right">ยอดที่ได้รับ</th>
												<th class="text-center">วันเวลา</th>
											</tr>
											</thead>
											<tbody class="bg-white">
											<tr v-for="result in result_ref_deposits">
												<th class="text-center">{{result.username}}</th>
												<td class="text-right">
													<span>
														<vue-numeric :read-only="true" empty-value="0.00" output-type="String" v-bind:precision="2" v-bind:value="result.sum_amount || 0" separator=","></vue-numeric><span class="ml-1">฿</span>
													</span>
												</td>
												<td class="text-center">{{result.created_at}}</td>
											</tr>
											<tr v-if="result_ref_deposits.length == 0">
												<td colspan="3" class="text-center">ไม่มีข้อมูล</td>
											</tr>
											</tbody>
											<tfoot>
												<tr>
													<td colspan="3" class="text-right text-muted">20 รายการล่าสุด</td>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<loading :active.sync="pre_loader"
		 :can-cancel="false"
		 :width="80"
		 :height="60"
		 :opacity="0.2"
		 color="#fff"
		 :is-full-page="true"></loading>
<script>
	const ref_turn = "<?php echo isset($web_setting['ref_turn']) && !empty($web_setting['ref_turn']['value']) ? $web_setting['ref_turn']['value'] : '0'?>"
	const user_id = "<?php echo $user['id']; ?>"
</script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/qrcode/qrcode.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/ref.js?').time() ?>"></script>

