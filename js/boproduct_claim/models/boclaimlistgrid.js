$(document).ready(function(){
    $("#myDIV").hide();
    var allParams = window.location.href.split('data=').pop();
    const urlParams = new URLSearchParams(allParams);
    var id_product_service_cost = urlParams.get("id_product_service_cost"); 
    var id_product_service_claim = document.getElementById("id_product_service_claim").innerHTML;
    //product + service from product service + supplier name + Dept for + Coast
    
    var product_name_1 = urlParams.get("product_name");
    var service_name_1 = urlParams.get("service_name");  
    is_pakage_chk = urlParams.get("is_pakage");  
    var concat_name = product_name_1 + ' / ' + service_name_1;
    $("#product_name_dtl").val(concat_name);

    allServicesGridClaim(id_product_service_cost, id_product_service_claim);
});

function allServicesGridClaim(id_product_service_cost,id_product_service_claim, addedClaim) {    
    var chkmultipleprice = document.getElementById("multiple_price");
    $('#tbl-productServicesClaim').DataTable({
        "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
            if (is_pakage_chk == "Y") {
                $('#btnAddExtraServicesClaim', nRow).css('display', 'none');
                $('#block_extra').css('display', 'none');
            }
        }, 
        "processing" : true,

        "ajax" : {
            "url" : "php/api/backofficeserviceclaim/gridclaimlist.php?t=" + encodeURIComponent(global_token) + "&id_product_service_cost=" +id_product_service_cost + "&id_product_service_claim=" +id_product_service_claim,
            "dataSrc" : ''
        },
        "destroy": true,
        "bProcessing": true,
        "bAutoWidth": false,
        "responsive": true,
        "pageLength": 5,
        "aaSorting": [ [0,'desc'] ],
        "dom": "<'row'<'form-inline' <'col-sm-5'B>>>"
        +"<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>"
        +"<'row'<'col-sm-12'tr>>"
        +"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        "columnDefs": [
            { width: 200, targets: -1 }
        ],
        "buttons":[
            {
            "extend":    "csvHtml5",
            "text":      "<i class='fa fa-file-text-o'> Excel</i>",
            "titleAttr": "Download in Excel Format",
            }
        ],
        "columnDefs": [
        ],
        "columns" : [ 
        {
            "data" : null,
                    render: function( data, type, row ) {
                        var id_product_service_claim = data.id_product_service_claim;
                        if (data.on_approved == "1") {
                            var icon =  '<i class = "fa fa-check fa-check-circle" style="font-size:18px;color:#00a65a" title="Service line Live"></i> &nbsp; &nbsp;';
                        } else {
                            var icon =  '<i class = "fa fa-check fa-check-circle" style="font-size:18px;color:#e6e6e6" title="Service line Not Live"></i> &nbsp; &nbsp;';                            
                        }
                        return icon+ ' ' +id_product_service_claim;
                    }
        }, {
            "data" : "allName"
        }, {
            "data" : "deptname"
        }, {
            "data" : "charge"
        }, {
            "data" : "currency"
        }, 
        {
            data: null,
                render: function ( data, type, row ) {
                    var start_date = data.valid_from;
                    var date_from = start_date.split("-");
                    var date_from_y = date_from[0];
                    var date_from_m = date_from[1];
                    var date_from_d = date_from[2];
                    var start_date = date_from_d+"/"+date_from_m+"/"+date_from_y;
                    var end_date = data.valid_to;
                    var date_to = end_date.split("-");
                    var date_to_y = date_to[0];
                    var date_to_m = date_to[1];
                    var date_to_d = date_to[2];
                    var end_date = date_to_d+"/"+date_to_m+"/"+date_to_y;
                    return start_date+' - '+end_date;
                },
                editField: ['valid_from', 'valid_to']
        },        
        { 
            "data" : "specific_to_name"
        },  
            {
                "targets": -1,
                "data": null,                
                "class": 'btnCol',
                "defaultContent": 
                '<div class="btn-group">' +
                '<i class="fa fa-fw fa-plus-circle" id="btnAddExtraServicesClaim"></i>' +
                '<i class="fa fa-fw fa-edit" id="btnEditClaim"></i>'+
                '<i class="fa fa-fw fa-trash-o" id="btnDeleteClaim"></i>'
            },
            {
            "data" : null,
                render: function( data, type, row ) {
                    var multiple_price = data.multiple_price ;
                    if (data.multiple_price == 1) {
                        var icon =  '<i class = "fa fa-fw fa-user-plus" style="font-size:18px;color:#00a65a" title="Multiple Price" id="btnPaxBreaks"></i> &nbsp; &nbsp;';
                    } else {
                        var icon =  '<i class = "fa fa-fw fa-user-plus" style="font-size:18px;color:#e6e6e6" title="Single price"></i> &nbsp; &nbsp;';                            
                    }
                    return icon;
                }
            }
        ],

        "initComplete": function () {
            $('#tbl-productServicesClaim tbody')
                .off()
                .on( 'click', '#btnAddExtraServicesClaim', function (e) {
                    var table = $('#tbl-productServicesClaim').DataTable();
                    var data = table.row( $(this).parents('tr') ).data();
                    addExtraServiceClaim(data);
                })
                .on( 'click', '#btnDeleteClaim', function (e) {
                    var table = $('#tbl-productServicesClaim').DataTable();
                    var data = table.row( $(this).parents('tr') ).data();
                    alertServiceClaimDelete(data);
                })
                .on( 'click', '#btnEditClaim', function (e) {
                    var table = $('#tbl-productServicesClaim').DataTable();
                    var data = table.row( $(this).parents('tr') ).data();
                    editServiceClaim(data);
                    extraServiceGridClaim(data);
                })
                .on( 'click', '#btnPaxBreaks', function (e) {
                    var table = $('#tbl-productServicesClaim').DataTable();
                    var data = table.row( $(this).parents('tr') ).data();
                    allServicesPaxBreakGridClaim(data);
                    multiplePriceServiceClaim(data);
                })
                .on('click', 'td', function(e) {
                    var table = $('#tbl-productServicesClaim').DataTable();
                    var rowData = table.row( $(this).parents('tr') ).data();
                    if (rowData.specific_to == 'C' || rowData.specific_to == 'E' || rowData.specific_to == 'F') {
                        countryDetails(rowData, e);
                        $("#myDIV").show();
                    }
                    else if (rowData.specific_to == 'A') {
                        toDetails(rowData, e);
                        $("#myDIV").show();
                    } else {
                        $("#myDIV").hide();
                    }
                })
                .on('click', function(e) {
                    if ( $(this).is(':visible') ) {
                        //$("#myDIV").hide();
                    }
                })
                .on('mousemove', function(e){
                    $('#myDIV').css({
                        left: e.pageX,
                        top: e.pageY
                    });
                });
                if (addedClaim == true) {
                    row = $('#tbl-productServicesClaim tr:first-child');
                    $(row).addClass('DTTT_selected');
                }
        }
    });
}

