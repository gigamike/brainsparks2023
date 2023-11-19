
var global_spinner = "fa fa-spinner fa-spin";
var global_pre_spinner = "";

function show_spinner(item) {
    global_pre_spinner = $("i", item).attr("class");
    $("i", item).attr("class", global_spinner);
}

function hide_spinner(item) {
    $("i", item).attr("class", global_pre_spinner);
}
