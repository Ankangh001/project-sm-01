<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Location | <?=($app_name) ? $app_name[0]->message : '' ?>
        </title>
        <?php base_url() . include 'include.php'; ?>
    </head>

    <body class="hold-transition sidebar-mini">
        <div class="wrapper">
            <?php base_url() . include 'header.php'; ?>
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1>Create and Manage Location</h1>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-secondary">
                                    <div class="card-header">
                                        <h3 class="card-title">Add Location</h3>
                                    </div>
                                    <form action="<?= APP_URL ?>store_location" role="form" id="insert_form" method="POST"
                                        enctype="multipart/form-data">
                                        <?= csrf_field() ?>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Location Name</label>
                                                <input type="text" name="location_name" class="form-control"
                                                    placeholder="Location Name" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Latitude</label>
                                                <input type="number" name="latitude" class="form-control"
                                                    placeholder="Latitude" step='any' required>
                                            </div>
                                            <div class="form-group">
                                                <label>Longitude</label>
                                                <input type="number" name="longitude" class="form-control"
                                                    placeholder="Longitude" step='any' required>
                                            </div>
                                            <?php
                                            if ($this->session->getFlashdata('error')) {
                                                ?>
                                                <div class="row form-group">
                                                    <p id="error_msg" class="alert alert-danger">
                                                        <?php echo $this->session->getFlashdata('error'); ?>
                                                    </p>
                                                </div>
                                            <?php } ?>
                                            <?php if ($this->session->getFlashdata('success')) { ?>
                                                <p id="success_msg" class="alert alert-success">
                                                    <?php echo $this->session->getFlashdata('success'); ?>
                                                </p>
                                            <?php } ?>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-secondary">
                                    <div class="card-header">
                                        <h3 class="card-title">Locations <small>View / Update / Delete</small></h3>
                                    </div>
                                    <div class="card-body">
                                        <table aria-describedby="mydesc" class='table-striped' id='location_list'
                                            data-toggle="table" data-url="<?= APP_URL . 'Table/location' ?>"
                                            data-click-to-select="true" data-side-pagination="server"
                                            data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                            data-search="true" data-trim-on-search="false" data-show-columns="true"
                                            data-show-refresh="true" data-fixed-columns="true" data-fixed-number="1"
                                            data-fixed-right-number="1" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true" data-maintain-selected="true"
                                            data-export-types='["txt","excel"]'
                                            data-export-options='{ "fileName": "location-list-<?= date('d-m-y') ?>" }'
                                            data-query-params="queryParams">
                                            <thead>
                                                <tr>
                                                    <th scope="col" data-field="id" data-sortable="true">ID</th>
                                                    <th scope="col" data-field="location_name" data-sortable="true">Location Name</th>
                                                    <th scope="col" data-field="latitude" data-sortable="false">Latitude</th>
                                                    <th scope="col" data-field="longitude" data-sortable="false">Longitude</th>
                                                    <th scope="col" data-field="operate" data-sortable="false" data-events="actionEvents">Operate</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- /.content -->
            </div>
            <div class="modal fade" id="editDataModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Edit Location</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="<?= APP_URL ?>update_location" role="form" id="update_form" method="POST"
                            enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type='hidden' name="edit_id" id="edit_id" value='' />
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Location Name</label>
                                    <input type="text" name="location_name" id="edit_location_name" class="form-control"
                                        placeholder="Location Name" required>
                                </div>
                                <div class="form-group">
                                    <label>Latitude</label>
                                    <input type="number" name="latitude" id="edit_latitude" class="form-control"
                                        placeholder="Latitude" required>
                                </div>
                                <div class="form-group">
                                    <label>Longitude</label>
                                    <input type="number" name="longitude" id="edit_longitude" class="form-control"
                                        placeholder="Longitude" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php base_url() . include 'footer.php'; ?>
        </div>
        <!-- ./wrapper -->
        <script type="text/javascript">
            window.actionEvents = {
                'click .edit-data': function (e, value, row, index) {
                    $('#edit_id').val(row.id);
                    $("#edit_location_name").val(row.location_name);
                    $("#edit_latitude").val(row.latitude);
                    $("#edit_longitude").val(row.longitude);
                }
            };
        </script>
        <script type="text/javascript">
            function queryParams(p) {
                return {
                    limit: p.limit,
                    order: p.order,
                    offset: p.offset,
                    search: p.search
                };
            }
    
        <script type="text/javascript">
            $(document).ready(function () {
                $('#insert_form').validate({
                    errorElement: 'span',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('.form-group').append(error);
                    },
                    highlight: function (element, errorClass, validClass) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function (element, errorClass, validClass) {
                        $(element).removeClass('is-invalid');
                    }
                });
            });
        </script>
        <script type="text/javascript">
            $(document).ready(function () {
                $('#update_form').validate({
                    errorElement: 'span',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('.form-group').append(error);
                    },
                    highlight: function (element, errorClass, validClass) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function (element, errorClass, validClass) {
                        $(element).removeClass('is-invalid');
                    }
                });
            });

            
        </script>
        
         <script type="text/javascript">
            $(document).on('click', '.delete-data', function () {
                if (confirm('Are you sure? Want to delete Location? All related data will also be deleted')) {
                    var base_url = "<?= APP_URL ?>";
                    var id = $(this).data("id");
                    $.ajax({
                        url: base_url + 'delete_location',
                        type: "POST",
                        dataType: "json",
                        data: { id: id },
                        success: function (result) {
                            if (result) {
                                $('#location_list').bootstrapTable('refresh');
                            } else {
                                alert('Location could not be deleted');
                            }
                        }
                    });
                }
            });
        </script>
    </body>

</html>