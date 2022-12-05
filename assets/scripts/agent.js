Vue.component('paginate', VuejsPaginate)
var agent_page = new Vue({
	el: "#"+page_id,
	data (){
		return {
			user_id : user_id,
			results : {
				ref :{
					page_count : 0,
					page : 1,
					from : 0,
					to : 0,
					per_page : 20,
					total : 0,
					results : [],
				},
				report :{
					page_count : 0,
					page : 1,
					from : 0,
					to : 0,
					per_page : 20,
					total : 0,
					results : [],
					result_totals : [],
				},
				commission :{
					page_count : 0,
					day_start : "",
					day_end : "",
					month_text : "",
					page : 1,
					from : 0,
					to : 0,
					per_page : 20,
					total : 0,
					results : [],
					result_totals : [],
				}
			},
			search :{
				report : {
					year : year,
					month : month,
				},
				commission : {
					year : year,
					month : month
				},
				all : {
					year : year,
					month : month,
					date_start : moment().startOf('month').format('YYYY-MM-DD'),
					date_end : moment().endOf('month').format('YYYY-MM-DD'),
				}
			},
			loading_ref : false,
			loading_report : false,
			loading_commission : false,
			pre_loader : true,
			interval_ref_list : null,
			interval_report_list : null,
			interval_commission_list : null,
		}

	},
	mounted(){
		let app =this
		app.pre_loader = false;
		app.getReportList(1);
		/*app.interval_report_list = setInterval(function(){
			app.getReportList(app.results.report.page);
		}, 15000);*/
		app.getCommissionList(false);
	/*	app.interval_commission_list = setInterval(function(){
			app.getCommissionList(false);
		}, 15000);*/
		app.getRefList(1);
		app.interval_ref_list = setInterval(function(){
			app.getRefList(app.results.ref.page);
		}, 15000);
		new QRCode(document.getElementById("qrcode"), BaseURL + 'register?ref=' + app.user_id);
	},
	methods: {
		copyLinkRef(url){
			let $temp = $("<input>");
			$("body").append($temp);
			$temp.val(url).select();
			document.execCommand("copy");
			$temp.remove();
			let Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 2500
			});
			Toast.fire({
				type: 'success',
				title: 'คัดลอกลิงค์แล้ว'
			});
		},
		getCommissionAndReportList(force_load,page){
			let app = this;
			if(app.search.all.date_start == "" || app.search.all.date_end == ""){
				sweetAlert2('warning', 'กรุณาระบุวันที่ (จาก) - วันที่ (ถึง)');
			}else if(moment(app.search.all.date_start).unix() > moment(app.search.all.date_end).unix()){
				sweetAlert2('warning', 'วันที่ (จาก) ไม่ควรมากกว่า วันที่ (ถึง)');
			}else if(moment(app.search.all.date_end).diff(moment(app.search.all.date_start),'days') > 60){
				sweetAlert2('warning', 'วันที่ (จาก) - วันที่ (ถึง) ไม่ควรห่างกันเกิน 60 วัน');
			}else{
				app.getCommissionList(force_load);
				app.getReportList(page,force_load);
			}

		},
		getReportList(page,force_load){
			let app = this;
			app.results.report.page = page;
			if(!app.loading_report || (typeof(force_load) != "undefined" && force_load)){
				app.loading_report = true;
				if(typeof(force_load) != "undefined" && force_load){
					app.pre_loader = true;
				}
				axios.get(BaseURL + "agent/report_member_list",{
					params: {
						per_page: app.results.report.per_page,
						page: app.results.report.page,
						date_start: app.search.all.date_start,
						date_end:  app.search.all.date_end,
						//year: app.search.all.year,
						//month: app.search.all.month,
					}
				})
					.then(function (response) {
						app.loading_report = false
						if(typeof(force_load) != "undefined" && force_load){
							app.pre_loader = false;
						}
						if (response.data.result) {
							app.results.report.results = response.data.result
							if (response.data.result_total) {
								app.results.report.result_totals = response.data.result_total
							}
							app.results.report.page = response.data.page
							app.results.report.page_count = response.data.page_count
							app.results.report.from = response.data.from
							app.results.report.to = response.data.to
							app.results.report.total = response.data.total
							app.results.report.per_page = response.data.per_page;
						}else{
							if(response.data.message){
								sweetAlert2('warning', response.data.message);
							}else{
								sweetAlert2('warning', 'วันที่ (จาก) - วันที่ (ถึง) ไม่ควรห่างกันเกิน 60 วัน');
							}
						}

					}).catch(err=>{
					sweetAlert2('warning', 'ไม่สามารถทำรายการได้');
					app.results.report.results = []
					app.results.report.result_totals = []
					app.loading_report = false
					if(typeof(force_load) != "undefined" && force_load){
						app.pre_loader = false;
					}
					app.results.report.page_count = 0
					app.results.report.from = 0
					app.results.report.to = 0
					app.results.report.total = 0
					app.results.report.per_page = 0;
				});
			}

		},
		getCommissionList(force_load){
			let app = this;
			if(!app.loading_commission || (typeof(force_load) != "undefined" && force_load)){
				app.loading_commission = true;
				if(typeof(force_load) != "undefined" && force_load){
					app.pre_loader = true;
				}
				axios.get(BaseURL + "agent/report_commission_list",{
					params: {
						//year: app.search.all.year,
						//month: app.search.all.month,
						date_start: app.search.all.date_start,
						date_end:  app.search.all.date_end,
					}
				})
					.then(function (response) {
						app.loading_commission = false
						if(typeof(force_load) != "undefined" && force_load){
							app.pre_loader = false;
						}
						if (response.data.result) {
							app.results.commission.results = response.data.result
							if (response.data.result_total) {
								app.results.commission.result_totals = response.data.result_total
							}
						}else{
							if(response.data.message){
								sweetAlert2('warning', response.data.message);
							}else{
								sweetAlert2('warning', 'วันที่ (จาก) - วันที่ (ถึง) ไม่ควรห่างกันเกิน 60 วัน');
							}
						}
						/*if (response.data.result_total.day_start) {
							app.results.commission.day_start = response.data.result_total.day_start;
						}
						if (response.data.result_total.day_end) {
							app.results.commission.day_end = response.data.result_total.day_end
						}
						if (response.data.result_total.month_text) {
							app.results.commission.month_text = response.data.result_total.month_text
						}*/

					}).catch(err=>{
					sweetAlert2('warning', 'ไม่สามารถทำรายการได้');
					app.results.commission.results = []
					app.results.commission.result_totals = []
					app.loading_commission = false
					if(typeof(force_load) != "undefined" && force_load){
						app.pre_loader = false;
					}
				});
			}

		},
		getRefList(page){
			let app = this;
			app.results.ref.page = page;
			if(!app.loading_ref){
				app.loading_ref = true;
				axios.get(BaseURL + "agent/ref_list",{
						params: {
							per_page: app.results.ref.per_page,
							page: app.results.ref.page,
						}
					})
					.then(function (response) {
						app.loading_ref = false
						if (response.data.result) {
							app.results.ref.results = response.data.result
						}
						app.results.ref.page = response.data.page
						app.results.ref.page_count = response.data.page_count
						app.results.ref.from = response.data.from
						app.results.ref.to = response.data.to
						app.results.ref.total = response.data.total
						app.results.ref.per_page = response.data.per_page;
					}).catch(err=>{
						app.results.ref.results = []
						app.loading_ref = false
						app.results.ref.page_count = 0
						app.results.ref.from = 0
						app.results.ref.to = 0
						app.results.ref.total = 0
						app.results.ref.per_page = 0;
				});
			}

		},
		changeDateStart(date_start){
			let app = this;
			app.search.all.date_start = date_start;
		},
		changeDateEnd(date_end){
			let app = this;
			app.search.all.date_end = date_end;
		}
	}
});
$(document).ready(function(){
	$('#date_start_commission').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {
		agent_page.changeDateStart(e.target.value);
		$('#date_start_report').datepicker('update',e.target.value);
	});
	$('#date_start_commission').datepicker('update',moment().startOf('month').format('YYYY-MM-DD'));
	$('#date_end_commission').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {
		agent_page.changeDateEnd(e.target.value);
		$('#date_end_report').datepicker('update',e.target.value);
	});
	$('#date_end_commission').datepicker('update',moment().endOf('month').format('YYYY-MM-DD'));
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {
		agent_page.changeDateStart(e.target.value);
		$('#date_start_commission').datepicker('update',e.target.value);
	});
	$('#date_start_report').datepicker('update',moment().startOf('month').format('YYYY-MM-DD'));
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {
		agent_page.changeDateEnd(e.target.value);
		$('#date_end_commission').datepicker('update',e.target.value);
	});
	$('#date_end_report').datepicker('update',moment().endOf('month').format('YYYY-MM-DD'));
});
