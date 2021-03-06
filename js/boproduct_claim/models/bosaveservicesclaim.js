$(document).ready(function () {
    var allParams = window.location.href.split('data=').pop();
    const urlParams = new URLSearchParams(allParams);
    // As TO is first in the list it should be loaded by default
    loadTourOperatorClaim();
    loadCountryClaim();
    dateRangePickerValid();
    var charge = urlParams.get("charge"); 
    var servicetype = urlParams.get("servicetype"); 
    $('#charge').val(charge);
    $('#charge_pax_break').val(charge);
    
    // Disable or Enable Charge - Child and Adult active for transfer
    if (servicetype == "TRANSFER") {
        $("#ps_teen_claim").css("display", "none"); 
        $("#ps_teen_claim_addon").css("display", "none"); 
        $("#ps_infant_claim").css("display", "none"); 
        $("#ps_infant_claim_addon").css("display", "none");
    }

    if (charge == 'UNIT') {
        $("#ps_teen_claim").css("display", "none");        
        $("#ps_child_claim").css("display", "none");
        $("#ps_infant_claim").css("display", "none");
        
        $("#ps_teen_claim_addon").css("display", "none");        
        $("#ps_child_claim_addon").css("display", "none");
        $("#ps_infant_claim_addon").css("display", "none");
        $('#ps_adult_claim_addon').text('Unit');
        $('#ps_adult_claim').attr("placeholder", "Unit");
    }

    var monday = urlParams.get("on_monday"); 
    var tuesday = urlParams.get("on_tuesday"); 
    var wednesday = urlParams.get("on_wednesday"); 
    var thursday = urlParams.get("on_thursday"); 
    var friday = urlParams.get("on_friday"); 
    var saturday = urlParams.get("on_saturday"); 
    var sunday = urlParams.get("on_sunday"); 

    if (monday == 1){
        $("#ex_monday1").css("display", "block");
    } if (tuesday == 1){
        $("#ex_tuesday1").css("display", "block");
    } if (wednesday == 1){
        $("#ex_wednesday1").css("display", "block");
    } if (thursday == 1){
        $("#ex_thursday1").css("display", "block");
    } if (friday == 1){
        $("#ex_friday1").css("display", "block");
    } if (saturday == 1){
        $("#ex_saturday1").css("display", "block");
    } if (sunday == 1){
        $("#ex_sunday1").css("display", "block");
    }
});

$("#specific_to").change(function () {
    var specificto = this.value;
    
    if (specificto == "B") {
        $("#multiSpecificTo").css("display", "none");
        $("#multiSpecificMarket").css("display", "none");
        
        $('#ddlmultiSpecificMarket').multiselect('destroy');
        $('#ddlMultiSpecificTo').multiselect('destroy');
    } 
    if (specificto == "D" || specificto == "E" || specificto == "F") {
        $("#multiSpecificTo").css("display", "none");
        // $("#multiSpecificMarket").css("display", "none");
        
        $('#ddlmultiSpecificMarket').multiselect('destroy');
        $('#ddlMultiSpecificTo').multiselect('destroy');
        $("#multiSpecificTo").css("display", "none");
        $("#multiSpecificMarket").css("display", "block");
        loadCountryClaim();
    }
    if (specificto == "A") {
        $("#multiSpecificMarket").css("display", "none");
        $("#multiSpecificTo").css("display", "block");
        loadTourOperatorClaim();
        $('#ddlMultiSpecificTo').multiselect('destroy');
    }
    if (specificto == "C") {
        $('#ddlmultiSpecificMarket').multiselect('destroy');
        $("#multiSpecificTo").css("display", "none");
        $("#multiSpecificMarket").css("display", "block");
        loadCountryClaim();
    }
});

