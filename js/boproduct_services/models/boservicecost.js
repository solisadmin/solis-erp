$(document).ready(function(){
    dateRangePickerValid();
    var allParams = window.location.href.split('productservicecost').pop();
    const urlParams = new URLSearchParams(allParams);    
    var charge = urlParams.get("charge"); 
    var servicetype = urlParams.get("servicetype"); 
    if (charge == 'UNIT') {
        $("#ps_teen_cost").css("display", "none");        
        $("#ps_child_cost").css("display", "none");
        $("#ps_infant_cost").css("display", "none");
        
        $("#ps_teen_cost_addon").css("display", "none");        
        $("#ps_child_cost_addon").css("display", "none");
        $("#ps_infant_cost_addon").css("display", "none");
        $('#ps_adult_cost_addon').text('Unit');
        $('#ps_adult_cost').attr("placeholder", "Unit");
    }
});

function dateRangePickerValid() {
    var allParams = window.location.href.split('productservicecost').pop();
    const urlParams = new URLSearchParams(allParams);

    var valid_from = urlParams.get("valid_from"); 
    var valid_to = urlParams.get("valid_to"); 
    console.log( 'From - 1',  valid_from, '/n', 'To - 1',  valid_to);

	var valid_from = valid_from.split("-");
    var valid_from1 = valid_from[2]+"/"+valid_from[1]+"/"+valid_from[0];
    
    console.log( 'From - z',  valid_from);
	//var valid_from1 = new Date(valid_from);
	var valid_to = valid_to.split("-");
	var valid_to1 = valid_to[2]+"/"+valid_to[1]+"/"+valid_to[0];
    //var valid_to1 = new Date(valid_to);
    $('#daterangeServiceFromTo').daterangepicker({
        "showDropdowns": true,
		"opens": "center",
        locale: {
            format: 'DD/MM/YYYY'
        },
        "autoApply": true,
        "opens": "center",
        startDate: valid_from1,
        endDate: valid_to1,
        "minDate" : valid_from1, 
        "maxDate" : valid_to1
    }, function(start, end, label) {
        valid_from1 = start.format('YYYY/DD/MM');
        valid_to1 = end.format('YYYY/DD/MM');
    });
}

function saveCost() {
    var allParams = window.location.href.split('data=').pop();
    const urlParams = new URLSearchParams(allParams);
    var id_product_service = urlParams.get("psid");

    var id_product_service_cost = document.getElementById("id_product_service_cost_1").innerHTML;

    var valid_from = $("#daterangeServiceFromTo").data('daterangepicker').startDate.format('YYYY-MM-DD');
    var valid_to = $("#daterangeServiceFromTo").data('daterangepicker').endDate.format('YYYY-MM-DD');
    const url_overlap_date = "php/api/backofficeproduct/gridservicecost.php?t=" + encodeURIComponent(global_token) + "&id_product_service=" + id_product_service;
    $.ajax({
        url : url_overlap_date,
        method : "POST", 
        dataType: 'JSON',                                                                                                                                                                                                                                                                                                                                                                                                                                            
        success : function(data) {
            if (id_product_service_cost == 0) {
                data.forEach(function (arrayItem) {
                    x = arrayItem;
                });
                if ((valid_from > x.valid_from) && (valid_to > x.valid_to) && (valid_from > x.valid_to)) {
                    addCostProductService();
                } else {
                    alert('Date Overlap');                   
                    resetFormAddServiceCost();
                }
            } else {
                addCostProductService();
            }
            
        },
        error: function(error) {
            console.log('Error ${error}');
        }
    })
}

