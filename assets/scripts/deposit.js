new Vue({
    el: "#" + page_id,
    data() {
        return {
            username_exist: typeof(username_exist) != "undefined" ? username_exist : false,
            amount_deposit_first: 0,
            bank_id: typeof(bank_id) != "undefined" ? bank_id : null,
            show_bank: true,
            bank_start_time_can_not_deposit: typeof(bank_start_time_can_not_deposit) != "undefined" ? bank_start_time_can_not_deposit : null,
            bank_end_time_can_not_deposit: typeof(bank_end_time_can_not_deposit) != "undefined" ? bank_end_time_can_not_deposit : null,
            message_can_not_deposit: typeof(bank_start_time_can_not_deposit) == "undefined" || message_can_not_deposit == null ? 'ระบบฝากออโต้ปิดปรับปรุงช่วงเวลานี้, กรุณาติดต่อเราเพื่อแจ้งฝากเงิน' : message_can_not_deposit,
            amount: 0,
            amount_deposit: amount_deposit,
            loading_wallet: false,
            promotion: promotion_active,
			auto_accept_bonus: auto_accept_bonus_active,
            loading_amount_auto_deposit: false,
            loading_chk_bank_can_deposit: false,
            loading_chk_username_exist: false,
            pre_loader: true,
            loading_history: false,
            results: [],
            interval_history_list: null,
            interval_amount: null,
            interval_amount_auto_deposit: null,
            interval_chk_bank_can_deposit: null,
            interval_chk_time_bank_can_deposit: null,
            interval_check_username_exist: null,
            qrcode_amount: null,
        }

    },
    mounted() {
        let app = this
        app.pre_loader = false;
        if (!app.username_exist) {
            app.checkUsernameExist();
            app.interval_check_username_exist = setInterval(function() {
                app.checkUsernameExist();
            }, 6000);
        }
        app.getAmountAutoDeposit();
        app.getList();
        app.getฺBankCanDeposit();
        app.interval_amount_auto_deposit = setInterval(function() {
            app.getAmountAutoDeposit();
        }, 5000);
        app.interval_history_list = setInterval(function() {
            app.getList();
        }, 10000);
        app.interval_chk_bank_can_deposit = setInterval(function() {
            app.getฺBankCanDeposit();
        }, 8000);
    },
    methods: {
        checkUsernameExist() {
            let app = this;
            if (!app.loading_chk_username_exist) {
                app.loading_chk_bank_can_deposit = true;
                axios.get(BaseURL + "account/check_username_exist")
                    .then(function(response) {
                        app.loading_chk_username_exist = false
                        if (response.data.amount) {
                            app.amount_deposit_first = response.data.amount;
                        }
                        if (response.data.result === true) {
                            //location.reload();
                        }
                    }).catch(err => {
                        app.loading_chk_username_exist = false
                    });
            }
        },
        getฺBankCanDeposit() {
            let app = this;
            if (!app.loading_chk_bank_can_deposit && bank_id != null) {
                app.loading_chk_bank_can_deposit = true;
                axios.get(BaseURL + "deposit/chk_bank_can_deposit/" + app.bank_id)
                    .then(function(response) {
                        app.loading_chk_bank_can_deposit = false
                        app.show_bank = response.data.result;
                        if (response.data.start_time_can_not_deposit) {
                            app.bank_start_time_can_not_deposit = response.data.start_time_can_not_deposit;
                        }
                        if (response.data.end_time_can_not_deposit) {
                            app.bank_end_time_can_not_deposit = response.data.end_time_can_not_deposit;
                        }
                    }).catch(err => {
                        app.loading_chk_bank_can_deposit = false
                    });
            }
        },
        getAmountAutoDeposit() {
            let app = this;
            if (!app.loading_amount_auto_deposit) {
                app.loading_amount_auto_deposit = true;
                axios.get(BaseURL + "account/remaining_amount_deposit")
                    .then(function(response) {
                        app.loading_amount_auto_deposit = false
                        if (response.data.result) {
                            app.amount_deposit = parseFloat(response.data.result.amount_deposit).toFixed(2)
                            $("#deposit_amount").val(parseFloat(app.amount_deposit).toFixed(2));
                        }
                    }).catch(err => {
                        app.loading_amount_auto_deposit = false
                    });
            }
        },
        doDeposit() {
            let app = this;
            if (!app.pre_loader) {
                if (app.amount_deposit.toString().length == 0) {
                    sweetAlert2('warning', 'กรุณาระบุยอดที่ต้องการฝาก');
                } else if (app.promotion == null || app.promotion == '' || app.promotion == 0) {
                    sweetAlert2('warning', 'กรุณาเลือกโปรโมชั่น');
                } else if (isNaN(app.amount_deposit)) {
                    sweetAlert2('warning', 'ระบบกำลังตรวจสอบยอดอัตโนมัติ...');
                } else if (Number(app.amount_deposit) == 0) {
                    sweetAlert2('warning', 'ยอดฝากต้องมากกว่า 0');
                } else {
                    Swal.fire({
                            text: 'ยืนยันยอดฝาก ' + numeral(app.amount_deposit).format('0,0') + ' ฿',
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
                                axios.post(BaseURL + "deposit/deposit_credit",
                                        Qs.stringify({
                                            promotion: app.promotion
                                        }), {
                                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                                        })
                                    .then(function(response) {
                                        app.pre_loader = false
                                        if (response.data.result) {
                                            app.amount_deposit = 0;
                                            app.getAmountAutoDeposit();
                                            app.getList();
                                            Swal.fire({
                                                    type: 'success',
                                                    // title: 'แจ้งเตือน',
                                                    text: 'ฝากเงินสำเร็จ',
                                                    confirmButtonText: 'ตกลง',
                                                    confirmButtonColor: '#2ABA66',
                                                    allowOutsideClick: false
                                                })
                                                .then((result) => {
                                                    if (result.value) {

                                                    }
                                                });
                                        } else {
                                            app.pre_loader = false
                                            sweetAlert2('warning', response.data.message);
                                        }
                                    }).catch(err => {
                                        app.pre_loader = false
                                        sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
                                    });
                            } else {
                                app.pre_loader = false
                            }
                        });
                }
            }
        },
		change_accept_bonus() {
			let app = this;
			if (!app.pre_loader) {

					Swal.fire({
						text: 'แก้ไขสถานะรับโบนัส auto ' ,
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
								axios.post(BaseURL + "account/change_accept_bonus",
									Qs.stringify({
										auto_accept_bonus: app.auto_accept_bonus
									}), {
										'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
									})
									.then(function(response) {
										app.pre_loader = false
										if (response.data.result) {

											Swal.fire({
												type: 'success',
												text: 'แก้ไขสถานะเรียบร้อยแล้ว',
												confirmButtonText: 'ตกลง',
												confirmButtonColor: '#2ABA66',
												allowOutsideClick: false
											})
												.then((result) => {
													location.reload();
												});
										}
									}).catch(err => {
									app.pre_loader = false
									sweetAlert2('warning', 'แก้ไขสถานะไม่สำเร็จ');
								});
							} else {
								app.pre_loader = false
							}
						});

			}
		},
        doUpload() {
            let app = this;
            if (!app.pre_loader) {
                let vidFileLength = $("#file")[0].files.length;
                if (vidFileLength === 0) {
                    sweetAlert2('warning', 'กรุณาเลือกไฟล์');
                } else {
                    Swal.fire({
                            text: 'ยืนยันอัพโหลดสลิป',
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
                                let formData = new FormData();
                                var imagefile = document.querySelector('#file');
                                formData.append("image", imagefile.files[0]);
                                axios.post(BaseURL + "qrcode/upload", formData, {
                                        headers: {
                                            'Content-Type': 'multipart/form-data'
                                        }
                                    })
                                    .then(function(response) {
                                        app.pre_loader = false
                                        if (response.data.result) {
                                            // app.amount_deposit = 0;
                                            // app.getAmountAutoDeposit();
                                            Swal.fire({
                                                    type: 'success',
                                                    // title: 'แจ้งเตือน',
                                                    text: 'อัพโหลดสำเร็จ',
                                                    confirmButtonText: 'ตกลง',
                                                    confirmButtonColor: '#2ABA66',
                                                    allowOutsideClick: false
                                                })
                                                .then((result) => {
                                                    if (result.value) {
                                                        location.reload();
                                                    }
                                                });
                                        } else {
                                            app.pre_loader = false
                                            sweetAlert2('warning', response.data.message);
                                        }
                                    }).catch(err => {
                                        app.pre_loader = false
                                        sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
                                    });
                            } else {
                                app.pre_loader = false
                            }
                        });
                }
            }
        },
        getList() {
            let app = this;
            if (!app.loading_history) {
                app.loading_history = true;
                axios.get(BaseURL + "account/history_list?type=1")
                    .then(function(response) {
                        app.loading_history = false
                        if (response.data.result) {
                            app.results = response.data.result
                        }
                    }).catch(err => {
                        app.loading_history = false
                    });
            }

        },
        copyBankAcc(bank_acc_number, text) {
            let $temp = $("<input>");
            $("body").append($temp);
            $temp.val(bank_acc_number).select();
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
                title: typeof(text) != "undefined" ? text : 'คัดลอกคัดลอกเลขบัญชีแล้ว'
            });
        },
        getQrCode() {
            let qrcode_amount = this.qrcode_amount;
            if (qrcode_amount != 0) {
                axios.get(BaseURL + "mamanee/maneeQR", {
                    params: {
                        qrcode_amount: qrcode_amount
                    }
                }).then(function(response) {
                    if (response.data) {
                        function display(seconds) {
                            const format = val => `0${Math.floor(val)}`.slice(-2)
                            const minutes = (seconds % 3600) / 60
                            return [minutes, seconds % 60].map(format).join(':')
                        }
                        var
                            closeInSeconds = 900,
                            displayText = '<h3><a href="javascript:void(0);" onclick="generate();">[SAVE QRcode]</a></h3><br>' + 'กรุณาโอนภายใน #1 นาที.',
                            timer;
                        Swal.fire({
                            title: 'จำนวนเงิน ' + response.data.amount + ' บาท',
                            imageUrl: response.data.qrcode,
                            html: displayText.replace(/#1/, display(closeInSeconds)),
                            timer: closeInSeconds * 1000,
                            showDenyButton: true,
                            showCancelButton: true,
                            confirmButtonText: `ตกลง`,
                            denyButtonText: `ยกเลิก`,
                        }).then((result) => {
                            if (result.value) {
                                clearInterval(timer);
                                clearInterval(closeInSeconds);
                                // axios.get(BaseURL + "deposit/get_transection", {
                                //     params: {
                                //         amount: response.data.amount
                                //     }
                                // }).then(function(response) {
                                //     Swal.fire({
                                //         title: response.data.id,
                                //         // text: response.data.amount,
                                //     });
                                // }).catch(function(error) {
                                //     // Swal.showValidationMessage(
                                //     //     `Request failed: ${error}`
                                //     // )
                                // })
                            } else {
                                clearInterval(timer);
                                clearInterval(closeInSeconds);
                            }
                        });

                        timer = setInterval(function() {
                            closeInSeconds--;
                            if (closeInSeconds < 0) {
                                clearInterval(timer);
                            }
                            $('html.swal2-shown.swal2-height-auto body.swal2-shown.swal2-height-auto div.swal2-container.swal2-center.swal2-fade.swal2-shown div.swal2-popup.swal2-modal.swal2-show div.swal2-content div#swal2-content').html(displayText.replace(/#1/, display(closeInSeconds)));
                        }, 1000);
                    }
                }).catch(function(error) {
                    // console.log(error);
                });
            } else {
                sweetAlert2('warning', 'กรุณาใส่จำนวนเงิน');
            }
        }
    }
});
(function(exports) {
    function urlsToAbsolute(nodeList) {
        if (!nodeList.length) {
            return [];
        }
        var attrName = 'href';
        if (nodeList[0].__proto__ === HTMLImageElement.prototype || nodeList[0].__proto__ === HTMLScriptElement.prototype) {
            attrName = 'src';
        }
        nodeList = [].map.call(nodeList, function(el, i) {
            var attr = el.getAttribute(attrName);
            if (!attr) {
                return;
            }
            var absURL = /^(https?|data):/i.test(attr);
            if (absURL) {
                return el;
            } else {
                return el;
            }
        });
        return nodeList;
    }

    function screenshotPage() {
        var wrapper = document.getElementsByClassName('swal2-popup swal2-modal swal2-show');
        html2canvas(wrapper, {
            onrendered: function(canvas) {
                canvas.toBlob(function(blob) {
                    saveAs(blob, 'qr_code.png');
                });
            }
        });
    }

    function addOnPageLoad_() {
        window.addEventListener('DOMContentLoaded', function(e) {
            var scrollX = document.documentElement.dataset.scrollX || 0;
            var scrollY = document.documentElement.dataset.scrollY || 0;
            window.scrollTo(scrollX, scrollY);
        });
    }

    function generate() {
        screenshotPage();
    }
    exports.screenshotPage = screenshotPage;
    exports.generate = generate;
})(window);
