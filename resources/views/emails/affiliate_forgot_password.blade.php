<html>
<head>
  <title>Confirmation</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <style>
    body {background-color: #ebebeb;}
    *{padding: 0; font-family: Helvetica, Arial, sans-serif;}
    a{border: none; text-decoration: none; outline: none;}
    img{border: none; text-decoration: none; outline: none; display: block;}
    tr{display: block;}
    .mail {background-color: #ebebeb;}
    .mail h5{text-align: center;font-size: 13px; color: #7e7e7e; margin: 2px 0; padding: 0;font-weight: lighter;}
  </style>
</head>
<body bgcolor="#ebebeb" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="mail">
<table width="600"  border="0" cellpadding="0" cellspacing="0" style="width: 600px; margin:0 auto;  ">
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

<table width="600" border="0" cellpadding="0" cellspacing="0" class="mail" style="background-color:#fff;margin: 0 auto;">
  <tbody>
  <tr>
    <td width="55">&nbsp;</td>
    <td width="490">
      <h3 style="text-align:left;color:#7e7e7e;font-size:16px;">You have requested to reset your password.</h3>

      <h4 style="text-align:left;color:#7e7e7e;font-size:16px; font-weight:300;">Please enter forgot password temporary key below to continue and reset your password.</h4>

      <h4 style="text-align:center;color:#7e7e7e;font-size:15px;margin-bottom:0;">{!! $data['temp_key'] !!}</h4>

      <h4 style="text-align:left;color:#7e7e7e;font-size:16px; margin-top:50px; font-weight:300;">Thanks,<br>The Groomit Team</h4>

    </td>
  </tr>
  </tbody>
</table>


@include('includes.mail_footer')

</body>
</html>
