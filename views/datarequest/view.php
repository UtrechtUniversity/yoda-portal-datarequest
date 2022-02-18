<script>
    var requestId = "<?php echo $requestId; ?>";
    var availableDocuments = <?php echo $availableDocuments; ?>;
</script>

<div class="modal" id="uploadDTA">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5>Upload a DTA (to be signed by the researcher).</h5>
                <div class="form-group">
                    <form id="dta" enctype="multipart/form-data">
                        <label for="file">Select a document to upload (must be a PDF file):</label><br />
                        <input type="file" accept=".pdf,application/pdf" name="file" id="file" />
                    </form>
                </div>
                <div id="dta-non-pdf-warning" class="hidden">
                    <p class="text-danger">File must be an actual PDF.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary grey cancel" data-dismiss="modal">Close</button>
                <button class="btn btn-secondary grey submit_dta">Upload</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="uploadSignedDTA">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5>Upload the signed DTA.</h5>
                <div class="form-group">
                    <form id="signed_dta" enctype="multipart/form-data">
                        <label for="file">Select a document to upload (must be a PDF file):</label><br />
                        <input type="file" accept=".pdf,application/pdf" name="file" id="file" />
                    </form>
                </div>
                <div id="signed-dta-non-pdf-warning" class="hidden">
                    <p class="text-danger">File must be an actual PDF.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary grey cancel" data-dismiss="modal">Close</button>
                <button class="btn btn-secondary grey submit_signed_dta">Upload</button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class=col-md-12>
        <?php if ($requestStatus == "DRAFT" && $isRequestOwner): ?>
        <a href="/datarequest/add_from_draft/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Resume draft data request</a>
        <?php endif ?>

        <?php if ($requestStatus == "PENDING_ATTACHMENTS" && $isRequestOwner): ?>
        <a href="/datarequest/add_attachments/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Add attachments</a>
        <?php endif ?>

        <?php if($requestStatus == "SUBMITTED" && $isProjectManager): ?>
        <a href="/datarequest/preliminary_review/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Preliminary review</a>
        <?php endif ?>

        <?php if($requestStatus == "PRELIMINARY_ACCEPT" && $isDatamanager): ?>
        <a href="/datarequest/datamanager_review/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Data manager review</a>
        <?php endif ?>

        <?php if(in_array($requestStatus, ["DATAMANAGER_ACCEPT", "DATAMANAGER_REJECT", "DATAMANAGER_RESUBMIT"]) && $isProjectManager): ?>
        <a href="/datarequest/assign/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Review / assign</a>
        <?php endif ?>

        <?php if($requestStatus == "UNDER_REVIEW" && $isReviewer): ?>
        <a href="/datarequest/review/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Review data request</a>
        <?php endif ?>

        <?php if(in_array($requestStatus, ["DAO_SUBMITTED", "REVIEWED"]) && $isProjectManager): ?>
        <a href="/datarequest/evaluate/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Evaluate data request</a>
        <?php endif ?>

        <?php if (in_array($requestStatus, ["APPROVED"]) && $isRequestOwner): ?>
        <a href="/datarequest/preregister/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Preregister study</a>
        <?php endif ?>

        <?php if (in_array($requestStatus, ["PREREGISTRATION_SUBMITTED"]) && $isProjectManager): ?>
        <a href="/datarequest/preregistration_confirm/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Confirm preregistration</a>
        <?php endif ?>

        <?php if (in_array($requestStatus, ["PREREGISTRATION_CONFIRMED", "DAO_APPROVED"]) && $isDatamanager): ?>
        <button type="button" class="btn btn-primary mb-3 float-right upload_dta" data-path="">Upload DTA</button>
        <?php endif ?>

        <?php if ($requestStatus == "DTA_READY" && $isRequestOwner): ?>
        <button type="button" class="btn btn-primary mb-3 ml-1 float-right upload_signed_dta" data-path="">Upload signed DTA</button>
        <?php endif ?>

        <?php if ($requestStatus == "DTA_SIGNED" && $isDatamanager): ?>
        <a href="/datarequest/data_ready/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 ml-1 float-right" role="button">Data ready</a>
        <?php endif ?>

        <?php if (in_array($requestStatus, array("PRELIMINARY_RESUBMIT", "RESUBMIT_AFTER_DATAMANAGER_REVIEW", "RESUBMIT")) && $isRequestOwner): ?>
        <a href="/datarequest/add/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Resubmit</a>
        <?php endif ?>

        <?php if (in_array($requestStatus, array("DTA_SIGNED", "DATA_READY")) && ($isRequestOwner || $isProjectManager || $isDatamanager)): ?>
        <a href="/datarequest/download_signed_dta/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 ml-1 float-right">Download signed DTA</a>
        <?php endif ?>

        <?php if (in_array($requestStatus, array("DTA_READY", "DTA_SIGNED", "DATA_READY")) && ($isRequestOwner || $isProjectManager || $isDatamanager)): ?>
        <a href="/datarequest/download_dta/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 ml-1 float-right">Download DTA</a>
        <?php endif ?>
    </div>
</div>