// Load Tour Operator depending on country ID
function loadTourOperatorClaim() {
    $("#multiSpecificMarket").css("display", "none");
    const url_to = "php/api/backofficeserviceclaim/tocombo.php?t=" + encodeURIComponent(global_token); 
    $.ajax({
        type: "POST",
        url: url_to,
        dataType: "json",
        cache: false,
        success: function(data)
            {
                $("#ddlMultiSpecificTo").empty();
                $.each(data, function (key, val) {
                $("#ddlMultiSpecificTo").append('<option value="' + val.id + '">' + val.toname + '</option>');
                toname = val.toname;
            });                
                $("#ddlMultiSpecificTo").attr('multiple', 'multiple'); 
                $("#ddlMultiSpecificTo").multiselect({
                    buttonWidth: '313px',
                    includeSelectAllOption: true,
                    nonSelectedText: 'Select an Option',
                    enableFiltering: true,
                    enableHTML: true,
                    buttonClass: 'btn large btn-default',
                    enableCaseInsensitiveFiltering: true,
                    onChange: function(element, checked) {
                        var brands = $('#ddlMultiSpecificTo option:selected');
                        var selected = [];
                        $(brands).each(function(index, brand){
                            selected.push($(this).val());
                            selectedTo = selected.join();
                        });
                    }
                });
            }
        }
    );
}

// Load Market By Default - On Button Edit click
function loadCountryClaim() {
    const url_market = "php/api/backofficeserviceclaim/attachcountriesclaim.php?t=" + encodeURIComponent(global_token);
    $.ajax({
        type: "POST",
        url: url_market,
        dataType: "json",
        cache: false,
        success: function(data)
            {
                $("#ddlmultiSpecificMarket").empty();
                $.each(data, function (key, val) {
                $("#ddlmultiSpecificMarket").append('<option value="' + val.id + '"  data-subtext="' + val.marketfk + '">'+ val.continent + ' - ' + val.market_name + ' - ' + val.country_name + '</option>');
            });                
                $("#ddlmultiSpecificMarket").attr('multiple', 'multiple'); 
                $("#ddlmultiSpecificMarket").multiselect({
                    buttonWidth: '313px',
                    includeSelectAllOption: true,
                    nonSelectedText: 'Select an Option',
                    enableFiltering: true,
                    enableHTML: true,
                    buttonClass: 'btn large btn-default',
                    enableCaseInsensitiveFiltering: true,
                    onChange: function(element, checked) {
                        var brands = $('#ddlmultiSpecificMarket option:selected');
                        var selectedCountryArr = [];
                        $(brands).each(function(index, brand){
                            selectedRatesType = $(this).val();
                            selectedCountryArr.push(selectedRatesType);
                            selectedCountryArrJoin = selectedCountryArr.join();
                        });
                    }
                });
            }
        }
    );
}


function dateRangePickerValid() {
    
    const urlParams = new URLSearchParams(allParams);

    var valid_from = urlParams.get("valid_from"); 
    var valid_to = urlParams.get("valid_to"); 
	
	var valid_from = valid_from.split("-");
	var valid_from = valid_from[0]+","+valid_from[1]+","+valid_from[2];
	var valid_from = new Date(valid_from);
	var valid_to = valid_to.split("-");
	var valid_to = valid_to[0]+","+valid_to[1]+","+valid_to[2];
    var valid_to1 = new Date(valid_to);
    
    console.log( 'From -',  valid_from, 'To -',  valid_to);

    $('#daterangeServiceFromTo').daterangepicker({
        "showDropdowns": true,
		"opens": "center",
        locale: {
            format: 'DD/MM/YYYY'
        },
        "autoApply": true,
        "opens": "center",
        startDate: valid_from,
        endDate: valid_to1,
        "minDate" : valid_from,
        "maxDate" : valid_to1
    }, function(start, end, label) {
        valid_from = start.format('YYYY/MM/DD');
        valid_to1 = end.format('YYYY/MM/DD');
    });
}

