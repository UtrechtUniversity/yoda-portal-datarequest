<script>
    var browsePageItems = <?php echo $items; ?>;
    var view = 'browse';
</script>

<div class="row">
    <a href="datarequest/add" class="btn btn-default pull-right" role="button">Add research proposal</a>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <table id="file-browser" class="table yoda-table table-striped">
                <thead>
                    <tr>
			<th>User</th>
                        <th>Name</th>
                        <th>Submission date</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
