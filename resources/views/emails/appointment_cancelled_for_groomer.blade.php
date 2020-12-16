<html>
<head>
    <title>Confirmation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <style>
        *{padding: 0; font-family: Helvetica, Arial, sans-serif;}
        a{border: none; text-decoration: none; outline: none;}
        img{border: none; text-decoration: none; outline: none; display: block;}
        tr{display: block;}
        .mail {background-color: #ebebeb;}
        .mail h5{text-align: center;font-size: 13px; color: #7e7e7e; margin: 2px 0; padding: 0;font-weight: lighter;}
    </style>

</head>
<body bgcolor="#ebebeb" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="mail">
<table width="600"  border="0" cellpadding="0" cellspacing="0" style="width: 600px; margin:0 auto;">
    <tbody width="600">
    <tr>
        <td width="600" height="25" bgcolor="#ebebeb" >&nbsp;</td>
    </tr>
    <tr>
        <td width="600" height="52" style="text-align:center;background-color: #ebebeb;">
            <a style="display:inline-block; margin:auto;" href="http://www.groomit.me/" target="_blank">
                <img src="{{ url('/') . '/images/email/thankyou_02.png'}}" alt='Groomit' />
            </a>
        </td>
    </tr>
    <tr>
        <td width="600" height="14" bgcolor="#ebebeb" >&nbsp;</td>
    </tr>
    <tr>
        <td width="600">
            <img src="{{ url('/') . '/images/email/thankyou_06.png'}}" alt='Groomit Dog' />
        </td>
    </tr>
    </tbody>
</table>

<table bgcolor="#fff" width="600"  border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
    <tbody>
    <tr>
        <td width="55">&nbsp;</td>
        <td width="490">
            <h3 style="text-align:center;color:#231f20;font-size:20px;">Your scheduled appointment has been cancelled</h3>

            <h4 style="text-align:center;color:#7e7e7e;font-size:15px;margin-bottom:0;">Address:</h4>
            <p style="text-align:center;color:#7e7e7e;font-size:15px;margin:0;">{!! $data['address'] !!}</p>

            <h4 style="text-align:center;color:#7e7e7e;font-size:15px;margin-bottom:0;">{!! $data['accepted_date'] !!}</h4>

            <p style="text-align:center;color:#7e7e7e;font-size:15px;margin:0;">
                @foreach ($data['pet'] as $p) Pet Name: {!! $p['pet_name'] !!} - Package: {!! $p['package_name'] !!} <br>@endforeach
                with {!! $data['user'] !!}
            </p>

        </td>
    </tr>
    </tbody>
</table>





<table width="600"  border="0" cellpadding="0" cellspacing="0" style="width: 600px; margin:0 auto;">
    <tbody width="600">
    <tr>
        <td width="600" height="25" bgcolor="#fff" >&nbsp;</td>
    </tr>
    <tr>
        <td width="600" height="20" bgcolor="#ebebeb" >&nbsp;</td>
    </tr>
    <tr>
        <td width="600" height="20" bgcolor="#fff" style="border-radius:6px 6px 0 0;">
        </td>
    </tr>
    </tbody>
</table>

<table bgcolor="#fff" width="600"  border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
    <tbody>
    <tr>
        <td width="55">&nbsp;</td>
        <td width="490">
            <h3 style="text-align:center;color:#231f20;font-size:20px;margin-bottom:5px;">To change or Cancel</h3>
            <p style="text-align:center;color:#7e7e7e;font-size:12px;margin:0;">Open the Groomit app and select my appointments.</p>
        </td>
    </tr>
    <tr>
        <td width="600" bgcolor="#fff">
            <img src="{{ url('/') . '/images/email/img-04.png'}}" alt='Groomit' style='margin:10px auto 30px auto; display:block;' />
        </td>
    </tr>
    <tr>
        <td width="55">&nbsp;</td>
        <td width="490">
            <h3 style="text-align:center;color:#231f20;font-size:23px;margin-bottom:25px;">Follow Us</h3>
        </td>
    </tr>
    <tr>
        <td width="226">&nbsp;</td>
        <td width="35">
            <a href="https://www.facebook.com/groomitapp" target="_blank">
                <img src="{{ url('/') . '/images/email/facebook.png'}}" alt='Fcebook' />
            </a>
        </td>
        <td width="78" style="text-align:center;">
            <a href="https://twitter.com/groomitapp" target="_blank" style="display:inline-block; margin-auto;">
                <img src="{{ url('/') . '/images/email/twitter.png'}}" alt='Twitter' />
            </a>
        </td>
        <td width="35">
            <a href="https://www.instagram.com/groomitapp/" target="_blank">
                <img src="{{ url('/') . '/images/email/instagram.png'}}" alt='Instagram' />
            </a>
        </td>
        <td width="226">&nbsp;</td>
    </tr>
    </tbody>
</table>

@include('includes.mail_footer')

</body>
</html>
