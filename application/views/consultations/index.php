<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row wrapper">
    <div class="col-sm-5">
        <h3>Consultations</h3>
    </div>
    <div class="col-sm-7 text-right">
    </div>
</div>
<!-- content wrapper start -->
<div class="row wrapper wrapper-content">
    <div class="col-sm-12">
        <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>
        <div class="ibox float-e-margins white-bg">
            <div class="ibox-content">
                <?php if($this->session->flashdata('error_message')): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Warning!</strong> <?php echo $this->session->flashdata('error_message'); ?>
                    </div>
                <?php endif; ?>

                <div class="row m-b-lg">
                    <div class="col-lg-4 text-center">
                        <h2>Dr. Ausome</h2>
                        <div class="m-b-sm">
                            <img alt="image" class="img-circle" src="<?php echo asset_url(); ?>img/chatbot-500x500.png" style="width: 50%">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <strong>
                            About me
                        </strong>
                        <p>
                            AI-enabled smart chatbots are designed to simulate near-human interactions with customers. They can have free-flowing conversations and understand intent, language, and sentiment.
                        </p>
                        <a href="<?php echo base_url(); ?>consultations/chatbot" class="btn btn-primary">Talk to me</a>
                    </div>
                </div>

                <div class="row m-b-lg">
                    <div class="col-lg-4 text-center">
                        <h2>Ausome Group Support</h2>
                        <div class="m-b-sm">
                            <img alt="image" class="img-circle" src="<?php echo asset_url(); ?>img/support-group-500x500.png" style="width: 50%">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <strong>
                            About me
                        </strong>
                        <p>
                            Community stories. See how Ausome Group Support Groups help people explore their interests, share knowledge and make connections. 
                        </p>
                        <a href="<?php echo base_url(); ?>consultations/forum" class="btn btn-primary">Go to forums</a>
                    </div>
                </div>

                <div class="row m-b-lg">
                    <div class="col-lg-4 text-center">
                        <h2>Telemedicine</h2>
                        <div class="m-b-sm">
                            <img alt="image" class="img-circle" src="<?php echo asset_url(); ?>img/telemedicine-500x500.png" style="width: 50%">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <strong>
                            About me
                        </strong>
                        <p>
                            Telehealth — sometimes called telemedicine — lets your health care provider care for you without an in-person office visit. Telehealth is done primarily online with internet access on your computer, tablet, or smartphone.
                        </p>
                        <a href="<?php echo base_url(); ?>consultations/telemedicine" class="btn btn-primary">Schedule</a>
                    </div>
                </div>

       



            </div>
        </div>
    </div>
</div>