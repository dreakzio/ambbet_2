<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-hand-holding-usd"></i>&nbsp;ถอนเงิน</span>
	<hr style="margin-top: 15px">
</section>
<div>
	<section class="credit">
		<div class="credit-box" style="background-color: #000">
			<div class="amount-box float-left">
				<small>ยอดเงินคงเหลือ</small>
				<small class="float-right mr-3">
					<i v-if="!loading_wallet" @click.prevent="getCreditBalance" title="อัพเดทยอดเงิน"
					   class="fas fa-sync-alt refresh pointer animated"></i>
					<i title="อัพเดทยอดเงิน"
					   v-else	   class="fas fa-sync-alt refresh fa-spin"></i>
				</small>
				<p class="amount">
					<vue-numeric id="main_wallet" :read-only="true" empty-value="0.00" output-type="String" v-bind:precision="2" v-bind:value="amount" separator=","></vue-numeric>
				</p>
			</div>
			<div class="button-box float-left">
				<a href="<?php echo base_url('deposit') ?>" class="btn-block btn-gold"><i class="fa fa-wallet"></i>
					ฝากเงิน</a>
				<a href="<?php echo base_url('withdraw') ?>" class="btn-block btn-silver"><i
							class="fa fa-hand-holding-usd"></i> ถอนเงิน</a>
			</div>
			<div class="clearfix"></div>
		</div>
	</section>
</div>

<div class="card bank-info mt-4">
	<h5 class="card-header">ถอนเงินเข้าบัญชีธนาคาร</h5>
	<div class="card-body">
		<div class="bank-user-logo">
			<?php if(in_array($user['bank'],["01","1"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/1.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["02","2"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/2.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["03","3"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/3.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["04","4"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/5.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["05","5"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/6.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["06","6"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/4.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["07","7"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/7.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["08","8"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/9.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["09","9"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/baac.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php else: ?>
				<img src="<?php echo base_url() ?>assets/images/banks/not-found.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php endif; ?>
		</div>
		<div class="bank-user-info">
			<p id="bank-user-bankname" style="font-family: DB-Heavent-light !important;">ธนาคาร :
				<?php echo array_key_exists($user['bank'],getBankList()) ? getBankList()[$user['bank']] : '-'; ?></p>
			<p id="bank-user-name" style="font-family: DB-Heavent-light !important;">ชื่อบัญชี :
				<?php echo $user['full_name']; ?></p>
			<p id="bank-user-number" style="font-family: DB-Heavent-light !important;">เลขบัญชี :
				<?php echo getBankNumberFormat($user['bank_number']); ?></p>
		</div>
	</div>
</div>
<br>
<div class="card">
	<div class="card-body">
		<div class="form-group col-md-12" id="Rolling">
			<label>ยอดเงินคงเหลือ</label>
			<input type="text" class="form-control text-muted" name="Amount" v-bind:value="amount" placeholder="" disabled="">
			<label>ถอนขั้นต่ำ</label>
			<input type="text" class="form-control text-muted" name="Bonus" v-model="withdraw_min_amount" placeholder="" disabled="">
			<label id="Tag">ยอดถอน</label>
			<vue-numeric :disabled="pre_loader" :placeholder="'กรอกจำนวนเงิน'" :class="'form-control'" id="withdraw_amount" :read-only="false"  v-bind:precision="0" v-model="amount_withdraw" separator=","></vue-numeric>
			<div class="text-left text-warning mb-2 mt-2">
				<a href="<?php echo $web_setting['line_url']['value']; ?>" class="text-warning" target="_blank">
				<span style="font-size:1em;">หากเกิดปัญหาถอนเงินหรืออื่นๆ กรุณาติดต่อเจ้าหน้าที่ Line ID : <span class="text-success"><?php echo isset($web_setting['line_id']) ? $web_setting['line_id']['value'] : ''; ?></span>
				</span>
				</a>
			</div>
			<div class="form-group">
				<button :disabled="pre_loader" type="button" @click.prevent="doWithdraw()" class="btn-red btn-lg btn-block" style="font-size: 20px" name="button">
					<i class="fa fa-hand-holding-usd"></i> ทำรายการถอนเงิน
				</button>
			</div>
		</div>
	</div>
	<script>

	</script>
</div>
<section class="py-4">
	<div class="withdraw-history">
		<div class="text-center text-dark title-withdraw-history">
			<h3>ประวัติการถอน (20 รายการล่าสุด)</h3>
		</div>
		<div class="table-deposit mx-auto">
			<table class="table table-striped">
				<thead class="bg-darkred-2">
				<tr class="text-white bg-danger">
					<th class="text-center">ลำดับ</th>
					<th class="text-left" width="40%">วันเวลา</th>
					<th class="text-center" width="10%">ประเภท</th>
					<th class="text-right" width="15%">จำนวน</th>
					<th class="text-center">สถานะ</th>
				</tr>
				</thead>
				<tbody class="bg-white">
				<tr v-for="(result,index) in results">
					<th class="text-center">{{results.length - index}}</th>
					<td class="text-left">{{result.created_at}}</td>
					<td class="text-center">
						<span v-if="result.type == '1'">ฝาก</span>
						<span v-else>ถอน</span>
					</td>
					<td class="text-right">
						<vue-numeric :read-only="true" empty-value="0.00" output-type="String" v-bind:precision="2" v-bind:value="result.amount || 0" separator=","></vue-numeric><span class="ml-1">฿</span>
					</td>
					<td class="text-center">
						<span v-if="result.status == '0'">กำลังดำเนินการ</span>
						<span v-else-if="result.status == '1' || result.status == '3'">สำเร็จ</span>
						<span v-else-if="result.status == '2'">ไม่อนุมัติ</span>
						<span v-else>กำลังดำเนินการ</span>
					</td>
				</tr>
				<tr v-if="results.length == 0">
					<td colspan="5" class="text-center">ไม่มีข้อมูล</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</section>
<loading :active.sync="pre_loader"
		 :can-cancel="false"
		 :width="100"
		 :height="100"
		 :opacity="0.6"
		 color="#fff"
		 :is-full-page="true"></loading>


	 <?php $withdraw_auto_status = $this->Setting_model->setting_find([
		'name' => 'withdraw_auto_status'
	]); ?>


<script>
	const withdraw_auto_status = '<?php echo $withdraw_auto_status['value'];?>';
	const withdraw_min_amount = '<?php echo $withdraw_min_amount!="" && is_numeric($withdraw_min_amount['value']) ? (int)$withdraw_min_amount['value'] : 0 ?>';
</script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/withdraw.js?').time() ?>"></script>
