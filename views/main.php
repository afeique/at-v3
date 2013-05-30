<!doctype html>
<html lang="en">
    <head>
        <title>
            <?= $title . $title_separator . $title_suffix ?>
        
        </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <?php
        $min = '';
        if ($use_minified_resources)
            $min = '.min';
        ?>

        <!-- bootstrap -->
        <link href="<?= $css_base_url ?>bootstrap<?= $min ?>.css" media="screen" rel="stylesheet" type="text/css" />

        <!-- main css -->
        <link href="<?= $css_base_url ?>screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <link href="<?= $css_base_url ?>print.css" media="print" rel="stylesheet" type="text/css" />
        <!--[if IE]>
            <link href="stylesheets/ie.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <![endif]-->

        <!-- head.js -->
        <script src="<?= $js_base_url ?>head<?= $min ?>.js"></script>
        <script type="text/javascript">
            <? foreach ($js as $scripts): 
            $seq = array();
            array_walk($scripts, function($script, $i) use (&$seq, $js_base_url, $min) {
                $seq[] = '"'. $js_base_url . $script . $min .'.js';

            }); ?>
            
            head.js(<?= implode(', ', $seq) ?>);
            <? endforeach; ?>

        </script>
    </head>

    <body>
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="/">acrosstime</a>
                </div>
            </div>
        </div>

        <div class="container" id="main-container">
            <?= Base::instance()->raw($content) ?>

        </div>
    </body>

</html>