function countryDetails(row2, e) {
    const url_display_selected_countries = "php/api/backofficeserviceclaim/selectedmarketclaim.php?t=" + encodeURIComponent(global_token) + "&id_product_service_claim=" + row2.id_product_service_claim;
    var objCountryClaim = {
        id_product_service_claim : row2.id_product_service_claim
    };
    tooltipText = "";
    $.ajax({
        url : url_display_selected_countries,
        method : "POST",
        data : objCountryClaim,
        cache: false,   
        dataType: "json",                                                                                                                                                                                                                                                                                                                                                                                                               
        success : function(data){
            var arrDisplay = [];
            data.forEach(function (arrayItem) {
                var y = arrayItem;   
                tooltipText = y.country_name;
                arrDisplay.push(tooltipText);
            });
            var vPool="";
            jQuery.each(arrDisplay, function(i, val) {
                vPool += val + ", ";
            });
    
            //We add vPool HTML content to #myDIV
            $('#myDIV').html(vPool);
        },
        error: function(error) {
            console.log('Error ${error}');
        }
    });
}


function alertServiceClaimDelete (data) {
    swal({
		title: "Are you sure?",
		text: "you want to delete ?",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#DD6B55',
		confirmButtonText: 'Yes, delete it!',
		closeOnConfirm: false,
		//closeOnCancel: false
	},
	function(){
        deleteServiceClaim(data);
	});
}

