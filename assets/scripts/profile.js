new Vue({
	el: "#"+page_id,
	data (){
		return {
			pre_loader : true,
			is_active_return_balance : is_active_return_balance,
			flow : 1,
			ref_turn : ref_turn,
			loading_return_balance : false,
			interval_return_balance : null,
			return_balance : 0.00,
			form : {
				old_password : '',
				password : '',
				password_confirm : ''
			}
		}

	},

	mounted(){
		let app =this
		app.pre_loader = false
		app.pre_loader = false
		app.getReturnBalance();
		app.interval_return_balance = setInterval(function(){
			app.getReturnBalance();
		}, 10000);
	},
	methods: {
		changeReturnBalance(){
			let app =this
			if(!app.pre_loader){
				app.pre_loader = true
				axios.post(BaseURL + "account/change_is_active_return_balance",
					Qs.stringify({is_active_return_balance : app.is_active_return_balance})
					,{
						'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
					})
					.then(function (response) {
						app.pre_loader = false
						if (response.data.result) {
							Swal.fire({
								type: 'success',
								text: 'เปลี่ยนสถานะโบนัสคืนยอดเสียสำเร็จ',
								confirmButtonText: 'ตกลง',
								confirmButtonColor: '#2ABA66',
								allowOutsideClick: false
							})
								.then((result) => {
									if (result.value) {

									}
								});

						} else {
							sweetAlert2('error', response.data.message);
						}
					}).catch(err=>{
						app.pre_loader = false
						sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
				});
			}
		},
		changeFlow(flow){
			this.form.password = '';
			this.form.password_confirm = '';
			this.flow = flow;
		},
		doChangePassword(){
			let app =this
			if (app.form.old_password.trim().length == 0) {
				$('#old_password').focus();
				sweetAlert2('warning', 'กรุณาระบุรหัสผ่านเก่า');
			}else if (app.form.password.trim().length == 0) {
				$('#password').focus();
				sweetAlert2('warning', 'กรุณาระบุรหัสผ่านใหม่');
			}else if (app.form.password_confirm.trim().length == 0) {
				$('#password_confirm').focus();
				sweetAlert2('warning', 'กรุณาระบุยืนยันรหัสผ่านใหม่');
			}
			else{
				if(!app.pre_loader){
					app.pre_loader = true
					axios.post(BaseURL + "account/change_password_user",
						Qs.stringify(app.form)
						,{
							'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
						})
						.then(function (response) {
							app.pre_loader = false
							if (response.data.result) {
								Swal.fire({
									type: 'success',
									text: 'เปลี่ยนรหัสผ่านสำเร็จ',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: '#2ABA66',
									allowOutsideClick: false
								})
									.then((result) => {
										if (result.value) {
											window.location.href = BaseURL+"dashboard";
										}
									});

							} else {
								sweetAlert2('error', response.data.message);
							}
						}).catch(err=>{
						app.pre_loader = false
						sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
					});
				}

			}
		},
		transferToMainWallet(){
			let app = this;
			if(!app.pre_loader){
				Swal.fire({
					text: 'ยืนยันโยกไปยังกระเป๋าเงิน ' + numeral(app.return_balance).format('0,0.00') + ' ฿ ทำเทิร์น ' + app.ref_turn + ' เท่า',
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
							axios.post(BaseURL + "deposit/wallet_ref_deposit_return_balance",
								Qs.stringify({})
								,{
									'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
								})
								.then(function (response) {
									app.pre_loader = false
									if (response.data.result) {
										app.return_balance = 0.00;
										Swal.fire({
											type: 'success',
											text: 'โยกไปยังเกมสำเร็จ',
											confirmButtonText: 'ตกลง',
											confirmButtonColor: '#2ABA66',
											allowOutsideClick: false
										})
											.then((result) => {
												app.getReturnBalance();
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
		getReturnBalance(){
			let app = this;
			if(!app.loading_return_balance){
				app.loading_return_balance = true;
				axios.get(BaseURL + "account/remaining_return_balance_ref")
					.then(function (response) {
						app.loading_return_balance = false
						if (response.data.result) {
							app.return_balance = response.data.result.remaining_return_balance_ref
						}
					}).catch(err=>{
					app.loading_return_balance = false
				});
			}
		},
	}
});
