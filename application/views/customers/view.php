<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<input type="hidden" name="customer_id" id="customer_id" value="<?php echo $customer->id; ?>">

<div class="row wrapper">
    <div class="col-sm-12">
        <a href="<?php echo base_url() . "customers"; ?>" class="m-l-n-sm text-navy">
            <i class="fa fa-angle-left m-r-xs"></i> Customers
        </a>
    </div>
    <div class="col-sm-5">
        <h3>View Customer <?php echo $customer->u_code; ?></h3>
    </div>
    <div class="col-sm-7 text-right">
        <button id="recommenderBtn" class="btn btn-md btn-primary btn-w-s m-b-xs" type="button">Recommender</button>
    </div>
</div>

<!-- content wrapper start -->
<div class="row wrapper wrapper-content">
    <div class="col-sm-12">

        <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

        <div class="ibox float-e-margins white-bg">
            <div class="ibox-content">

                <div class="row">
                    <div class="col-sm-3 col-sm-push-9 text-center">
                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <div class="fileinput-new thumbnail" style="width: 100px; height: 100px;">
                                <img src="<?php echo asset_url(); ?>img/default/profile-photo.jpg" alt="..."/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-9 col-sm-pull-3">
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label class="control-label">First Name</label>
                                <input type="text" class="form-control font-mono" value="<?php echo $customer->first_name; ?>" readonly>
                            </div><!-- column -->
                            <div class="col-sm-4">
                                <label class="control-label">Last Name</label>
                                <input type="text" class="form-control font-mono" value="<?php echo $customer->last_name; ?>" readonly>
                            </div><!-- column -->
                        </div>

                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label class="control-label">Email</label>
                                <div class="input-group">
                                    <input type="text" id="email" name="email" class="form-control font-mono" value="<?php echo $customer->email; ?>" readonly>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-white border-radius-right copy-to-clipboard" data-clipboard-action="copy" data-clipboard-target="#email">Copy</button>
                                    </span>
                                </div>
                            </div><!-- column -->
                            <div class="col-sm-4">
                                <label class="control-label">Mobile Phone</label>
                                <div class="input-group">
                                    <input type="text" id="mobile_phone" name="mobile_phone" class="form-control font-mono" value="<?php echo $customer->mobile_phone; ?>" readonly>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-white border-radius-right copy-to-clipboard" data-clipboard-action="copy" data-clipboard-target="#mobile_phone">Copy</button>
                                    </span>
                                </div>
                            </div><!-- column -->
                        </div>

                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label class="control-label">Birth Date</label>
                                <div class="input-group">
                                    <input type="text" id="date_of_birth" name="date_of_birth" class="form-control font-mono" value="<?php echo $customer->date_of_birth; ?>" readonly>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-white border-radius-right copy-to-clipboard" data-clipboard-action="copy" data-clipboard-target="#date_of_birth">Copy</button>
                                    </span>
                                </div>
                            </div><!-- column -->
                            <div class="col-sm-4">
                              
                            </div><!-- column -->
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-8">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Purchase History
                            </div>
                            <div class="panel-body">
                                
                                <div class="row">
                                    <div class="col-sm-12">
                                        <button type="button" id="toggleSearchBtn" class="btn btn-md btn-link datatable-top-togglers"><i class="fa fa-search"></i> <span class="action-label">Search</span></button>
                                    </div><!-- column -->
                                </div><!-- row -->

                                <div class="row wrapper m-t" id="dtSearchContainer" style="display:none;">
                                    <div class="col-sm-8 gray-bg p-md">
                                        <div class="col-sm-8 m-b-xs">
                                            <input type="text" class="form-control" name="dtSearchText" id="dtSearchText" placeholder="Search">
                                        </div>
                                        <div class="col-sm-4 m-b-xs">
                                            <button type="button" id="dtSearchBtn" class="btn btn-primary search-button">Search</button>
                                        </div>
                                    </div><!-- column -->
                                </div><!-- row -->

                                <table id="dtInvoicesTbl" class="table table-responsive">
                                    <thead>
                                        <tr>
                                            <th>Invoice Code</th>
                                            <th>Date Purchased</th>
                                            <th>Product Code</th>
                                            <th>Product Name</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Date Added</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Activities 
                            </div>
                            <div class="panel-body">
                                <div class="ibox-content inspinia-timeline white-bg">

                                    
                                    <div class="timeline-item">
                                        <div class="row">
                                            <div class="col-xs-3 date">
                                                <i class="fa fa-mobile"></i>
                                                7:00 am
                                                <br/>
                                                <small class="text-navy">3 hour ago</small>
                                            </div>
                                            <div class="col-xs-7 content">
                                                <p class="m-b-xs"><strong>SMS Offer</strong></p>
                                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="row">
                                            <div class="col-xs-3 date">
                                                <i class="fa fa-envelope"></i>
                                                8:00 am
                                                <br/>
                                            </div>
                                            <div class="col-xs-7 content">
                                                <p class="m-b-xs"><strong>Email Offer</strong></p>
                                                <p>
                                                    Go to shop and find some products.
                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="row">
                                            <div class="col-xs-3 date">
                                                <i class="fa fa-envelope"></i>
                                                11:00 am
                                                <br/>
                                                <small class="text-navy">21 hour ago</small>
                                            </div>
                                            <div class="col-xs-7 content">
                                                <p class="m-b-xs"><strong>Email Welcome</strong></p>
                                                <p>
                                                    Lorem Ipsum has been the industry's standard dummy text ever since.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="row">
                                            <div class="col-xs-3 date">
                                                <i class="fa fa-user-plus"></i>
                                                12:50 pm
                                                <br/>
                                                <small class="text-navy">48 hour ago</small>
                                            </div>
                                            <div class="col-xs-7 content">
                                                <p class="m-b-xs"><strong>Customer Created</strong></p>
                                                <p>
                                                    Created via import
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                

            </div>
        </div>

    </div>
</div>

<div class="modal inmodal" id="recommenderModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeIn">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <img style="width:50px;height:50px" src="<?php echo asset_url(); ?>img/amazon-personalize.png">
                <h4 class="modal-title">Recommender System</h4>
                <small class="font-bold">Powered By Amazon Personalize</small>
            </div>
            <div class="modal-body white-bg">

                <div class="row m-b-lg">
                    <div class="col-sm-12">
                        <form>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Select Recipe</label>

                                <select name="recipe" id="recipe" class="select2-input form-control filter-set filter-operator" style="width:100%;">
                                    <option value="aws-similar-items">Similar Items - Predicts items similar to a given item based on co-occurence of items.</option>
                                    <option value="aws-personalized-ranking">Personalized Ranking - reranks and input list of items for a given user.</option>
                                    <option value="aws-user-personalization">User Personalization -predicts items a user will interact and performs exploration on cold items.</option>
                                    <option value="aws-trending-now">Item Popularity Trending Now - Get trending now item as recommendation</option>
                                    <option value="aws-popularity-count">Item Popularity Popularity Count - Calculates popularity of items based on total number of events for each item in the user-item interactions.</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
           
                <div class="productsWrapper"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


