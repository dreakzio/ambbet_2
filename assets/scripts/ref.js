new Vue({
	el: "#"+page_id,
	data (){
		return {
			commission : 0,
			minimum_com : minimum_com,
			user_id : user_id,
			ref_turn : ref_turn,
			loading_commission : false,
			loading_wallet : false,
			loading_ref : false,
			loading_ref_deposit : false,
			pre_loader : true,
			result_refs : [],
			result_ref_deposits : [],
			interval_ref_list : null,
			interval_ref_deposit_list : null,
			interval_commission : null,
			tab_active : {
				qrcode : true,
				ref : false,
				ref_deposit : false
			}
		}

	},
	mounted(){
		let app =this
		app.tab_active.qrcode = true;
		app.tab_active.ref = false;
		app.tab_active.ref_deposit = false;
		app.pre_loader = false;
		app.getCommission();
		app.getRefList();
		app.getRefDepositList();
		app.interval_commission = setInterval(function(){
			app.getCommission();
		}, 5000);
		app.interval_ref_list = setInterval(function(){
			app.getRefList();
		}, 15000);
		app.interval_ref_deposit_list = setInterval(function(){
			app.getRefDepositList();
		}, 15000);
		new QRCode(document.getElementById("qrcode"), BaseURL + 'register?ref=' + app.user_id);
	},
	methods: {
		copyLinkRef(url){
			let $temp = $("<input>");
			$("body").append($temp);
			$temp.val(url).select();
			document.execCommand("copy");
			$temp.remove();
			let Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 2500
			});
			Toast.fire({
				type: 'success',
				title: 'คัดลอกลิงค์แล้ว'
			});
		},
		changeTab(dashboard_tab){
			let app = this
			app.tab_active.qrcode = true;
			app.tab_active.ref = false;
			app.tab_active.ref_deposit = false;
			if(dashboard_tab == "ref"){
				app.tab_active.qrcode = false;
				app.tab_active.ref = true;
			}else if(dashboard_tab == "ref_deposit"){
				app.tab_active.qrcode = false;
				app.tab_active.ref_deposit = true;
			}
		},
		getCreditBalance(){
			let app = this;
			if(!app.loading_wallet){
				app.loading_wallet = true;
				axios.get(BaseURL + "account/remaining_credit")
					.then(function (response) {
						app.loading_wallet = false
						if (response.data.result) {
							if(typeof(response.data.result.MAIN) != "undefined" && typeof(response.data.result.MAIN.value) != "undefined"){
								$(main_wallet_profile).text(numeral(response.data.result.MAIN.value).format('0,0.00'));
								$(main_wallet_header).text(numeral(response.data.result.MAIN.value).format('0,0.00'));
							}
							var ingame_wallet = 0;
							for(key in response.data.result){
								if(key != "MAIN"){
									if(typeof(response.data.result[key]) != "undefined" && typeof(response.data.result[key].value) != "undefined"){
										ingame_wallet = parseFloat(parseFloat(ingame_wallet) + parseFloat(response.data.result[key].value)).toFixed(2);
									}
								}
							}
							$(main_wallet_ingame).text(numeral(ingame_wallet).format('0,0.00'));
						}
					}).catch(err=>{
					app.loading_wallet = false
				});
			}
		},
		transferToMainWallet(){
			let app = this;
			if(!app.pre_loader){
				Swal.fire({
					text: 'ยืนยันโยกไปยังกระเป๋าเงิน ' + numeral(app.commission).format('0,0.00') + ' ฿ ทำเทิร์น ' + app.ref_turn + ' เท่า',
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
							axios.post(BaseURL + "deposit/wallet_ref_deposit",
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
												app.getCreditBalance();
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
				axios.get(BaseURL + "account/remaining_wallet_ref")
					.then(function (response) {
						app.loading_commission = false
						if (response.data.result) {
							app.commission = response.data.result.remaining_wallet_ref
						}
					}).catch(err=>{
					app.loading_commission = false
				});
			}
		},
		getRefList(){
			let app = this;
			if(!app.loading_ref){
				app.loading_ref = true;
				axios.get(BaseURL + "account/ref_list")
					.then(function (response) {
						app.loading_ref = false
						if (response.data.result) {
							app.result_refs = response.data.result
						}
					}).catch(err=>{
					app.loading_ref = false
				});
			}

		},
		getRefDepositList(){
			let app = this;
			if(!app.loading_ref_deposit){
				app.loading_ref_deposit = true;
				axios.get(BaseURL + "account/ref_deposit_list")
					.then(function (response) {
						app.loading_ref_deposit = false
						if (response.data.result) {
							app.result_ref_deposits = response.data.result
						}
					}).catch(err=>{
					app.loading_ref_deposit = false
				});
			}

		}
	}
});
