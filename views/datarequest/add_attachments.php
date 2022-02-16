<script>
    var requestId = "<?php echo $requestId; ?>";
</script>

<div class="row metadata-form">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header clearfix">
                <h5 class="card-header float-left">Attachments</h5>
            </div>
            <div class="card-body collapse show">
                <div>Please upload each file that should be attached to your data request here and click the Submit button to confirm your file attachments.</div>
                <div>The maximum file size per file is <strong>100 MiB</strong>.</div>
                <hr class="border-0 bg-secondary" style="height: 1px;">
                <div class="form-group">
                    <form id="attachment" enctype="multipart/form-data">
                        <label for="file">Select a file to upload:</label><br />
                        <input type="file" name="file" id="file" />
                    </form>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary upload_attachment">Upload</button>
                </div>
                <hr class="border-0 bg-secondary" style="height: 1px;">
                <div class="form-group">
                    <p>Currently attached files</p>
                    <ul>
                        <?php $i=0; foreach($attachments as $attachment) { echo "<li><a href=\"/datarequest/download_attachment/" . html_escape($requestId) . "?file=" . $i . "\">" . html_escape($attachment) . "</a></li>"; $i++; } ?>
                    </ul>
                </div>
                <hr class="border-0 bg-secondary" style="height: 1px;">
                <div class="form-group">
                <a href="/datarequest/submit_attachments/<?php echo html_escape($requestId) ?>" class="btn btn-primary mb-3 mr-1 float-left <?php if(count($attachments) < 1) { echo 'disabled'; } ?>" role="button">Submit attachments</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/datarequest/static/js/datarequest/add_attachments.js" type="text/javascript"></script>
