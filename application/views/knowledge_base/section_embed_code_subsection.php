<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- START OF <?php echo strtoupper($this->config->item('mm8_system_provider')); ?> CODE -->
<div id="<?php echo $this->config->item('mm8_system_provider'); ?>DocsMainDiv<?php echo $random_string; ?>"></div>
<div id="<?php echo $this->config->item('mm8_system_provider'); ?>DocsHandDiv<?php echo $random_string; ?>"></div>
<script>var <?php echo $this->config->item('mm8_system_provider'); ?>DocsHandDivId = "<?php echo $this->config->item('mm8_system_provider'); ?>DocsHandDiv<?php echo $random_string; ?>";</script>
<script type="text/javascript" src="<?php echo $this->config->item('mhub_docs_url'); ?>assets/js/embed/widgets/<?php echo $this->config->item('mm8_system_provider'); ?>.handshake.nologo.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('mhub_docs_url'); ?>assets/js/embed/widgets/iframe-resizer/iframeResizer.min.js"></script>
<script type="text/javascript">
    function createIframe<?php echo $random_string; ?>() {
        if (document.getElementById("<?php echo $this->config->item('mm8_system_provider'); ?>DocsHandDiv<?php echo $random_string; ?>").innerHTML === "") {
                    var i = document.createElement("h2");
                    var itext = document.createTextNode("Sorry! There was an error loading the content.");
                    i.appendChild(itext);
                    document.getElementById("<?php echo $this->config->item('mm8_system_provider'); ?>DocsMainDiv<?php echo $random_string; ?>").appendChild(i);
                            } else {
                                var i = document.createElement("iframe");
                                i.setAttribute("src", "<?php echo $this->config->item('mhub_docs_url'); ?>embed/subsection/<?php echo $id; ?>");
                                            i.setAttribute("scrolling", "no");
                                            i.setAttribute("frameborder", "0");
                                            i.setAttribute("width", "100%");
                                            i.setAttribute("style", "border:none;");
                                            document.getElementById("<?php echo $this->config->item('mm8_system_provider'); ?>DocsMainDiv<?php echo $random_string; ?>").appendChild(i);
                                                        iFrameResize({log: false, autoResize: true, checkOrigin: false, heightCalculationMethod: "taggedElement", warningTimeout: 10000});
                                                    }
                                                }
                                                if (window.addEventListener) {
                                                    window.addEventListener("load", createIframe<?php echo $random_string; ?>, false);
                                                } else if (window.attachEvent) {
                                                    window.attachEvent("onload", createIframe<?php echo $random_string; ?>);
                                                } else {
                                                    window.onload = createIframe<?php echo $random_string; ?>;
                                                }
</script>
<!-- END OF <?php echo strtoupper($this->config->item('mm8_system_provider')); ?> CODE -->