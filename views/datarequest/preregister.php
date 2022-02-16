<script>
    var requestId = "<?php echo $requestId; ?>";
</script>

<?php if ($approvalConditions): ?>
<div class="row">
    <div class="col-md-12">
        <div class="border border-danger">
            <h5 class="text-danger">Caution: approval conditions apply!</h5>
            <p>The YOUth project manager has approved your data request, but has added one or more approval conditions. Please note that by proceeding, you will consent to these conditions.</p>
            <p class="font-weight-bold">Approval conditions:</p>
            <p><?php echo nl2br(html_escape($approvalConditions)) ?></p>
        </div>
    </div>
</div>
<br/>
<?php endif ?>

<div class="row">
    <div class="col-md-12">
        <div id="preregister" class="metadata-form"
             data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
             data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
            <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
        </div>
        <hr>
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
                <h5 class="card-header float-left">Data request <?php echo html_escape($requestId) ?></h5>
            </div>
            <div id="datarequestDiv" class="card-body collapse">
                <div id="datarequest" class="metadata-form"
                     data-csrf_token_name="<?php echo rawurlencode($tokenName); ?>"
                     data-csrf_token_hash="<?php echo rawurlencode($tokenHash); ?>">
                    <p>Loading metadata <i class="fa fa-spinner fa-spin fa-fw"></i></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/datarequest/static/js/datarequest/preregister.js" type="text/javascript"></script>
