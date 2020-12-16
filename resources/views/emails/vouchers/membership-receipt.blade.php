<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!--<![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome!</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,800&display=swap" rel="stylesheet">
    <style type="text/css">
        * {
            padding: 0;
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
        }
        
        html,
        body {
            background-color: #ebebeb;
        }
        
        a {
            border: none;
            text-decoration: none;
            outline: none;
        }
        
        img {
            border: none;
            text-decoration: none;
            outline: none;
            display: block;
        }
        
        tr {
            display: block;
        }
        
        .mail {
            background-color: #ebebeb;
        }
        
        .mail h5 {
            text-align: center;
            font-size: 13px;
            color: #7e7e7e;
            margin: 2px 0;
            padding: 0;
            font-weight: lighter;
        }
    </style>
</head>

<body style="margin: 0; padding: 0;background-color:#ebebeb;" class="mail">
    <table width="600" border="0" cellpadding="0" cellspacing="0" style="width: 600px; margin:0 auto;" align="center">
        <tbody>
            <tr>
                <td width="600" height="25" bgcolor="#ebebeb">&nbsp;</td>
            </tr>
            <tr>
                <td width="600" height="52" bgcolor="#ebebeb" align="center">
                    <p style="text-align:center;margin:0;">
                        <a href="http://www.groomit.me/" style="display:inline-block;text-align:center;margin:0 auto;" target="_blank"> <img src="{{ url('/') . '/images/email/groomit-pet-grooming.png' }}" alt="Groomit" border="0" /> </a>
                    </p>
                </td>
            </tr>
            <tr>
                <td width="600" height="14" bgcolor="#ebebeb">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <!-- BODY -->
    <table bgcolor="#fff" width="600" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;" align="center">
        <tbody>
            <tr>
                <td width="600" height="45">&nbsp;</td>
            </tr>
            <!-- VOUCHER CODE -->
            <tr>
                <td width="600">
                    <table width="600" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="117">&nbsp;</td>
                            <td width="366">
                                <h3 style="text-align:center;color:#000000;font-size:18px;font-weight:700;font-family: 'Open Sans', Helvetica, Arial, sans-serif;">Thank you for your order</h3>
                                <!--<img src="{!! url('/') . $data['image'] !!}" width="366" height="197" alt="Gift Card" border="0" />-->
                                <img src="{!! url('/') . $data['image'] !!}" width="340" height="183" alt="Membership" border="0" style="margin: 0 auto;" />
                                <p style="text-align:center;color:#000000;font-size:20px;margin:30px 0 0 0;font-family: 'Open Sans', Helvetica, Arial, sans-serif;line-height:120%;">YOUR GROOMIT <br /> CODE IS: <strong>{!! $data['promo_code'] !!}</strong></p>
                            </td>
                            <td width="117">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="600" height="32">&nbsp;</td>
            </tr>
            <!-- HOW IT WORKS -->
            <tr>
                <td width="600">
                    <table width="600" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="26">&nbsp;</td>
                            <td width="548">
                                <h3 style="text-align:center;color:#000000;font-size:15px;font-family: 'Open Sans', Helvetica, Arial, sans-serif;font-weight: 700;margin:0;margin-bottom:25px;">How it works</h3>
                            </td>
                            <td width="26">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="26">&nbsp;</td>
                            <td width="548">
                                <table width="548" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="178" height="40" valign="top" align="center">
                                            <p style="text-align:center;"><img src="{{ url('/') . '/images/email/voucher-select.png' }}" width="21" height="32" alt="Gift Card" border="0" align="top" style="text-align:center;margin:0 auto;" /></p>
                                        </td>
                                        <td width="6">&nbsp;</td>
                                        <td width="179" height="40" valign="top" align="center">
                                            <p style="text-align:center;"><img src="{{ url('/') . '/images/email/voucher-email.png' }}" width="28" height="28" alt="Gift Card" border="0" align="top" style="text-align:center;margin:0 auto;" /></p>
                                        </td>
                                        <td width="6">&nbsp;</td>
                                        <td width="178" height="40" valign="top" align="center">
                                            <p style="text-align:center;"><img src="{{ url('/') . '/images/email/voucher-service.png' }}" width="51" height="24" alt="Gift Card" border="0" align="top" style="text-align:center;margin:0 auto;" /></p>
                                        </td>

                                    </tr>
                                </table>
                            </td>
                            <td width="26">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="26">&nbsp;</td>
                            <td width="548">
                                <table width="548" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="178" valign="top">
                                            <p style="text-align:center;color:#000000;font-size:14px;font-family: 'Open Sans', Helvetica, Arial, sans-serif;line-height: 110%;">
                                            Select the preferred<br/> membership</p>
                                        </td>
                                        <td width="6">&nbsp;</td>
                                        <td width="179" valign="top">
                                            <p style="text-align:center;color:#000000;font-size:14px;font-family: 'Open Sans', Helvetica, Arial, sans-serif;line-height: 110%;">
                                            We Email <br/>your code</p>
                                        </td>
                                        <td width="6">&nbsp;</td>
                                        <td width="178" valign="top">
                                            <p style="text-align:center;color:#000000;font-size:14px;font-family: 'Open Sans', Helvetica, Arial, sans-serif;line-height: 110%;">
                                            Schedule a <br/>Groomit Appointment</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td width="26">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="600" height="58">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <table width="600" border="0" cellpadding="0" cellspacing="0" style="width: 600px; margin:0 auto;" align="center">
        <tbody>
            <tr>
                <td width="600" height="14" bgcolor="#ebebeb">&nbsp;</td>
            </tr>
            <tr>
                <td width="600"><img src="{{ url('/') . '/images/email/review.png' }}" alt="Groomit Review" width="600" border="0" /></td>
            </tr>
        </tbody>
    </table>
    <table width="600" border="0" cellpadding="0" cellspacing="0" style="width: 600px; margin:0 auto;" align="center" bgcolor="#fff">
        <tbody>
            <tr>
                <td width="600" height="45" bgcolor="#fff">&nbsp;</td>
            </tr>
            <tr>
                <td width="600" bgcolor="#fff">
                    <table width="600" border="0" cellpadding="0" cellspacing="0" bgcolor="#fff">
                        <tbody>
                            <tr>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                                <td width="140" bgcolor="#fff" align="center"><img src="{{ url('/') . '/images/email/cert-groomers.png' }}" style="margin:0 auto;" alt="Groomit" /></td>
                                <td width="20" bgcolor="#fff">&nbsp;</td>
                                <td width="290" bgcolor="#fff" style="vertical-align:middle;">
                                    <h3 style="text-align:left;color:#000000;font-size:16px;font-family: Open Sans, Helvetica, Arial, sans-serif;font-weight:700;margin: 0; margin-bottom: 10px; line-height: 120%;">Certified Groomers</h3>
                                    <p style="line-height: 130%;text-align:left;color:#191826;font-size:13px;margin:0; margin-bottom:10px;font-family: Open Sans, Helvetica, Arial, sans-serif;">Groomit app connects certified pet groomers with pet owners. Groomit runs extensive background checks on all groomers.</p>
                                </td>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="600" height="25" bgcolor="#fff">&nbsp;</td>
            </tr>
            <tr>
                <td width="600">
                    <table width="600" border="0" cellpadding="0" cellspacing="0" bgcolor="#fff">
                        <tbody>
                            <tr>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                                <td width="140" bgcolor="#fff" align="center"><img src="{{ url('/') . '/images/email/stress-free.png' }}" alt="Groomit" /></td>
                                <td width="20" bgcolor="#fff">&nbsp;</td>
                                <td width="290" bgcolor="#fff" style="vertical-align:middle;">
                                    <h3 style="text-align:left;color:#000000;font-size:16px;font-family: Open Sans, Helvetica, Arial, sans-serif;font-weight:700;margin: 0; margin-bottom: 10px; line-height: 120%;">Convenient &amp; Stress Free</h3>
                                    <p style="line-height: 130%;text-align:left;color:#191826;font-size:13px;margin:0; margin-bottom:10px;font-family: Open Sans, Helvetica, Arial, sans-serif;">Getting an in-home pet grooming service is convenient, safe and offers the least amount of stress in a familiar environment.</p>
                                </td>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="600" height="25" bgcolor="#fff">&nbsp;</td>
            </tr>
            <tr>
                <td width="600">
                    <table width="600" border="0" cellpadding="0" cellspacing="0" bgcolor="#fff">
                        <tbody>
                            <tr>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                                <td width="140" bgcolor="#fff" align="center"><img src="{{ url('/') . '/images/email/insurance.png' }}" alt="Groomit" /></td>
                                <td width="20" bgcolor="#fff">&nbsp;</td>
                                <td width="290" bgcolor="#fff" style="vertical-align:middle;">
                                    <h3 style="text-align:left;color:#000000;font-size:16px;font-family: Open Sans, Helvetica, Arial, sans-serif;font-weight:700;margin: 0; margin-bottom: 10px; line-height: 120%;">Premium Insurance</h3>
                                    <p style="line-height: 130%;text-align:left;color:#191826;font-size:13px;margin:0; margin-bottom:10px;font-family: Open Sans, Helvetica, Arial, sans-serif;">Every booking made through our site or app are bonded and covered by premium insurance. Safety is our priority.</p>
                                </td>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="600" height="50">&nbsp;</td>
            </tr>
            <tr>
                <td width="600">
                    <table width="600" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="180">&nbsp;</td>
                            <td width="240" bgcolor="#a2262d" style="padding: 8px 18px 8px 18px; border-radius:25px" align="center"><a href="https://www.groomit.me/user/" target="_blank" style="font-size: 14px; font-family: Open Sans, Helvetica, Arial, sans-serif; font-weight: normal; color: #ffffff; text-decoration: none; display: inline-block;">SCHEDULE APPOINTMENT</a></td>
                            <td width="180">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="600" height="50" bgcolor="#fff">&nbsp;</td>
            </tr>
            <tr>
                <td width="600" height="14" bgcolor="#ebebeb">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <table bgcolor="#ebebeb" width="600" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;" align="center">
        <tbody>
            <tr>
                <td width="600" height="20" bgcolor="#ebebeb">&nbsp;</td>
            </tr>
            <tr>
                <td width="600" bgcolor="#ebebeb" align="center">
                    <p style="text-align:center;"><img src="{{ url('/') . '/images/email/thankyou_12.jpg' }}" alt="Groomit" width="63" border="0" style="margin: 0 auto;display:block;" /></p>
                </td>
            </tr>
            <tr>
                <td width="600" height="5">&nbsp;</td>
            </tr>
            <tr>
                <td width="600" bgcolor="#ebebeb" align="center">
                    <h5 style="text-align:center;">© 2020 Groomit. All Rights Reserved.</h5>
                    <h5 style="text-align:center;">Questions or to <a target="_blank" href="#" style="color:#2e9fd9;">unsubscribe</a>, contact <a target="_blank" href="mailto:help@groomit.me" style="color:#2e9fd9;">help@groomit.me</a></h5>
                    <h5 style="text-align:center;">1091 Yonkers Ave.</h5>
                    <h5 style="text-align:center;"> Yonkers, NY 10704</h5>
                </td>
            </tr>
            <tr>
                <td width="600" height="15" bgcolor="#ebebeb">&nbsp;</td>
            </tr>
            <tr>
                <td width="600" height="55" bgcolor="#ebebeb">&nbsp;</td>
            </tr>
        </tbody>
    </table>
</body>

</html>