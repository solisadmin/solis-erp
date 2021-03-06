$('#tbl-product, #tbl-productServices').DataTable({
    'paging'      : true,
    'lengthChange': false,
    'searching'   : true,
    'ordering'    : true,
    'info'        : true,
    'autoWidth'   : false
});

//Date picker
$('#valid_from').datepicker({		
    autoclose: true,
    format: 'yyyy-mm-dd'
});
$('#valid_to').datepicker({		
    autoclose: true,
    format: 'yyyy-mm-dd'
});
$('.select2').select2();

$('#duration').durationPicker({
    showDays : false
});

/////////////////////////////////////////
// model --> fetch Api Currency sell ////
/////////////////////////////////////////
const url_currency_buy = "php/api/backofficeproduct/currency_combo_rates.php?t=" + encodeURIComponent(global_token); 

$.ajax({
    url : url_currency_buy,
    type : "GET",
    success : function(data) {
        helpersDropdownCurrency.buildDropdown(
            jQuery.parseJSON(data),
            $('#id_currency'),
            'Select an option'
        );
    }, 
    error:function(error) {
        console.log('Error ${error}');
    }
});
var helpersDropdownCurrency = {
    buildDropdown: function(result, dropdown, emptyMessage) {
        // Remove current options
        dropdown.html('');
        // Add the empty option with the empty message
        dropdown.append('<option value="">' + emptyMessage + '</option>');
        // Check result isnt empty
        if(result != '') {
            // Loop through each of the results and append the option to the dropdown
            $.each(result, function(data, result) {
                dropdown.append('<option value="' + result.value + '"name="' + result.text + '">' + result.text + '</option>');
            });
        }
    }
}



/////////////////////////////////////////
// model --> fetch Api Service //////////
/////////////////////////////////////////
const url_supplier = "php/api/backofficeproduct/combocreditor.php?t=" + encodeURIComponent(global_token); 

$.ajax({
    url : url_supplier,
    type : "GET",
    success : function(data) {
        helpersDropdownSupplier.buildDropdown(
            jQuery.parseJSON(data),
            $('#id_creditor'),
            'Select an option'
        );
    }, 
    error:function(error) {
        console.log('Error ${error}');
    }
});
var helpersDropdownSupplier = {
    buildDropdown: function(result, dropdown, emptyMessage) {
        // Remove current options
        dropdown.html('');
        // Add the empty option with the empty message
        dropdown.append('<option value="">' + emptyMessage + '</option>');
        // Check result isnt empty
        if(result != '') {
            // Loop through each of the results and append the option to the dropdown
            $.each(result, function(data, result) {
                dropdown.append('<option value="' + result.id_creditor + '">' + result.creditor_name + '</option>');
            });
        }
    }
}

