<section class="register">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> <?php echo $this->lang->line('back'); ?></a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fas fa-life-ring mb-2"></i>&nbsp;<?php echo $this->lang->line('event_wheel'); ?></span>
	<hr style="margin-top: 15px">
	<div class="text-center pb-0">
		<h5 class="mt-3 text-dark"><?php echo $this->lang->line('getwheeleverydeposit'); ?> (<?php echo number_format($web_setting['wheel_amount_per_point']['value']); ?> <?php echo $this->lang->line('bath_per_1coin'); ?>)
		</h5>
		<p class="text-danger mb-1 font-weight-bold">** <?php echo $this->lang->line('doturn_deposit_belong_day'); ?></p>
	</div>
	<div class="container" >
		<div class="mb-2 p-0">
			<div class="d-flex justify-content-center profile-box-top">
				<div class="p-0 align-self-center text-center" >
					<div class="user-name  text-silver text-center mb-1">
						<h4 class="text-success text-bold-700 mb-0"><?php echo $this->lang->line('coin'); ?> :	<span id="point_for_wheel"><?php echo number_format($user['point_for_wheel']); ?></span></h4>
						<small class="mb-0 text-danger">*<?php echo $this->lang->line('used_coin'); ?> (<?php echo number_format($web_setting['wheel_point_for_spin']['value']); ?> <?php echo $this->lang->line('coin_per_1time'); ?>)</small>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="menu-bar-box">
		<div class="bg-darkred mb-2">
			<div class="row">
				<div class="col-12 text-center">
					<?php
					$result_wheel = $this->Setting_model->setting_for_wheel_list(['sort_by'=>"name"]);
					?>
					<div id="chart"></div>
					<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
					<script src="<?php echo base_url('assets/plugins/d3.v3.min.js') ?>" charset="utf-8"></script>
					<script type="text/javascript" charset="utf-8">
						var container,innerWidth,padding,w,h,r,rotation,oldrotation,picked,oldpick,color,vis,start;
						var data = JSON.parse('<?php echo json_encode($result_wheel); ?>');
						$(document).ready(function(){
							innerWidth = window.innerWidth;
							innerWidth -= 50;
							initWheel();
						})
						function initWheel(){
							$("#chart").empty();

							padding = {top:20, right:30, bottom:5, left:15},
									w = innerWidth > 400 ? 350 : innerWidth-10 - padding.left - padding.right,
									h = 400 - padding.top  - padding.bottom,
									r = Math.min(w, h)/2,
									rotation = 0,
									oldrotation = 0,
									picked = 100000,
									oldpick = [],
									color = d3.scale.category20();


							var svg = d3.select('#chart')
									.append("svg")
									.data([data])
									.attr("width",  w + padding.left + padding.right)
									.attr("height", h + padding.top + padding.bottom + padding.top + padding.bottom);


							container = svg.append("g")
									.attr("class", "chartholder")
									.attr("transform", "translate(" + (w/2 + padding.left) + "," + (h/2 + padding.top) + ")");

							var c = container.append("circle")
									.attr("cx", 0)
									.attr("cy",0)
									.attr("r", ((innerWidth > 400 ? 350+2 : innerWidth-9 - padding.left - padding.right)/2))
									//.attr("stroke", "#34495e")
									.attr("stroke", "black")
									.attr("stroke-width", "6")
									.attr("fill", "none");

							vis = container
									.append("g");

							var pie = d3.layout.pie().sort(null).value(function(d){return 1;});

							// declare an arc generator function
							var arc = d3.svg.arc().outerRadius(r);

							// select paths, use arc generator to draw
							var arcs = vis.selectAll("g.slice")
									.data(pie)
									.enter()
									.append("g")
									.attr("class", "slice");


							arcs.append("path")
									.attr("fill", function(d, i){return d.data.color; })
									.attr("stroke", function(d, i){ return "#ffffff"; })
									.attr("stroke-width", "2")
									.attr("d", function (d) { return arc(d); });

							// add the text
							arcs.append("text").attr("transform", function(d){

								d.innerRadius = 0;
								d.outerRadius = r;
								d.angle = (d.startAngle + d.endAngle)/2;

								if((d.angle * 180 / Math.PI - 90) >= 0 && (d.angle * 180 / Math.PI - 90) <= 35){
									start = d.data.id;
								}
								return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")translate(" + (d.outerRadius -10) +")";
							})
									.attr("text-anchor", "end")
									.attr("fill", "white")
									.attr("x", "-3")
									.text( function(d, i) {
										return data[i].name;
									});



							//make arrow
							svg.append("g")
									.attr("transform", "translate(" + (w + padding.left + padding.right) + "," + ((h/2)+padding.top) + ")")
									.append("path")
									.attr("d", "M-" + (r*.15) + ",0L0," + (r*.05) + "L0,-" + (r*.05) + "Z")
									//.style({"fill":"#34495e"});
									.style({"fill":"black"});

							//draw spin circle
							container.append("circle")
									.attr("cx", 0)
									.attr("cy", 0)
									.attr("r", 35)
									.attr("stroke", "#ffffff")
									.attr("stroke-width", "3")
									.style({"fill":"#34495e","cursor":"pointer"});

							//spin text
							container.append("text")
									.attr("x", 0)
									.attr("y", 6)
									.attr("text-anchor", "middle")
									.text("SPIN")
									.style({"font-weight":"bold", "font-size":"16px","fill":"#ffffff","cursor":"pointer"});

							container.on("click", spin);
						}
						function spin(d){
							Swal.fire({
								text: 'ยืนยันการหมุนวงล้อพารวยใช้ <?php echo number_format($web_setting["wheel_point_for_spin"]["value"]); ?> เหรียญ',
								confirmButtonText: 'ตกลง',
								confirmButtonColor: '#2ABA66',
								showCancelButton: true,
								cancelButtonText: 'ยกเลิก',
								cancelButtonColor: 'red',
								reverseButtons: true,
							})
									.then((result) => {

										if (result.value) {
											initWheel();
											axios.get(BaseURL + "account/spin_wheel")
													.then(function (response) {
														$("#point_for_wheel").text(numeral(response.data.point_for_wheel).format('0,0'));
														//var length = data.length;
														var length = data.length;
														if (response.data.result) {

															var id = parseInt(response.data.id);
															if(id > 0){
																container.on("click", null);
																var r_add = 0;
																var  ps       = 360/length,
																		rng      = Math.floor((Math.random() * 1440) + 360);
																if(parseInt(start) > id){
																	r_add = length -  (parseInt(start) - id);
																}else if(parseInt(start) == id){
																	r_add = length ;
																}else{
																	r_add = (length - parseInt(id)) + (length);
																	if(length  == id){
																		r_add += (length - parseInt(start) );
																	}
																	//r_add = length - (id - parseInt(start));
																}
																rotation = parseInt(r_add) * 35;
																let rand = Math.floor(Math.random() * 11)
																rotation += (35 * 50) + 50 + rand ;
																rotation = rotation > (parseInt(r_add) * 35) + (35 * 50) +50 ? (parseInt(r_add) * 35) + (35 * 50) +50 : rotation;
																//rotation = (Math.round(rng*10/ ps) * ps);
																picked = Math.round(length - (rotation % 360)/ps);
																picked = picked >= length ? (picked % length) : picked;

																vis.transition()
																		.duration(10000)
																		.attrTween("transform", rotTween)
																		.each("end", function(){

																			Swal.fire({
																				type: 'success',
																				// title: 'แจ้งเตือน',
																				text: 'ผลการหมุนวงล้อ : '+(response.data.name == "" ? "ไม่ได้รางวัล" : response.data.name),
																				confirmButtonText: 'ตกลง',
																				confirmButtonColor: '#2ABA66',
																				allowOutsideClick: false
																			})
																					.then((result) => {
																						if (result.value) {
																							if(parseInt(response.data.credit) > 0){
																								header_menu_page.getCreditBalance();
																							}
																						}
																					});

																			//mark question as seen
																			d3.select(".slice:nth-child(" + (picked + 1) + ") path")
																			oldrotation = rotation;
																			container.on("click", spin);
																		});
															}else{
																sweetAlert2('error', "เกิดข้อผิดพลาด, กรุณาลองใหม่อีกครั้ง");
															}


														} else {
															sweetAlert2('warning', response.data.message);
														}
													}).catch(err=>{
												sweetAlert2('error', "เกิดข้อผิดพลาด, กรุณาลองใหม่อีกครั้ง");
											});
										}
									});

						}




						function rotTween(to) {
							var i = d3.interpolate(oldrotation % 360, rotation);

							return function(t) {
								return "rotate(" + i(t) + ")";
							};
						}

					</script>
				</div>
			</div>
		</div>
	</div>
</section>


