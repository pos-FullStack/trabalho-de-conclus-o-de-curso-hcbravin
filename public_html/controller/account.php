<?php

    if($URI[0] == 'account'){

        if($URI[1] == 'convites'){

            $Usuario = new Usuario();
            $Convites = $Usuario -> getConvites();
            require_once Views . '/account/convites.php';
        }

    }