function saveClaim() {
    var allParams = window.location.href.split('data=').pop();
    const urlParams = new URLSearchParams(allParams);
    var id_product_service_cost = urlParams.get("id_product_service_cost"); 

    var id_product_service_claim = document.getElementById("id_product_service_claim").innerHTML;
    var valid_from = $("#daterangeServiceFromTo").data('daterangepicker').startDate.format('YYYY-MM-DD');
    var valid_to = $("#daterangeServiceFromTo").data('daterangepicker').endDate.format('YYYY-MM-DD');
    var specific_to = $('#specific_to').val();
    var id_currency = $('#id_currency').val();
    
    const url_overlap_claim_date = "php/api/backofficeserviceclaim/gridclaimlist.php?t=" + encodeURIComponent(global_token) + "&id_product_service_cost=" +id_product_service_cost + "&id_product_service_claim=" +id_product_service_claim;
    $.ajax({
        url : url_overlap_claim_date,
        method : "POST", 
        dataType: 'JSON',                                                                                                                                                                                                                                                                                                                                                                                                                                            
        success : function(data){
            var overlap = false; 
                data.forEach(function (arrayItem) {
                    x = arrayItem;
                });
                // Check Date from datatable then compare - with value input
                if ((valid_from > x.valid_from) && (valid_to > x.valid_to) && (valid_from > x.valid_to)) {
                    overlap = false;
                    addClaimProductService();
                } else {
                    overlap = true;
                    if (specific_to == 'A') { // To
                        checkTo(x);
                        //alert('Date Overlap - Tour Operator');   
                    } else if (specific_to == 'C' || specific_to == 'D' || specific_to == 'E' || specific_to == 'F') { // Market
                        checkMarket(x);  
                        //alert('Date Overlap - Market');   
                    } 
                    else if (specific_to == 'B') { //Worldwide
                        if (((valid_to == x.valid_to) || (valid_from == x.valid_from))) {
                            if (id_currency == x.id_currency) {
                                alert('Only One Currency for One Category');
                            } 
                        } else {
                            addClaimProductService();
                        }
                    }
                    else if (specific_to == 'D' || specific_to == 'E' || specific_to == 'F') { // Directsales
                        console.log((valid_to == x.valid_to) || (valid_from == x.valid_from));
                        
                        if (((valid_to == x.valid_to) || (valid_from == x.valid_from))) {
                            alert('Please choose another Date for Direct Sales');
                        } 
                        console.log('id_currency', id_currency);
                        if (id_currency == "Select an option" || id_currency == "") {
                            alert('Please choose a currency for Direct Sales');
                        }
                        
                        if (specific_to != x.specific_to) {
                            if (id_currency == x.id_currency) {
                                alert('Please choose another currency for Direct Sales');
                            }
                            else {
                                addClaimProductService();
                            }
                        } else { 
                            alert('Please choose another Date for Direct Sales');
                        }
                        
                    }
                   // resetProductServicesClaim();
                }
        },
        error: function(error) {
            console.log('Error ${error}');
        }
    });
}

function checkTo(data) {
    var id_product_service_claim = data.id_product_service_claim;
    var id_tour_operator = $('#ddlMultiSpecificTo').val();

    const url_claim_to = "php/api/backofficeserviceclaim/populateselectedto.php?t=" + encodeURIComponent(global_token)+ "&id_product_service_claim=" + id_product_service_claim; 
    $.ajax({
        type: "POST",
        url: url_claim_to,
        dataType: "json",
        cache: false,
        success: function(data)
                {
                z = [];
                data.forEach(function (arrayItem) {
                    var x = arrayItem;
                    z.push(x.id_tour_operator);
                });
                arrayCompareTo(z, id_tour_operator);     
        },    
        error: function(error) {
            addClaimProductService();
        }
    });
}

function arrayCompareTo(z, id_tour_operator) {
    var bExistCountry = false;
    $.each(id_tour_operator, function(index,value) {
        if($.inArray(value,z)!=-1) {
            bExistCountry = true;
        }
        // Does not exist in both array
        if(bExistCountry){
            return false;
        }
    });

    if (!bExistCountry) {  
        addClaimProductService();
    } else {
        alert('bad request');
    }
}

function checkMarket(data) { 
    var id_product_service_claim = data.id_product_service_claim;    
    var id_country = $('#ddlmultiSpecificMarket').val();
    const url_claim_countries = "php/api/backofficeserviceclaim/populateselectedcountries.php?t=" + encodeURIComponent(global_token)+ "&id_product_service_claim=" + id_product_service_claim; 
    $.ajax({
        type: "POST",
        url: url_claim_countries,
        dataType: "json",
        cache: false,
        success: function(data)
                {
                arrC = [];
                data.forEach(function (arrayItem2) {
                    var xy = arrayItem2;
                    arrC.push(xy.id_country);
                });
                arrayCompareCountries(arrC, id_country);
        },    
        error: function(error) {
            addClaimProductService();
        }
    });
}