$(document).ready(function(){
    var allParams = window.location.href.split('data=').pop();
    const urlParams = new URLSearchParams(allParams);
    var servicetype = urlParams.get("servicetype");

    applyFor();
    if (servicetype == "TRANSFER") {
        $("#id_coast_label").css("display", "none");
        $("#id_coast").css("display", "none");        
        $("#id_service_1").css("display", "none");
        $("#id_service_2").css("display", "block");
        $("#duration1").css("display", "none");
        $("#duration2").css("display", "none");
        $("#duration_label").css("display", "none");
        $("#chk_operation").css("display", "none");
        $("#ageActivity").css("display", "none");
        $("#adultActivity").css("display", "none");
        $("#infantActivity").css("display", "none");
        $("#childActivity").css("display", "none");
        $("#id_creditor_blk").css("display", "none");
        $("#special_name_all").css("display", "none");
        $("#special_name_transfer_blk").css("display", "block");
        $("#id_tax_blk").css("display", "none");
        $("#age_inf_from").val('0');
        $("#age_inf_to").val('2');
        $("#age_child_from").val('3');
        $("#age_child_to").val('12');
        $("#is_package_blk").css("display", "none");
        $("#teenActivity").css("display", "none");
        $("#applyForLabel").css("display", "none");
        $("#blckAgePolicy").css("display", "none");
    
        $( "#service_name_transfer" ).change(function () {
            $( "#service_name_transfer option:selected" ).each(function() {
                service_name = $( this ).text();
                if (service_name == "SOUTH EAST" || service_name == "OTHER COAST") {
                    $('#special_name_transfer').css('display', 'block');  
                    $('#special_name_transfer').val('AIRPORT');
                   // $("#special_name_transfer option[value='DROP ON']").hide();
                    $("#special_name_transfer option[value='DROP OFF']").hide();
                    $("#special_name_transfer option[value='FULL DAY']").hide();
                    $("#special_name_transfer option[value='HALF DAY']").hide();
                    $("#special_name_transfer option[value='NIGHT TOUR']").hide();
                    $("#special_name_transfer option[value='AIRPORT']").show();
                    $("#special_name_transfer option[value='PORT']").show();
                } else if (service_name == "INTER HOTEL" || service_name == "ONE WAY"|| service_name == "NORTHERN COAST") {                     
                    $('#special_name_transfer').css('display', 'none');     
                    $('#special_name_transfer').val('');
                } else if (service_name == "ACTIVITY") {        
                    $('#special_name_transfer').css('display', 'block');              
                    $("#special_name_transfer option[value='AIRPORT']").hide();
                    $("#special_name_transfer option[value='PORT']").hide();                    
                   // $("#special_name_transfer option[value='DROP ON']").show();
                    $("#special_name_transfer option[value='DROP OFF']").show();
                    $("#special_name_transfer option[value='FULL DAY']").show();
                    $("#special_name_transfer option[value='HALF DAY']").show();
                    $("#special_name_transfer option[value='NIGHT TOUR']").show();
                    $('#special_name_transfer').val('Select an Option');
                }
            });
        }).change();

    } if(servicetype == "ACTIVITY" || servicetype == "OTHER") {
        $("#id_creditor_blk").css("display", "block");
        $("#id_tax_blk").css("display", "block");
        $("#special_name_all").css("display", "block");
        $("#special_name_transfer_blk").css("display", "none");
        var for_adult = urlParams.get("for_adult");
        var for_child = urlParams.get("for_child");
        var for_infant = urlParams.get("for_infant");
        var for_teen = urlParams.get("for_teen");

        if (for_infant > 0) { 
            $("#ps_infant_cost").css("display", "block");
            $("#ps_infant_cost").attr("placeholder", "Infant");
            $("#ps_infant_cost_modal").css("display", "block");
            $("#ps_infant_cost_modal").attr("placeholder", "Infant");
        } if (for_infant <= 0) { 
            $("#ps_infant_cost_addon").css("display", "none");
            $("#ps_infant_cost").css("display", "none");
            $("#ps_infant_cost_addon_modal").css("display", "none");
            $("#ps_infant_cost_modal").css("display", "none");
        }

        if (for_teen > 0) { 
            $("#ps_teen_cost").css("display", "block");
            $("#ps_teen_cost").attr("placeholder", "Teen");
            $("#ps_teen_cost_modal").css("display", "block");
            $("#ps_teen_cost_modal").attr("placeholder", "Teen");
        } if (for_teen <= 0) { 
            $("#ps_teen_cost_addon").css("display", "none");
            $("#ps_teen_cost").css("display", "none");
            $("#ps_teen_cost_addon_modal").css("display", "none");
            $("#ps_teen_cost_modal").css("display", "none");
        }

        if (for_child > 0) { 
            $("#ps_child_cost").css("display", "block");
            $("#ps_child_cost").attr("placeholder", "Child");
            $("#ps_child_cost_modal").css("display", "block");
            $("#ps_child_cost_modal").attr("placeholder", "Child");
        } if (for_child <= 0) { 
            $("#ps_child_cost_addon").css("display", "none");
            $("#ps_child_cost").css("display", "none");
            $("#ps_child_cost_addon_modal").css("display", "none");
            $("#ps_child_cost_modal").css("display", "none");
        }

        if (for_adult > 0) { 
            $("#ps_adult_cost").css("display", "block");
            $("#ps_adult_cost").attr("placeholder", "Adult");
            $("#ps_adult_cost_modal").css("display", "block");
            $("#ps_adult_cost_modal").attr("placeholder", "Adult");
        } if (for_adult <= 0) { 
            $("#ps_adult_cost_addon").css("display", "none");
            $("#ps_adult_cost").css("display", "none");
            $("#ps_adult_cost_addon_modal").css("display", "none");
            $("#ps_adult_cost_modal").css("display", "none");
        }
    }
});

function applyFor() {
    var chkinfant = document.getElementById("for_infant");
    var chkchild = document.getElementById("for_child");
    var chkteen = document.getElementById("for_teen");
    var chkadult = document.getElementById("for_adult");

    $('.requiredChkApplyFor').on('click',function () {
        if (chkinfant.checked == true) {
            $("#age_inf_from").prop("readonly", false);
            $("#age_inf_to").prop("readonly", false);
        } if (chkinfant.checked == false) {
            $("#age_inf_from").prop("readonly", true);
            $("#age_inf_to").prop("readonly", true);
            $("#age_inf_from").val(0);
            $("#age_inf_to").val(0);
        }

        if (chkchild.checked) {
            $("#age_child_from").prop("readonly", false);
            $("#age_child_to").prop("readonly", false);
        } if (chkchild.checked == false) {
            $("#age_child_from").prop("readonly", true);
            $("#age_child_to").prop("readonly", true);
            $("#age_child_from").val(0);
            $("#age_child_to").val(0);
        }

        if (chkteen.checked) {
            $("#age_teen_from").prop("readonly", false);
            $("#age_teen_to").prop("readonly", false);
        } if (chkteen.checked == false) {
            $("#age_teen_from").prop("readonly", true);
            $("#age_teen_to").prop("readonly", true);
            $("#age_teen_from").val(0);
            $("#age_teen_to").val(0);
        }

        if (chkadult.checked) {
            $("#min_age").prop("readonly", false);
            $("#max_age").prop("readonly", false);
        }
        if (chkadult.checked == false) {
            $("#min_age").prop("readonly", true);
            $("#max_age").prop("readonly", true);
            $("#min_age").val(0);
            $("#max_age").val(0);
        }

    });
}

