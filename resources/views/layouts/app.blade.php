<!DOCTYPE html>
<html class="h-full bg-gray-100" lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Bolton College')</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
       
        @vite('resources/css/app.css')

        <link rel="icon" type="image/x-icon" href="{{ Vite::asset('resources/favicons/favicon.ico') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ Vite::asset('resources/favicons/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ Vite::asset('resources/favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ Vite::asset('resources/favicons/favicon-16x16.png') }}">
      
    </head>
    <body class="h-full cursor-none">
        <div class="min-h-full bg-gradient-custom gradient-young-people h-full">
    

            <main class="h-full">

                <div class="px-5 mx-auto sm:px-6 lg:px-8 pb-4 h-full">

                    @yield('content')
                
                </div>

            </main>
            
        </div>

    
    </body>
</html>