function addCostProductService() {
        var allParams = window.location.href.split('productservicecost').pop();
        const urlParams = new URLSearchParams(allParams);
        var id_dept = urlParams.get("iddept"); 
        var charge = urlParams.get("charge"); 
        var id_product_service = urlParams.get("psid"); 
        var valid_from = $("#daterangeServiceFromTo").data('daterangepicker').startDate.format('YYYY-MM-DD');
		var valid_to = $("#daterangeServiceFromTo").data('daterangepicker').endDate.format('YYYY-MM-DD');
        var ps_adult_cost = $('#ps_adult_cost').val();
        var ps_teen_cost = $('#ps_teen_cost').val();
        var ps_child_cost = $('#ps_child_cost').val();
        var ps_infant_cost = $('#ps_infant_cost').val();
        var id_currency = $('#id_currency').val();
        var currency = $('#id_currency').find(":selected").text();
        var id_product_service_cost = document.getElementById("id_product_service_cost_1").innerHTML;

        var chkmultipleprice = document.getElementById("multiple_price_cost"); 
         if (chkmultipleprice.checked == true) {
            var multiple_price_cost = 1;
        } else { 
            var multiple_price_cost = 0;
        }

        if (id_product_service_cost != 0) {
            var objServiceCostEdit = {
                id_product_service: id_product_service,
                valid_from: valid_from,
                valid_to : valid_to,
                ps_adult_cost: ps_adult_cost,
                ps_teen_cost: ps_teen_cost,
                ps_child_cost: ps_child_cost,
                ps_infant_cost: ps_infant_cost,
                id_currency: id_currency,
                currency: currency,
                id_dept: id_dept,
                multiple_price_cost: multiple_price_cost
            };
            const url_edit_service_cost = "php/api/backofficeproduct/updateservicecost.php?t=" + encodeURIComponent(global_token) + "&id_product_service_cost=" + id_product_service_cost;
            $.ajax({
                url : url_edit_service_cost,
                method : "POST",
                data : objServiceCostEdit,                                                                                                                                                                                                                                                                                                                                                                                                                                              
                success : function(data){
                    console.log('value', data);
                   // resetFormAddServiceCost();
                    allServicesGridCost();
                },
                error: function(error) {
                    console.log('Error ${error}');
                }
            });
        } else {
            var objServiceCost = {
                id_product_service_cost:-1, //for new items, id is always -1
                id_product_service: id_product_service,
                valid_from: valid_from,
                valid_to : valid_to,
                ps_adult_cost: ps_adult_cost,
                ps_teen_cost: ps_teen_cost,
                ps_child_cost: ps_child_cost,
                ps_infant_cost: ps_infant_cost,
                id_currency: id_currency,                
                currency: currency,                
                charge: charge,
                id_dept: id_dept,
                multiple_price_cost: multiple_price_cost
            };
            const url_save_service_cost = "php/api/backofficeproduct/saveservicecost.php?t=" + encodeURIComponent(global_token);
            $.ajax({
                url : url_save_service_cost,
                method : "POST",
                data : objServiceCost,  
                dataType: "json",     
                success : function(data){
                    console.log('value', data.OUTCOME);
                    if (data.OUTCOME == 'OK') {
                        resetFormAddServiceCost();
                        allServicesGridCost();
                        var addedCost = true;
                        allServicesGridCost(addedCost);
                    }
                },
                error: function(error) {
                    console.log('Error ${error}');
                }
            });
        }
        document.getElementById("id_product_service_cost_1").innerHTML = 0;
    }

// Function Reset Form Add New Service
function resetFormAddServiceCost() {
    $('.toast_added').stop().fadeIn(400).delay(3000).fadeOut(500);
    $('#valid_from').val('').end();
    $('#valid_to').val('').end();
    $('#ps_adult_cost').val('').end();
    $('#ps_teen_cost').val('').end();
    $('#ps_child_cost').val('').end();
    $('#ps_infant_cost').val('').end();
    $('#id_currency').val('').end();
    document.getElementById("id_product_service_cost_1").innerHTML = 0;
    $('#multiple_price_cost').prop('checked', false);
    var multiple_price_cost = 0;        
    document.getElementById("ps_adult_cost").disabled = false;
    document.getElementById("ps_teen_cost").disabled = false;
    document.getElementById("ps_child_cost").disabled = false;
    document.getElementById("ps_infant_cost").disabled = false;
}