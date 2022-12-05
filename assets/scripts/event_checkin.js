new Vue({
	el: "#"+page_id,
	data (){
		return {
			commission : 0,
			login_turn : login_turn,
			loading_commission : false,
			pre_loader : true,
			interval_commission : null
		}

	},
	mounted(){
		let app =this
		app.pre_loader = false;
		app.getCommission();
		app.interval_commission = setInterval(function(){
			app.getCommission();
		}, 5000);
	},
	methods: {
		transferToMainWallet(){
			let app = this;
			if(!app.pre_loader){
				Swal.fire({
					text: 'ยืนยันโยกไปยังกระเป๋าเงิน ' + numeral(app.commission).format('0,0.00') + ' ฿ ทำเทิร์น ' + app.login_turn + ' เท่า',
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
							axios.post(BaseURL + "deposit/wallet_login_point",
								Qs.stringify({})
								,{
									'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
								})
								.then(function (response) {
									app.pre_loader = false
									if (response.data.result) {
										app.commission = 0;
										Swal.fire({
											type: 'success',
											text: 'โยกไปยังเกมสำเร็จ',
											confirmButtonText: 'ตกลง',
											confirmButtonColor: '#2ABA66',
											allowOutsideClick: false
										})
											.then((result) => {
												app.getCommission();
											});
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
		},
		getCommission(){
			let app = this;
			if(!app.loading_commission){
				app.loading_commission = true;
				axios.get(BaseURL + "account/remaining_login_point")
					.then(function (response) {
						app.loading_commission = false
						if (response.data.result) {
							app.commission = response.data.result.remaining_login_point
						}
					}).catch(err=>{
					app.loading_commission = false
				});
			}
		}
	}
});
