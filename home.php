<?php session_start(); ?>
<?php
if (isset($_POST['start_cont'])) {
    $cont_name = $_POST['cont_name'];
    $str = shell_exec("sudo docker start $cont_name");
    echo $str;
    exit;
}
if (isset($_POST['stop_cont'])) {
    $cont_name = $_POST['cont_name'];
    $str = shell_exec("sudo docker stop $cont_name");
    echo $str;
    exit;
}
if (isset($_POST['remove_cont'])) {
    $cont_name = $_POST['cont_name'];
    $str = shell_exec("sudo docker stop $cont_name");
    $str = shell_exec("sudo docker rm $cont_name");
    include_once 'db_connect.php';
    mysql_query("Delete from containers where container_name='$cont_name'");
    $containers = array();
    $cont = mysql_query("SELECT * from containers where user_id=" . $_SESSION['id']);
    if (mysql_num_rows($cont) > 0) {
        while ($c = mysql_fetch_assoc($cont)) {
            $containers[] = $c;
        }
    }
    $_SESSION['containers'] = $containers;
    mysql_close($link);
    echo $str;
    exit;
}

function status($cont_name) {
    $str = shell_exec("sudo docker ps -a | grep $cont_name");
    if (strpos($str, "Exited ") !== false) {
        return "Start";
    } else {
        return "Stop";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>PaaS framework with docker</title>

        <!-- Bootstrap core CSS -->
        <link href="css/cloud.css" rel="stylesheet">
        <link href="css/bootstrap.css" rel="stylesheet">


        <!-- Custom styles for this template -->
        <link href="jumbotron.css" rel="stylesheet">
        <style>
            #containers {
                border-left: 2px dashed grey;
                display: inline-block;
                height: 350px;
                margin-left: 7%;
                margin-top: 3%;
                padding-left: 4%;
                vertical-align: top;
                width: 45%;
            }
        </style>
    </head>

    <body>

        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button> 
                    <span class="glyphicon glyphicon-cloud" style="font-size: 25px; float: left; margin-top: 12px; margin-right: 5px; color: rgb(221, 221, 221);"></span><a class="navbar-brand" href="#">PaaS with Docker</a>
                </div>
                <div class="navbar-collapse collapse">
                    <?php
                    if (!isset($_SESSION['loggedin'])) {
                        header("Location:index.html");
                    }
                    ?>
                    <span><a style="text-decoration: none; color: wheat;float: right;margin: 1%" href="login.php?logout=1">Logout</a></span>
                    <span style="color: snow;float: right;margin: 1%">Welcome, <?php echo $_SESSION['loggedin']; ?></span>
                </div><!--/.navbar-collapse -->
            </div>
        </div>

        <!-- Main jumbotron for a primary marketing message or call to action -->
        <div class="jumbotron">
            <div class="container">
                <div style="display: inline-block">
                    <div class="main" style="margin-left: -12%">
                        <div class="cloud_base">
                            <span class="rounds"></span></div>
                    </div>
                    <form id="multiform" style="width: 100%;max-width:80% ; margin: 12%;" class="form-signin form-horizontal" role="form" action="index.php" method="post" enctype="multipart/form-data">
                        <h2 class="form-signin-heading">Deploy an application</h2>
                        <input type="text" name="app_name" class="form-control" placeholder="Application name" required autofocus>
                        <br/>
                        <select name="lang" class="form-control">
                            <option value="" selected disabled="disabled">Select framework</option>
                            <option value="html">HTML</option>
                            <option value="php">PHP</option>
                            <option value="php_mysql">PHP With Mysql</option>
                            <option value="web2py">Python web2py Framework</option>
                        </select>
                        <br/>
                        <div id="mysqlinfo" style="display:none">
                            <label>Default username: root, password:root</label>
                            <input type="text" name="mysql_user" class="form-control" placeholder="Mysql username" value="root" > 
                            <br/><input type="password" name="mysql_pass" class="form-control" placeholder="Mysql password" value="root">
                            <br/><input type="file" name="mysqldump" class="filestyle" data-buttonName="btn-primary" data-buttonText="mysql dump(.sql)"> <br/>
                        </div>
                        <input type="file" name="appArchive" accept="application/zip" class="filestyle" data-buttonName="btn-primary" data-iconName="glyphicon glyphicon-cloud-upload" data-buttonText="Choose app zip" required> <br/>
                        <button class="btn btn-lg btn-primary" type="submit"><span class="glyphicon glyphicon-cloud"></span> <font>Deploy Now!</font></button>
                    </form>
                </div>
                <div id="containers">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">My Apps</h3>
                        </div>
                        <div class="panel-body">
                            <?php if (sizeof($_SESSION['containers']) == 0) { ?>
                                Oops! No application deployed yet. Do it now! :-)
                            <?php } ?>
                            <table class="table">
                                <tbody>
                                    <?php foreach ($_SESSION['containers'] as $k => $c) { ?>        
                                        <tr>
                                            <td><?php echo $k + 1; ?></td>
                                            <td><?php echo $c['app_url']; ?></td>
                                            <td><button class="btn btn-sm btn-primary startstop" id="<?php echo $c['container_name'] ?>"><?php echo status($c['container_name']) ?></button></td>
                                            <td><button class="btn btn-sm btn-primary remove" data-cont="<?php echo $c['container_name'] ?>">Remove</button></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!-- /container -->
        </div>

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/bootstrap-filestyle.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                var alert_dialog = '<div role="alert" class="alert alert-success" style="width:100%;margin:auto;display:none"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><strong>Well done!</strong> <font>You successfully read this important alert message.</font></div>';

                $("#multiform").submit(function (e)
                {
                    $('body').css('cursor', 'wait');
                    $('button[type="submit"] font').html('Deploying...');
                    $('button[type="submit"]').prop('disabled', true);
                    var formObj = $(this);
                    var formURL = formObj.attr("action");
                    var formData = new FormData(this);
                    $.ajax({
                        url: formURL,
                        type: 'POST',
                        data: formData,
                        mimeType: "multipart/form-data",
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (data, textStatus, jqXHR)
                        {
                            console.log(data);
                            $('.alert-success').alert('close');
                            $(alert_dialog).insertBefore(".main");
                            $('.alert-success font').html('Your application url is <a href="http://' + data + '" target="_blank">http://' + data + '</a>');
                            $('.alert-success').show();
                            $('body').css('cursor', 'default');
                            $('button[type="submit"] font').html('Deploy Now!');
                            $('button[type="submit"]').prop('disabled', false);
                        },
                        error: function (jqXHR, textStatus, errorThrown)
                        {
                            alert('error');
                            $('body').css('cursor', 'default');
                            $('button[type="submit"] font').html('Deploy Now!');
                            $('button[type="submit"]').prop('disabled', false);
                        }
                    });
                    e.preventDefault(); //Prevent Default action.

                });

                $("select[name=lang]").change(function () {
                    if ($(this).val() === 'php_mysql') {
                        $("#mysqlinfo").show();
                    } else {
                        $("#mysqlinfo").hide();
                    }
                });
                $('.startstop').click(function () {
                    elem = $(this).attr('id');
                    if ($(this).html() === 'Start') {
                        $('#' + elem).html('Starting...');
                        $.post("home.php", {cont_name: $(this).attr('id'), start_cont: true})
                                .done(function (data) {
                                    $('#' + elem).html('Stop');
                                });
                        //$(this).html('Stop1');
                    } else {
                        $('#' + elem).html('Stopping...');
                        $.post("home.php", {cont_name: $(this).attr('id'), stop_cont: true})
                                .done(function (data) {
                                    $('#' + elem).html('Start');
                                });
                    }
                });
                $('.remove').click(function () {
                    elem = $(this);
                    $(elem).html('wait...');
                    $.post("home.php", {cont_name: $(this).data('cont'), remove_cont: true})
                            .done(function (data) {
                                $(elem).parent().parent().remove();
                            });
                });
            });
        </script>
    </body>
</html>
