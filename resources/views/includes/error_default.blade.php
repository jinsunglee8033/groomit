<!DOCTYPE html>
<html lang="en">
<head>
	
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TKX9ZHP');</script>
<!-- End Google Tag Manager -->
	
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>{{ Helper::get_meta_title() }}</title>
    <link rel="canonical" href="{{ Helper::get_meta_canonical() }}">
    <meta name="description" content="{{ Helper::get_meta_description() }}">
    <meta name="keywords" content="{{ Helper::get_meta_keyword() }}">
    <!-- Bootstrap -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../desktop/css/animate.min.css" rel="stylesheet">
	<link href="../desktop/js/owlCarousel/assets/owl.carousel.min.css" rel="stylesheet">
	<link href="../js/aos/aos.css" rel="stylesheet">
    <link href="../css/style.css?v=20190372" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">
    <script defer src="../font-awesome/js/fontawesome-all.js"></script>


    <!-- add meta -->
    <meta name="p:domain_verify" content="76b6192626eab9089eda18fd19ae4a4b"/>

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=almAEvpzoq">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=almAEvpzoq">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=almAEvpzoq">
    <link rel="manifest" href="/site.webmanifest?v=almAEvpzoq">
    <link rel="mask-icon" href="/safari-pinned-tab.svg?v=almAEvpzoq" color="#bf372b">
    <link rel="shortcut icon" href="/favicon.ico?v=almAEvpzoq">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
	
</head>
<body>
	
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TKX9ZHP"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

@include('includes.header')

@yield('content')

@include('includes.footer')

</body>
</html>
