import React, { Component } from "react";
import { render } from "react-dom";
import Form from "@rjsf/bootstrap-4"; 
import BootstrapTable from 'react-bootstrap-table-next';
import filterFactory, { numberFilter, textFilter, selectFilter, multiSelectFilter, Comparator } from 'react-bootstrap-table2-filter';
import paginationFactory from 'react-bootstrap-table2-paginator';
import DataSelection, { DataSelectionTable } from "./DataSelection.js";


// Upload attachment
$("body").on("click", "button.upload_attachment", data => {
    // Prepare form data
    var fd = new FormData(document.getElementById('attachment'));
    fd.append(Yoda.csrf.tokenName, Yoda.csrf.tokenValue);

    // Prepare XHR
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/datarequest/upload_attachment/" + requestId);
    // Reload page after DTA upload
    xhr.onload = location.reload();

    // Send DTA
    xhr.send(fd);
});
