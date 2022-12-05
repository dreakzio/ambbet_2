new Vue({
	el: "#"+page_id,
	data (){
		return {
			loading_history : false,
			loading_history_bonus_return : false,
			pre_loader : true,
			results : [],
			bonus_return_results : [],
			chk_return_balance : typeof(chk_return_balance) != "undefined" ? chk_return_balance : false,
			interval_history_list : null,
			interval_history_bonus_return_list : null,
		}

	},
	mounted(){
		let app =this
		app.pre_loader = false;
		app.getList();
		app.interval_history_list = setInterval(function(){
			app.getList();
		}, 15000);
		if(chk_return_balance){
			app.getListBonusReturn();
			app.interval_history_bonus_return_list = setInterval(function(){
				app.getListBonusReturn();
			}, 15000);
		}
	},
	methods: {
		getList(){
			let app = this;
			if(!app.loading_history){
				app.loading_history = true;
				axios.get(BaseURL + "account/history_list")
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
		getListBonusReturn(){
			let app = this;
			if(!app.loading_history_bonus_return){
				app.loading_history_bonus_return = true;
				axios.get(BaseURL + "account/history_list_bonus_return")
					.then(function (response) {
						app.loading_history_bonus_return = false
						if (response.data.result) {
							app.bonus_return_results = response.data.result
						}
					}).catch(err=>{
					app.loading_history_bonus_return = false
				});
			}

		}
	}
});
