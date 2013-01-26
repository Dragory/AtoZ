<!doctype html>
<html>
<head>
    <meta charset="UTF-8">

    <title>AtoZ</title>

    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div id="header">
        <div id="header-wrap">
            <div id="logo">
                A to Z
            </div>
            <div id="menu">
                {{ $menu }}
            </div>
        </div>
    </div>
    <div id="content">
        <div id="content-wrap">
            {{ $notifications }}
            {{ $content }}
        </div>
    </div>

    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
</body>
</html>