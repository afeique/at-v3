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

        <div class="container">
            <?= $this->content ?>

        </div>
    </body>

</html>