<!doctype html>
<html lang="en">
    <head>
        <title>
            <?= $this->title . $this->title_separator . $this->title_suffix ?>
        
        </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <?php
        $min = '';
        if ($this->use_minified_resources)
            $min = '.min';
        ?>

        <!--[if lt IE 9]>
        <script src="/js/html5shiv.js"></script>
        <![endif]-->

        <!-- bootstrap -->
        <link href="/css/bootstrap<?= $min ?>.css" media="screen" rel="stylesheet" type="text/css" />

        <!-- main css -->
        <link href="/css/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <link href="/css/print.css" media="print" rel="stylesheet" type="text/css" />
        <!--[if IE]>
            <link href="/stylesheets/ie.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <![endif]-->

        <!-- head.js -->
        <script src="/js/head<?= $min ?>.js"></script>
        <script type="text/javascript">
            <? foreach ($this->js->to_array() as $scripts): 
            $seq = array();
            array_walk($scripts, function($script, $i) use (&$seq, $min) {
                $seq[] = '"/js/' . $script . $min .'.js"';

            }); ?>
            
            head.js(<?= implode(', ', $seq) ?>);
            <? endforeach; ?>

        </script>
    </head>

    <body>
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <ul class="nav">
                        <li><a class="brand" href="/">acrosstime</a></li>
                    </ul>
                    <div class="pull-right">
                        <ul class="nav secondary-nav">
                            <li>
                                <a href="/">not a member? <strong>sign up</strong></a>
                            </li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="login-dropdown-toggle">
                                    login <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu" aria-labelledby="login-dropdown-toggle" id="login-dropdown">
                                    <li>
                                        <form class="login-dropdown-form">
                                            <input type="text" placeholder="email" />
                                            <input type="password" placeholder="password" />
                                            <div class="pull-right">
                                                <button type="submit" class="btn btn-success">sign in</button>
                                            </div>
                                            <a href="#" class="btn btn-info">recover account</a>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="container" id="main-container">
            <?= $this->content ?>

        </div>
    </body>

</html>