function arrayCompareCountries(z, id_country) {
    var bExists = false;
    $.each(id_country, function(index,value) {
        if($.inArray(value,z)!=-1) {
            bExists = true;
        }
        // Does not exist in both array
        if(bExists){
            return false;
        }
    });

    if (!bExists) {    
        addClaimProductService();
    } else {
        alert('bad request - Please Try another country');
    }
}

function addClaimProductService(){ 
    var id_product_service_claim = document.getElementById("id_product_service_claim").innerHTML;
    var id_country = $('#ddlmultiSpecificMarket').val();
    var id_tour_operator = $('#ddlMultiSpecificTo').val();
    var valid_from = $("#daterangeServiceFromTo").data('daterangepicker').startDate.format('YYYY-MM-DD');
	var valid_to = $("#daterangeServiceFromTo").data('daterangepicker').endDate.format('YYYY-MM-DD');
    
    var ps_adult_claim_num = $('#ps_adult_claim').val();
    var ps_teen_claim_num = $('#ps_teen_claim').val();
    var ps_child_claim_num = $('#ps_child_claim').val();
    var ps_infant_claim_num = $('#ps_infant_claim').val();

    var ps_adult_claim = $('#ps_adult_claim').val();
    var ps_teen_claim = $('#ps_teen_claim').val();
    var ps_child_claim = $('#ps_child_claim').val();
    var ps_infant_claim = $('#ps_infant_claim').val();

    var id_currency = $('#id_currency').val();
    var currency = $('#id_currency').find(":selected").text();
    var specific_to = $('#specific_to').val();
    var specific_to_name = $('#specific_to').find(":selected").text();

    var chkmonday = document.getElementById("ex_monday");
    var chktuesday = document.getElementById("ex_tuesday");    
    var chkwednesday = document.getElementById("ex_wednesday");    
    var chkthursday = document.getElementById("ex_thursday");    
    var chkfriday = document.getElementById("ex_friday");
    var chksaturday = document.getElementById("ex_saturday");
    var chksunday = document.getElementById("ex_sunday");
    
    var chkmultipleprice = document.getElementById("multiple_price"); 
    var allParams = window.location.href.split('data=').pop();
    const urlParams = new URLSearchParams(allParams);    
    var id_dept = urlParams.get("id_dept"); 
    var id_product_service_cost = urlParams.get("id_product_service_cost"); 
    var id_product_service = urlParams.get("id_product_service"); 
    var charge = urlParams.get("charge");

    var rollover_type = $('#roll_over').val();
    var rollover_value = $('#txtRollOver').val();

    var on_approved_chk_claim = $('#on_approved_claim').prop('checked');
    var on_api_chk_claim = $('#on_api_claim').prop('checked');
    if (on_api_chk_claim == true) { 
        var on_api_1 = 1;
    } else { 
        var on_api_1 = 0;
    }

    if (on_approved_chk_claim == true) { 
        var on_approved_1 = 1;
    } else  { 
        var on_approved_1 = 0;
    }

    if (rollover_type == 'Percentage') {

        if (ps_adult_claim_num == "" || ps_adult_claim_num == "0") {
            ps_adult_claim_rollover = 0;
        } else { 
            ps_adult_claim_per = (parseInt(rollover_value) / 100) * parseInt(ps_adult_claim_num);
            ps_adult_claim_rollover = ps_adult_claim_per + parseInt(ps_adult_claim_num);
        }

        if (ps_teen_claim_num == "" || ps_teen_claim_num == "0") {
            ps_teen_claim_rollover = 0;
        } else {
            console.log('ps_teen_claim_num', ps_teen_claim_num);
            ps_teen_claim_per = (parseInt(rollover_value) / 100) * parseInt(ps_teen_claim_num);
            ps_teen_claim_rollover = ps_teen_claim_per + parseInt(ps_teen_claim_num);
        }

        if (ps_child_claim_num == "" || ps_child_claim_num == "0") {
            ps_child_claim_rollover = 0;
        } else {            
            ps_child_claim_per = (parseInt(rollover_value) / 100) * parseInt(ps_child_claim_num);
            ps_child_claim_rollover = ps_child_claim_per + parseInt(ps_child_claim_num);
        }

        if (ps_infant_claim_num == "" || ps_infant_claim_num == "0") {
            ps_infant_claim_rollover = 0;
        } else {
            ps_infant_claim_per = (parseInt(rollover_value) / 100) * parseInt(ps_infant_claim_num);
            ps_infant_claim_rollover = ps_infant_claim_per + parseInt(ps_infant_claim_num);
        }

    } else if (rollover_type == 'Fix Amount') {
        //check if zero
        if (ps_adult_claim_num == "" || ps_adult_claim_num == "0") {
            ps_adult_claim_rollover = 0;
        } else {
            ps_adult_claim_rollover = parseInt(ps_adult_claim_num) + parseInt(rollover_value);
        }

        if (ps_teen_claim_num == "" || ps_teen_claim_num == "0") {
            ps_teen_claim_rollover = 0;
        } else {
            ps_teen_claim_rollover = parseInt(ps_teen_claim_num) + parseInt(rollover_value);
        }

        if (ps_child_claim_num == "" || ps_child_claim_num == "0") {
            ps_child_claim_rollover = 0;
        } else {
            ps_child_claim_rollover = parseInt(ps_child_claim_num) + parseInt(rollover_value);
        }

        if (ps_infant_claim_num == "" || ps_infant_claim_num == "0") {
            ps_infant_claim_rollover = 0;
        } else {
            ps_infant_claim_rollover = parseInt(ps_infant_claim_num) + parseInt(rollover_value); 
        }

    } else { 
        ps_adult_claim_rollover = 0;
        ps_teen_claim_rollover = 0;
        ps_child_claim_rollover = 0;
        ps_infant_claim_rollover = 0;
    }

    if (chkmonday.checked) {
        ex_monday = 1;
    } else if (chkmonday.checked == false) {
        ex_monday = 0;
    } 
    if (chktuesday.checked) {
        ex_tuesday = 1;
    } else if (chktuesday.checked == false) {
        ex_tuesday = 0;
    } 
    if (chkwednesday.checked) {
        ex_wednesday = 1;
    } else if (chkwednesday.checked == false) {
        ex_wednesday = 0;
    } 
    if (chkthursday.checked) {
        ex_thursday = 1;
    } else if (chkthursday.checked == false) {
        ex_thursday = 0;
    } 
    if (chkfriday.checked) {
        ex_friday = 1;
    } else if (chkfriday.checked == false) {
        ex_friday = 0;
    } 
    if (chksaturday.checked) {
        ex_saturday = 1;
    } else if (chksaturday.checked == false) {
        ex_saturday = 0;
    } 
    if (chksunday.checked) {
        ex_sunday = 1;
    } else if (chksunday.checked == false) {
        ex_sunday = 0;
    }  

    if (specific_to == 'A') { 
        id_country = 0;
    } else if (specific_to == 'B') {
        id_country = 0;
        id_tour_operator = 0;
    } else if (specific_to == 'C' || specific_to == 'D' || specific_to == "E" || specific_to == "F") {
       
        id_tour_operator = 0;
    }

    if (chkmultipleprice.checked == true) {
        var multiple_price = 1; 
    } else { 
        var multiple_price = 0; 
    }

    var allParams = window.location.href.split('data=').pop();
    var charge = urlParams.get("charge"); 
    var servicetype = urlParams.get("servicetype"); 
    if (id_product_service_claim == 0) {
        const url_save_productservice_claim = "php/api/backofficeserviceclaim/saveproductservicesclaim.php?t=" + encodeURIComponent(global_token);
        var objProductServiceClaim = {
            id_product_service_claim: -1,
            id_product_service_cost: id_product_service_cost,
            id_product_service: id_product_service,
            valid_from: valid_from,
            valid_to: valid_to,
            id_dept: id_dept,
            specific_to: specific_to,
            charge: charge,
            ps_adult_claim: ps_adult_claim,
            ps_teen_claim: ps_teen_claim,
            ps_child_claim: ps_child_claim,
            ps_infant_claim: ps_infant_claim,        
            id_currency: id_currency,
            currency: currency,
            ex_monday: ex_monday,
            ex_tuesday: ex_tuesday,
            ex_wednesday: ex_wednesday,
            ex_thursday: ex_thursday,
            ex_friday: ex_friday,
            ex_saturday: ex_saturday,
            ex_sunday: ex_sunday,
            id_country: id_country,
            id_tour_operator: id_tour_operator,
            specific_to_name: specific_to_name, 
            rollover_type : rollover_type,
            rollover_value : rollover_value,
            ps_adult_claim_rollover: ps_adult_claim_rollover,
            ps_teen_claim_rollover: ps_teen_claim_rollover,
            ps_child_claim_rollover: ps_child_claim_rollover,
            ps_infant_claim_rollover: ps_infant_claim_rollover, 
            on_approved: on_approved_1, 
            on_api: on_api_1,
            multiple_price: multiple_price
        };

        console.log(objProductServiceClaim);

        $.ajax({
            url : url_save_productservice_claim,
            method : "POST",
            data : objProductServiceClaim, 
            cache: false,        
            dataType: 'JSON',                                                                                                                                                                                                                                                                                                                                                                                                            
            success : function(data) {
                console.log('data', data);
                if (data.multiple_price == "1") {
                    modalPaxBreaks();
                }                
                resetProductServicesClaim();
                $('.toast_added').stop().fadeIn(400).delay(3000).fadeOut(500);
            },
            error: function(error) {
                console.log('Error ${error}');
            }
        });
    } else {
        var id_product_service_claim = document.getElementById("id_product_service_claim").innerHTML;
        const url_update_productservice_claim = "php/api/backofficeserviceclaim/updateproductservicesclaim.php?t=" + encodeURIComponent(global_token) + "&id_product_service_claim=" +id_product_service_claim;
        var objProductServiceClaimUpdate = {            
            valid_from: valid_from,
            valid_to: valid_to,
            specific_to: specific_to,
            charge: charge,
            ps_adult_claim: ps_adult_claim,
            ps_teen_claim: ps_teen_claim,
            ps_child_claim: ps_child_claim,
            ps_infant_claim: ps_infant_claim,        
            id_currency: id_currency,
            currency: currency,
            ex_monday: ex_monday,
            ex_tuesday: ex_tuesday,
            ex_wednesday: ex_wednesday,
            ex_thursday: ex_thursday,
            ex_friday: ex_friday,
            ex_saturday: ex_saturday,
            ex_sunday: ex_sunday,
            id_country: id_country,
            id_tour_operator: id_tour_operator,
            specific_to_name: specific_to_name,
            rollover_type : rollover_type,
            rollover_value : rollover_value,
            ps_adult_claim_rollover: ps_adult_claim_rollover,
            ps_teen_claim_rollover: ps_teen_claim_rollover,
            ps_child_claim_rollover: ps_child_claim_rollover,
            ps_infant_claim_rollover: ps_infant_claim_rollover,
            on_approved: on_approved_1, 
            on_api: on_api_1,
            multiple_price: multiple_price
        };

        $.ajax({
            url : url_update_productservice_claim,
            method : "POST",
            data : objProductServiceClaimUpdate,                                                                                                                                                                                                                                                                                                                                                                                                                
            success : function(data){
               // resetProductServicesClaim();
                $('.toast_added').stop().fadeIn(400).delay(3000).fadeOut(500);
            },
            error: function(error) {
                console.log('Error ${error}');
            }
        });

        if (specific_to == 'A' || specific_to == 'B' || specific_to == 'C' || specific_to == 'D' || specific_to == 'E' || specific_to == 'F') { 
            // UPDATE MULTISELECT
            var id_product_service_claim = document.getElementById("id_product_service_claim").innerHTML;
            const url_update_to_claim = "php/api/backofficeserviceclaim/deleteselectedto.php?t=" + encodeURIComponent(global_token) + "&id_product_service_claim=" +id_product_service_claim;
            var objProductServiceClaimUpdateTo = {id_product_service_claim: id_product_service_claim};
            $.ajax({
                url: url_update_to_claim,
                method: "POST",
                data: objProductServiceClaimUpdateTo,
                success: function (data) {
                },
                error: function (error) {
                    console.log('Error ${error}');
                }
            });

             // UPDATE MULTISELECT
            const url_update_country_claim = "php/api/backofficeserviceclaim/deleteselectedcountries.php?t=" + encodeURIComponent(global_token) + "&id_product_service_claim=" +id_product_service_claim;
            var objProductServiceClaimUpdateCountry = {id_product_service_claim: id_product_service_claim};
            $.ajax({
                url: url_update_country_claim,
                method: "POST",
                data: objProductServiceClaimUpdateCountry,
                success: function (data) {
                },
                error: function (error) {
                    console.log('Error ${error}');
                }
            });
        }

        if (specific_to == 'A') { 
            // UPDATE MULTISELECT
            var id_product_service_claim = document.getElementById("id_product_service_claim").innerHTML;
                    
            const url_save_country_claim = "php/api/backofficeserviceclaim/saveselectedto.php?t=" + encodeURIComponent(global_token);
            var objProductServiceClaimSaveCountry = {
                id_product_service_claim_to: -1,
                id_product_service_claim: id_product_service_claim,
                id_tour_operator: id_tour_operator
            };
            $.ajax({
                url: url_save_country_claim,
                method: "POST",
                data: objProductServiceClaimSaveCountry,
                success: function (data) {
                },
                error: function (error) {
                    console.log('Error ${error}');
                }
            });
        } else if (specific_to == 'B' || specific_to == 'C' || specific_to == 'D' || specific_to == 'E' || specific_to == 'F') {
            var id_product_service_claim = document.getElementById("id_product_service_claim").innerHTML;
            const url_save_country_claim = "php/api/backofficeserviceclaim/saveselectedcountries.php?t=" + encodeURIComponent(global_token);
            var objProductServiceClaimSaveCountry = {
                id_product_service_claim_country: -1,
                id_product_service_claim: id_product_service_claim,
                id_country: id_country
            };
            $.ajax({
                url: url_save_country_claim,
                method: "POST",
                data: objProductServiceClaimSaveCountry,
                success: function (data) {
                },
                error: function (error) {
                    console.log('Error ${error}');
                }
            });
        }
    }
    var addedClaim = true;
    allServicesGridClaim(id_product_service_cost, 0, addedClaim);
}

