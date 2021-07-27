<script>
    var requestId = <?php echo $requestId; ?>;
</script>


<div class="row">
    <div class=col-md-12>
        <a href="/datarequest/confirm_preregistration/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 float-right" role="button">Confirm preregistration</a>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#preregistrationDiv" role="button" aria-expanded="true">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Preregistration form for data request <?php echo html_escape($requestId) ?></h5>
            </div>
            <div id="preregistrationDiv" class="card-body collapse show">
                <div id="preregistration" class="metadata-form"
                     data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                     data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/datarequest/static/js/datarequest/preregistration_confirm.js" type="text/javascript"></script>
