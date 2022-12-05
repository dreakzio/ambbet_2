<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-history"></i>&nbsp;ประวัติฝาก-ถอน</span>
	<hr style="margin-top: 15px">
</section>
<section class="">
	<div class="row">
	<div class="bg-darkred col-12 mb-2">
			<div class="d-flex justify-content-center">
				<ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist" style="width: 100%">
					<li class="nav-item mx-1 mt-2" role="presentation">
						<a class="nav-link btn btn-category active" id="pills-deposit-withdraw-tab" data-toggle="pill" href="#pills-deposit-withdraw" role="tab" aria-controls="pills-deposit-withdraw" aria-selected="true"><i class="fa fa-history  mr-1" ></i>ฝาก-ถอน</a>
					</li>
					<?php if(isset($web_setting['feature_bonus_return_balance_winlose']) && $web_setting['feature_bonus_return_balance_winlose']['value'] == "1"): ?>
						<li class="nav-item mx-1 mt-2" role="presentation">
							<a class="nav-link btn btn-category" id="pills-return-tab" data-toggle="pill" href="#pills-return" role="tab" aria-controls="pills-return" aria-selected="false"><i class="fa fa-history mr-1" ></i>โบนัสคืนยอดเสีย</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>

			<div class="tab-content p-0" id="pills-tabContent">
				<div class="tab-pane fade show active" id="pills-deposit-withdraw" role="tabpanel" aria-labelledby="pills-deposit-withdraw-tab">
					<div class="container pl-0 pr-0">
						<div class="row mx-auto">
							<div class="col-12 text-center mx-auto">
								<div class="table-history mx-lg-auto">
									<table class="table table-striped">
										<thead class="bg-darkred-2">
										<tr class="text-white bg-success">
											<th class="text-center">ลำดับ</th>
											<th class="text-left" width="40%">วันเวลา</th>
											<th class="text-center" width="10%">ประเภท</th>
											<th class="text-right" width="15%">จำนวน</th>
											<th class="text-center">สถานะ</th>
										</tr>
										</thead>
										<tbody class="">
										<tr v-for="result in results">
											<th class="text-center">{{result.id}}</th>
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
												<span v-else-if="result.status == '1' && result.type == '1'">สำเร็จ</span>
												<span v-else-if="(result.status == '1' || result.status == '3') && result.type == '2'">สำเร็จ</span>
												<span v-else-if="result.status == '2'">ไม่อนุมัติ</span>
												<span v-else>กำลังดำเนินการ</span>
											</td>
										</tr>
										<tr v-if="results.length == 0">
											<td colspan="5" class="text-center">ไม่มีข้อมูล</td>
										</tr>
										</tbody>
										<tfoot>
										<tr>
											<td colspan="5" class="text-right text-muted">20 รายการล่าสุด</td>
										</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php if(isset($web_setting['feature_bonus_return_balance_winlose']) && $web_setting['feature_bonus_return_balance_winlose']['value'] == "1"): ?>
					<div class="tab-pane fade" id="pills-return" role="tabpanel" aria-labelledby="pills-return-tab">
						<div class="container">
							<div class="row">
								<div class="col-12 mx-auto">
									<div class="table-history mx-lg-auto">
										<table class="table table-striped">
											<thead class="bg-darkred-2">
											<tr class="text-white bg-success">
												<th class="text-center">ลำดับ</th>
												<th class="text-left" width="30%">วันเวลา</th>
												<th class="text-center" width="20%">ยอดเล่นเสีย</th>
												<th class="text-right" width="15%">จำนวน</th>
												<th class="text-center">สถานะ</th>
											</tr>
											</thead>
											<tbody class="bg-white">
											<tr v-for="result in bonus_return_results">
												<th class="text-center">{{result.id}}</th>
												<td class="text-left">{{result.created_at}}</td>
												<td class="text-right">
													- <vue-numeric :read-only="true" empty-value="0.00" output-type="String" v-bind:precision="2" v-bind:value="result.wl_amount || 0" separator=","></vue-numeric>
												</td>
												<td class="text-right">
													<vue-numeric :read-only="true" empty-value="0.00" output-type="String" v-bind:precision="2" v-bind:value="result.sum_amount || 0" separator=","></vue-numeric><span class="ml-1">฿</span>
												</td>
												<td class="text-center">
													<span>สำเร็จ</span>
												</td>
											</tr>
											<tr v-if="bonus_return_results.length == 0">
												<td colspan="5" class="text-center">ไม่มีข้อมูล</td>
											</tr>
											</tbody>
											<tfoot>
											<tr>
												<td colspan="5" class="text-right text-muted">20 รายการล่าสุด</td>
											</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<script>
	const chk_return_balance = '<?php echo isset($web_setting['feature_bonus_return_balance_winlose']) && $web_setting['feature_bonus_return_balance_winlose']['value'] == "1" ? true : false ?>';
</script>
<script src="<?php echo base_url('assets/scripts/history.js?').time() ?>"></script>
