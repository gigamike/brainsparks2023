<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal inmodal" id="quickStartModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeIn">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 m-t-n-sm">
                        <button type="button" id="closeBtn" class="close m-r-n-sm loading-disabler" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 m-b-lg">
                        <div class="row">
                            <div class="col-sm-12 m-b-md text-center">
                                <h2 class="no-margins font-bold text-navy"><i class="fa fa-info-circle fa-2x"></i></h2>
                                <h2 class="no-margins font-bold">Quick Start Guide</h2>
                                <p class="m-t-md">Welcome to the <?php echo $this->config->item('mm8_product_name'); ?>! Use the quick guide below to help you get started.</p>
                            </div>
                        </div>
                        <?php if (isset($this->config->item('hub_quick_start_guide')[$this->session->utilihub_hub_user_role]) && !empty($this->config->item('hub_quick_start_guide')[$this->session->utilihub_hub_user_role])) { ?>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="table-responsive">
                                        <table class="table issue-tracker">
                                            <thead>
                                                <tr>
                                                    <td class="text-center">
                                                        Checklist
                                                    </td>
                                                    <td>
                                                        Take me to...
                                                    </td>
                                                    <td class="text-right">

                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($this->config->item('hub_quick_start_guide')[$this->session->utilihub_hub_user_role] as $qs_item) {
                                                    echo '<tr id="div_' . $qs_item['id'] . '">';
                                                    echo '<td class="text-center"><div class="i-checks-kb"><label><input type="checkbox" id="' . $qs_item['id'] . '" value="1"' . (isset($this->session->utilihub_hub_user_settings[$qs_item['id']]) && (int) $this->session->utilihub_hub_user_settings[$qs_item['id']] === STATUS_OK ? ' checked' : '') . '></label></div></td>';
                                                    echo '<td class="qs-text' . (isset($this->session->utilihub_hub_user_settings[$qs_item['id']]) && (int) $this->session->utilihub_hub_user_settings[$qs_item['id']] === STATUS_OK ? ' todo-completed' : '') . '"><h3 class="no-margins">' . $qs_item['label'] . '</h3>' . (!empty($qs_item['description']) ? '<small class="text-muted">' . $qs_item['description'] . '</small>' : '') . '</td>';

                                                    echo '<td class="text-right">';
                                                    if (!empty($qs_item['embed_content_id'])) {
                                                        echo '<a class="btn btn-white  m-l-xs m-b-xs" href="javascript:void(0);" onclick="show_quickstart_embed_content(\'' . $qs_item['embed_content_id'] . '\');"><i class="fa fa-book"></i><span class="desktop-only"> Teach me how</span></a>';
                                                    }

                                                    if (!empty($qs_item['embed_video_id'])) {
                                                        echo '<a class="btn btn-white  m-l-xs m-b-xs" href="javascript:void(0);" onclick="show_quickstart_embed_video(\'' . $qs_item['embed_video_id'] . '\');"><i class="fa fa-play"></i><span class="desktop-only"> Play video</span></a>';
                                                    }
                                                    echo '</td>';

                                                    echo '</tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
