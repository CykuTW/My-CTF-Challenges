<?php
    if (!isset($from_render) || !$from_render) {
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.5/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <title>CureURL</title>
    <style>
        * {
            font-size: 1.2rem;
        }
        .section {
            padding: 1.5rem 1.5rem;
        }
        .is-horizontal-center {
            justify-content: center;
        }
    </style>