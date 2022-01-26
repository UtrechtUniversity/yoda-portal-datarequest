<script>
    var browsePageItems = <?php echo $items; ?>;
    var archived = <?php echo json_encode($archived); ?>;
</script>

<div class="row">
    <div class="col-md-12">
        <?php if ($submissionAllowed): ?>
        <a href="/datarequest/add" class="btn btn-primary mb-3 float-right" role="button">Submit data request</a>
        <?php endif ?>
        <?php if (!$isDACMember and !$archived): ?>
        <a href="/datarequest/archive" class="btn btn-primary mb-3 mr-1 float-right" role="button">View archived requests</a>
        <?php endif ?>
        <?php if (!$isDACMember and $archived): ?>
        <a href="/datarequest" class="btn btn-primary mb-3 mr-1 float-right" role="button">View active requests</a>
        <?php endif ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table id="file-browser" class="table yoda-table table-striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Request ID</th>
                    <th>Research proposal</th>
                    <th>Submission date</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<hr>

<h4 class="text-center">What are the steps to receiving YOUth data?</h4>

<div class="row bs-wizard" style="border-bottom:0;">
    <div class="col-md-3 bs-wizard-step disabled">
        <div class="text-md-center bs-wizard-stepnum">1. Submission</div>
        <div class="progress"><div class="progress-bar"></div></div>
        <a href="#" class="bs-wizard-dot"></a>
    </div>

    <div class="col-md-3 bs-wizard-step disabled">
        <div class="text-md-center bs-wizard-stepnum">2. Under review</div>
        <div class="progress"><div class="progress-bar"></div></div>
        <a href="#" class="bs-wizard-dot"></a>
    </div>

    <div class="col-md-3 bs-wizard-step disabled">
        <div class="text-md-center bs-wizard-stepnum">3. Reviewed</div>
        <div class="progress"><div class="progress-bar"></div></div>
        <a href="#" class="bs-wizard-dot"></a>
    </div>

    <div class="col-md-3 bs-wizard-step disabled">
        <div class="text-md-center bs-wizard-stepnum">4. Approved</div>
        <div class="progress"><div class="progress-bar"></div></div>
        <a href="#" class="bs-wizard-dot"></a>
    </div>
</div>
<div class="row bs-wizard" style="border-bottom:0;">
    <div class="col-md-3 bs-wizard-step disabled">
        <div class="text-md-center bs-wizard-stepnum">5. Preregistration</div>
        <div class="progress"><div class="progress-bar"></div></div>
        <a href="#" class="bs-wizard-dot"></a>
    </div>

    <div class="col-md-3 bs-wizard-step disabled">
        <div class="text-md-center bs-wizard-stepnum">6. DTA ready</div>
        <div class="progress"><div class="progress-bar"></div></div>
        <a href="#" class="bs-wizard-dot"></a>
    </div>

    <div class="col-md-3 bs-wizard-step disabled">
        <div class="text-md-center bs-wizard-stepnum">7. DTA signed</div>
        <div class="progress"><div class="progress-bar"></div></div>
        <a href="#" class="bs-wizard-dot"></a>
    </div>

    <div class="col-md-3 bs-wizard-step disabled">
        <div class="text-md-center bs-wizard-stepnum">8. Data ready</div>
        <div class="progress"><div class="progress-bar"></div></div>
        <a href="#" class="bs-wizard-dot"></a>
    </div>
</div>

<hr />

<h4 class="text-center">What happens at each step?</h4>

<table class="process-table">
    <tr>
        <td><img src=/datarequest/static/img/button.png></img></td>
        <td>Submission</td>
        <td>The researcher submits the data request.</td>
    </tr>
    <tr>
        <td><img src=/datarequest/static/img/button.png></img></td>
        <td>Under review</td>
        <td>The YOUth data manager has assigned the data request for review to one or more members of the YOUth Data Access Committee</td>
    </tr>
    <tr>
        <td><img src=/datarequest/static/img/button.png></img></td>
        <td>Reviewed</td>
        <td>The data request has been reviewed by the YOUth Data Access Committee</td>
    </tr>
    <tr>
        <td><img src=/datarequest/static/img/button.png></img></td>
        <td>Approved</td>
        <td>The YOUth project manager has approved the proposal.</td>
    </tr>
    <tr>
        <td><img src=/datarequest/static/img/button.png></img></td>
        <td>Preregistration</td>
        <td>The researcher preregisters the study in the YOUth Open Science Framework preregistry. The YOUth project manager has confirmed the preregistration.</td>
    </tr>
    <tr>
        <td><img src=/datarequest/static/img/button.png></img></td>
        <td>DTA ready</td>
        <td>The YOUth data manager has created a Data Transfer Agreement, stipulating the terms and conditions under which the researcher is allowed to use the data.</td>
    </tr>
    <tr>
        <td><img src=/datarequest/static/img/button.png></img></td>
        <td>DTA signed</td>
        <td>The researcher has submitted a signed copy of the DTA.</td>
    </tr>
    <tr>
        <td><img src=/datarequest/static/img/button.png></img></td>
        <td>Data ready</td>
        <td>The researcher may now download the requested data.</td>
    </tr>
</table>

<hr />

<h4 class="text-center">Need help?</h4>
<p class="text-center">Send an email to <?php echo $help_contact_name ?> at <a href=mailto:<?php echo $help_contact_email ?>><?php echo $help_contact_email ?></a>.</p>
