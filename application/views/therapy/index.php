<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row wrapper">
    <div class="col-sm-5">
        <h3>Therapy</h3>
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
                        <h2>Facial Expression</h2>
                        <div class="m-b-sm">
                            <img alt="image" class="img-circle" src="<?php echo asset_url(); ?>img/therapy/facial-expression.jpeg" style="width: 50%">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <strong>
                            About me
                        </strong>
                        <p>
                            One approach is the use of computer-based training programs that focus on facial recognition and emotion identification. These programs use a variety of techniques, including visual aids, verbal prompts, and feedback, to help individuals with autism learn to identify and interpret facial expressions more accurately.
                        </p>
                        <a href="<?php echo base_url(); ?>therapy/facial-expression" class="btn btn-primary">Let's Play</a>
                    </div>
                </div>

                <div class="row m-b-lg">
                    <div class="col-lg-4 text-center">
                        <h2>Other Therapy Soon</h2>
                        <div class="m-b-sm">
                            <img alt="image" class="img-circle" src="<?php echo asset_url(); ?>img/therapy/coming-soon.jpeg" style="width: 50%">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <strong>
                            About me
                        </strong>
                        <p>
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                        </p>
                    </div>
                </div>

                <div class="row m-b-lg">
                    <div class="col-lg-4 text-center">
                        <h2>Other Therapy Soon</h2>
                        <div class="m-b-sm">
                            <img alt="image" class="img-circle" src="<?php echo asset_url(); ?>img/therapy/coming-soon.jpeg" style="width: 50%">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <strong>
                            About me
                        </strong>
                        <p>
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>