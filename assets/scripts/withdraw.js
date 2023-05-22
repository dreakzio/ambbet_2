

new Vue({
	el: "#"+page_id,
	data (){
		return {
			withdraw_all_status : withdraw_all_status,
			withdraw_min_amount : withdraw_min_amount,
			amount : 0,
			amount_withdraw : 0,
			loading_wallet : false,
			pre_loader : true,
			loading_history : false,
			results : [],
			interval_history_list : null,
			interval_amount : null,
		}

	},
	mounted(){
		let app =this
		app.pre_loader = false;
		app.getCreditBalance();
		app.getList();
		app.interval_amount = setInterval(function(){
			app.getCreditBalance();
		}, 8000);
		app.interval_history_list = setInterval(function(){
			app.getList();
		}, 10000);
	},
	methods: {
		doWithdraw(){
			let app = this;
			if(!app.pre_loader){
				if (app.amount_withdraw.toString().length == 0) {
					sweetAlert2('warning', 'กรุณาระบุยอดที่ต้องการถอน');
				}
				else if (Number(app.amount_withdraw) == 0) {
					sweetAlert2('warning', 'ยอดถอนต้องมากกว่า 0');
				}else if(app.amount_withdraw.toString().indexOf(".") >= 0){
					sweetAlert2('warning', 'ยอดถอนต้องไม่มีทศนิยม');
				}
				else {
					if (Number(app.amount_withdraw) > Number(app.amount)) {
						sweetAlert2('warning', 'ยอดคงเหลือไม่เพียงพอ');
					}else if(Number(app.amount_withdraw) < app.withdraw_min_amount) {
						sweetAlert2('warning', 'ยอดถอนต้องมากกว่าหรือเท่ากับ : '+numeral(app.withdraw_min_amount).format('0,0')+" บาท");
					} else {
						Swal.fire({
							text: 'ยืนยันการถอนเงิน ' + numeral(app.amount_withdraw).format('0,0') + ' ฿',
							confirmButtonText: 'ตกลง',
							confirmButtonColor: '#2ABA66',
							showCancelButton: true,
							cancelButtonText: 'ยกเลิก',
							cancelButtonColor: 'red',
							reverseButtons: true,
						})
							.then((result) => {
								if (result.value) {
									app.pre_loader = true;
									axios.post(BaseURL + "withdraw/withdraw_credit",
										Qs.stringify({
											amount: Number(app.amount_withdraw)
										})
										,{
											'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
										})
										.then(function (response) {
											app.pre_loader = false

									
											if (response.data.result) {


												app.amount_withdraw = 0;
												setTimeout(function(){
													app.getCreditBalance();
												},3500);
												app.getList();

												// app.amount_withdraw = 0;
												// setTimeout(function(){
												// 	app.getCreditBalance();
												// },3500);
												// app.getList();

												if(withdraw_auto_status == 0){

													Swal.fire({
														type: 'success',
														// title: 'แจ้งเตือน',
														text: 'ทำรายการถอนสำเร็จ โปรดตรวจสอบข้อมูล',
														confirmButtonText: 'ตกลง',
														confirmButtonColor: '#2ABA66',
														allowOutsideClick: false
													})
													.then((result) => {

															if (result.value) {

																location.reload();
	
															}
													});

												}else if (withdraw_auto_status == 1){


													Swal.fire({
														type: 'success',
														// title: 'แจ้งเตือน',
														text: 'ทำรายการถอนสำเร็จ โปรดตรวจสอบข้อมูล',
														confirmButtonText: 'ตกลง',
														confirmButtonColor: '#2ABA66',
														allowOutsideClick: false
													})
													.then((result) => {
														
															if (result.value) {

																location.reload();
	
															}
													});

												// direct withdraw

												// $.ajax({
												// 	url: BaseURL + "admin/withdraw/withdraw_status/" + response.data.finance_id,
												// 	method: "POST",
												// 	dataType: 'json',
												// 	data: {
												// 		status: 1,
												// 		direct: 1
												// 	},
												// 	success: function(response) {
												// 		loading_withdraw_auto = false;
												// 		if (response.result) {

												// 		 Swal.fire({
												// 			type: 'success',
												// 			// title: 'แจ้งเตือน',
												// 			text: 'ถอนเงินสำเร็จ',
												// 			confirmButtonText: 'ตกลง',
												// 			confirmButtonColor: '#2ABA66',
												// 			allowOutsideClick: false
												// 		})
												// 			.then((result) => {
												// 				if (result.value) {

												// 					location.reload();

												// 				}
												// 			});
							
												// 		} else {
												// 			loading_withdraw_auto = false;
												// 			if(response.error && response.message){
												// 				Swal.fire({
												// 					type: 'warning',
												// 					title: 'แจ้งเตือน',
												// 					text: 'รอตรวจสอบข้อมูลการถอน',
												// 					confirmButtonText: 'ตกลง',
												// 					confirmButtonColor: '#7cd1f9',
												// 				}).then(()=>{

												// 					// location.reload();
												
												// 				});
												// 			}else{
												// 				Swal.fire({
												// 					type: 'warning',
												// 					title: 'แจ้งเตือน',
												// 					text: "รอตรวจสอบข้อมูลการถอน",
												// 					confirmButtonText: 'ตกลง',
												// 					confirmButtonColor: '#7cd1f9',
												// 				}).then(()=>{

												// 					// location.reload();
												
												// 				});
												// 			}
							
												// 		}
												// 	},
												// 	error: function() {
												// 		loading_withdraw_auto = false;
												// 		Swal.fire({
												// 			type: 'warning',
												// 			title: 'แจ้งเตือน',
												// 			text: "ทำรายการไม่สำเร็จ",
												// 			confirmButtonText: 'ตกลง',
												// 			confirmButtonColor: '#7cd1f9',
												// 		}).then(()=>{
												// 			// location.reload();
												// 		});
												// 	}
												// });

												// end of direct withdraw

												}
				

									
											} else {
												app.pre_loader = false
												sweetAlert2('warning', response.data.message);
											}



										}).catch(err=>{
										app.pre_loader = false
										sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
									});
								}else{
									app.pre_loader = false
								}
							});
					}
				}
			}
		},
		getCreditBalance(){
			let app = this;
			if(!app.loading_wallet){
				app.loading_wallet = true;
				axios.get(BaseURL + "account/remaining_credit")
					.then(function (response) {
						app.loading_wallet = false
						if (response.data.result) {
							app.amount = response.data.result
						}
					}).catch(err=>{
					app.loading_wallet = false
				});
			}
		},
		getList(){
			let app = this;
			if(!app.loading_history){
				app.loading_history = true;
				axios.get(BaseURL + "account/history_list?type=2")
					.then(function (response) {
						app.loading_history = false
						if (response.data.result) {
							app.results = response.data.result
						}
					}).catch(err=>{
					app.loading_history = false
				});
			}

		},
	}
});
