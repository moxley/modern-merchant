<?php
/**
 * @package mminstall
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */
?>
<html>
    <head>
        <title>Modern Merchant Installation</title>
        <style type="text/css">
            .pass, .fail, .warn { font-weight: bold; }  
            .pass { color: green; }
            .fail { color: red; }
            .warn { color: orange; }
            td { vertical-align: top }
            table.tests td {
                background-color: #ddd;
                padding: 10px;
            }
            td.test-title {
                width: 600px;
            }
            td.test-result {
                
            }
            td.row-title {
                width: 350px;
            }
                td.row-title label {
                    font-weight: bold;
                    display: block;
                }
                td.row-title .help {
                    font-weight: normal;
                    font-size: 0.95em;
                    margin-left: 15px;
                }
            h1.main {
                font-family: Arial, Helvetica, Sans-Serif;
                margin-top: 0;
            }
        </style>
    </head>
    <body>
        <div>
            <a href="http://www.modernmerchant.org">
                <img src="/mm/plugins/mminstall/images/mm_logo.png" width="301" height="85" border="0" />
            </a>
        </div>
        <div>
            Version: <?php ph($version = mm_version(true)) ?><br />
        </div>
        <h1 class="main">Installer</h1>

<?php echo $this->content; ?>

    </body>
</html>
