<!doctype html>
<html lang="en">
    <head>
        <!-- Force latest IE rendering engine or ChromeFrame if installed -->
        <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
        
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="/css/bootstrap.min.css" rel="stylesheet" media="screen" type="text/css" />
        <link href="/css/bootstrap-responsive.min.css" rel="stylesheet" media="screen" type="text/css" />
        <link href="/css/bootstrap-image-gallery.min.css" rel="stylesheet" type="text/css" />
        <link href="/css/jquery.fileupload-ui.css" rel="stylesheet" type="text/css" />
        <noscript>
            <link href="/css/jquery.fileupload-ui-noscript.css" rel="stylesheet" type="text/css" />
        </noscript>
        <link href="/css/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <link href="/css/print.css" media="print" rel="stylesheet" type="text/css" />
        <!--[if IE]>
            <link href="/css/ie.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <![endif]-->
        <!--[if lt IE 9]>
            <script src="/js/html5shiv.js" type="text/javascript"></script>
        <![endif]-->

        
        <script src="/js/head.load.js" type="text/javascript"></script>
        <script type="text/javascript">
            head.js(
                "/js/jquery.min.js",
                "/js/angular.min.js",
                "/js/vendor/jquery.ui.widget.js",
                "/js/load-image.min.js",
                "/js/canvas-to-blob.min.js",
                "/js/jquery.iframe-transport.js",
                "/js/jquery.fileupload.js",
                "/js/jquery.fileupload-process.js",
                "/js/jquery.fileupload-image.js",
                "/js/jquery.fileupload-audio.js",
                "/js/jquery.fileupload-video.js",
                "/js/jquery.fileupload-validate.js",
                "/js/jquery.fileupload-angular.js",
                "/js/app.js"
                );
            head.js(
                "/js/bootstrap.min.js",
                "/js/bootstrap-image-gallery.min.js"
                );
        </script>
        

        <? /*
        <!-- jQuery -->
        <script src="/js/jquery.min.js"></script>
        <!-- angularjs -->
        <script src="/js/angular.min.js"></script>
        <!-- jQuery UI -->
        <script src="/js/vendor/jquery.ui.widget.js"></script>
        <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
        <script src="/js/load-image.min.js"></script>
        <!-- The Canvas to Blob plugin is included for image resizing functionality -->
        <script src="/js/canvas-to-blob.min.js"></script>
        <!-- Bootstrap JS and Bootstrap Image Gallery are not required, but included for the demo -->
        <script src="/js/bootstrap.min.js"></script>
        <script src="/js/bootstrap-image-gallery.min.js"></script>
        <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
        <script src="/js/jquery.iframe-transport.js"></script>
        <!-- The basic File Upload plugin -->
        <script src="/js/jquery.fileupload.js"></script>
        <!-- The File Upload processing plugin -->
        <script src="/js/jquery.fileupload-process.js"></script>
        <!-- The File Upload image preview & resize plugin -->
        <script src="/js/jquery.fileupload-image.js"></script>
        <!-- The File Upload audio preview plugin -->
        <script src="/js/jquery.fileupload-audio.js"></script>
        <!-- The File Upload video preview plugin -->
        <script src="/js/jquery.fileupload-video.js"></script>
        <!-- The File Upload validation plugin -->
        <script src="/js/jquery.fileupload-validate.js"></script>
        <!-- The File Upload Angular JS module -->
        <script src="/js/jquery.fileupload-angular.js"></script>
        <!-- The main application script -->
        <script src="/js/app.js"></script>
        */ ?>

        <title>acrossti.me</title>
    </head>

    <body>
        <div class="container">
            <!-- The file upload form used as target for the file upload widget -->
            <form id="fileupload" action="/upload" method="post" enctype="multipart/form-data" data-ng-app="demo" data-ng-controller="DemoFileUploadController" data-fileupload="options" ng-class="{true: 'fileupload-processing'}[!!processing() || loadingFiles]">
                <h1>What is worth remembering?</h1>
                <div class="row">
                    <div class="span3">
                        <h2><label for="name">Name it</label></h2>
                        <input id="name" name="name" type="text" />
                    </div>
                    <div class="span3">
                        <h2><label for="words">Describe it</label></h2>
                        <input id="words" name="words" type="text" />
                    </div>
                </div>

                <h2>Upload it</h2>

                <!-- Redirect browsers with JavaScript disabled to the origin page -->
                <noscript><input type="hidden" name="redirect" value="/"></noscript>
                <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                <div class="row fileupload-buttonbar">
                    <div class="span7">
                        <!-- The fileinput-button span is used to style the file input field as button -->
                        <span class="btn btn-success fileinput-button">
                            <i class="icon-plus icon-white"></i>
                            <span>Add files...</span>
                            <input type="file" name="files[]" multiple>
                        </span>
                        <button type="button" class="btn btn-primary start" data-ng-click="submit()">
                            <i class="icon-upload icon-white"></i>
                            <span>Start upload</span>
                        </button>
                        <button type="button" class="btn btn-warning cancel" data-ng-click="cancel()">
                            <i class="icon-ban-circle icon-white"></i>
                            <span>Cancel upload</span>
                        </button>
                        <!-- The loading indicator is shown during file processing -->
                        <div class="fileupload-loading"></div>
                    </div>
                    <!-- The global progress information -->
                    <div class="span5 fade" data-ng-class="{true: 'in'}[!!active()]">
                        <!-- The global progress bar -->
                        <div class="progress progress-success progress-striped active" data-progress="progress()"><div class="bar" ng-style="{width: num + '%'}"></div></div>
                        <!-- The extended global progress information -->
                        <div class="progress-extended">&nbsp;</div>
                    </div>
                </div>
                <!-- The table listing the files available for upload/download -->
                <table class="table table-striped files ng-cloak" data-toggle="modal-gallery" data-target="#modal-gallery">
                    <tr data-ng-repeat="file in queue">
                        <td data-ng-switch on="!!file.thumbnail_url">
                            <div class="preview" data-ng-switch-when="true">
                                <a data-ng-href="{{file.url}}" title="{{file.name}}" data-gallery="gallery" download="{{file.name}}"><img data-ng-src="{{file.thumbnail_url}}"></a>
                            </div>
                            <div class="preview" data-ng-switch-default data-preview="file"></div>
                        </td>
                        <td>
                            <p class="name" data-ng-switch on="!!file.url">
                                <a data-ng-switch-when="true" data-ng-href="{{file.url}}" title="{{file.name}}" data-gallery="gallery" download="{{file.name}}">{{file.name}}</a>
                                <span data-ng-switch-default>{{file.name}}</span>
                            </p>
                            <div ng-show="file.error"><span class="label label-important">Error</span> {{file.error}}</div>
                        </td>
                        <td>
                            <p class="size">{{file.size | formatFileSize}}</p>
                            <div class="progress progress-success progress-striped active fade" data-ng-class="{pending: 'in'}[file.$state()]" data-progress="file.$progress()"><div class="bar" ng-style="{width: num + '%'}"></div></div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary start" data-ng-click="file.$submit()" data-ng-hide="!file.$submit">
                                <i class="icon-upload icon-white"></i>
                                <span>Start</span>
                            </button>
                            <button type="button" class="btn btn-warning cancel" data-ng-click="file.$cancel()" data-ng-hide="!file.$cancel">
                                <i class="icon-ban-circle icon-white"></i>
                                <span>Cancel</span>
                            </button>
                            <button data-ng-controller="FileDestroyController" type="button" class="btn btn-danger destroy" data-ng-click="file.$destroy()" data-ng-hide="!file.$destroy">
                                <i class="icon-ban-circle icon-white"></i>
                                <span>Delete</span>
                            </button>
                        </td>
                    </tr>
                </table>

            </form>
        </div>


        <!-- modal-gallery is the modal dialog used for the image gallery -->
        <div id="modal-gallery" class="modal modal-gallery hide fade" data-filter=":odd" tabindex="-1">
            <div class="modal-header">
                <a class="close" data-dismiss="modal">&times;</a>
                <h3 class="modal-title"></h3>
            </div>
            <div class="modal-body"><div class="modal-image"></div></div>
            <div class="modal-footer">
                <a class="btn modal-download" target="_blank">
                    <i class="icon-download"></i>
                    <span>Download</span>
                </a>
                <a class="btn btn-success modal-play modal-slideshow" data-slideshow="5000">
                    <i class="icon-play icon-white"></i>
                    <span>Slideshow</span>
                </a>
                <a class="btn btn-info modal-prev">
                    <i class="icon-arrow-left icon-white"></i>
                    <span>Previous</span>
                </a>
                <a class="btn btn-primary modal-next">
                    <span>Next</span>
                    <i class="icon-arrow-right icon-white"></i>
                </a>
            </div>
        </div>
    </body>
</html>