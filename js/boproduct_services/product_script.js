
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


/////////////////////////////////////////
// model --> fetch Api Currency sell ////
/////////////////////////////////////////
const url_currency_buy = "php/api/backoffservices_rates/currency_combo_rates.php?t=" + encodeURIComponent(global_token); 

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
                dropdown.append('<option value="' + result.value + '">' + result.text + '</option>');
            });
        }
    }
}