<script>
    var requestId = <?php echo $requestId; ?>;
    var username = "<?php echo $username; ?>";
</script>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#assignDiv" role="button" aria-expanded="false">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Project manager's response to data manager review (if any)</h5>
            </div>
            <div id="assignDiv" class="card-body collapse">
                <div id="assign" class="metadata-form"
                    data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                    data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
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
    </div>
</div>

<div class="row">
    <div class="col-md-12">
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
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#datarequestDiv" role="button" aria-expanded="true">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Data request <?php echo html_escape($requestId) ?></h5>
            </div>
            <div id="datarequestDiv" class="card-body collapse show">
                <div id="datarequest" class="metadata-form"
                     data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                     data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
    </div>
    <div class=col-md-6>
        <div class="card">
            <div class="card-header clearfix">
                <h5 class="card-header float-left">
                    Data request review form
                </h5>
            </div>
            <div class="card-body">
                <div id="form" class="metadata-form"
                     data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                     data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/datarequest/static/js/datarequest/review.js" type="text/javascript"></script>