// Delete Product
function deleteServiceClaim(data) {
    var objDelClaim = {id_product_service_claim: data.id_product_service_claim};
    const url_delete_claim = "php/api/backofficeserviceclaim/deleteclaim.php?t=" + encodeURIComponent(global_token) + "&id_product_service_claim=" + data.id_product_service_claim;
    $.ajax({
        url: url_delete_claim,
        method: "POST",
        data: objDelClaim,
        dataType: "json",
        success: function (data) {
            if (data.OUTCOME == 'OK') { 
                swal("Deleted!", "Deleted !", "success");
                resetProductServicesClaim();
                extraServiceGridClaim(data);
            }
        },
        error: function (error) {
            swal("Cancelled", "Not Deleted - Please try again...", "error");
        }
    });
    
    var allParams = window.location.href.split('data=').pop();
    const urlParams = new URLSearchParams(allParams);
    var id_product_service_cost = urlParams.get("id_product_service_cost"); 
    var id_product_service_claim = 0;
    allServicesGridClaim(id_product_service_cost, id_product_service_claim);
}

function editServiceClaim(data) {
     if (data.on_api == 1) { 
        $('.toggle:eq(1)').addClass('btn-success').removeClass('btn-default off');
        $('#on_api_claim').prop('checked', true);
    } else { 
        $('.toggle:eq(1)').addClass('btn-default off').removeClass('btn-success');
        $('#on_api_claim').prop('checked', false);
        $(".toggle:eq(1)").removeClass("add_disabled");
    }
    if (data.on_approved == 1) { 
        $('.toggle:eq(0)').addClass('btn-success').removeClass('btn-default off');
        $('#on_approved_claim').prop('checked', true);
    } else { 
        $('.toggle:eq(0)').addClass('btn-default off').removeClass('btn-success');
        $('#on_approved_claim').prop('checked', false);
    }
    document.getElementById("id_product_service_claim").innerHTML = data.id_product_service_claim;

    if (data.specific_to == 'A') {
        loadTourOperatorClaim();
        specificToCtrl(data.id_product_service_claim);
    } else if (data.specific_to == 'C' || data.specific_to == 'D' || data.specific_to == 'E'  || data.specific_to == 'F') {
        $('#ddlmultiSpecificMarket').multiselect('destroy');
        specificCountryCtrl(data.id_product_service_claim);
        loadCountryClaim();
    } else if (data.specific_to == 'B') {
        $('#ddlmultiSpecificMarket').multiselect('destroy');
        $('#ddlMultiSpecificTo').multiselect('destroy');
        $('#ddlmultiSpecificMarket').css('display', 'none');
        $('#ddlMultiSpecificTo').css('display', 'none');
    } 
    // else if (data.specific_to == 'D') {
    //     $('#ddlmultiSpecificMarket').multiselect('destroy');
    //     $('#ddlMultiSpecificTo').multiselect('destroy');
    //     $('#ddlmultiSpecificMarket').css('display', 'none');
    //     $('#ddlMultiSpecificTo').css('display', 'none');
    // }

    $('#valid_from').val(data.valid_from);
    $('#valid_to').val(data.valid_to);
    $('#ps_adult_claim').val(data.ps_adult_claim);
    $('#ps_teen_claim').val(data.ps_teen_claim);
    $('#ps_child_claim').val(data.ps_child_claim);
    $('#ps_infant_claim').val(data.ps_infant_claim);
    $('#id_currency').val(data.id_currency);
    $('#specific_to').val(data.specific_to);

    var chkmonday = data.ex_monday;
    var chktuesday = data.ex_tuesday;    
    var chkwednesday = data.ex_wednesday;    
    var chkthursday = data.ex_thursday;    
    var chkfriday = data.ex_friday;
    var chksaturday = data.ex_saturday;
    var chksunday = data.ex_sunday;

    var chkmultipleprice = data.multiple_price;

    if (data.rollover_type == 'Same Rate') {
        $('#txtRollOver').val(0);
        $('#txtRollOver').attr('disabled', 'disabled');
        $('#roll_over').val('Same Rate');
    }
    if (data.rollover_type == 'On Request') {
        $('#txtRollOver').val(0);
        $('#txtRollOver').attr('disabled', 'disabled');
        $('#roll_over').val('On Request');
    }
    if (data.rollover_type == 'Percentage') {
        $('#txtRollOver').val(data.rollover_value);
        $('#roll_over').val('Percentage');
        $('#txtRollOver').removeAttr('disabled');
    }

    if (data.rollover_type == 'Fix Amount') {
        $('#txtRollOver').val(data.rollover_value);        
        $('#roll_over').val('Fix Amount');
        $('#txtRollOver').removeAttr('disabled');
    }


    if (chkmonday == '1') {
        $('#ex_monday').prop('checked', true);
    } else if (chkmonday == '0') {
        $('#ex_monday').prop('checked', false);
    } 
    if (chktuesday == '1') {
        $('#ex_tuesday').prop('checked', true);    
    } else if (chktuesday == '0') {
        $('#ex_tuesday').prop('checked', false);
    } 
    if (chkwednesday == '1') {
        $('#ex_wednesday').prop('checked', true);
    } else if (chkwednesday == '0') {
        $('#ex_wednesday').prop('checked', false);
    } 
    if (chkthursday == '1') {
        $('#ex_thursday').prop('checked', true);
    } else if (chkthursday == '0') {
        $('#ex_thursday').prop('checked', false);
    } 
    if (chkfriday == '1') {
        $('#ex_friday').prop('checked', true);
    } else if (chkfriday == '0') {
        $('#ex_friday').prop('checked', false);
    } 
    if (chksaturday == '1') {
        $('#ex_saturday').prop('checked', true);
    } else if (chksaturday == '0') {
        $('#ex_saturday').prop('checked', false);
    } 
    if (chksunday == '1') {
        $('#ex_sunday').prop('checked', true);
    } else if (chksunday == '0') {
        $('#ex_sunday').prop('checked', false);
    }  

    if (chkmultipleprice == '1') {
        $('#multiple_price').prop('checked', true);           
        document.getElementById("ps_adult_claim").disabled = true;
        document.getElementById("ps_teen_claim").disabled = true;
        document.getElementById("ps_child_claim").disabled = true;
        document.getElementById("ps_infant_claim").disabled = true;
        $("#ps_adult_claim").val(0); 
        $("#ps_teen_claim").val(0); 
        $("#ps_child_claim").val(0); 
        $("#ps_infant_claim").val(0); 

    } else if (chkmonday == '0') {
        $('#multiple_price').prop('checked', false);
    }

}

function toDetails(row2, e) {
    const url_display_selected_countries = "php/api/backofficeserviceclaim/selectedtoclaim.php?t=" + encodeURIComponent(global_token) + "&id_product_service_claim=" + row2.id_product_service_claim;
    var objCountryClaim = {
        id_product_service_claim : row2.id_product_service_claim
    };
    tooltipText = "";
    $.ajax({
        url : url_display_selected_countries,
        method : "POST",
        data : objCountryClaim,
        cache: false,   
        dataType: "json",                                                                                                                                                                                                                                                                                                                                                                                                               
        success : function(data){
            var arrDisplay = [];
            data.forEach(function (arrayItem) {
                var y = arrayItem; 
                tooltipText = y.toname;
                arrDisplay.push(tooltipText);
            });
            var vPool="";
            count = 1;
            jQuery.each(arrDisplay, function(i, val) {
                vPool += count++ + " - " + val + "<br> ";
            });
    
            //We add vPool HTML content to #myDIV
            $('#myDIV').html(vPool);
        },
        error: function(error) {
            console.log('Error ${error}');
        }
    });
}