<div class="row">
    <div class=col-md-12>
        <div class="card">
            <div class="card-header clearfix">
                <h5 class="card-header float-left">Summary and progress</h5>
                <div class="float-right">
                    <a class="btn btn-secondary" href="/datarequest">Back</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($requestType == "REGULAR"): ?>
                <div class="row bs-wizard" style="border-bottom:0;">
                    <div class="col-md-3 bs-wizard-step disabled" id="step-0">
                        <div class="text-md-center bs-wizard-stepnum">1. Submission</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-1">
                        <div class="text-md-center bs-wizard-stepnum">2. Under review</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-2">
                         <div class="text-md-center bs-wizard-stepnum">3. Reviewed</div>
                         <div class="progress"><div class="progress-bar"></div></div>
                         <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-3">
                         <div class="text-md-center bs-wizard-stepnum">4. Approved</div>
                         <div class="progress"><div class="progress-bar"></div></div>
                         <a href="#" class="bs-wizard-dot"></a>
                    </div>
                </div>
                <div class="row bs-wizard" style="border-bottom:0;">
                    <div class="col-md-3 bs-wizard-step disabled" id="step-4">
                        <div class="text-md-center bs-wizard-stepnum">5. Preregistration</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-5">
                        <div class="text-md-center bs-wizard-stepnum">6. DTA ready</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-6">
                        <div class="text-md-center bs-wizard-stepnum">7. DTA signed</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-7">
                        <div class="text-md-center bs-wizard-stepnum">8. Data ready</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                </div>
                <?php elseif ($requestType == "DAO"): ?>
                <div class="row bs-wizard offset-md-4" style="border-bottom:0;">
                    <div class="col-md-3 bs-wizard-step disabled" id="step-0">
                        <div class="text-md-center bs-wizard-stepnum">1. Submission</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-1">
                         <div class="text-md-center bs-wizard-stepnum">2. Approved</div>
                         <div class="progress"><div class="progress-bar"></div></div>
                         <a href="#" class="bs-wizard-dot"></a>
                    </div>
                </div>
                <div class="row bs-wizard offset-md-2" style="border-bottom:0;">
                    <div class="col-md-3 bs-wizard-step disabled" id="step-2">
                        <div class="text-md-center bs-wizard-stepnum">3. DTA ready</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-3">
                        <div class="text-md-center bs-wizard-stepnum">4. DTA signed</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                    <div class="col-md-3 bs-wizard-step disabled" id="step-4">
                        <div class="text-md-center bs-wizard-stepnum">5. Data ready</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                    </div>
                </div>
            <?php endif ?>

                <?php if (in_array($requestStatus, array("PRELIMINARY_REJECT", "REJECTED_AFTER_DATAMANAGER_REVIEW", "REJECTED"))): ?>
                <div class="rejected"><h5>Proposal rejected</h5></div>
                    <?php if ($isRequestOwner): ?>
                    <h5>Feedback for researcher</h5>
                    <hr class="border-0 bg-secondary" style="height: 1px;">
                    <p><?php echo nl2br(html_escape($feedback)) ?></p>
                    <?php endif ?>
                <?php elseif (in_array($requestStatus, array("PRELIMINARY_RESUBMIT", "RESUBMIT_AFTER_DATAMANAGER_REVIEW", "RESUBMIT"))): ?>
                <div class="resubmit"><h5>Resubmission requested</h5></div>
                    <?php if ($isRequestOwner): ?>
                    <div class="resubmit">
                        <p>(click <a href=/datarequest/add/<?php echo html_escape($requestId) ?>>here</a> to open the resubmission form)</p>
                    </div>
                    <h5>Feedback for researcher</h5>
                    <hr class="border-0 bg-secondary" style="height: 1px;">
                    <p><?php echo nl2br(html_escape($feedback)) ?></p>
                    <?php endif ?>
                <?php elseif ($requestStatus == "RESUBMITTED"): ?>
                <div class="resubmitted"><h5>Resubmitted</h5></div>
                <div class="resubmitted">
                    <p>(click <a href=/datarequest/view/<?php echo html_escape($resubmissionId) ?>>here</a> to go to the resubmision)</p>
                </div>
                <?php endif ?>

                <hr />

                <p><b>Title: </b><?php echo $request['datarequest']['study_information']['title']; ?></p>
                <p><b>Status: </b><?php echo $humanRequestStatus; ?></p>
                <p><b>Requestee: </b><?php echo $request['owner']; ?></p>
                <p><b>Purpose: </b><?php echo $request['datarequest']['purpose']; ?>
                <?php if ($requestType == "REGULAR") echo "<p><b>Publication type: </b>" . $request['datarequest']['publication_type'] . "</p>"; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#datarequestDiv" role="button" aria-expanded="false">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Data request</h5>
            </div>
            <div id="datarequestDiv" class="card-body collapse">
                <div id="datarequest" class="metadata-form"
                     data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                     data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>

        <?php if(count($attachments) > 0): ?>
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#attachmentsDiv" role="button" aria-expanded="false">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Attachments (<?php echo count($attachments) ?>)</h5>
            </div>
            <div id="attachmentsDiv" class="card-body collapse">
                <ul>
                    <?php $i=0; foreach($attachments as $attachment) { echo "<li><a href=\"/datarequest/download_attachment/" . html_escape($requestId) . "?file=" . $i . "\">" . html_escape($attachment) . "</a></li>"; $i++; } ?>
                </ul>
            </div>
        </div>
        <?php endif ?>

        <?php if ($requestType == "REGULAR" and in_array($requestStatus, ["PRELIMINARY_ACCEPT", "PRELIMINARY_REJECT", "PRELIMINARY_RESUBMIT", "DATAMANAGER_ACCEPT", "DATAMANAGER_REJECT", "DATAMANAGER_RESUBMIT", "UNDER_REVIEW", "REJECTED_AFTER_DATAMANAGER_REVIEW", "RESUBMIT_AFTER_DATAMANAGER_REVIEW", "REVIEWED", "APPROVED", "REJECTED", "RESUBMIT", "RESUBMITTED", "DAO_APPROVED", "PREREGISTRATION_SUBMITTED", "PREREGISTRATION_CONFIRMED", "DTA_READY", "DTA_SIGNED", "DATA_READY"]) && ($isProjectManager || $isDatamanager || $isReviewer)): ?>
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#preliminaryReviewDiv" role="button" aria-expanded="false">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Project manager's preliminary review</h5>
            </div>
            <div id="preliminaryReviewDiv" class="card-body collapse">
                <div id="preliminaryReview" class="metadata-form"
                     data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                     data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
        <?php endif ?>

        <?php if ($requestType == "REGULAR" and in_array($requestStatus, ["DATAMANAGER_ACCEPT", "DATAMANAGER_REJECT", "DATAMANAGER_RESUBMIT", "UNDER_REVIEW", "REJECTED_AFTER_DATAMANAGER_REVIEW", "RESUBMIT_AFTER_DATAMANAGER_REVIEW", "REVIEWED", "APPROVED", "REJECTED", "RESUBMIT", "RESUBMITTED", "DAO_APPROVED", "PREREGISTRATION_SUBMITTED", "PREREGISTRATION_CONFIRMED", "DTA_READY", "DTA_SIGNED", "DATA_READY"]) && ($isProjectManager || $isDatamanager || $isReviewer)): ?>
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#datamanagerReviewDiv" role="button" aria-expanded="false">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Data manager review</h5>
            </div>
            <div id="datamanagerReviewDiv" class="card-body collapse">
                <div id="datamanagerReview" class="metadata-form"
                     data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                     data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
        <?php endif ?>

        <?php if ($requestType == "REGULAR" and in_array($requestStatus, ["UNDER_REVIEW", "REJECTED_AFTER_DATAMANAGER_REVIEW", "RESUBMIT_AFTER_DATAMANAGER_REVIEW", "REVIEWED", "APPROVED", "REJECTED", "RESUBMIT", "RESUBMITTED", "DAO_APPROVED", "PREREGISTRATION_SUBMITTED", "PREREGISTRATION_CONFIRMED", "DTA_READY", "DTA_SIGNED", "DATA_READY"]) && ($isProjectManager || $isReviewer)): ?>
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#assignDiv" role="button" aria-expanded="false">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Assignment</h5>
            </div>
            <div id="assignDiv" class="card-body collapse">
                <div id="assign" class="metadata-form"
                    data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                    data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
        <?php endif ?>
    </div>

    <?php if ($requestType == "REGULAR" and in_array($requestStatus, ["UNDER_REVIEW", "REVIEWED", "APPROVED", "REJECTED", "RESUBMIT", "RESUBMITTED", "DAO_APPROVED", "PREREGISTRATION_SUBMITTED", "PREREGISTRATION_CONFIRMED", "DTA_READY", "DTA_SIGNED", "DATA_READY"]) && ($isProjectManager || $isReviewer)): ?>
    <div class="col-md-12" id="reviews"></div>
    <?php endif ?>

    <div class="col-md-12">
        <?php if (in_array($requestStatus, ["APPROVED", "REJECTED", "RESUBMIT", "RESUBMITTED", "DAO_APPROVED", "PREREGISTRATION_SUBMITTED", "PREREGISTRATION_CONFIRMED", "DTA_READY", "DTA_SIGNED", "DATA_READY"]) && ($isProjectManager || $isReviewer)): ?>
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#evaluationDiv" role="button" aria-expanded="false">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Evaluation</h5>
            </div>
            <div id="evaluationDiv" class="card-body collapse">
                <div id="evaluation" class="metadata-form"
                    data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                    data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
        <?php endif ?>

        <?php if ($requestType == "REGULAR" and in_array($requestStatus, ["PREREGISTRATION_SUBMITTED", "PREREGISTRATION_CONFIRMED", "DTA_READY", "DTA_SIGNED", "DATA_READY"]) && ($isRequestOwner || $isProjectManager)): ?>
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#preregistrationDiv" role="button" aria-expanded="false">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Preregistration</h5>
            </div>
            <div id="preregistrationDiv" class="card-body collapse">
                <div id="preregistration" class="metadata-form"
                    data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                    data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
        <?php endif ?>
    </div>
</div>