function resetProductServicesClaim() {
    $('#ddlmultiSpecificMarket').val([]).multiselect('refresh');
    $('#ddlMultiSpecificTo').val([]).multiselect('refresh');
    $('#valid_to').val('');
    $('#ps_adult_claim').val('');
    $('#ps_teen_claim').val('');
    $('#ps_child_claim').val('');
    $('#ps_infant_claim').val('');
    $('#id_currency').val('');
    $('#specific_to').val('A');
    $("#ex_monday").prop("checked", false);
    $("#ex_tuesday").prop("checked", false);
    $("#ex_wednesday").prop("checked", false);
    $("#ex_thursday").prop("checked", false);
    $("#ex_friday").prop("checked", false);
    $("#ex_saturday").prop("checked", false);
    $("#ex_sunday").prop("checked", false);
    $("#ddlmultiSpecificMarket").val('');
    $("#ddlMultiSpecificTo").val('');
    $('#roll_over').val('Same Rate');    
    $('#txtRollOver').attr('disabled', 'disabled');
    $('#txtRollOver').val(0);
    $('.toggle:eq(1)').addClass('btn-default off').removeClass('btn-success');
    $('#on_api_claim').prop('checked', false);
    $('.toggle:eq(0)').addClass('btn-default off').removeClass('btn-success');
    $('#on_approved_claim').prop('checked', false);
    // MULTIPLE PRICE
    document.getElementById("ps_adult_claim").disabled = false;
    document.getElementById("ps_teen_claim").disabled = false;
    document.getElementById("ps_child_claim").disabled = false;
    document.getElementById("ps_infant_claim").disabled = false;
    $("#multiple_price").prop("checked", false);
    $("#ps_teen_claim").attr("placeholder", "Teen");
    $("#ps_infant_claim").attr("placeholder", "Infant");
    $("#ps_child_claim").attr("placeholder", "Child");
    $("#ps_adult_claim").attr("placeholder", "Adult");
    // MULTIPLE PRICE
    document.getElementById("id_product_service_claim").innerHTML = '0';
}

