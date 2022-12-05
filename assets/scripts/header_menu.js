var header_menu_page = new Vue({
	el: "#header_menu",
	data (){
		return {
		}
	},
	mounted(){
	},
	methods: {
		logout(){
			Swal.fire({
				text: 'ท่านต้องการออกจากระบบ ?',
				confirmButtonText: 'ตกลง',
				confirmButtonColor: '#2ABA66',
				showCancelButton: true,
				cancelButtonText: 'ยกเลิก',
				cancelButtonColor: 'red',
				reverseButtons: true,
			})
				.then((result) => {
					if (result.value) {
 						window.location.href = BaseURL+"auth/logout";
					}
				});
		},
	}
});
