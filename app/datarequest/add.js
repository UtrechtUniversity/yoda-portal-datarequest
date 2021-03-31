import React, { Component } from "react";
import { render } from "react-dom";
import Form from "@rjsf/bootstrap-4"; 
import BootstrapTable from 'react-bootstrap-table-next';
import filterFactory, { numberFilter, textFilter, selectFilter, multiSelectFilter, Comparator } from 'react-bootstrap-table2-filter';
import paginationFactory from 'react-bootstrap-table2-paginator';
import DataSelection, { DataSelectionTable } from "./DataSelection.js";
import wordcount from 'wordcount';

document.addEventListener("DOMContentLoaded", async () => {

    // Get data request schema and uiSchema
    Yoda.call("datarequest_schema_get", {schema_name: "datarequest"})
    .then(response => {
        let datarequestSchema = response.schema;
        let datarequestUiSchema = response.uischema;

        // If specified, get data of previous data request (of which the present
        // data request will become a resubmission) and prefill data request
        // form
        if (typeof previousRequestId !== 'undefined') {
            var datarequestFormData = {};

            Yoda.call('datarequest_get',
                {request_id: previousRequestId},
                {errorPrefix: "Could not get datarequest"})
            .then(previousDatarequest => {
                datarequestFormData = JSON.parse(previousDatarequest.requestJSON);

                render(<Container schema={datarequestSchema}
                                  uiSchema={datarequestUiSchema}
                                  formData={datarequestFormData}
                                  validate={validate} />,
                       document.getElementById("form"));
            });
        // Else, render blank data request form
        } else {
            render(<Container schema={datarequestSchema}
                              uiSchema={datarequestUiSchema}
                              validate={validate} />,
                   document.getElementById("form"));
        }
    });
});

// Validate whether the background field contains less than 500 words
function validate(formData, errors) {
    
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

    return errors;
}

class Container extends React.Component {
    constructor(props) {
        super(props);
        this.submitForm = this.submitForm.bind(this);
    }

    submitForm() {
        this.form.submitButton.click();
    }

    render() {
        return (
        <div>
          <YodaForm schema={this.props.schema}
                    uiSchema={this.props.uiSchema}
                    formData={this.props.formData}
                    validate={this.props.validate}
                    ref={(form) => {this.form=form;}}/>
          <YodaButtons submitButton={this.submitForm}/>
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
                        <button onClick={this.props.submitButton} type="submit" className="btn btn-primary">Submit</button>
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
    // Disable submit button
    $("button:submit").attr("disabled", "disabled");

    // Submit form and redirect to overview page
    Yoda.call("datarequest_submit",
        {data: data,
         previous_request_id: typeof(previousRequestId) !== 'undefined' ? previousRequestId : null},
        {errorPrefix: "Could not submit data"})
    .then(() => {
        window.location.href = "/datarequest/";
    })
    .catch(error => {
        // Re-enable submit button if submission failed
        $('button:submit').attr("disabled", false);
    });
}

// https://stackoverflow.com/a/2631198
function getNested(obj, ...args) {
  return args.reduce((obj, level) => obj && obj[level], obj)
}