function specificToCtrl(id_product_service_claim) {
    const url_claim_to = "php/api/backofficeserviceclaim/populateselectedto.php?t=" + encodeURIComponent(global_token)+ "&id_product_service_claim=" + id_product_service_claim; 
    $.ajax({
        type: "POST",
        url: url_claim_to,
        dataType: "json",
        cache: false,
        success: function(data)
                {
                var valArr = [data];
                valArr.forEach(myFunction);
                function myFunction(value) {
                    loadTourOperatorClaim2(value);              
                }
        }    
    });
}

function loadTourOperatorClaim2(value) {
    $("#multiSpecificMarket").css("display", "none");
    $("#multiSpecificTo").css("display", "block");
    const url_to = "php/api/backofficeserviceclaim/tocombo.php?t=" + encodeURIComponent(global_token); 
    $.ajax({
        type: "POST",
        url: url_to,
        dataType: "json",
        cache: false,
        success: function(data)
            {
                $("#ddlMultiSpecificTo").empty();
                $.each(data, function (key, val) {
                    $("#ddlMultiSpecificTo").append('<option value="' + val.id + '">' + val.toname + '</option>');
                }); 
                arrToSelected = [];
                for (var i = 0, l = value.length; i < l; i++) {
                    var objSelected = value[i].id_tour_operator;
                    arrToSelected.push(objSelected);
                
                    $("#ddlMultiSpecificTo").find("option[value=" + objSelected + "]").prop("selected", true)
                    $("#ddlMultiSpecificTo").multiselect("refresh")    
                }  
                $("#ddlMultiSpecificTo").attr('multiple', 'multiple'); 
                $("#ddlMultiSpecificTo").multiselect({
                    buttonWidth: '313px',
                    includeSelectAllOption: true,
                    nonSelectedText: 'Select an Option',
                    enableFiltering: true,
                    enableHTML: true,
                    buttonClass: 'btn large btn-default',
                    enableCaseInsensitiveFiltering: true
                });
            }
        }
    );
}


