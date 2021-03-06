var areas_obj = new areas();

function areas()
{
    

    document.getElementById("aTitle").innerHTML = "AREAS";

    var main_layout = new dhtmlXLayoutObject("main_body", "1C");


    main_layout.cells('a').setText("Areas");

    var grid_areas = main_layout.cells("a").attachGrid();
    grid_areas.setIconsPath('libraries/dhtmlx/imgs/');
    grid_areas.setHeader("Area,Country,Latitude,Longitude");
    grid_areas.setColumnIds("areaname,country_name,lat,lon");
    grid_areas.setColTypes("ro,ro,ro,ro,ro");
    grid_areas.setInitWidths("*,250,100,100");
    grid_areas.setColAlign("left,left,right,right");
    grid_areas.setColSorting('str,str,int,int');
    grid_areas.attachHeader("#text_filter,#select_filter,#text_filter,#text_filter");
    grid_areas.init();


    var toolbar = main_layout.cells("a").attachToolbar();
    toolbar.setIconsPath("images/");
    toolbar.addButton("new", 1, "Add New", "add.png", "add.png");
    toolbar.addButton("modify", 2, "Modify", "modify.png", "modify.png");
    toolbar.addButton("delete", 3, "Delete", "delete.png", "delete.png");
    toolbar.setIconSize(32);
    
    applyrights();


    toolbar.attachEvent("onClick", function (id) {
        if (id == "new")
        {
            form_areas.clear();
            form_areas.setItemValue("id", "-1");
            
            if(default_country_id != "-1")
            {
                cboCountry.setComboValue(default_country_id);
            }
            
            popupwin_areas.setModal(true);
            popupwin_areas.center();
            popupwin_areas.show();
            
            
        } else if (id == "modify")
        {
            var uid = grid_areas.getSelectedRowId();
            if (!uid)
            {
                return;
            }

            var data = dsAreas.item(uid);
            form_areas.setFormData(data);

            popupwin_areas.setModal(true);
            popupwin_areas.center();
            popupwin_areas.show();


        } else if (id == "delete")
        {
            var gid = grid_areas.getSelectedRowId();
            if (!gid)
            {
                return;
            }


            dhtmlx.confirm({
                title: "Delete Area",
                type: "confirm",
                text: "Confirm Deletion?",
                callback: function (tf) {
                    if (tf)
                    {
                        var params = "gid=" + gid + "&t=" + encodeURIComponent(global_token);
                        dhtmlxAjax.post("php/api/areas/deletearea.php", params, function (loader) {

                            if (loader)
                            {
                                if (loader.xmlDoc.responseURL == "")
                                {
                                    dhtmlx.alert({
                                        text: "Connection Lost!",
                                        type: "alert-warning",
                                        title: "DELETE",
                                        callback: function () {
                                        }
                                    });
                                    return false;
                                }

                                var json_obj = utils_response_extract_jsonobj(loader, false, "", "");

                                if (!json_obj)
                                {
                                    dhtmlx.alert({
                                        text: loader.xmlDoc.responseText,
                                        type: "alert-warning",
                                        title: "DELETE",
                                        callback: function () {
                                        }
                                    });
                                    return false;
                                }
                                if (json_obj.OUTCOME == "OK")
                                {
                                    grid_areas.deleteRow(gid);
                                } else
                                {
                                    dhtmlx.alert({
                                        text: json_obj.OUTCOME,
                                        type: "alert-warning",
                                        title: "DELETE",
                                        callback: function () {
                                        }
                                    });
                                }

                            }
                        });
                    }
                }
            });
        }
    });


    var dsAreas = new dhtmlXDataStore();
    dsAreas.load("php/api/areas/areagrid.php?t=" + encodeURIComponent(global_token), "json", function () {
        grid_areas.sync(dsAreas);

    });


    resizeLayout();


    if (window.attachEvent)
        window.attachEvent("onresize", resizeLayout);
    else
        window.addEventListener("resize", resizeLayout, false);

    var t;
    function resizeLayout() {
        window.clearTimeout(t);
        t = window.setTimeout(function () {

            var x = $("#main_body").parent().width();
            //====================
            var body = document.body,
            html = document.documentElement;
            var y = Math.max( body.scrollHeight, body.offsetHeight, 
                       html.clientHeight, html.scrollHeight, html.offsetHeight );
            y -= 150;

            $("#main_body").height(y - 25);
            $("#main_body").width(x - 20);

            main_layout.setSizes(true);

        }, 1);
    }
    
    var dhxWins = new dhtmlXWindows();
    dhxWins.enableAutoViewport(false);
    dhxWins.attachViewportTo(main_layout.cells("a"));
    
    var popupwin_areas = dhxWins.createWindow("popupwin_areas", 50, 50, 500, 250);
    popupwin_areas.setText("Area Details:");
    popupwin_areas.denyResize();
    popupwin_areas.denyPark();

    /*=== WINDOW ON CLOSE EVENT ===*/
    dhxWins.attachEvent("onClose", function (win) {
        //do let user close window by clicking on close icon in window header
        //so catch it in the event and return false. Simply hide the window
        win.setModal(false);
        win.hide();
    });


    var str_frm_ug = [
        {type: "settings", position: "label-left", id: "form_areas"},
        {type: "newcolumn", offset: 0},
        {type: "hidden", name: "id", required: true},
        {type: "hidden", name: "token"},
        {type: "input", name: "areaname", label: "Area Name:", labelWidth: "130",
            labelHeight: "22", inputWidth: "250", inputHeight: "28", labelLeft: "0",
            labelTop: "10", inputLeft: "10", inputTop: "10", required: true
        },
        {type: "combo", name: "countryfk", label: "Country:", labelWidth: "130",
            labelHeight: "22", inputWidth: "250", inputHeight: "28", labelLeft: "0",
            labelTop: "10", inputLeft: "10", inputTop: "10", required: true,
            comboType: "image",
            comboImagePath: "../../images/",
        },
        {type: "input", name: "lat", label: "Latitude:", labelWidth: "130",
            labelHeight: "22", inputWidth: "250", inputHeight: "28", labelLeft: "0",
            labelTop: "10", inputLeft: "10", inputTop: "10", 
            validate: "ValidNumeric"
        },
        {type: "input", name: "lon", label: "Longitude:", labelWidth: "130",
            labelHeight: "22", inputWidth: "250", inputHeight: "28", labelLeft: "0",
            labelTop: "10", inputLeft: "10", inputTop: "10", 
            validate: "ValidNumeric"
        },
        {type: "button", name: "cmdSave", value: "Save", width: "80", offsetLeft: 0},
        {type: "button", name: "cmdCancel", value: "Cancel", width: "80", offsetLeft: 0}
    ];

    var arealayout = popupwin_areas.attachLayout("1C");

    arealayout.cells("a").hideHeader();

    var form_areas = arealayout.cells("a").attachForm(str_frm_ug);



    form_areas.attachEvent("onButtonClick", function (name, command) {
        if (name == "cmdCancel")
        {
            popupwin_areas.setModal(false);
            popupwin_areas.hide();
        }
        if (name == "cmdSave")
        {
            if (!form_areas.validate())
            {
                dhtmlx.alert({
                    text: "Please fill highlighted fields correctly!",
                    type: "alert-warning",
                    title: "Save Area",
                    callback: function () {
                    }
                });
                return;
            }
            
            if(!utils_validate_autocompletecombo(cboCountry))
            {
                dhtmlx.alert({
                    text: "Please select a valid Country!",
                    type: "alert-warning",
                    title: "Save Area",
                    callback: function () {
                        cboCountry.openSelect();
                    }
                });
                return;
            }



            arealayout.cells("a").progressOn();

            form_areas.setItemValue("token", global_token);

            form_areas.send("php/api/areas/savearea.php", "post", function (loader)
            {
                if (loader)
                {
                    if (loader.xmlDoc.responseURL == "")
                    {
                        dhtmlx.alert({
                            text: "Connection Lost!",
                            type: "alert-warning",
                            title: "SAVE",
                            callback: function () {
                            }
                        });
                        arealayout.cells("a").progressOff();
                        return false;
                    }

                    var json_obj = utils_response_extract_jsonobj(loader, false, "", "");


                    if (!json_obj)
                    {
                        dhtmlx.alert({
                            text: loader.xmlDoc.responseText,
                            type: "alert-warning",
                            title: "SAVE",
                            callback: function () {
                            }
                        });
                        arealayout.cells("a").progressOff();
                        return false;
                    }

                    if (json_obj.OUTCOME == "OK")
                    {
                        dhtmlx.message({
                            text: "<b><font color='green'>Save Successful!</font></b>",
                            expire: 1500
                        });

                        dsAreas.clearAll();
                        grid_areas.clearAll();

                        dsAreas.load("php/api/areas/areagrid.php?t=" + encodeURIComponent(global_token), "json", function () {
                            grid_areas.sync(dsAreas);
                            popupwin_areas.setModal(false);
                            popupwin_areas.hide();
                            arealayout.cells("a").progressOff();

                            grid_areas.selectRowById(json_obj.ID, false, true, false);
                        });

                    } else
                    {
                        dhtmlx.alert({
                            text: json_obj.OUTCOME,
                            type: "alert-warning",
                            title: "SAVE",
                            callback: function () {
                            }
                        });
                        arealayout.cells("a").progressOff();
                    }
                }
            });
        }
    });

    var cboCountry = form_areas.getCombo("countryfk");
    var dsCountry = new dhtmlXDataStore();
    dsCountry.load("php/api/combos/country_combo.php?t=" + encodeURIComponent(global_token), "json", function () {

        for (var i = 0; i < dsCountry.dataCount(); i++)
        {
            var item = dsCountry.item(dsCountry.idByIndex(i));
            var value = item.value;
            var txt = item.text;
            cboCountry.addOption([{value: value, text: txt, img_src: "images/country.png"}]);
        }

        cboCountry.readonly(false);
        cboCountry.enableFilteringMode(true);
    });


    function applyrights()
    {
        for (var i = 0; i < json_rights.length; i++)
        {
            if (json_rights[i].PROCESSNAME == "ADD" && json_rights[i].ALLOWED == "N")
            {
                toolbar.disableItem("new");
                toolbar.setItemToolTip("new", "Not Allowed");
            } else if (json_rights[i].PROCESSNAME == "MODIFY" && json_rights[i].ALLOWED == "N")
            {
                toolbar.disableItem("modify");
                toolbar.setItemToolTip("modify", "Not Allowed");
            } else if (json_rights[i].PROCESSNAME == "DELETE" && json_rights[i].ALLOWED == "N")
            {
                toolbar.disableItem("delete");
                toolbar.setItemToolTip("delete", "Not Allowed");
            }
        }
    }

    popupwin_areas.hide();

}