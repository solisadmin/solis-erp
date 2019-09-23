$(document).ready(function(){
    allProductGridCost();
});

function allProductGridCost() {
    // Request call everything from database
    $('#productServiceSort').DataTable({       
        "processing" : true,

        "ajax" : {
            "url" : "php/api/backofficeproduct/gridproduct.php?t=" + encodeURIComponent(global_token),
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
            "data" : "id_product"
        }, {
            "data" : "servicetype"
        }, {
            "data" : "product_name"
        }, 
            {
                "targets": -1,
                "data": null,                
                "class": 'btnCol',
                "defaultContent": 
                '<div class="btn-group">' +
                '<button type="button" id="btnAddProductServices" class="btn btn-primary"><i class="fa fa-fw fa-plus-circle"></i></button>' +
                '<button type="button" id="btnEditProduct" class="btn btn-primary"><i class="fa fa-fw fa-edit"></i>' +
                '<button type="button" id="btnDeleteProduct" class="btn btn-primary"><i class="fa fa-fw fa-trash"></i></button></button></div>'
            }
        ]
    });
    $('#productServiceSort tbody').on( 'click', '#btnDeleteProduct', function () {
        var table = $('#productServiceSort').DataTable();
        var data = table.row( $(this).parents('tr') ).data();
        productServiceClaimDelete(data);
    });
    $('#productServiceSort tbody').on( 'click', '#btnEditProduct', function () {
        var table = $('#productServiceSort').DataTable();
        var data = table.row( $(this).parents('tr') ).data();
        productServiceClaimEdit(data);
    });
    $('#productServiceSort tbody').on( 'click', '#btnAddProductServices', function () {
        var table = $('#productServiceSort').DataTable();
        var data = table.row( $(this).parents('tr') ).data();
        addProductServices(data);
    });
}

// Delete Product
function productServiceClaimDelete(data) {
    var objDelProduct = {id_product: data.id_product};
    const url_delete_product = "php/api/backofficeproduct/deleteproduct.php?t=" + encodeURIComponent(global_token) + "&id_product=" + data.id_product;
    $.ajax({
        url: url_delete_product,
        method: "POST",
        data: objDelProduct,
        success: function (data) {
        },
        error: function (error) {
            console.log('Error ${error}');
        }
    });
    allProductGridCost();
}

// Edit Product
function productServiceClaimEdit(data) {
    $('#productName').val(data.product_name);
    $("#ddlType").val(data.id_service_type);
    $("#ddlProductType").val(data.id_product_type);
    document.getElementById("productId").textContent = data.id_product;
}

// Add Product Services
function addProductServices(data) { 
    window.location.href = "index.php?m=productservices&pid=" + data.id_product;
}