import '../css/global.scss';
import '../css/app.css';

import $ from "jquery";
import "bootstrap";
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');


console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

global.deleteDOI = function(id){
    var url = $("a.doi[data-id='"+id+"']").attr("href");
    var citation = $("a.doi[data-id='"+id+"'] .doi-citation").html();

    $("#deleteDoiModal .modal-body").empty().append(
        `
        <h4>${url}</h4>
        <p>${citation}</p>
        `
    );
    var link = $("#deleteDoiModal .modal-footer a")
    link.attr("href",link.data("link").replace("0", id));
    $('#deleteDoiModal').modal('show');
    return false; //prevent open link
}

global.editDOI = function(id){
    var url = $("a.doi[data-id='"+id+"']").attr("href");
   
    var form = $("#editDoiModal form");
    form.attr('action', form.data('action').replace("0", id));
    form.find("input[name='doi[uri]']").val(url);
    
    $('#editDoiModal').modal('show');
    return false; //prevent open link
}