new Vue({
	el: "#"+page_id,
	data (){
		return {
			pre_loader : true,
			step : register_step,
			checkbankAuto : 0,
			register_verify_otp_status : register_verify_otp_status,
			form : {
				phone : register_data != null ? register_data.phone : '',
				otp : '',
				password : '',
				bank : '',
				bank_number : '',
				full_name : '',
				ref : register_data != null ? register_data.ref : '',
				auto_accept_bonus : '',
			}
		}
	},
	mounted(){
		this.pre_loader = false
	},
	methods: {
		handleCheckboxChange(value) {
			this.form.auto_accept_bonus = this.form.auto_accept_bonus === value ? null : value;
		},
		sendOtp(){
			let app =this;
			if (app.form.phone.trim().length == 0) {
				$('#phone').focus();
				sweetAlert2('warning', 'กรุณาระบุเบอร์มือถือ');
			}
			else{
				app.pre_loader = true
				axios.post(BaseURL + "auth/send_otp",
					Qs.stringify(app.form)
					,{
						'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
					})
					.then(function (response) {
						app.pre_loader = false
						if (response.data.result) {
							app.step = response.data.step;
						} else {
							sweetAlert2('error', response.data.message);
						}
					}).catch(err=>{
					app.pre_loader = false
					sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
				});
			}
		},
		checkOtp(){
			let app =this;
			if (app.form.phone.trim().length == 0) {
				$('#phone').focus();
				sweetAlert2('warning', 'กรุณาระบุเบอร์มือถือ');
			}
			else{
				if(register_verify_otp_status == "1"){
					app.pre_loader = true
					axios.post(BaseURL + "auth/check_otp",
						Qs.stringify(app.form)
						,{
							'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
						})
						.then(function (response) {
							app.pre_loader = false
							if (response.data.result) {
								app.step = response.data.step;
							} else {
								sweetAlert2('error', response.data.message);
							}
						}).catch(err=>{
						app.pre_loader = false
						sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
					});
				}else{
					app.step = 3;
				}

			}
		},
		chooseBank(){
			let app = this
			if(app.form.bank=="10"){
				app.form.bank_number = app.form.phone
				this.checkbankAuto =1
			}
		},
		doRegister(){
			let app =this;
			if (app.form.phone.trim().length == 0) {
				$('#phone').focus();
				sweetAlert2('warning', 'กรุณาระบุเบอร์มือถือ');
			}
			else if (app.form.phone.trim().length != 10) {
				sweetAlert2('warning', 'เบอร์มือถือไม่ถูกต้อง');
			}
			else if (app.form.full_name.trim().length == 0) {
				$('#full_name').focus();
				sweetAlert2('warning', 'กรุณาระบุชื่อ - นามสกุล');
			}
			else if (app.form.bank == "") {
				$('#bank').focus();
				sweetAlert2('warning', 'กรุณาเลือกธนาคาร');
			}
			else if (app.form.bank_number.trim().length == 0) {
				$('#bank_number').focus();
				sweetAlert2('warning', 'กรุณาระบุเลขบัญชี');
			}
			else if (app.form.bank_number.trim().length < 10) {
				sweetAlert2('warning', 'เลขบัญชีไม่ถูกต้อง');
			}
			else if (app.form.password.trim().length == 0) {
				$('#password').focus();
				sweetAlert2('warning', 'กรุณาระบุพาสเวิร์ด');
			}
			else if (app.form.password.trim().length < 5) {
				$('#password').focus();
				sweetAlert2('warning', 'พาสเวิร์ดต้องมี 6 ตัวอักษรขึ้นไป');
			}
			else if (app.form.auto_accept_bonus == "") {
				$('#auto_accept_bonus').focus();
				sweetAlert2('warning', 'กรุณาเลือกการรับโปรโมชั่น');
			}
			else{
				let register_ref = window.localStorage.getItem("register_ref");
				if(typeof(register_ref) != "undefined" && register_ref != null && register_ref != ""){
					app.form.ref = register_ref;
				}else{
					app.form.ref = "";
				}
				app.pre_loader = true
				axios.post(BaseURL + "account/register",
					Qs.stringify(app.form)
					,{
						'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
					})
					.then(function (response) {
						app.pre_loader = false
						if (response.data.result) {
							Swal.fire({
								type: 'success',
								// title: 'แจ้งเตือน',
								text: 'สมัครสมาชิกสำเร็จ',
								confirmButtonText: 'ตกลง',
								confirmButtonColor: '#2ABA66',
								allowOutsideClick: false
							})
								.then((result) => {
									if (result.value) {
										location.href = BaseURL;
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
		checkBankAcc(){
			let app = this
			let bank_code = app.form.bank;
			let bank_number = app.form.bank;
			if(app.form.bank=='10'){
				this.checkbankAuto =1
				return
			}
			if(app.form.bank_number.trim().length >= 10){
				//console.log(app.form.bank_number)
				app.pre_loader = true
				this.checkbankAuto =1
				axios.post(BaseURL + "account/checkbankacc",
					Qs.stringify(app.form)
					,{
						'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
					})
					.then(function (response) {
						app.pre_loader = false
						//console.log(response);
						if (response.data.status===true) {
							app.form.full_name = response.data.msg
						}
					}).catch(err=>{
					app.pre_loader = false
					sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
				});
			}
		},
	}
});
$(document).on('keypress', '#bank_number,#phone', function(e) {
	let key = e.keyCode;
	let app = this
	if (key >= 48 && key <= 57 && key != 32) {
		return true;
	} else {
		return false;
	}
});
