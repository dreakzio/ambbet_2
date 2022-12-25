<section class="ref text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> <?php echo $this->lang->line('back'); ?></a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-handshake"></i>&nbsp;<?php echo $this->lang->line('alliance'); ?></span>
	<hr style="margin-top: 15px">
</section>
<section class="">
	<div class="text-center pb-3">
		<h5 class="mt-3 text-success"><i class="fa fa-credit-card mr-2"></i><?php echo $this->lang->line('money_result'); ?> : <vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="results.commission.result_totals.sum_amount < 0 ? 0 : results.commission.result_totals.sum_amount" separator=","></vue-numeric><span class="ml-2"><?php echo $this->lang->line('bath'); ?></span>
		</h5>
		<h6 class="mt-3 text-dark"><?php echo $this->lang->line('installment_date'); ?> : {{search.all.date_start != '' ? moment(search.all.date_start).format('DD/MM/YYYY')+' <?php echo $this->lang->line('to'); ?> '+moment(search.all.date_end).format('DD/MM/YYYY') : '-'}}
		</h6>
		<h6 class="mt-3 text-dark"><?php echo $this->lang->line('commission'); ?> : <?php echo $user['commission_percent']; ?> %
		</h6>
		<h6 class="mt-3 text-dark"><i class="fa fa-users mr-2"></i><?php echo $this->lang->line('userundertotal'); ?> : <vue-numeric  :read-only="true" v-bind:value="results.ref.total" separator=","></vue-numeric><span class="ml-2"><?php echo $this->lang->line('people'); ?></span>
		</h6>
	</div>
	<div class="red-line mb-2" style="width:10%; margin:auto;"></div>
	<div class="menu-bar-box">
		<div class="bg-darkred mb-2">
			<div class="">
				<div class="d-flex justify-content-center">
					<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
						<li class="nav-item mx-1 mt-2" role="presentation">
							<a class="nav-link text-center active" id="pills-commission-tab" data-toggle="pill" href="#pills-commission" role="tab" aria-controls="pills-commission" aria-selected="true"><i class="fa fa-gift  mr-1" ></i><?php echo $this->lang->line('commission'); ?></a>
						</li>
						<li class="nav-item mx-1 mt-2" role="presentation">
							<a class="nav-link text-center" id="pills-report-tab" data-toggle="pill" href="#pills-report" role="tab" aria-controls="pills-report" aria-selected="false"><i class="fa fa-history  mr-1" ></i><?php echo $this->lang->line('deposit_withdraw_summary'); ?></a>
						</li>
						<li class="nav-item mx-1 mt-2" role="presentation">
							<a class="nav-link text-center" id="pills-ref-tab" data-toggle="pill" href="#pills-ref" role="tab" aria-controls="pills-ref" aria-selected="false"><i class="fa fa-handshake mr-1" ></i><?php echo $this->lang->line('his_recommend'); ?></a>
						</li>
						<li class="nav-item mx-1 mt-2" role="presentation">
							<a class="nav-link text-center" id="pills-qrcode-tab" data-toggle="pill" href="#pills-qrcode" role="tab" aria-controls="pills-qrcode" aria-selected="false"><i class="fa  text-dark mr-1" ></i>QR Code</a>
						</li>
					</ul>
				</div>

				<div class="tab-content p-0" id="pills-tabContent">
					<div class="tab-pane fade show active" id="pills-commission" role="tabpanel" aria-labelledby="pills-commission-tab">
						<div class="container pl-0 pr-0">
							<div class="row mx-auto" style="width: 98%">
								<div class="col-12 col-md-10 pr-1 pl-1">
									<div class="input-group input-group-sm mb-3">
										<div class="input-daterange input-group input-group-sm" id="datepicker">
											<div class="input-group-prepend">
												<span class="input-group-text" id="basic-addon1"><?php echo $this->lang->line('date_from'); ?></span>
											</div>
											<input type="text" class="input-sm form-control" v-model="search.all.date_start" id="date_start_commission" name="start" />
											<div class="input-group-prepend">
												<span class="input-group-text" id="basic-addon1"><?php echo $this->lang->line('date_to'); ?></span>
											</div>
											<input type="text" class="input-sm form-control" v-model="search.all.date_end" id="date_end_commission" name="end" />
										</div>
									</div>
								</div>
								<div class="col-12 col-md-2 mx-auto">
									<div class="form-group">
										<button type="button" @click.prevent="getCommissionAndReportList(true,1)" name="button" class="btn btb-light btn-block border-0 btn-sm"><span class="text-silver"><?php echo $this->lang->line('search'); ?></span></button>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12 mx-auto pl-1 pr-1">
									<div class="table-history mx-auto" style="width: 95% !important;">
										<table class="table table-striped">
											<thead class="bg-darkred-2">
											<tr class="text-white bg-success">
												<th class="text-center"><?php echo $this->lang->line('date'); ?></th>
												<th class="text-right"><?php echo $this->lang->line('deposit_amount'); ?></th>
												<th class="text-right"><?php echo $this->lang->line('deposit_withdraw'); ?></th>
												<th class="text-right"><?php echo $this->lang->line('bad_amount'); ?></th>
											</tr>
											</thead>
											<tbody class="bg-white">
											<tr v-for="result in results.commission.results">
												<th class="text-center">{{result.day}}</th>
												<td class="text-right"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="result.deposit" separator=","></vue-numeric></td>
												<td class="text-right"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="result.withdraw" separator=","></vue-numeric></td>
												<td class="text-right"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="result.sum" separator=","></vue-numeric></td>
											</tr>
											<tr v-if="results.commission.results.length == 0">
												<td colspan="4" class="text-center"><?php echo $this->lang->line('nodata'); ?></td>
											</tr>
											</tbody>
											<tfoot>
											<tr style="background-color: lightgray">
												<td class="text-center text-dark"><strong>{{results.commission.result_totals.day}}</strong></td>
												<td class="text-right text-dark"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="results.commission.result_totals.deposit" separator=","></vue-numeric></td>
												<td class="text-right text-dark"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="results.commission.result_totals.withdraw" separator=","></vue-numeric></td>
												<td class="text-right text-dark"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="results.commission.result_totals.sum_amount" separator=","></vue-numeric></td>
											</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="pills-report" role="tabpanel" aria-labelledby="pills-report-tab">
						<div class="container pl-0 pr-0">
							<div class="row mx-auto" style="width: 98%">
								<div class="col-12 col-md-10 pr-1 pl-1">
									<div class="input-group input-group-sm mb-3">
										<div class="input-daterange input-group input-group-sm" id="datepicker">
											<div class="input-group-prepend">
												<span class="input-group-text" id="basic-addon1"><?php echo $this->lang->line('date_from'); ?></span>
											</div>
											<input type="text" class="input-sm form-control" v-model="search.all.date_start" id="date_start_report" name="start" />
											<div class="input-group-prepend">
												<span class="input-group-text" id="basic-addon1"><?php echo $this->lang->line('date_to'); ?></span>
											</div>
											<input type="text" class="input-sm form-control" v-model="search.all.date_end" id="date_end_report" name="end" />
										</div>
									</div>
								</div>
								<div class="col-12 col-md-2 mx-auto">
									<div class="form-group">
										<button type="button" @click.prevent="getCommissionAndReportList(true,1)" name="button" class="btn btn-block btn-custom border-0 btn-sm"><span class="text-silver"><?php echo $this->lang->line('search'); ?></span></button>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12 mx-auto pr-1 pl-1">
									<div class="table-history mx-auto" style="width: 95% !important;">
										<table class="table table-striped">
											<thead class="bg-darkred-2">
											<tr class="text-white bg-success">
												<th class="text-center"><?php echo $this->lang->line('user'); ?></th>
												<th class="text-right"><?php echo $this->lang->line('deposit_amount'); ?></th>
												<th class="text-right"><?php echo $this->lang->line('deposit_withdraw'); ?></th>
												<th class="text-right"><?php echo $this->lang->line('bad_amount'); ?></th>
											</tr>
											</thead>
											<tbody class="bg-white">
											<tr v-for="result in results.report.results">
												<th class="text-center">{{result.username}}</th>
												<td class="text-right"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="result.deposit" separator=","></vue-numeric></td>
												<td class="text-right"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="result.withdraw" separator=","></vue-numeric></td>
												<td class="text-right"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="result.sum" separator=","></vue-numeric></td>
											</tr>
											<tr v-if="results.report.results.length == 0">
												<td colspan="4" class="text-center"><?php echo $this->lang->line('nodata'); ?></td>
											</tr>
											</tbody>
											<tfoot>
											<tr style="background-color: lightgray">
												<td class="text-center text-dark"><strong>{{results.report.result_totals.username}}</strong></td>
												<td class="text-right text-dark"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="results.report.result_totals.deposit" separator=","></vue-numeric></td>
												<td class="text-right text-dark"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="results.report.result_totals.withdraw" separator=","></vue-numeric></td>
												<td class="text-right text-dark"><vue-numeric  :read-only="true"  v-bind:precision="2" v-bind:value="results.report.result_totals.sum_amount" separator=","></vue-numeric></td>
											</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
							<div class="row justify-content-center">
								<paginate
										:page-count="results.report.page_count"
										:page-range="parseInt(results.report.per_page)"
										:margin-pages="5"
										:prev-text="'«'"
										:force-page="parseInt(results.report.page) - 1"
										:next-text="'»'"
										:container-class="'pagination flex-wrap'"
										:page-link-class="'page-link'"
										:prev-link-class="'page-link'"
										:next-link-class="'page-link'"
										:first-last-button=true
										:last-button-text="'สุดท้าย'"
										:first-button-text="'แรก'"
										:click-handler="getReportList"
										:page-class="'page-item'">
								</paginate>
							</div>
							<div class="row mb-4 justify-content-center">
								<p style="font-size: .9em" class="mb-0 text-muted mt-1 mr-2"><?php echo $this->lang->line('display'); ?> {{results.report.from || 0}} <?php echo $this->lang->line('to'); ?> {{results.report.to || 0}} <?php echo $this->lang->line('from'); ?> {{results.report.total}} <?php echo $this->lang->line('list'); ?></p>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="pills-ref" role="tabpanel" aria-labelledby="pills-ref-tab">
						<div class="container  pl-0 pr-0">
							<div class="row">
								<div class="col-12 mx-auto">
									<div class="table-history mx-lg-auto">
										<table class="table table-striped">
											<thead class="bg-darkred-2">
											<tr class="text-white bg-success">
												<th class="text-center"><?php echo $this->lang->line('user'); ?></th>
												<th class="text-center"><?php echo $this->lang->line('registered_date'); ?></th>
											</tr>
											</thead>
											<tbody class="bg-white">
											<tr v-for="result in results.ref.results">
												<th class="text-center">{{result.to_account_username}}</th>
												<td class="text-center">{{result.created_at}}</td>
											</tr>
											<tr v-if="results.ref.results.length == 0">
												<td colspan="2" class="text-center"><?php echo $this->lang->line('nodata'); ?></td>
											</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div class="row justify-content-center">
								<paginate
										:page-count="results.ref.page_count"
										:page-range="parseInt(results.ref.per_page)"
										:margin-pages="5"
										:prev-text="'«'"
										:force-page="parseInt(results.ref.page) - 1"
										:next-text="'»'"
										:container-class="'pagination flex-wrap'"
										:page-link-class="'page-link'"
										:prev-link-class="'page-link'"
										:next-link-class="'page-link'"
										:first-last-button=true
										:last-button-text="'สุดท้าย'"
										:first-button-text="'แรก'"
										:click-handler="getRefList"
										:page-class="'page-item'">
								</paginate>
							</div>
							<div class="row mb-4 justify-content-center">
							<p style="font-size: .9em" class="mb-0 text-muted mt-1 mr-2"><?php echo $this->lang->line('display'); ?> {{results.report.from || 0}} <?php echo $this->lang->line('to'); ?> {{results.report.to || 0}} <?php echo $this->lang->line('from'); ?> {{results.report.total}} <?php echo $this->lang->line('list'); ?></p>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="pills-qrcode" role="tabpanel" aria-labelledby="pills-qrcode-tab">
						<div class="container pl-0 pr-0">
							<div class="row mx-auto">
								<div class="col-12 text-center mx-auto">
									<center class="">
										<div id="qrcode"></div>
										<button type="button"  @click.prevent="copyLinkRef('<?php echo base_url('register?ref=').$user['id'] ?>')"  class="btn btn-custom border-0 mt-3" name="button">
											<span  class="text-silver"><i class="fa fa-clipboard"></i>&nbsp;<?php echo $this->lang->line('copylink'); ?></span>
										</button>
									</center>
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
	const user_id = "<?php echo $user['id']; ?>"
	const year = "<?php echo date('Y'); ?>"
	const month = "<?php echo date('m'); ?>"
</script>
<link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8"></script>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js?'.date('Y-m-d')) ?>"></script>
<script src="<?php echo base_url('assets/plugins/vue-paginate.js?'.date('Y-m-d')) ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/qrcode/qrcode.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/agent.js?').time() ?>"></script>

