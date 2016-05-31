<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kajona admin [%%webpathTitle%%]</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="generator" content="Kajona, www.kajona.de" />

        <link rel="stylesheet" href="_skinwebpath_/less/styles.css?_system_browser_cachebuster_" type="text/css" />
    <link rel="stylesheet" href="_skinwebpath_/less/responsive.css?_system_browser_cachebuster_" type="text/css" />

    <script src="_webpath_/core/module_system/admin/scripts/jquery/jquery.min.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js?_system_browser_cachebuster_"></script>
    %%head%%
    <script src="_webpath_/core/module_system/system/scripts/loader.js?_system_browser_cachebuster_"></script>
    <script src="_webpath_/core/module_system/admin/scripts/kajona.js?_system_browser_cachebuster_"></script>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="_skinwebpath_/js/html5.js?_system_browser_cachebuster_"></script>
    <![endif]-->

    <link rel="shortcut icon" href="_skinwebpath_/img/favicon.png">
    <!--
    <link rel="apple-touch-icon" href="_skinwebpath_/img/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="_skinwebpath_/img/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="_skinwebpath_/img/apple-touch-icon-114x114.png">
    -->
</head>

<body class="dialogBody">


<div class="container-fluid">
    <div class="row-fluid">

        <!-- CONTENT CONTAINER -->
        <div id="content">
            %%content%%
        </div>
    </div>
</div>



<script src="_skinwebpath_/js/jquery.ui.touch-punch.min.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-transition.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-alert.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-modal.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-dropdown.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-scrollspy.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-tab.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-tooltip.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-popover.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-button.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-collapse.js?_system_browser_cachebuster_"></script>
<script src="_skinwebpath_/js/bootstrap-carousel.js?_system_browser_cachebuster_"></script>

<script src="_skinwebpath_/js/v4skin.js?_system_browser_cachebuster_"></script>


<div class="modal hide fade fullsize" id="folderviewDialog" role="dialog">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3>BROWSER</h3>
    </div>
    <div class="modal-body">
        <div id="folderviewDialog_content"><!-- filled by js --></div>
    </div>
</div>


<script type="text/javascript">
    KAJONA.admin.loader.loadFile("_skinwebpath_/js/kajona_dialog.js", function() {
        KAJONA.admin.folderview.dialog = new KAJONA.admin.ModalDialog('folderviewDialog', 0, true, true);
    }, true);
</script>

<div id="jsStatusBox" class="" style="display: none; position: absolute;"><div class="jsStatusBoxHeader">Status-Info</div><div id="jsStatusBoxContent" class="jsStatusBoxContent"></div></div>

</body>
</html>