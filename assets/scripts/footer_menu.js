var header_menu_page = new Vue({
	el: "#footer_menu",
	data (){
		return {
			lao : "lao",
			english : "english",
			thailand : "thailand"
		}
	},
	mounted(){
	},
	methods: {
		PickLanguageLao(){
			let app = this;
			// console.log(app.lao);
			Swal.fire({
				text: 'ต้องการเปลี่ยนเป็นภาษา Lao ?',
				confirmButtonText: 'ตกลง',
				confirmButtonColor: '#2ABA66',
				showCancelButton: true,
				cancelButtonText: 'ยกเลิก',
				cancelButtonColor: 'red',
				reverseButtons: true,
			}).then((result) => {
					if (result.value) {
						console.log("result.value",result.value);
							axios.post(BaseURL + "auth/ChangeLanguage",
								Qs.stringify({
									lao: app.lao
								}),{
									'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
								})
								.then(function (response) {
									console.log("response :",response);
									if (response.data.result) {
										// location.reload();
										sweetAlert2('success',"กำลังเปลี่ยนเป็นภาษาลาว");
										location.reload();
										// location.href = "dashboard"
									} else {
										sweetAlert2('success',"เปลี่ยนภาษาไม่สำเร็จ");
									}
								}).catch(err=>{
									console.log("err :",err);
									sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
								});
					}
				});
		},
		PickLanguageEnglish(){
			let app = this;
			// console.log(app.english);
			Swal.fire({
				text: 'ต้องการเปลี่ยนเป็นภาษา English ?',
				confirmButtonText: 'ตกลง',
				confirmButtonColor: '#2ABA66',
				showCancelButton: true,
				cancelButtonText: 'ยกเลิก',
				cancelButtonColor: 'red',
				reverseButtons: true,
			}).then((result) => {
					if (result.value) {
							axios.post(BaseURL + "auth/ChangeLanguage",
								Qs.stringify({
									english: app.english
								}),{
									'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
								})
								.then(function (response) {
									console.log("response :",response.data.result);
									if (response.data.result) {
										// location.reload();
										sweetAlert2('success',"กำลังเปลี่ยนเป็นภาษาอังกฤษ");
										location.reload();
										// location.href = "dashboard"
									} else {
										sweetAlert2('success',"เปลี่ยนภาษาไม่สำเร็จ");
									}
								}).catch(err=>{
									console.log("err :",err);
									sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
								});
					}
				});
		},
		PickLanguageThai(){
			let app = this;
			// console.log(app.thailand);
			Swal.fire({
				text: 'ต้องการเปลี่ยนเป็นภาษา Thai ?',
				confirmButtonText: 'ตกลง',
				confirmButtonColor: '#2ABA66',
				showCancelButton: true,
				cancelButtonText: 'ยกเลิก',
				cancelButtonColor: 'red',
				reverseButtons: true,
			}).then((result) => {
					if (result.value) {
							axios.post(BaseURL + "auth/ChangeLanguage",
								Qs.stringify({
									thailand: app.thailand
								}),{
									'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
								})
								.then(function (response) {
									console.log("response :",response.data.result);
									if (response.data.result) {
										// location.reload();
										sweetAlert2('success',"กำลังเปลี่ยนเป็นภาษาไทย");
										location.reload();
										// location.href = "dashboard"
									} else {
										sweetAlert2('success',"เปลี่ยนภาษาไม่สำเร็จ");
									}
								}).catch(err=>{
									console.log("err :",err);
									sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
								});
					}
				});
		},
	}
});