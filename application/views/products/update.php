<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row wrapper">
    <div class="col-sm-5">
        <h3>Merchant <?php echo $merchant->first_name; ?> <?php echo $merchant->last_name; ?></h3>
    </div>
    <div class="col-sm-7 text-right">
        <div class="form-group">
            <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
            <?php if ($merchant->status == 'pending' || $merchant->status == 'denied') { ?>
            <button id="approvedBtn" class="btn btn-md btn-primary m-b-xs">Approved</button>
            <?php } ?>

            <?php if ($merchant->status == 'pending' || $merchant->status == 'approved') { ?>
            <button id="deniedBtn" class="btn btn-md btn-alert m-b-xs">Denied</button>
            <?php } ?>

            <?php if ($merchant->status == 'approved') { ?>
            <a target="_blank" href="<?php echo base_url() . "downloads/certificates/" . $merchant->u_code . ".pdf"; ?>" class="btn btn-md btn-primary m-b-xs">Generate Certificate</a>
            <?php } ?>
        </div>
    </div>
</div>

<form id="merchantForm">
    <input type="hidden" name="merchant_id" id="merchant_id" value="<?php echo $merchant->id; ?>">
    <div class="row wrapper">
        <div class="col-sm-12">
            <?php if ($merchant->status == 'denied') { ?>
                <div class="alert alert-danger">
                    This merchant is <?php echo $merchant->status; ?>
                </div>
            <?php } ?>

            <?php if ($merchant->status == 'approved') { ?>
                <div class="alert alert-success">
                    This merchant is <?php echo $merchant->status; ?>
                </div>
            <?php } ?>

            <?php if ($merchant->status == 'pending') { ?>
                <div class="alert alert-warning">
                    This merchant is <?php echo $merchant->status; ?>
                </div>
            <?php } ?>

            <div class="ibox ibox-rounded float-e-margins">
                <div class="ibox-content">
            
                    <div class="row">
                        <div class="col-md-4 col-md-push-8 m-t-sm m-b-sm">
                            <div class="ibox ibox-rounded float-e-margins white-bg p-sm">
                                <div class="ibox-content m-t-sm">
                                    <center><img alt="image" class="img-responsive" style="max-height:100px !important;" src="<?php echo!empty($merchant->product_photo) ? $merchant->product_photo : asset_url() . "img/default/partner-logo.png"; ?>"></center>
                                </div>
                            </div>
                           
                        </div>
                        <div class="col-md-8 col-md-pull-4 m-t-sm m-b-sm">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-12 text-left">
                                            <p class="no-margins">Name</p>
                                            <p class="font-bold">
                                                <?php echo $merchant->first_name; ?> <?php echo $merchant->last_name; ?>
                                                <?php echo!empty($merchant->u_code) ? '<span class="badge badge-success m-l-xs">' . $merchant->u_code . '</span>' : ''; ?>
                                            </p>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 text-left">
                                            <p class="no-margins">Address</p>
                                            <p class="font-bold"><?php echo!empty($merchant->address) ? $merchant->address : '-'; ?></p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 text-left">
                                            <p class="no-margins">Mobile Number</p>
                                            <p class="font-bold"><?php echo!empty($merchant->mobile_phone) ? $merchant->mobile_phone : '-'; ?></p>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-12 text-left">
                                            <p class="no-margins">Product</p>
                                            <p class="font-bold">
                                                <?php echo!empty($merchant->product) ? $merchant->product : '-'; ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 text-left">
                                            <p class="no-margins">Email</p>
                                            <p class="font-bold"><?php echo!empty($merchant->email) ? $merchant->email : '-'; ?></p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 text-left">
                                            <p class="no-margins">Unionbank Account</p>
                                            <p class="font-bold"><?php echo!empty($merchant->unionbank_account) ?  $merchant->unionbank_account : '-'; ?></p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>


                </div>
            </div>

            <div class="ibox ibox-rounded float-e-margins">
                <div class="ibox-title">
                    <h3>Scorecards</h3>
                </div>
                <div class="ibox-content">

                    <!-- stats -->
                    <div class="row">
                        <div class="col-sm-3 col-xs-6">
                            <div class="ibox ibox-rounded float-e-margins white-bg">
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h2 class="no-margins font-bold" id="metricApplicationsAdded">0</h2>
                                            <p class="no-margins">Email verified</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-6">
                            <div class="ibox ibox-rounded float-e-margin white-bg">
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h2 class="no-margins font-bold">0</h2>
                                            <p class="no-margins">SMS verified</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-6">
                            <div class="ibox ibox-rounded float-e-margins white-bg">
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h2 class="no-margins font-bold">0</h2>
                                            <p class="no-margins">GEOIP</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-6">
                            <div class="ibox ibox-rounded float-e-margins white-bg">
                                <div class="ibox-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h2 class="no-margins font-bold">0</h2>
                                            <p class="no-margins">Product</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- stats -->
                </div>
            </div>    

        </div>
    </div>

</form>