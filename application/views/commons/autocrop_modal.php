<div class="modal inmodal fade" id="autocrop-modal-container" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 m-t-n-sm">
                        <button type="button" class="close m-r-n-sm loading-disabler" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <h2>Crop and Resize Image</h2>
                    </div>
                </div><!-- header -->

                <!-- autocrop start -->
                <div id="autocrop-modal">
                    <div class="row text-center">
                        <div class="col-md-12 text-center">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-md btn-secondary " id="btnAutocropZoomIn"><i class="fa fa-search-plus"></i></button>
                                <button type="button" class="btn btn-md btn-secondary " id="btnAutocropZoomOut"><i class="fa fa-search-minus"></i></button>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-md btn-secondary " id="btnAutocropRotateLeft"><i class="fa fa-rotate-left"></i></button>
                                <button type="button" class="btn btn-md btn-secondary " id="btnAutocropRotateRight"><i class="fa fa-rotate-right"></i></button>
                            </div>
                            <button type="button" class="btn btn-md btn-secondary " id="btnAutocropUpload"><i class="fa fa-save"></i> Resize and Upload</button>
                            <button type="button" class="btn btn-md btn-secondary " id="btnAutocropUploadRaw"><i class="fa fa-upload"></i> Upload Raw (dont resize)</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-1"></div>
                        <div class="col-md-10 col-sm-10 m-t text-center" style="max-height:600px;">
                            <img id="autocrop-preview-image" src=""/>
                            <input type="hidden" id="uploaded-file-name"/>
                            <input type="hidden" id="uploaded-file-size"/>
                        </div>
                        <div class="col-md-1"></div>
                    </div>
                </div>
                <!-- autocrop end -->
            </div><!-- modal body -->
        </div>
    </div>
</div>