function specificCountryCtrl(id_product_service_claim) {
    const url_claim_country = "php/api/backofficeserviceclaim/populateselectedcountries.php?t=" + encodeURIComponent(global_token)+ "&id_product_service_claim=" + id_product_service_claim; 
    $.ajax({
        type: "POST",
        url: url_claim_country,
        dataType: "json",
        cache: false,
        success: function(data)
                {
                var valArr1 = [data];
                valArr1.forEach(myFunction1);
                function myFunction1(value1) {
                    loadCountryClaim2(value1);              
                }
        }    
    });
}

// Load Market By Default - On Button Edit click
function loadCountryClaim2(value1) {
    console.log(value1);
    $("#multiSpecificMarket").css("display", "block");
    $("#multiSpecificTo").css("display", "none");
    const url_market_2 = "php/api/backofficeserviceclaim/attachcountriesclaim.php?t=" + encodeURIComponent(global_token);
    $.ajax({
        type: "POST",
        url: url_market_2,
        dataType: "json",
        cache: false,
        success: function(data)
            {
                $("#ddlmultiSpecificMarket").empty();
                $.each(data, function (key, val) {
                    console.log(val.countryId);
                // $("#ddlmultiSpecificMarket").append('<option value="' + val.countryId + '">'+ val.country_name + '</option>');
                $("#ddlmultiSpecificMarket").append('<option value="' + val.id + '"  data-subtext="' + val.marketfk + '">'+ val.continent + ' - ' + val.market_name + ' - ' + val.country_name + '</option>');

                });          
                arrCountrySelected = [];
                for (var i = 0, l = value1.length; i < l; i++) {
                    var objSelected = value1[i].id_country;
                    arrCountrySelected.push(objSelected);
                    $("#ddlmultiSpecificMarket").find("option[value=" + objSelected + "]").prop("selected", true);
                    $("#ddlmultiSpecificMarket").multiselect("refresh");    
                }      
                $("#ddlmultiSpecificMarket").attr('multiple', 'multiple'); 
                $("#ddlmultiSpecificMarket").multiselect({
                    buttonWidth: '313px',
                    includeSelectAllOption: true,
                    nonSelectedText: 'Select an Option',
                    enableFiltering: true,
                    enableHTML: true,
                    buttonClass: 'btn large btn-default',
                    enableCaseInsensitiveFiltering: true
                });
            }
            
        }
    );
}

