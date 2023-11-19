<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="right-sidebar">
    <div class="sidebar-container">
        <?php echo isset($sidebar_body) ? $sidebar_body : ""; ?>

        <?php if (isset($this->config->item('hub_quick_start_guide')[$this->session->utilihub_hub_user_role]) && !empty($this->config->item('hub_quick_start_guide')[$this->session->utilihub_hub_user_role])) { ?>
        
        <div class="wrapper m-t-lg">
            
            <div class="row">
                <div class="col-xs-12">
                    <h3>Tasks</h3>
                    <p>Use this guide to get you set up.</p>
                </div><!-- col -->
            </div><!-- row -->

            <div class="row">
                <div class="col-xs-12">
                    <div class="panel-group" id="task-guide-checklist">

                    <?php foreach ($this->config->item('hub_quick_start_guide')[$this->session->utilihub_hub_user_role] as $idx => $qs_item) { ?>
                        <div class="panel panel-default" id="div_<?php echo $qs_item['id'];?>">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-sm-1 col-xs-2 col-md-2 text-right p-r-none p-t-3">
                                        <div class="i-checks-kb">
                                            <input type="checkbox" id="<?php echo $qs_item['id']; ?>" value="1" <?php echo (isset($this->session->utilihub_hub_user_settings[$qs_item['id']]) && (int) $this->session->utilihub_hub_user_settings[$qs_item['id']] === STATUS_OK) ? ' checked' : ''; ?> />
                                        </div>
                                    </div>
                                    <div class="col-sm-11 col-xs-10 col-md-10">
                                        <h5 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $qs_item['id'];?>" aria-expanded="true" class=""><?php echo $qs_item['label']?></a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div id="collapse<?php echo $qs_item['id'];?>" data-id="<?php echo $qs_item['id'];?>" data-target-iframe-url="<?php echo !empty($qs_item['embed_content_id']) ? $this->config->item('mhub_docs_url') . 'embed/content/'. $qs_item['embed_content_id'] : '';  ?>" class="panel-collapse collapse" aria-expanded="true">
                                <div class="panel-body task-guide-steps-body">
                                    <?php if (!empty($qs_item['embed_content_id'])) {
                                        echo '<div id="task-guide-iframe-placeholder-'. $qs_item['id'] .'">loading...</div>';
                                    }
                                    ?>

                                    <?php 
                                        if (!empty($qs_item['embed_video_id'])) {
                                            echo '<p><a class="btn btn-white  m-l-xs m-b-xs" href="javascript:void(0);" onclick="show_quickstart_embed_video(\'' . $qs_item['embed_video_id'] . '\');"><i class="fa fa-play"></i><span class="desktop-only"> Play video</span></a></p>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div><!-- panel -->
                    <?php } // foreach ?>

                    </div><!-- panel group -->
                </div><!-- col -->
            </div><!-- row -->

        </div><!-- wrapper -->
    <?php } ?>
    </div>
</div>