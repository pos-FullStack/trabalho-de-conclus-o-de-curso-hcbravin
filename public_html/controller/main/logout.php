<?php

    unset($_SESSION);
    session_destroy();
    print '<script>window.location = "/";</script>';