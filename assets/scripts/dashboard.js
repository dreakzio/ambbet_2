new Vue({
	el: "#"+page_id,
	data (){
		return {
			pre_loader : true,
			loading_wallet : false,
			wallet : 0.00,
			interval_amount : null,
		}

	},

	mounted(){
		this.getCreditBalance();
		this.pre_loader = false
		let app = this
		app.interval_amount = setInterval(function(){
			app.getCreditBalance();
		}, 10000);
	},
	methods: {
		getCreditBalance(){
			let app = this;

			if(!app.loading_wallet){
				app.loading_wallet = true;
				axios.get(BaseURL + "account/remaining_credit")
					.then(function (response) {
						app.loading_wallet = false
						if (response.data.result) {
							app.wallet = response.data.result
						}
					}).catch(err=>{
					app.loading_wallet = false
				});
			}

		},
		openGameOnce(game_code,game_interface){
			let app =this;
			if(!app.pre_loader){
				app.pre_loader = true;
				game_interface = typeof(game_interface) != 'undefined' ? game_interface : '';
				let _device_type = getOS();
				axios.get(BaseURL + "play/"+game_code+"?game_interface="+game_interface+"&device_type="+_device_type)
					.then(function (response) {
						app.pre_loader = false
						if (response.data.result && response.data.url != '') {
							openInNewTab(BaseURL+"play-game/?token="+response.data.url);
							setTimeout(function(){
								app.getCreditBalance();
							},10000);
						} else {
							if(response.data.res && response.data.res.toString().indexOf("Game is under maintenance") >= 0){
								sweetAlert2('error', 'เกมส์อยู่ระหว่างการบำรุงรักษา');
							}if(response.data.res && response.data.res.toString().indexOf("Server is under maintenance") >= 0){
								sweetAlert2('error', 'เซิร์ฟเวอร์อยู่ระหว่างการบำรุงรักษา');
							}if(response.data.res && response.data.res.toString().indexOf("System is under maintenance") >= 0){
								sweetAlert2('error', 'ระบบอยู่ระหว่างการบำรุงรักษา');
							}else{
								sweetAlert2('error', 'เข้าเกมส์ไม่สำเร็จ');
							}
						}
					}).catch(err=>{
					app.pre_loader = false
					sweetAlert2('error', 'เข้าเกมส์ไม่สำเร็จ');
				});
			}
		},
	}
});
