import React, { Component } from "react";
import { render } from "react-dom";
import Form from "@rjsf/bootstrap-4"; 
import BootstrapTable from 'react-bootstrap-table-next';
import filterFactory, { numberFilter, textFilter, selectFilter, multiSelectFilter, Comparator } from 'react-bootstrap-table2-filter';
import paginationFactory from 'react-bootstrap-table2-paginator';
import DataSelection, { DataSelectionTable } from "./DataSelection.js";
import wordcount from 'wordcount';

let save = false;

document.addEventListener("DOMContentLoaded", async () => {

    // Get data request schema and uiSchema
    Yoda.call("datarequest_schema_get", {schema_name: "datarequest"})
    .then(response => {
        let datarequestSchema = response.schema;
        let datarequestUiSchema = response.uischema;

        // Determine if form should be prefilled...
        if (typeof(draftRequestId) !== 'undefined' || typeof(previousRequestId) !== 'undefined') {
            let datarequestFormData = {};

            // Determine with which data to prefill the form
            let requestId = typeof(draftRequestId) !== 'undefined' ? draftRequestId : previousRequestId;

            // Get that data and render the prefilled form
            Yoda.call('datarequest_get',
                {request_id: requestId},
                {errorPrefix: "Could not get datarequest"})
            .then(data => {
                datarequestFormData = JSON.parse(data.requestJSON);

                // Add previous request ID to form data if applicable
                if (typeof(previousRequestId) !== 'undefined') {
                    datarequestFormData['previous_request_id'] = previousRequestId;
                }

                render(<Container schema={datarequestSchema}
                                  uiSchema={datarequestUiSchema}
                                  formData={datarequestFormData}
                                  validate={validate} />,
                       document.getElementById("form"));
            });
        // ...if not, render blank form
        } else {
            render(<Container schema={datarequestSchema}
                              uiSchema={datarequestUiSchema}
                              validate={validate} />,
                   document.getElementById("form"));
        }
    });
});

// Some validations that cannot be done in the schema itself
function validate(formData, errors) {
    
    // Validate whether the background field contains less than 500 words
    //
    // First confirm that the background field is present, as this isn't
    // necessarily the case
    let background = getNested(formData, 'research_context', 'background');
    if (typeof(background) !== 'undefined') {
        // Then validate number of words
        let num_words = wordcount(background);
        if (num_words > 500) {
            errors.research_context.background.addError(
                `Please use at most 500 words. You have used ${num_words} words.`);
        }
    }

    // Validate whether CC email addresses are valid
    //
    // First check whether any CC email addresses have been entered
    let cc_email_addresses = getNested(formData, 'contact', 'cc_email_addresses');
    if (typeof(cc_email_addresses) !== 'undefined') {
        // Then remove all spaces
        cc_email_addresses = cc_email_addresses.replace(/\s+/g, '');
        // Then check if they look like valid email addresses
        let cc_split = cc_email_addresses.split(",");
        for (const address of cc_split) {
            if (!isEmailAddress(address)) {
                errors.contact.cc_email_addresses.addError(
                    "One or more of the entered email addresses is invalid.");
                break;
            }
        };
    }

    return errors;
}

class Container extends React.Component {
    constructor(props) {
        super(props);
        this.submitForm = this.submitForm.bind(this);
        this.saveForm = this.saveForm.bind(this);
    }

    submitForm() {
        save = false;
        this.form.submitButton.click();
    }

    saveForm() {
        save = true;
        this.form.submitButton.click();
    }

    render() {
        return (
        <div>
          <YodaForm schema={this.props.schema}
                    uiSchema={this.props.uiSchema}
                    formData={this.props.formData}
                    validate={this.props.validate}
                    ref={(form) => {this.form=form;}} />
          <YodaButtons submitButton={this.submitForm} saveButton={this.saveForm} />
        </div>
      );
    }
};

class YodaForm extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <Form className="form"
                  schema={this.props.schema}
                  idPrefix={"yoda"}
                  uiSchema={this.props.uiSchema}
                  formData={this.props.formData}
                  validate={this.props.validate}
                  fields={fields}
                  onSubmit={onSubmit}
                  showErrorList={false}
                  noHtml5Validate
                  transformErrors={transformErrors}>
                  <button ref={(btn) => {this.submitButton=btn;}}
                          className="hidden" />
            </Form>
        );
    }
};

class YodaButtons extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div className="form-group">
                <div className="row yodaButtons">
                    <div className="col-sm-12">
                        <button id="saveButton" onClick={this.props.saveButton} type="submit" className="btn btn-secondary mr-1">Save as draft</button>
                        <button id="submitButton" onClick={this.props.submitButton} type="submit" className="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        );
    }
}

const onSubmit = ({formData}) => submitData(formData);

const CustomDescriptionField = ({id, description}) => {
  return <div id={id} dangerouslySetInnerHTML={{ __html: description }}></div>;
};

const fields = {
  DescriptionField: CustomDescriptionField,
  DataSelection: DataSelectionTable
};

function transformErrors(errors) {
    return errors.map(error => {
        if(error.name === "not" && error.property === ".contribution") {
            error.message = "Please specify at least one contribution."
        }
        return error;
    });
}

function submitData(data)
{
    // Disable button
    if (save) {
        $("#saveButton").text("Saving...");
        $("#saveButton").attr("disabled", "disabled");
    } else {
        $("#submitButton").text("Submitting...");
        $("#submitButton").attr("disabled", "disabled");
    }

    // Submit form
    Yoda.call("datarequest_submit",
        {data: data,
         draft: save,
         draft_request_id: typeof(draftRequestId) !== 'undefined' ? draftRequestId : null},
        {errorPrefix: "Could not submit data"})
    // Redirect if applicable
    .then(response => {
        if (save) {
            // If this is the first time the draft is saved, redirect to
            // add_from_draft/{draftRequestId}
            //
            // We know this is the case when the call returns a requestId, i.e. the requestId of the
            // newly created draft data request
            if (response.requestId) {
                window.location.href = "/datarequest/add_from_draft/" + response.requestId;
            // If no draft requestId is returned, we are already working on a draft proposal and can
            // therefore stay on the same page (i.e. add_from_draft/{draftRequestId})
            } else {
                $("#saveButton").text("Save as draft");
                $('#saveButton').attr("disabled", false);
            }
        // If we are submitting the data request instead of saving it as a draft, redirect to index
        } else {
            window.location.href = "/datarequest/";
        }
    })
    .catch(error => {
        // Re-enable submit button if submission failed
        $("#submitButton").text("Submit");
        $("#saveButton").text("Save as draft");
        $('button:submit').attr("disabled", false);
    });
}

// https://stackoverflow.com/a/2631198
function getNested(obj, ...args) {
  return args.reduce((obj, level) => obj && obj[level], obj)
}

// https://stackoverflow.com/a/9204568
function isEmailAddress(address) {
    return address.match(/^[^\s@]+@[^\s@]+$/) !== null
}
