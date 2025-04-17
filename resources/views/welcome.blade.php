<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>AviTech - Test</title>

    </head>
    <body>
        <center>
            <img src="{{ url('assets/logo.png') }}">
            <p>Click the button below to generate the PDF file</p>
            <a href="{{ url('generate-pdf') }}" style="text-decoration:none;background:gray;border-radius:3px;border:0px;padding:10px 23px;color:white;">
                Generate PDF
            </a>
        </center>
    </body>
</html>
