<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> <?php echo $this->lang->line('back'); ?></a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-wallet"></i>&nbsp;<?php echo $this->lang->line('deposit'); ?></span>
	<hr style="margin-top: 15px">
</section>
<section class="">
	<?php
		$auto_accept_bonus = $user['auto_accept_bonus'];
		if($auto_accept_bonus=='1'){
			$classbg  ='btn-green';
			$color = '#ffffff';
			$text ='รับโบนัสออโต้';
		}else{
			$classbg  ='btn-red';
			$color = '#ffffff';
			$text ='ไม่ได้รับโบนัสออโต้ กดรับโบนัสออโต้';
		}
	?>
	<div class=" mx-auto mb-1 text-center">
		<div class="mt-12">
			<a  href="#" @click.prevent="change_accept_bonus()" class="btn <?php echo $classbg;?> btn-md" style="color: <?php echo $color;?>;" id="change_accept_bonus">
				<i class="fa fa-check-circle" style="color: <?php echo $color;?>"></i> <?php echo $text; ?></a>
		</div>
	</div>
	<div class="mx-auto mb-4 form-withdraw" v-if="amount_deposit > 0">
		<?php if (!empty($promotion)): ?>
			<div class="card  mt-4">
				<h5 class="card-header" style="background-image: linear-gradient(to right, gold , yellow);"><?php echo $this->lang->line('deposit'); ?></h5>
				<div class="card-body">
					<div class="row mt-3 mb-0 text-left">
						<div class="col-12">
							<p class="text-dark mb-2"><?php echo $this->lang->line('please_select_promotion'); ?> : </p>
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-12">
							<?php foreach ($promotion as $key => $value): ?>

								<div class="div_radio_promotion fac fac-radio fac-default"><span></span>
									<input class="radio_promotion" id="radio<?php echo $value['id'] ?>" type="radio" v-model="promotion" name="promotion" value="<?php echo $value['id'] ?>">
									<label class="ml-2 text-dark" for="radio<?php echo $value['id'] ?>">
										<small>
											<?php echo $value['name'] ?>
											<?php if (!empty($value['start_time'])): ?>
												<span class="text-warning"><?php echo $this->lang->line('time'); ?> <?php echo $value['start_time']?> - <?php echo $value['end_time'] ?> <?php echo $this->lang->line('minute'); ?></span>
											<?php endif; ?>
											<?php if (!empty($value['number_of_deposit_days'])): ?>
												<span class="text-warning"><?php echo $this->lang->line('continue'); ?> <?php echo $value['number_of_deposit_days']?> <?php echo $this->lang->line('day'); ?></span>
											<?php endif; ?>
											<?php if ($value['max_value']>0 && $value['category'] == "1"): ?>
												<?php echo $this->lang->line('max'); ?> <?php echo number_format($value['max_value']) ?> <?php echo $this->lang->line('bath'); ?> (<?php echo $this->lang->line('do_turn'); ?> <a
														href="#" data-toggle="modal" style="text-decoration: underline;color: var(--base-color-main)" data-target="#modal_turn_<?php echo $value['id']; ?>"><?php echo $this->lang->line('detail'); ?></a>)
											<?php elseif ($value['category'] == "2"): ?>
												(<?php echo $this->lang->line('do_turn'); ?> <a
														href="#" data-toggle="modal"  style="text-decoration: underline;color: var(--base-color-main)" data-target="#modal_turn_<?php echo $value['id']; ?>"><?php echo $this->lang->line('detail'); ?></a>)
											<?php endif; ?>
											<?php if ($value['max_value']==0 && $value['percent'] == 0 && $value['category'] == "1"): ?>
												(<?php echo $this->lang->line('do_turn'); ?> <a
														href="#" data-toggle="modal"  style="text-decoration: underline;color: var(--base-color-main)" data-target="#modal_turn_<?php echo $value['id']; ?>"><?php echo $this->lang->line('detail'); ?></a>)
											<?php endif; ?>
											<?php if ($value['type']>1): ?>
												<?php echo $this->lang->line('used'); ?> (<?php echo $value['max_use']-$value['remaining'] ?>/<?php echo $value['max_use'] ?>)
											<?php endif; ?>
										</small>
										<div class="modal fade" id="modal_turn_<?php echo $value['id']; ?>" tabindex="-1" role="dialog"  aria-hidden="true">
											<div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
												<div class="modal-content">
													<div class="modal-header" style="background-color: var(--base-color-main)">
														<h5 class="modal-title text-white" ><?php echo $this->lang->line('detail_do_turn'); ?></h5>
														<button type="button" class="close" data-dismiss="modal" aria-label="Close">
															<span aria-hidden="true">&times;</span>
														</button>
													</div>
													<div class="modal-body">
														<h5 class="text-danger font-weight-bold">** <?php echo $this->lang->line('just_do_some_turn'); ?></h5>
														<hr class="mt-1 mb-1">
														<div class="row">
															<?php foreach (game_code_list() as $game_code): ?>
																<?php if(isset($value['turn_'.strtolower($game_code)])): ?>
																	<div class="col-md-6">
																		<strong class=""><?php echo array_key_exists($game_code,game_code_text_list()) ? game_code_text_list()[$game_code] : $game_code; ?></strong> : <span class="float-right mr-2"><?php echo is_numeric($value['turn_'.strtolower($game_code)]) && (float)$value['turn_'.strtolower($game_code)] >0 ? $value['turn_'.strtolower($game_code)].' เท่า' : '-'; ?></span>
																	</div>
																<?php endif; ?>
															<?php endforeach; ?>
														</div>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-success" data-dismiss="modal"><?php echo $this->lang->line('ok'); ?></button>
													</div>
												</div>
											</div>
										</div>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="input-group mb-4">
						<div class="input-group-prepend">
							<span class="input-group-text"><img src="<?php echo base_url(); ?>assets/images/money.png" alt="" width="25"></span>
						</div>
						<vue-numeric :disabled="pre_loader" :readonly="true" :placeholder="'กรอกจำนวนเงิน'" :class="'form-control'" id="deposit_amount" :read-only="false"  v-bind:precision="2" v-bind:value="amount_deposit" separator=","></vue-numeric>
					</div>
					<div class="">
						<div class="" style="">
							<button :disabled="pre_loader" type="button" @click.prevent="doDeposit()" class="btn-red btn-lg btn-block" style="font-size: 20px" name="button">
								<i class="fa fa-wallet"></i> <?php echo $this->lang->line('deposit'); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php if ($bank!="" || $bank_all): ?>
	<div class="card bank-info mt-4">
		<div class="card-body">
			<div class="deposit-box-top">
				<div class="text-center text-dark">
					<span style="font-size:1.3em;"><?php echo $this->lang->line('plase_used_account_registerd_tran'); ?></span>
				</div>
				<div class="text-center text-danger mb-2 mt-2" style="color: red" v-if="!username_exist">
					<span style="font-size:1.3em;" class="blink_text"><i class="fa fa-exclamation-triangle mr-2"></i><?php echo $this->lang->line('plese_tranfer_firstime'); ?> <vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="amount_deposit_first" separator=","></vue-numeric> บาทเพื่อเปิดยูสเซอร์เล่นเกมส์</span>
				</div>
				<div class="text-center text-danger mb-2 mt-2" v-if="amount_deposit <= 0">
					<span style="font-size:1.3em;"><?php echo $this->lang->line('system_detect_amount_auto'); ?></span>
				</div>
				<div class="text-center text-dark mb-1">
					<span style="font-size:1.3em;"><?php echo $this->lang->line('bank_number_deposit'); ?> : <?php echo getBankNumberFormat($user['bank_number']); ?></span>
				</div>
				<div class="text-center text-warning mb-1">
					<a href="<?php echo $web_setting['line_url']['value']; ?>" class="text-warning" target="_blank">
			<span style="font-size:1.0em;"><?php echo $this->lang->line('issue_contact_admin'); ?><span class="text-success"><?php echo isset($web_setting['line_id']) ? $web_setting['line_id']['value'] : ''; ?></span>
			</span>
					</a>
				</div>
			</div>
		</div>
	</div>
		<div class="mx-auto mb-4 deposit-box " v-if="show_bank">
			<div class="card bank-info mt-4">
				<h5 class="card-header" style="background-image: linear-gradient(to right, gold , yellow);" ><?php echo $this->lang->line('account_details_bank'); ?></h5>
				<div class="card-body">
				<?php foreach ($bank_all as $key => $value): ?>
				<div class="d-flex justify-content-between p-1 p-lg-3">
					<div class="align-self-center text-center">
						<?php
						$backend_url = $this->config->item('backend_url');
						?>	
						<div class="text-center">
							<?php switch($value['bank_code']):
								case '01': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/1.png" ?>"  />
									<?php break; ?>
								<?php case '02': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/2.png" ?>"  />
									<?php break; ?>
								<?php case '03': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/3.png" ?>"  />
									<?php break; ?>
								<?php case '04': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/5.png" ?>"  />
									<?php break; ?>
								<?php case '05': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/6.png" ?>"  />
									<?php break; ?>
								<?php case '06': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/4.png" ?>" />
									<?php break; ?>
								<?php case '07': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/7.png" ?>"  />
									<?php break; ?>
								<?php case '08': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/9.png" ?>"  />
									<?php break; ?>
								<?php case '09': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/baac.png" ?>"  />
									<?php break; ?>
								<?php case '10': ?>
									<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/10.png" ?>"  />
									<?php break; ?>
								<?php default: ?>
									<?php break; ?>
								<?php endswitch; ?>
						</div>
					</div>
					<div class="deposit-content align-self-center ml-sm-0 ml-3 d-none d-sm-block">
						<h5 class="">
							<?php echo $this->lang->line('bank_number'); ?> : <?php echo getBankNumberFormat($value['bank_number']); ?>
						</h5>
						<h5 class="">
							<?php echo $this->lang->line('bank_name'); ?> : <?php echo $value['account_name']; ?>
						</h5>
						<h5 class="">
							<?php echo $this->lang->line('bank_code'); ?> : <?php echo array_key_exists($value['bank_code'],getBankList()) ? getBankList()[$value['bank_code']] : '-'; ?>
						</h5>
					</div>
					<div class="deposit-content align-self-center ml-sm-0 ml-3  d-block d-sm-none">
						<h6 class="">
							<?php echo $this->lang->line('bank_number'); ?> : <?php echo getBankNumberFormat($value['bank_number']); ?>
						</h6>
						<h6 class="">
							<?php echo $this->lang->line('bank_name'); ?> : <?php echo $value['account_name']; ?>
						</h6>
						<h6 class="">
							<?php echo $this->lang->line('bank_code'); ?> : <?php echo array_key_exists($value['bank_code'],getBankList()) ? getBankList()[$value['bank_code']] : '-'; ?>
						</h6>
					</div>
					<div class="align-self-center text-center">
						<div class="border-copy-silver">
							<button type="button" @click.prevent="copyBankAcc('<?php echo $value['bank_number']; ?>')" class="btn-dark btn-lg btn-block pl-2 pr-2 pt-0 pb-0 pl-sm-4 pr-sm-4 pt-sm-3 pb-sm-3">
								<span style="color: white !important;"><?php echo $this->lang->line('copy'); ?><br><?php echo $this->lang->line('bank_number'); ?></span>
							</button>
						</div>
					</div>
				</div>
				<hr>
				<?php endforeach; ?>
			</div>
			</div>
		</div>
		<div v-else class="mt-2 mb-2 mx-auto mb-4 deposit-box">
			<div class="card bank-info mt-4">
				<h5 class="card-header"><?php echo $this->lang->line('account_detail'); ?></h5>
				<div class="card-body">
					<div class="text-center text-danger mb-3 " style="color: red">
						<div class="row mx-auto justify-content-center">
							<div class="col-6 col-lg-6 text-center px-2">
								<a href="<?php echo $web_setting['line_url']['value']; ?>" target="_blank">
									<img class="mb-2" src="<?php echo base_url(); ?>assets/images/line-logo.png" alt="" width="50%">
									<div class="text-success">
										<h5>ID LINE :</h5>
									</div>
									<div class="text-dark">
										<h5><?php echo isset($web_setting['line_id']) ? $web_setting['line_id']['value'] : ''; ?></h5>
									</div>
								</a>
							</div>
						</div>
						<span style="font-size:1.5em;" class="blink_text">{{message_can_not_deposit}}...</span>
					</div>
				</div>
			</div>

		</div>
	<?php else: ?>
		<div class="mx-auto mb-4 deposit-box ">
			<div class="card bank-info mt-4">
				<h5 class="card-header"><?php echo $this->lang->line('account_details_bank'); ?></h5>
				<div class="card-body">
					<div class="d-flex justify-content-center p-1 p-lg-3">
						<div class="align-self-center text-center">
							<div class="text-center text-danger mb-3">
								<span style="font-size:1.3em;"><?php echo $this->lang->line('temporarily_closed'); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($bank['status']=='2'): ?>
		<div class="mx-auto mb-4 deposit-box ">
			<div class="card bank-info mt-4">
				<h5 class="card-header"><?php echo $this->lang->line('uploadslip_bank'); ?></h5>
				<div class="card-body">
					<div class="d-flex justify-content-center p-1 p-lg-3">
						<div class="align-self-center text-center">
							<!-- <form class="" action="<?php echo site_url('qrcode/upload') ?>" method="post" enctype="multipart/form-data"> -->
								<div class="text-center text-danger mb-3">
									<input type="file" class="form-control" id="file" required>
								</div>
								<div class="form-group">
									<button :disabled="pre_loader" type="button" @click.prevent="doUpload()" type="button" class="btn-red btn-lg btn-block" style="font-size: 20px" name="button">
										<i class="fa fa-check"></i> <?php echo $this->lang->line('upload'); ?>
									</button>
								</div>
							<!-- </form> -->
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

    <?php if ($promptpay == 1):?>
		<div class="mx-auto mb-4 deposit-box " v-if="show_bank" style="<?php echo isset($bank_truewallet_chk) && $bank_truewallet_chk ? 'padding:5px;border-radius: 1rem;border:1px solid white;max-width: 95%' : '';?>">
			<div class="card bank-info mt-4">
                <h5 class="card-header"><?php echo $this->lang->line('deposit_by_qrcode'); ?></h5>
                <div class="card-body">
                    <div class="d-flex justify-content-between p-1 p-lg-3">
                        <div class="align-self-center text-center mx-1">
                            <div class="text-center">
                                <img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/promptpay.png" ?>"  />
                            </div>
                        </div>
                        <div class="deposit-content align-self-center ml-sm-0 ml-3 d-none d-sm-block">
                            <div class="">
                                
                            </div>
                            <h6 class="">
                                <input type="number" placeholder="กรอกจำนวนเงิน" class="form-control" v-model="qrcode_amount"/>
                            </h6>
                            <div class="">

                            </div>
                        </div>
                        <div class="align-self-center text-center mx-1">
                            <div class="border-copy-silver">
                                <button @click.prevent="getQrCode()" class="btn-dark btn-lg btn-block pl-2 pr-2 pt-0 pb-0 pl-sm-4 pr-sm-4 pt-sm-3 pb-sm-3">
                                    <span style="color: white !important;"><?php echo $this->lang->line('recieve_qrcode'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	<?php endif; ?>

	<?php if ($bank_truewallet!=""): ?>
		<div class="mx-auto mb-4 deposit-box ">
			<div class="card bank-info mt-4">
				<h5 class="card-header" style="background-image: linear-gradient(to right, gold , yellow);"><?php echo $this->lang->line('account_details_truewallet'); ?></h5>
				<div class="card-body">
					<div class="d-flex justify-content-between p-1 p-lg-3">
						<div class="align-self-center text-center">
							<?php
							$backend_url = $this->config->item('backend_url');
							?>
							<div class="text-center">
								<img class="bank-deposit-img" src="<?php echo "{$backend_url}/assets/images/bank/10.png" ?>"  />
							</div>
						</div>
						<div class="deposit-content align-self-center ml-sm-0 ml-3 d-none d-sm-block">
							<h5 class="">
								<?php echo $bank_truewallet['bank_code'] == "10" ? $this->lang->line('tel') : $this->lang->line('bank_number') ?> : <?php echo getBankNumberFormat($bank_truewallet['bank_number'],$bank_truewallet['bank_code']); ?>
							</h5>
							<h5 class="">
								<?php echo $this->lang->line('bank_name'); ?> : <?php echo $bank_truewallet['account_name']; ?>
							</h5>
							<h5 class="">
								<?php echo $this->lang->line('bank_code'); ?> : <?php echo array_key_exists($bank_truewallet['bank_code'],getBankList()) ? getBankList()[$bank_truewallet['bank_code']] : '-'; ?>
							</h5>
						</div>
						<div class="deposit-content align-self-center ml-sm-0 ml-3  d-block d-sm-none">
							<h6 class="">
								<?php echo $bank_truewallet['bank_code'] == "10" ? $this->lang->line('tel') : $this->lang->line('bank_number') ?> : <?php echo getBankNumberFormat($bank_truewallet['bank_number'],$bank_truewallet['bank_code']); ?>
							</h6>
							<h6 class="">
								<?php echo $this->lang->line('bank_name'); ?> : <?php echo $bank_truewallet['account_name']; ?>
							</h6>
							<h6 class="">
								<?php echo $this->lang->line('bank_code'); ?> : <?php echo array_key_exists($bank_truewallet['bank_code'],getBankList()) ? getBankList()[$bank_truewallet['bank_code']] : '-'; ?>
							</h6>
						</div>
						<div class="align-self-center text-center">
							<div class="border-copy-silver">
								<button type="button" @click.prevent="copyBankAcc('<?php echo $bank_truewallet['bank_number']; ?>','คัดลอกเบอร์แล้ว')" class="btn-dark btn-lg btn-block pl-2 pr-2 pt-0 pb-0 pl-sm-4 pr-sm-4 pt-sm-3 pb-sm-3">
									<span style="color: white !important;"><?php echo $this->lang->line('copy'); ?><br><?php echo $this->lang->line('tel'); ?></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else: ?>
		<div class="mx-auto mb-4 deposit-box ">
			<div class="card bank-info mt-4">
				<h5 class="card-header"><?php echo $this->lang->line('account_details_truewallet'); ?></h5>
				<div class="card-body">
					<div class="d-flex justify-content-center p-1 p-lg-3">
						<div class="align-self-center text-center">
							<div class="text-center text-danger mb-3">
								<span style="font-size:1.3em;"><?php echo $this->lang->line('temporarily_closed'); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="withdraw-history">
		<hr>
		<div class="text-center text-dark title-withdraw-history">
			<h3><?php echo $this->lang->line('deposit_hist_20_list'); ?></h3>
		</div>
		<div class="table-deposit mx-auto">
			<table class="table table-striped">
				<thead class="bg-darkred-2">
				<tr class="text-white bg-success">
					<th class="text-center"><?php echo $this->lang->line('index'); ?></th>
					<th class="text-left" width="40%"><?php echo $this->lang->line('datetime'); ?></th>
					<th class="text-center" width="10%"><?php echo $this->lang->line('type'); ?></th>
					<th class="text-right" width="15%"><?php echo $this->lang->line('amount'); ?></th>
					<th class="text-center"><?php echo $this->lang->line('status'); ?></th>
				</tr>
				</thead>
				<tbody class="bg-white">
				<tr v-for="(result,index) in results">
					<th class="text-center">{{results.length - index}}</th>
					<td class="text-left">{{result.created_at}}</td>
					<td class="text-center">
						<span v-if="result.type == '1'"><?php echo $this->lang->line('deposit'); ?></span>
						<span v-else><?php echo $this->lang->line('withdraw'); ?></span>
					</td>
					<td class="text-right">
						<vue-numeric :read-only="true" empty-value="0.00" output-type="String" v-bind:precision="2" v-bind:value="result.amount || 0" separator=","></vue-numeric><span class="ml-1">฿</span>
					</td>
					<td class="text-center">
						<span v-if="result.status == '0'"><?php echo $this->lang->line('inprogress'); ?></span>
						<span v-else-if="result.status == '1'"><?php echo $this->lang->line('success'); ?></span>
						<span v-else-if="result.status == '2'"><?php echo $this->lang->line('unsuccess'); ?></span>
						<span v-else><?php echo $this->lang->line('inprogress'); ?></span>
					</td>
				</tr>
				<tr v-if="results.length == 0">
					<td colspan="5" class="text-center"><?php echo $this->lang->line('nodata'); ?></td>
				</tr>
				</tbody>
			</table>
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
	// const bank = '<?php echo json_encode($bank) ?>';
	// const bank_all = '<?php echo json_encode($bank_all); ?>';
	const amount_deposit = '<?php echo $user['amount_deposit_auto']; ?>';
	// const promotion = '<?php echo json_encode($promotion); ?>';
	const promotion_active = '<?php echo $promotion_active; ?>';
	const auto_accept_bonus_active ='<?php echo $auto_accept_bonus;?>';
	const bank_start_time_can_not_deposit = '<?php echo $bank!="" && isset($bank['start_time_can_not_deposit']) ? $bank['start_time_can_not_deposit'] : null; ?>';
	const bank_end_time_can_not_deposit = '<?php echo $bank!="" && isset($bank['end_time_can_not_deposit']) ? $bank['end_time_can_not_deposit'] : null; ?>';
	const message_can_not_deposit = '<?php echo $bank!="" && isset($bank['message_can_not_deposit']) ? preg_replace("/[\n\r]/","", $bank['message_can_not_deposit'] ) : null; ?>';
	const bank_id = '<?php echo $bank!="" && isset($bank['id']) ? $bank['id'] : null; ?>';
	const username_exist = "<?php echo $auto_create_member!="" && $auto_create_member['value'] == "0" && empty($_SESSION['user']['member_username']) ? false : true ?>";
</script>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/deposit.js?').time() ?>"></script>
