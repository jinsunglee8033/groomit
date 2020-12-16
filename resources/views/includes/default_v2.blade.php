<!DOCTYPE html>
<html lang="en">
<head>

<!-- Segment Analytics Service -->
<script>
 !function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","debug","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t<analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t,e){var n=document.createElement("script");n.type="text/javascript";n.async=!0;n.src="https://cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(n,a);analytics._loadOptions=e};analytics.SNIPPET_VERSION="4.1.0";
 analytics.load("vVEIBQcDH1zdJDWvAqBlMcEWcGO9rz7z");
 analytics.page();
 }}();
</script>
	
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
    <!-- Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="/js/aos/aos.css" rel="stylesheet">
    <link href="/css/style_v2.css?v=1.0.01" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>


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

@include('includes.header_v2')

@yield('contents')

@include('includes.footer_v2')

</body>
</html>
