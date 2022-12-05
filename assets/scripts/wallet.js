new Vue({
	el: "#"+page_id,
	data (){
		return {
			loading_wallet : false,
			pre_loader : true,
			wallet : {

			}
		}

	},
	mounted(){
		this.pre_loader = false;
		this.getCreditBalance();
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
							if(typeof(app.wallet.MAIN) != "undefined" && typeof(app.wallet.MAIN.value) != "undefined"){
								$(main_wallet_profile).text(numeral(app.wallet.MAIN.value).format('0,0.00'));
								$(main_wallet_header).text(numeral(app.wallet.MAIN.value).format('0,0.00'));
							}
							var ingame_wallet = 0;
							for(key in app.wallet){
								if(key != "MAIN"){
									if(typeof(app.wallet[key]) != "undefined" && typeof(app.wallet[key].value) != "undefined"){
										ingame_wallet = parseFloat(parseFloat(ingame_wallet) + parseFloat(app.wallet[key].value)).toFixed(2);
									}
								}
							}
							$(main_wallet_ingame).text(numeral(ingame_wallet).format('0,0.00'));
						}
					}).catch(err=>{
					app.loading_wallet = false
				});
			}

		}
	}
});
