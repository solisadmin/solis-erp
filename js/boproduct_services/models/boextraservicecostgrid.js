function allExtraServicesCostGrid(id_product_service_cost) {
    // Request call everything from database
    $('#tbl-extraServiceCost').DataTable({       
        "processing" : true,

        "ajax" : {
            "url" : "php/api/backofficeproduct/gridextraservicecost.php?t=" + encodeURIComponent(global_token)  + "&id_product_service_cost=" + id_product_service_cost,
            dataSrc : ''
        },
        "destroy": true,
        "bProcessing": true,
        "bAutoWidth": false,
        "responsive": true,
        "pageLength": 4,
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
        "columns" : [ {
            "data" : "id_product_service_extra_cost"
        }, {
            "data" : "extra_name"
        }, {
            "data" : "charge"
        }, 
            {
                "targets": -1,
                "data": null,                
                "class": 'btnCol',
                "defaultContent": 
                '<div class="btn-group">' +
                '<i id="btnEditExtraServiceCost" class="fa fa-fw fa-edit"></i>'+
                '<i id="btnDeleteExtraServiceCost" class="fa fa-fw fa-trash-o" title="Delete Line"></i></div>'
            }
        ],
        "initComplete": function () {
            $('#tbl-extraServiceCost tbody')
                .off()
                .on( 'click', '#btnEditExtraServiceCost', function (e) {
                    var table = $('#tbl-extraServiceCost').DataTable();
                    var data = table.row( $(this).parents('tr') ).data();
                    editServiceCostExtra(data);
                })
                .on( 'click', '#btnDeleteExtraServiceCost', function (e) {
                    var table = $('#tbl-extraServiceCost').DataTable();
                    var data = table.row( $(this).parents('tr') ).data();
                    alertExtraServiceCostDelete(data);
                })
        }
    });
}

function alertExtraServiceCostDelete (data) {
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
        deleteExtraServiceCost(data);
	});
}

// Delete Product
function deleteExtraServiceCost(data) {
    var objDelExtraServiceCost = {id_product_service_extra_cost: data.id_product_service_extra_cost};
    const url_delete_extraservice_cost = "php/api/backofficeproduct/deleteextraservicecost.php?t=" + encodeURIComponent(global_token) + "&id_product_service_extra_cost=" + data.id_product_service_extra_cost;
    $.ajax({
        url: url_delete_extraservice_cost,
        method: "POST",
        data: objDelExtraServiceCost,
        dataType: "json",
        success: function (data) {
            if (data.OUTCOME == 'OK') { 
                swal("Deleted!", "Deleted !", "success");
            }
        },
        error: function (error) {
            swal("Cancelled", "Not Deleted - Please try again...", "error");
        }
    });
    allExtraServicesCostGrid(data.id_product_service_cost);
}

// Edit Extra Service
function editServiceCostExtra(data) {
    $('#modal-extraServices').modal('show');
    document.getElementById("id_product_service_cost_extra").innerHTML = data.id_product_service_cost;
    editAllExtraServiceCost(data);
}