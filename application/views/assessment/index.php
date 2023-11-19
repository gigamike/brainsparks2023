<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


    <div class="row wrapper">
        <div class="col-sm-12">
            <h3>Assessment</h3>
        </div>
    </div>

    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
     
        <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>
        <form id="workflowBuilderForm" action="#" class="wizard-big">
            <input type="hidden" class="txt_csrfname" id="csrfid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

            <h1 class="desktop-only">Preliminary Questions</h1>
            <fieldset>
                 <?php $this->load->view('assessment/questions1'); ?>
            </fieldset>

            <h1 class="desktop-only">Observations Skills</h1>
            <fieldset>
                <?php $this->load->view('assessment/questions2'); ?>
            </fieldset>

            <h1 class="desktop-only">Social Interactions</h1>
            <fieldset>
                <?php $this->load->view('assessment/questions3'); ?>
            </fieldset>
            
            <h1 class="desktop-only">Communication Skills</h1>
            <fieldset>
                <?php $this->load->view('assessment/questions4'); ?>
            </fieldset>

            <h1 class="desktop-only">Behavior, Sensory & Motor Skills</h1>
            <fieldset>
                <?php $this->load->view('assessment/questions5'); ?>
            </fieldset>

            <h1 class="desktop-only">Misc Questions</h1>
            <fieldset>
                <?php $this->load->view('assessment/questions6'); ?>
            </fieldset>
        </form>

        <br><br><br>
          
    </div>
