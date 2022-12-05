new Vue({
	el: "#"+page_id,
	data (){
		return {
			pre_loader : true,
		}

	},

	mounted(){
		this.pre_loader = false
	},
	methods: {
		openGameById(game_code,$game_code_id){
			let app =this;
			if(!app.pre_loader){
				app.pre_loader = true;
				let _device_type = getOS();
				axios.get(BaseURL + "play/"+game_code+"/"+$game_code_id+"?device_type="+_device_type)
					.then(function (response) {
						app.pre_loader = false
						if (response.data.result && response.data.url != '') {
							openInNewTab(BaseURL+"play-game/?token="+response.data.url);
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
		}
	}
});
