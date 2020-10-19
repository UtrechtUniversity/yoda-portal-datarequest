import React, { Component } from "react";
import { render } from "react-dom";
import Form from "react-jsonschema-form";
import DataSelection, { DataSelectionCart } from "./DataSelection.js";

document.addEventListener("DOMContentLoaded", async () => {

    var datarequestSchema = {};
    var datarequestUiSchema = {};
    var datarequestFormData = {};

    // Get data request
    Yoda.call('datarequest_get',
        {request_id: requestId},
        {errorPrefix: "Could not get datarequest"})
    .then(datarequest => {
        datarequestFormData = JSON.parse(datarequest.requestJSON);
    })
    // Get data request schema and uischema
    .then(() => {
        Yoda.call("schema_get", {schema_name: "datarequest"})
        .then(response => {
            datarequestSchema   = response.schema;
            datarequestUiSchema = response.uischema;
        })
    })
    // Render data request as disabled form
    .then(() => {
        render(<ContainerReadonly schema={datarequestSchema}
                                  uiSchema={datarequestUiSchema}
                                  formData={datarequestFormData} />,
               document.getElementById("datarequest")
        );
    });

    var prSchema   = {};
    var prUiSchema = {};
    var prFormData = {};

    // Get preliminary review
    Yoda.call('datarequest_preliminary_review_get',
        {request_id: requestId},
        {errorPrefix: "Could not get preliminary review"})
    .then(response => {
        prFormData = JSON.parse(response);
    })
    // Get preliminary review schema and uischema
    .then(() => {
        Yoda.call("schema_get", {schema_name: "preliminary_review"})
        .then(response => {
            prSchema   = response.schema;
            prUiSchema = response.uischema;
        })
    })
    // Render preliminary review as disabled form
    .then(() => {
        render(<ContainerReadonly schema={prSchema}
                                  uiSchema={prUiSchema}
                                  formData={prFormData} />,
               document.getElementById("preliminaryReview"));
    });

    var dmrSchema   = {};
    var dmrUiSchema = {};
    var dmrFormData = {};

    // Get data manager review
    Yoda.call("datarequest_datamanager_review_get",
              {request_id: requestId},
              {errorPrefix: "Could not get datamanager review"})
    .then(response => {
        dmrFormData = JSON.parse(response);
    })
    // Get data manager review schema and uischema
    .then(() => {
        Yoda.call("schema_get", {schema_name: "datamanager_review"})
        .then(response => {
            dmrSchema   = response.schema;
            dmrUiSchema = response.uischema;
        })
    })
    // Render data manager review as disabled form
    .then(() => {
        render(<ContainerReadonly schema={dmrSchema}
                                  uiSchema={dmrUiSchema}
                                  formData={dmrFormData} />,
               document.getElementById("datamanagerReview"));
    });

    var assignSchema   = {};
    var assignUiSchema = {};
    var assignFormData = {};

    // Get assignment
    Yoda.call("datarequest_assignment_get",
              {request_id: requestId},
              {errorPrefix: "Could not get assignment"})
    .then(response => {
        assignFormData = JSON.parse(response);
    })
    // Get assignment schema and uischema
    .then(() => {
        Yoda.call("schema_get", {schema_name: "assignment"})
        .then(response => {
            assignSchema   = response.schema;
            assignUiSchema = response.uischema;
        })
    })
    // Render assignment as disabled form
    .then(() => {
        render(<ContainerReadonly schema={assignSchema}
                                  uiSchema={assignUiSchema}
                                  formData={assignFormData} />,
               document.getElementById("assign"));
    });

    var reviewSchema = {};
    var reviewUiSchema = {};
    var reviewFormData = {};

    // Get the reviews and render them in as dissabled forms
    Yoda.call("datarequest_reviews_get",
              {request_id: requestId},
              {errorPrefix: "Could not get reviews"})
    .then(response => {
        reviewFormData = JSON.parse(response);
    })
    // Get review schema and uischema
    .then(() => {
        Yoda.call("schema_get", {schema_name: "review"})
        .then(response => {
            reviewSchema   = response.schema;
            reviewUiSchema = response.uischema;
        })
    })
    .then(() => {
        var reviews = reviewFormData.map((line, i) => {
          return(
            <div class="col-md-6">
                <div class="row">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <h3 class="panel-title pull-left">
                                Review by {reviewFormData[i].username}
                            </h3>
                        </div>
                        <div class="panel-body">
                      <ContainerReadonly schema={reviewSchema}
                                         uiSchema={reviewUiSchema}
                                         formData={reviewFormData[i]} />
                        </div>
                    </div>
                </div>
            </div>
          );
        });

        render(<div>{reviews}</div>, document.getElementById("reviews"));
    });

    // Get the schema of the data request evaluation form
    Yoda.call("schema_get", {schema_name: "evaluation"})
    .then(response => {
        let evaluationSchema = response.schema;
        let evaluationUiSchema = response.uiSchema;

        render(<Container schema={evaluationSchema}
                          uiSchema={evaluationUiSchema} />,
               document.getElementById("evaluation"));
    });
});

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
                    ref={(form) => {this.form=form;}}/>
          <YodaButtons submitButton={this.submitForm}/>
        </div>
        );
    }
}

class ContainerReadonly extends React.Component {
    render() {
        return (
        <div>
          <YodaFormReadonly schema={this.props.schema}
                            uiSchema={this.props.uiSchema}
                            formData={this.props.formData} />
        </div>
      );
    }
}

class YodaForm extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <Form className="form"
                  schema={this.props.schema}
                  uiSchema={this.props.uiSchema}
                  idPrefix={"yoda"}
                  onSubmit={onSubmit}>
                  <button ref={(btn) => {this.submitButton=btn;}}
                          className="hidden" />
            </Form>
        );
    }
};

class YodaFormReadonly extends React.Component {
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
                  fields={fields}
                  disabled>
                  <button className="hidden" />
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
                        <button onClick={this.props.submitButton}
                                type="submit"
                                className="btn btn-primary">Submit</button>
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
  DataSelection: DataSelectionCart
};

function submitData(data)
{
    // Disable submit button
    $("button:submit").attr("disabled", "disabled");

    // Submit form and direct to view/
    Yoda.call("datarequest_evaluation_submit",
        {data: data, request_id: requestId},
        {errorPrefix: "Could not submit assignment"})
    .then(() => {
        window.location.href = "/datarequest/view/" + requestId;
    })
    .catch(error => {
        // Re-enable submit button if submission failed
        $("button:submit").attr("disabled", false);
    });
}
