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
        
        html, body{
    height:100%;
    width:100%;            background-color: #ebebeb;

    padding:0;
    margin:0;
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

<body style="margin: 0; padding: 0;background-color:#ebebeb; max-width:100%;" class="mail">
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
            <tr width="600" style="background:#ebebeb;">
                <td>
                    <img src="{{ url('/') . '/images/email/spooky/header.png' }}" width="600" alt="Spa Day" border="0" style="margin: 0 auto;" />
                </td>
            </tr>
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
                                <h3 style="text-align:center; color:black; font-size:20px; margin: 10px 0 0 0; line-height:26px;font-weight: 700;">
                                    WELCOME TO GROOMIT!</h3>

                                <p style="text-align:center;color: black;font-size:14px;margin: 30px 0 10px 0; line-height: 26px;">
                                    <strong>Dear {!! $data['name'] !!}, you have $20 Gift Card from Groomit!</strong>
                                </p>
                            </td>
                            <td width="117">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="200">&nbsp;</td>
                            <td>
                                <img src="{{ url('/') . '/images/email/spooky/gift-card.jpg' }}" width="400" alt="Spa Day" border="0" style="margin: 0 auto;" />
                            </td>
                            <td width="200">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="117">&nbsp;</td>
                            <td width="366">
                                <p style="text-align:center;color: black;font-size:14px;margin: 15px 0 0 0; line-height: 26px;">
                                    <strong>YOUR GROOMIT GIFT CARD CODE IS</strong>
                                </p>
                                <h3 style="text-align:center; color:black; font-size:20px; margin: 0 0 40px 0; line-height:26px;font-weight: 500;">
                                    {!! $data['promo_code'] !!}
                                </h3>
                            </td>
                            <td width="117">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="180">&nbsp;</td>
                            <td width="240" bgcolor="#a2262d" style="padding: 8px 18px 8px 18px; border-radius:25px" align="center"><a href="https://www.groomit.me/user/" target="_blank" style="font-size: 14px; font-family: Open Sans, Helvetica, Arial, sans-serif; font-weight: normal; color: #ffffff; text-decoration: none; display: inline-block;">SCHEDULE APPOINTMENT</a></td>
                            <td width="180">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="600" height="58">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <!-- BODY -->
    <table bgcolor="#fff" width="600" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;" align="center">
        <tbody>
            <tr width="600" style="background:#ebebeb;">
                <td>&nbsp;
                </td>
            </tr>
        </tbody>
    </table>


    <!-- BODY -->
    <table bgcolor="#fff" width="600" border="0" cellpadding="0" cellspacing="0" style="border-radius:10px 10px 0 0; margin: 0 auto;" align="center">
        <tbody>
            <tr>
                <td width="600" height="25">&nbsp;</td>
            </tr>
            <tr>
                <td width="600">
                    <table width="600" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="117">&nbsp;</td>
                            <td width="366">
                                <h3 style="text-align:center; color:#9c2429; font-size:24px; margin: 10px 0 50px 0; line-height:26px;font-weight: 300;">
                                    HOW IT WORKS</h3>
                            </td>
                            <td width="117">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="20">&nbsp;</td>
                            <td width="180">
                                <img src="{{ url('/') . '/images/email/spooky/hw-1.jpg' }}" width="140" alt="Download" border="0" style="margin: 0 auto;" />
                            </td>
                            <td width="20">&nbsp;</td>
                            <td width="180">
                                <img src="{{ url('/') . '/images/email/spooky/hw-2.jpg' }}" width="140" alt="Download" border="0" style="margin: 0 auto;" />
                            </td>
                            <td width="20">&nbsp;</td>
                            <td width="180">
                                <img src="{{ url('/') . '/images/email/spooky/hw-3.jpg' }}" width="140" alt="Download" border="0" style="margin: 0 auto;" />
                            </td>
                            <td width="20">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="20">&nbsp;</td>
                            <td width="180">
                                <p style="font-size: 13px; font-family: Open Sans, Helvetica, Arial, sans-serif; font-weight: 400; color: #191826; text-decoration: none; text-align:center;">
                                    <a href="https://www.groomit.me/" target="_blank" style="font-size: 13px; font-family: Open Sans, Helvetica, Arial, sans-serif; font-weight: normal; color: #9c2429; text-decoration: none; display: inline-block;">Download</a> our App or go to <a href="https://www.groomit.me/" target="_blank" style="font-size: 13px; font-family: Open Sans, Helvetica, Arial, sans-serif; font-weight: normal; color: #9c2429; text-decoration: none; display: inline-block;">www.groomit.me</a>
                                </p>
                            </td>
                            <td width="20">&nbsp;</td>
                            <td width="180">
                                <p style="font-size: 13px; font-family: Open Sans, Helvetica, Arial, sans-serif; font-weight: 400; color: #191826; text-decoration: none; text-align:center;">
                                Select the Groomit<br>service you preffer
                                </p>                            
                            </td>
                            <td width="20">&nbsp;</td>
                            <td width="180">
                                <p style="font-size: 13px; font-family: Open Sans, Helvetica, Arial, sans-serif; font-weight: 400; color: #191826; text-decoration: none; text-align:center;">
                                Redeem code<br>at the checkout
                                </p>                           
                            </td>
                            <td width="20">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="600" height="32">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    
    <!-- BODY -->
    <table bgcolor="#fff" width="600" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;" align="center">
        <tbody>
            <tr width="600" style="background:#ebebeb;">
                <td>&nbsp;
                </td>
            </tr>
        </tbody>
    </table>

    <!-- BODY -->
    <table bgcolor="#fff" width="600" border="0" cellpadding="0" cellspacing="0" style="border-radius:10px 10px 0 0; margin: 0 auto;" align="center">
        <tbody>
            <tr>
                <td width="600" height="25">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <table width="600" border="0" cellpadding="0" cellspacing="0" style="width: 600px; margin:0 auto;" align="center" bgcolor="#fff">
        <tbody>
            <tr>
                <td width="600" bgcolor="#fff">
                    <table width="600" border="0" cellpadding="0" cellspacing="0" bgcolor="#fff">
                        <tbody>
                            <tr>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                                <td width="140" bgcolor="#fff" align="center">
                                &nbsp;                                </td>
                                <td width="20" bgcolor="#fff">&nbsp;</td>
                                <td width="290" bgcolor="#fff" style="vertical-align:middle;">
                                <h3 style="text-align:left; color:#9c2429; font-size:24px; margin: 10px 0 40px 0; line-height:26px;font-weight: 300;">
                                WHY GROOMT?</h3>                                </td>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>


            <tr>
                <td width="600" bgcolor="#fff">
                    <table width="600" border="0" cellpadding="0" cellspacing="0" bgcolor="#fff">
                        <tbody>
                            <tr>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                                <td width="140" bgcolor="#fff" align="center"><img src="{{ url('/') . '/images/email/spooky/w-1.jpg' }}" style="margin:0 auto;" alt="Groomit" /></td>
                                <td width="20" bgcolor="#fff">&nbsp;</td>
                                <td width="290" bgcolor="#fff" style="vertical-align:middle;">
                                    <h3 style="text-align:left;color:#000000;font-size:16px;font-family: Open Sans, Helvetica, Arial, sans-serif;font-weight:700;margin: 0; margin-bottom: 10px; line-height: 120%;">Convenient &amp; Stress Free</h3>
                                    <p style="line-height: 130%;text-align:left;color:#191826;font-size:13px;margin:0; margin-bottom:10px;font-family: Open Sans, Helvetica, Arial, sans-serif;">Getting an in-home pet grooming service is convenient and safe and since your pet is in a familiar environment, it's less stressful. We make sure to clean after every grooming.</p>
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
                                <td width="140" bgcolor="#fff" align="center"><img src="{{ url('/') . '/images/email/spooky/w-2.jpg' }}" style="margin:0 auto;" alt="Groomit" /></td>
                                <td width="20" bgcolor="#fff">&nbsp;</td>
                                <td width="290" bgcolor="#fff" style="vertical-align:middle;">
                                    <h3 style="text-align:left;color:#000000;font-size:16px;font-family: Open Sans, Helvetica, Arial, sans-serif;font-weight:700;margin: 0; margin-bottom: 10px; line-height: 120%;">Premium Insurance</h3>
                                    <p style="line-height: 130%;text-align:left;color:#191826;font-size:13px;margin:0; margin-bottom:10px;font-family: Open Sans, Helvetica, Arial, sans-serif;">Every booking made through our site or app is bonded and covered by premium insurance. Safety is our priority.</p>
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
                                <td width="140" bgcolor="#fff" align="center"><img src="{{ url('/') . '/images/email/spooky/w-3.jpg' }}" style="margin:0 auto;" alt="Groomit" /></td>
                                <td width="20" bgcolor="#fff">&nbsp;</td>
                                <td width="290" bgcolor="#fff" style="vertical-align:middle;">
                                    <h3 style="text-align:left;color:#000000;font-size:16px;font-family: Open Sans, Helvetica, Arial, sans-serif;font-weight:700;margin: 0; margin-bottom: 10px; line-height: 120%;">Certified Groomers</h3>
                                    <p style="line-height: 130%;text-align:left;color:#191826;font-size:13px;margin:0; margin-bottom:10px;font-family: Open Sans, Helvetica, Arial, sans-serif;">The Groomit app connects certified pet groomers with pet owners. Groomit runs an extensive background check on all groomers.</p>
                                </td>
                                <td width="75" bgcolor="#fff">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>           
            <tr>
                <td width="600" height="40" bgcolor="#fff">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <table bgcolor="#ebebeb" width="600" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;" align="center">
        <tbody>
            <tr>
                <td width="600" height="30" bgcolor="#ebebeb">&nbsp;</td>
            </tr>
            <tr>
                <td width="600" bgcolor="#ebebeb" align="center">
                    <p style="text-align:center;margin:0;"><img src="{{ url('/') . '/images/email/thankyou_12.jpg' }}" alt="Groomit" width="63" border="0" style="margin: 0 auto;display:block;" /></p>
                </td>
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