$(document).on('keypress', '#username,#password', function(e) {
	let key = e.keyCode;
	if (key == 32) {
		return false;
	}
});
$(document).on('keypress', function (e) {
	if (e.which == 27) {
		sweetAlert2.closeModal();
	}
});
new Vue({
	el: "#loginModal",
	data (){
		return {
			pre_loader : true,
			form : {
				username : login_username,
				password : login_password,
			}
		}

	},
	mounted(){
		this.pre_loader = false
	},
	methods: {
		doLogin(){
			let app =this;
			if (app.form.username.trim().length == 0) {
				$('#username').focus();
				sweetAlert2('warning', 'กรุณาระบุเบอร์มือถือ');
			}
			else if (app.form.username.trim().length != 10) {
				$('#username').focus();
				sweetAlert2('warning', 'เบอร์มือถือไม่ถูกต้อง');
			}
			else if (app.form.password.trim().length == 0) {
				$('#password').focus();
				sweetAlert2('warning', 'กรุณาระบุพาสเวิร์ด');
			}
			else if (app.form.password.trim().length < 5) {
				$('#password').focus();
				sweetAlert2('warning', 'พาสเวิร์ดต้องมี 6 ตัวอักษรขึ้นไป');
			}else{
				app.pre_loader = true
				axios.post(BaseURL + "auth/login",
					Qs.stringify(app.form)
					,{
						'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
					})
					.then(function (response) {
						app.pre_loader = false
						if (response.data.result) {
							location.href = "dashboard"
						} else {
							sweetAlert2('error',response.data.message+"\n"+"กด ESC เพื่อปิด");
						}
					}).catch(err=>{
						app.pre_loader = false
						sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
					});
			}
		},
		FuncA(){
			console.log("Test handle enter.key");
		},
	}
});
