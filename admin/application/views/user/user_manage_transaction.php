<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2/dist/css/select2.css') ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2-theme/dist/select2-bootstrap4.css') ?>">
<link rel="stylesheet"
    href="<?php echo base_url('assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css')?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link href="<?php echo base_url('assets/plugins/clockpicker/dist/bootstrap-clockpicker.min.css')?>" rel="stylesheet">
<style>
.form-control:disabled,
.form-control[readonly] {
    background-color: #FFF;
    opacity: 1;
}

.card-body {
    padding: 0 !important;
}
</style>

<div class="content-wrapper ">
    <div class="content-header row">
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0">ระบบจัดการสมาชิก</h2>
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo site_url('user') ?>">จัดการข้อมูลธุรกรรม</a>
                            </li>
                            <li class="breadcrumb-item active">ดูข้อมูลการจัดการข้อมูลธุรกรรม</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <section class="card">
            <div class="card-content">
                <div class="card-body" style="padding 0 0 0 0 !important">

                    <div class="card col-xl-12 col-lg-12">
                        <div class="card-header">
                            <h4 class="card-title">ข้อมูลธุรกรรม</h4>
                        </div>
                        <div class="card-body ">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="withdraw-deposit-tab-fill" data-toggle="tab"
                                        href="#withdraw-deposit-fill" role="tab" aria-controls="withdraw-deposit-fill"
                                        aria-selected="false"> <span
                                            class="fa fa-arrow-up font-small-3 text-success "></span> <span
                                            class="fa fa-arrow-down font-small-3 text-danger mr-50"></span> ฝาก-ถอน </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="deposit-tab-fill" data-toggle="tab" href="#deposit-fill"
                                        role="tab" aria-controls="deposit-fill" aria-selected="false"> <span
                                            class="fa fa-arrow-up font-small-3 text-success mr-50"></span> ฝาก </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="withdraw-tab-fill" data-toggle="tab" href="#withdraw-fill"
                                        role="tab" aria-controls="withdraw-fill" aria-selected="false"> <span
                                            class="fa fa-arrow-down font-small-3 text-danger mr-50"></span> ถอน </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="credit-tab-fill" data-toggle="tab" href="#credit-fill"
                                        role="tab" aria-controls="credit-fill" aria-selected="true"> <span
                                            class="fa fa-credit-card font-small-3 text-info mr-50"></span> เครดิต </a>
                                </li>
                            </ul>
                            <div class="row mx-auto" style="width: 98%">
                                <div class="col-12 col-md-12 pr-1 pl-1">
                                    <div class="input-group mb-1">
                                        <div class="input-daterange input-group " id="datepicker">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1">วันที่ (จาก)</span>
                                            </div>
                                            <input type="text" class="input-sm form-control" id="date_start_report"
                                                name="start" />
                                            <input type="text" class="input-sm form-control" id="time_start_report"
                                                name="time_start" />
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1">วันที่ (ถึง)</span>
                                            </div>
                                            <input type="text" class="input-sm form-control" id="date_end_report"
                                                name="end" />
                                            <input type="text" class="input-sm form-control" id="time_end_report"
                                                name="time_nd" />
                                            <div class="input-group-prepend">
                                                <button type="button" id="btn-search" name="button"
                                                    class="btn bg-gradient-primary waves-effect waves-light"> ค้นหา
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <!-- Start div Credit !!! -->
                                <div class="tab-pane" id="credit-fill" role="tabpanel"
                                    aria-labelledby="credit-tab-fill">
                                    <div class="content-body">
                                        <section class="card">
                                            <div class="card-header ">
                                                <h4 class="card-title ">รายการเครดิต</h4>
                                            </div>
                                            <div class="card-content">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="table-responsive">
                                                                <!-- table-bordered -->
                                                                <table id="table"
                                                                    class="table dataTables_wrapper dt-bootstrap4  table-hover"
                                                                    style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center" width="60px">
                                                                                แก้ไขให้</th>
                                                                            <th class="text-center">ยอดก่อนหน้านี้
                                                                            </th>
                                                                            <th class="text-center">ประเภท</th>
                                                                            <th class="text-center">จำนวน</th>
                                                                            <th class="text-center">รวม</th>
                                                                            <th class="text-center">ทำรายการโดย</th>
                                                                            <th class="text-center">วัน-เวลาสลิป
                                                                            </th>
                                                                            <th class="text-center">วันที่สร้าง</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>


                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>

                                </div>
                                <!-- end div credit -->
                                <div class="tab-pane" id="deposit-fill" role="tabpanel"
                                    aria-labelledby="deposit-tab-fill">
                                    <div class="content-body">
                                        <section class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">รายการฝากเงิน</h4>
                                            </div>
                                            <div class="card-content">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="table-responsive">
                                                                <!-- table-bordered -->
                                                                <table id="table2"
                                                                    class="table dataTables_wrapper dt-bootstrap4  table-hover"
                                                                    style="width:100%">
                                                                    <thead>
                                                                        <tr>

                                                                            <th class="text-center">Username</th>
                                                                            <!-- <th class="text-center">Username (member)</th> -->
                                                                            <th class="text-center">ธนาคาร</th>
                                                                            <th class="text-center">เลขบัญชี</th>
                                                                            <th class="text-center">ฝากเมื่อ</th>
                                                                            <th class="text-center">โปรโมชั่น</th>
                                                                            <th class="text-right">จำนวนเงิน</th>
                                                                            <th class="text-right">โบนัส</th>
                                                                            <th class="text-right">รวม</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>


                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                                <div class="tab-pane" id="withdraw-fill" role="tabpanel"
                                    aria-labelledby="withdraw-tab-fill">
                                    <div class="content-body">
                                    </div>
                                    <div class="content-body">
                                        <section class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">รายการถอนเงิน</h4>
                                            </div>
                                            <div class="card-content">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="table-responsive">
                                                                <!-- table-bordered -->
                                                                <table id="table3"
                                                                    class="table dataTables_wrapper dt-bootstrap4  table-hover"
                                                                    style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">Username</th>
                                                                            <!-- <th class="text-center">Username (member)</th> -->
                                                                            <th class="text-center">ธนาคาร</th>
                                                                            <th class="text-center">เลขบัญชี</th>
                                                                            <th class="text-center">ถอนเมื่อ</th>
                                                                            <th class="text-center">ดำเนินการโดย
                                                                            </th>
                                                                            <th class="text-right">จำนวนเงิน</th>
                                                                            <th class="text-center">หมายเหตุ</th>
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>


                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                                <div class="tab-pane active" id="withdraw-deposit-fill" role="tabpanel"
                                    aria-labelledby="withdraw-deposit-tab-fill">
                                    <div class="content-body">
                                    </div>
                                    <div class="content-body">
                                        <section class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">รายการ (ฝาก-ถอน) </h4>
                                            </div>
                                            <div class="card-content">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="table-responsive">
                                                                <table id="table4"
                                                                    class="table dataTables_wrapper dt-bootstrap4  table-hover"
                                                                    style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">#</th>
                                                                            <th class="text-center">เบอร์มือถือ</th>
                                                                            <th class="text-right">กระเป๋าเงิน (ก่อน)
                                                                            </th>
                                                                            <th class="text-right">จำนวนเงิน (ดำเนินการ)
                                                                            </th>
                                                                            <th class="text-center">ประเภท</th>
                                                                            <th class="text-center">โปรโมชั่น</th>
                                                                            <th class="text-center">รายละเอียด</th>
                                                                            <th class="text-center">วันที่</th>
                                                                            <th class="text-center">ทำรายการโดย</th>

                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<!-- start script from credit -->
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
<link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8">
</script>
<script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/xlsx.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/clockpicker/dist/bootstrap-clockpicker.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') ?>">
</script>
<script src="<?php echo base_url('assets/plugins/bootstrap-datepicker/dist/locales/bootstrap-datepicker.th.min.js') ?>">
</script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/i18n/th.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<!-- end script from credit -->

<script>
const user_id = '<?php echo $user['id']; ?>';
</script>
<script>
const bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
</script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/i18n/th.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/user/user_manage_transaction.js?').time() ?>"></script>