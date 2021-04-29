<script>
    var requestId = <?php echo $requestId; ?>;
</script>

<div class="row">
    <div class="col-md-12">
        <div id="preliminaryReview" class="metadata-form"
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
        <?php if(count($attachments) > 0): ?>
        <div class="card">
            <div class="card-header clearfix">
                <a class="btn btn-secondary float-left collapse-buttons" data-toggle="collapse" href="#attachmentsDiv" role="button" aria-expanded="true">
                    <span class="text-collapsed">Show</span>
                    <span class="text-expanded">Hide</span>
                </a>
                <h5 class="card-header float-left">Attachments</h5>
            </div>
            <div id="attachmentsDiv" class="card-body collapse show">
                <ul>
                    <?php $i=0; foreach($attachments as $attachment) { echo "<li><a href=\"/datarequest/download_attachment/" . html_escape($requestId) . "?file=" . $i . "\">" . html_escape($attachment) . "</a></li>"; $i++; } ?>
                </ul>
            </div>
        </div>
        <?php endif ?>
    </div>
</div>

<script src="/datarequest/static/js/datarequest/preliminaryreview.js" type="text/javascript"></script>
