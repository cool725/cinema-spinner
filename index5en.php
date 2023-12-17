<?php
header('Content-type: text/html; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';

const ERROR_API = "Error during API call\n";
const ERROR_FILE = "The specified file does not exist\n";
const URL = "https://api.smsmode.com/http/1.6/";
const PATH_SEND_SMS = "sendSMS.do";
const PATH_SEND_SMS_BATCH = "sendSMSBatch.do";

function getBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Inconnue';
    $platform = 'Inconnue';

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera';
        $ub = "Opera";
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    return array(
        'userAgent' => $u_agent,
        'name' => $ub,
        'platform' => $platform,
    );
}

if (!defined('IMAGETYPE_WEBP')) {
    define('IMAGETYPE_WEBP', 18);
}

$ipaddress = 'Inconnue';
if (isset($_SERVER['REMOTE_ADDR'])) {
    $ipaddress = $_SERVER['REMOTE_ADDR'];
} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
    $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
} else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
    $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
} else if (isset($_SERVER['HTTP_FORWARDED'])) {
    $ipaddress = $_SERVER['HTTP_FORWARDED'];
} else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
}

file_put_contents('Mosaiques/Mos' . $ipaddress . '.txt', '');
file_put_contents('Mosaiques/ID' . $ipaddress . '.txt', '');

$ua = getBrowser();
$myBrowser = $ua['name'];
$myPlatform = $ua['platform'];
$useragent = strtolower($ua['u_agent']);
$withSMS = 0;
$myMobile = 'Station';
if (strpos($useragent, "iphone") !== false) {
    $myMobile = 'iPhone';
}
if (strpos($useragent, "android") !== false) {
    $myMobile = 'Android';
}
if (strpos($useragent, "iPad") !== false) {
    $myMobile = 'iPad';
}

$pathMosaic = file_get_contents('Mosaiques/Mos' . $ipaddress . '.txt');
$pathMosaic2 = str_replace('Mockup', 'Mosaic', $pathMosaic);
// var_dump($pathMosaic);
$emetteurAvis = file_get_contents('Mosaiques/ID' . $ipaddress . '.txt');
$placeholderAvis = "Your opinion on our service";

if (isset($_POST["yourEmail"])) {
    $target_dir = "/var/www/cgi-bin/Images/";
    $target_file = $target_dir . ($_FILES["fileToUpload"]["tmp_name"]);
    $uploadOk = 1;

    $fileName = date('Ymd_His');
    $rand = (string) rand(0, 999);

    $fileName = 'Photo_' . $fileName . '_' . $rand . '.png';

    $target_file = $target_dir . $fileName; //echo $target_file;

    $iswebp = 0;
    if (imagecreatefromwebp($filename)) {
        $iswebp = 1;
    }
    //echo "Etape 1";
    $isjpeg = 0;
    if (imagecreatefromjpeg($filename)) {
        $isjpeg = 1;
    }
    //echo "Etape 2";
    //$pathMosaic =$imageFileType;
    $uploadOk = 0;
    //foreach ($_POST as $key => $value)
    //        echo $key.'='.$value.'<br />';

    //echo $pathMosaic;

    //var_dump($_POST);
    // echo "Post OK";
    $prospectName = "Empty";
    $prospectEmail = "Empty";
    $prospectTel = "Empty";
    $prospectVille = "Empty";
    $prospectLargeur = "Empty";
    $prospectHauteur = "Empty";

    if (!empty($_POST["yourName"])) {
        $prospectName = str_replace(' ', '', $_POST["yourName"]);
    }
    if (!empty($_POST["yourEmail"])) {
        $prospectEmail = str_replace(' ', '', $_POST["yourEmail"]);
    }
    if (!empty($_POST["yourPhone"])) {
        $prospectTel = str_replace(' ', '', $_POST["yourPhone"]);
    }
    if (!empty($_POST["yourVille"])) {
        $prospectVille = str_replace(' ', '', $_POST["yourVille"]);
    }
    if (!empty($_POST["yourLargeur"])) {
        $prospectLargeur = str_replace(' ', '', $_POST["yourLargeur"]);
    }
    if (!empty($_POST["yourHauteur"])) {
        $prospectHauteur = str_replace(' ', '', $_POST["yourHauteur"]);
    }

    //echo $prospectName."-";
    //echo $prospectEmail."-";
    //echo $prospectTel."-";
    //echo $prospectVille."-";

    $mosaicStyle = "1";
    if (isset($_POST['GroupMosaicstyle'])) {
        switch ($_POST['GroupMosaicstyle']) {
            case 'st1':
                $mosaicStyle = "1";
                break;
            case 'st2':
                $mosaicStyle = "2";
                break;
            case 'st3':
                $mosaicStyle = "3";
                break;
            case 'st4':
                $mosaicStyle = "4";
                break;
            default:
                $mosaicStyle = "1";
                break;
        }
    }
    //    echo $mosaicStyle;

    $yourProject = "1";
    if (isset($_POST['GroupVotreprojet'])) {
        switch ($_POST['GroupVotreprojet']) {
            case 'pr1':
                $yourProject = "1";
                break;
            case 'pr2':
                $yourProject = "2";
                break;
            case 'pr3':
                $yourProject = "3";
                break;
            case 'pr4':
                $yourProject = "4";
                break;
            case 'pr5':
                $yourProject = "5";
                break;
            case 'pr6':
                $yourProject = "6";
                break;
            default:
                $yourProject = "1";
                break;
        }
    }

    $yourPosition = "Mur";
    if (isset($_POST['GroupPosition'])) {
        switch ($_POST['GroupPosition']) {
            case 'po1':
                $yourPosition = "Sol";
                break;
            case 'po2':
                $yourPosition = "Mur";
                break;
        }
    }
    //    echo $yourProject;
    $colorJoint = "4"; // Valeur par défaut
    if (isset($_POST['groutColorJoint'])) {
        $colorJoint = $_POST['groutColorJoint'];
    }
    $lang_flag = "en";
    //    echo $colorJoint;
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    $image_type = $check[2];

    // echo '<br /><br /><br />TypeT'.$image_type.'T';

    if (($isjpeg == 1) or ($iswebp == 1) or (in_array($image_type, array(IMAGETYPE_JPEG, 2, 18, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM, IMAGETYPE_JP2)))) {
        //       echo 'Step1';
        if ($check !== false) {
            //echo 'Step2';
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                //    echo 'Step3';
                $bytes = random_bytes(26);
                $vstr = bin2hex($bytes);
                $randomName = 'Mos' . $ipaddress . '.txt';
                file_put_contents('Mosaiques/' . $randomName, ''); // Vide
                //    echo $randomName;
                $target_dir1 = "/var/www/cgi-bin/Images/" . $fileName;
                copy("$target_file", $target_dir1);
                //            $res = system('/var/www/cgi-bin/qWebAppMagicMosaic -platform offscreen '.$target_dir1.' '.$randomName.' '.$mosaicStyle.' '.$yourProject.' '.$myMobile.' '.$myPlatform.' '.$myBrowser.' '.$ipaddress);
                $res = system('/var/www/cgi-bin/qWebAppMagicMosaic2 -platform offscreen ' . $target_dir1 . ' ' . $randomName . ' ' . $mosaicStyle . ' ' . $yourProject . ' ' . $myMobile . ' ' . $myPlatform . ' ' . $myBrowser . ' ' . $ipaddress . ' ' . $prospectName . ' ' . $prospectEmail . ' ' . $prospectTel . ' ' . $prospectVille . ' ' . $colorJoint . ' ' . $lang_flag . ' ' . $prospectLargeur . ' ' . $prospectHauteur . ' ' . $yourPosition);
                $pathMosaic = file_get_contents('Mosaiques/' . $randomName);
                $pathMosaic2 = str_replace('Mockup', 'Mosaic', $pathMosaic);
                $pieces = explode("_", $pathMosaic);
                // $sourceImg = "Mosaiques/Image_" . $pieces[1] . "_" . $pieces[2] . "_" . $pieces[3] . ".png";
                // var_dump($pathMosaic);
                $infoID = $prospectName . '  ' . $prospectEmail . '  ' . $prospectTel . '  ' . $prospectVille;
                file_put_contents('Mosaiques/ID' . $ipaddress . '.txt', ''); // Vide
                file_put_contents('Mosaiques/ID' . $ipaddress . '.txt', $infoID);

                //echo $pathMosaic;
                //   $pathMosaic = $res;
                $uploadOk = 1;
            }
            if ($withSMS == 1) {
                $accessToken = "cx1ErpN1P2bjFQ9BeE8OVf44F4BYeMG3";
                $message = "[Magic Mosaic] Nouveau prospect : " . $infoID;
                $destinataires = "0767993269";
                $emetteur = "";
                $optionStop = "";

                $message = iconv("UTF-8", "ISO-8859-15", $message);
                $fields_string = 'accessToken=' . $accessToken . '&message=' . urlencode($message) . '&numero=' . $destinataires . '&emetteur=' . $emetteur . '&stop=' . $optionStop;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, URL . PATH_SEND_SMS);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                $result = curl_exec($ch);
                curl_close($ch);
            }
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }
}
if (isset($_POST["submitAvis"])) {
    //    echo "Post OK";
    $texteAvis = "Empty";
    if (!empty($_POST["monAvis"])) {
        $texteAvis = $_POST["monAvis"];
    }
    $emetteurAvis = file_get_contents('Mosaiques/ID' . $ipaddress . '.txt');
    $placeholderAvis = "Thank you for your opinion";

    $mail = new PHPMailer(false); // Passing `true` enables exceptions
    $mail->CharSet = "UTF-8";
    //Server settings
    //    $mail->isSMTP();                                      // Set mailer to use SMTP 6 A ENLEVER POUR GMAIL
    $mail->SMTPDebug = 0; // Enable verbose debug output
    $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
    $mail->Port = 587; // TCP port to connect to
    $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'mosaique.paris1@gmail.com'; // SMTP username
    $mail->Password = 'yKfMmXDRad6CwvVF'; // SMTP password
    //Recipients
    $mail->setFrom('mosaique.paris1@gmail.com', 'Mosaikoo');
    $mail->addAddress('cjacquelin@magicmosaic.net'); // Add a recipient
    //    $mail->addAddress('ellen@example.com');               // Name is optional
    //    $mail->addReplyTo('info@example.com', 'Information');
    //    $mail->addCC('cc@example.com');
    //    $mail->addBCC('bcc@example.com');

    //Attachments
    $mail->SMTPDebug = 0;
    //Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = "[Mosaikoo] Nouvel AVIS client English";
    $mail->Body = "Emetteur : " . $emetteurAvis . "; L'Avis : " . $texteAvis;

    //send the message, check for errors
    if (!$mail->send()) {
        //         header('Location: index2.php?lang_flag='.$lang_flag);
    } else {
        //       header('Location: index2.php?lang_flag='.$lang_flag);
    }
    if ($withSMS == 1) {
        $accessToken = "cx1ErpN1P2bjFQ9BeE8OVf44F4BYeMG3";
        $message = "[Magic Mosaic] Nouvel AVIS client English: Emetteur : " . $emetteurAvis . "; L'Avis : " . $texteAvis;
        $destinataires = "0767993269";
        $emetteur = "";
        $optionStop = "";

        $message = iconv("UTF-8", "ISO-8859-15", $message);
        $fields_string = 'accessToken=' . $accessToken . '&message=' . urlencode($message) . '&numero=' . $destinataires . '&emetteur=' . $emetteur . '&stop=' . $optionStop;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, URL . PATH_SEND_SMS);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
if (!isset($_POST["yourEmail"])) {
    echo '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>MagicMosaic</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&amp;display=swap">
    <link rel="stylesheet" href="assets/css/Drag--Drop-Upload-Form.css">
    <link rel="stylesheet" href="assets/css/styles.css">
<title>Mosaikoo: Simulateur en ligne pour obtenir votre mosaïque artistique à partir de vos photos</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<meta name="googlebot" content="noindex">
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-78472730-2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag("js", new Date());
   gtag("config", "UA-78472730-2");
</script>
<style>
.form-control::placeholder {
  color: var(--bs-black-rgbgray-600);
}
.errorEle {
  display: none;
  font-size: 0.8em;
}

.errorEle.visible {
  display: block;
  color: var(--bs-gray-400);
}

input.invalid {
  border-color: white !important;
}
#spinner-html #img_show-container {
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    overflow: hidden;
    background-image: url("./square.png"); /* Add your film reel background image */
    background-size: cover;
    position: relative;
  }
  
  #spinner-html #img_show-container::before,
  #spinner-html #img_show-container::after {
    content: "";
    position: absolute;
    background-color: black;
  }
  
  #spinner-html #img_show-container::before {
    width: 100%;
    height: 2px;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
  }
  
  #spinner-html #img_show-container::after {
    height: 100%;
    width: 2px;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
  }
  
  #spinner-html .film-border {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 250px;
    height: 250px;
    border: 2px solid #fff;
    border-radius: 50%;
    z-index: 1;
  }
  
  #spinner-html .film-container {
    position: relative;
    color: black;
    font-size: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 200px;
    height: 200px;
    border: 2px solid #fff;
    border-radius: 50%;
    z-index: 2;
  }
  
  #spinner-html .film-reel {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0.6;
    z-index: -1;
    /* Adding x and y axes lines */
    &::before,
    &::after {
      content: "";
      position: absolute;
      background-color: black;
    }
  
    &::before {
      width: 100%;
      height: 2px;
      top: 50%;
      left: 0;
      transform: translateY(-50%);
    }
  
    &::after {
      height: 100%;
      width: 2px;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
    }
  }
  
  @keyframes countdownAnimation {
    0% {
      opacity: 0;
    }
    20% {
      opacity: 1;
    }
    80% {
      opacity: 1;
    }
    100% {
      opacity: 0;
    }
  }
</style>
</head>

<body style="background-color: var(--bs-gray-200);color: var(--bs-black-rgb);">
    <div class="container" id="main-container">
        <div class="row" style="border-color: var(--bs-secondary);border-top-color: rgb(33,;border-right-color: 37,;border-bottom-color: 41);border-left-color: 37,;">
            <div class="col-xxl-3" id="toobox">
                <form class="text-dark" method="POST" action="index4en.php" enctype="multipart/form-data">
				<label class="form-label" style="color: var(--bs-dark-rgb);"><strong>Upload your photo:</strong>&nbsp;</label>
				<input class="form-control" type="file" id="fileToUpload" style="background: var(--bs-gray-400);color: var(--bs-black-rgb);width: 310px;" name="fileToUpload0" accept=".webp, .bmp, .jpg, .jpeg, .png, .tif, .tiff" data-input="false" required="true">
				<span class="errorEle" role="alert" id="fileError" aria-hidden="true">
					Please enter Image
				</span>
					<label class="form-label" style="color: var(--bs-dark-rgb);"><strong>Mosaic style:</strong></label>
                    <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="st1" name="GroupMosaicstyle" value="st1" checked="true">
					<label class="form-check-label" for="st1" style="color: var(--bs-dark-rgb);">Artistic</label></div>
                    <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="st2" name="GroupMosaicstyle" value="st2">
					<label class="form-check-label" for="st2" style="color: var(--bs-dark-rgb);">Modern</label></div>
                    <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="st3" name="GroupMosaicstyle" value="st3">
					<label class="form-check-label" for="st3" style="color: var(--bs-dark-rgb);">Blend</label></div><label class="form-label" style="color: var(--bs-black-rgb);">
                   <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="st4" name="GroupMosaicstyle" value="st4">
					<label class="form-check-label" for="st4" style="color: var(--bs-dark-rgb);">Printed</label></div>
				
					<label class="form-label" style="color: var(--bs-dark-rgb);"><strong>Your project:</strong>&nbsp;</label>
<!-- <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="pr1" name="GroupVotreprojet" value="pr1" checked="true">
					<label class="form-check-label" for="pr1" style="color: var(--bs-dark-rgb);">House decor</label></div>    
-->
                    <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="pr2" name="GroupVotreprojet" checked="true" value="pr2">
                    <label class="form-check-label" for="pr2" style="color: var(--bs-dark-rgb);">Bathroom</label></div>
                    <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="pr6" name="GroupVotreprojet" value="pr6">
                    <label class="form-check-label" for="pr6" style="color: var(--bs-dark-rgb);">Kitchen</label></div>
                    <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="pr3" name="GroupVotreprojet" value="pr3">
					<label class="form-check-label" for="pr3" style="color: var(--bs-dark-rgb);">Swimming pool</label></div>
                    <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="pr4" name="GroupVotreprojet" value="pr4">
					<label class="form-check-label" for="pr4" style="color: var(--bs-dark-rgb);">Business</label></div>
                    <div class="form-check" style="width: 200px;"><input class="form-check-input" type="radio" id="pr5" name="GroupVotreprojet" value="pr5">
					<label class="form-check-label" for="pr5" style="color: var(--bs-dark-rgb);">Urban space</label></div>

					<label class="form-label" id="nom" style="color: var(--bs-black-rgb);"><strong>Mosaic size:</strong></label>

					<input class="form-control" type="text" id="yourLargeur" style="width: 260px;background: var(--bs-gray-400);color: var(--bs-dark-rgb);border-color: var(--bs-secondary);" placeholder="Width (in meters)" name="yourLargeur">
					<span class="errorEle" role="alert" id="largeurError" aria-hidden="true">
						Please enter a width
					</span>
					<input class="form-control" type="text" id="yourHauteur" style="width: 260px;background: var(--bs-gray-400);color: var(--bs-dark-rgb);border-color: var(--bs-secondary);" placeholder="Height (in meters)" name="yourHauteur">
					<span class="errorEle" role="alert" id="hauteurError" aria-hidden="true">
						Please enter a height
					</span>
					
					
					<label class="form-label" id="position" style="color: var(--bs-black-rgb);"><strong>Location:</strong></label>
					</br>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="GroupPosition" id="po1" value="po1">
						<label class="form-check-label" for="po1">Ground</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="GroupPosition" id="po2" value="po2" checked="true">
						<label class="form-check-label" for="po2">Wall</label>
					</div>
					</br>
					
					<label class="form-label" id="nom" style="color: var(--bs-dark-rgb);"><strong>Your informations:</strong></label>

					<input class="form-control" type="text" id="yourName" style="width: 260px;background: var(--bs-gray-400);color: var(--bs-dark-rgb);border-color: var(--bs-secondary);" placeholder="Your name:" name="yourName">
					<span class="errorEle" role="alert" id="nameError" aria-hidden="true">
						Please enter Name
					</span>
					<input class="form-control" type="email" name="yourEmail" required="true" id="yourEmail" style="width: 260px;background: var(--bs-gray-400);border-color: var(--bs-secondary);border-top-color: rgb(33,;border-right-color: 37,;border-bottom-color: 41);border-left-color: 37,;color: var(--bs-dark-rgb);" required="true" placeholder="Your E-mail:">
					<span class="errorEle" role="alert" id="emailError" aria-hidden="true">
						Please enter Email
					</span>
					<input class="form-control" type="tel" id="yourPhone" required="true" style="width: 260px;background: var(--bs-gray-400);border-color: var(--bs-secondary);color: var(--bs-dark-rgb);" placeholder="Your telephone:" required="true" name="yourPhone">
					<span class="errorEle" role="alert" id="telephoneError" aria-hidden="true">
						Please enter Telephone
					</span>
					<input class="form-control" type="text" id="yourVille" style="width: 260px;background: var(--bs-gray-400);border-color: var(--bs-secondary);color: var(--bs-dark-rgb);" placeholder="Your town:" name="yourVille">
					<span class="errorEle" role="alert" id="yourVilleError" aria-hidden="true">
						Please enter Ville
					</span>

					<button class="btn btn-primary" id="creer" type="button" name="submit" onclick="validate()" >Create my mosaic</button>
                </form>
				<img src="" id="preview" style="display: none;">
				<img src="" id="output" style="display: none;">
            </div>
            <div class="col" id="img_show-container">
                <div id="img_show"></div>
                <div style="position: absolute; display: none;" id="canvas-container">
                    <canvas id="myCanvas" width="2000" height="2000"></canvas>
                </div>
                <div id="spinner" style="display: none;">
                    <div class="film-border">
                    <div class="film-container">
                        <div class="film-reel"></div>
                        <div class="countdown">20</div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container"></div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
	<script>
    $(document).ready(function () {

        $("#fileToUpload").change(function (evt) {

            var files = evt.target.files;
            var file = files[0];

            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById("preview").src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
	function validate() {
		const fileField = document.getElementById("fileToUpload");
		const nameField = document.getElementById("yourName");
		const emailField = document.getElementById("yourEmail");
		const phoneField = document.getElementById("yourPhone");
		const villeField = document.getElementById("yourVille");
		const hauteurField = document.getElementById("yourLargeur");
		const largeurField = document.getElementById("yourHauteur");
		let valid = true;
		let success = true;

		if (!fileField.value) {
		  success = false;
		  const fileError = document.getElementById("fileError");
		  fileError.classList.add("visible");
		  fileField.classList.add("invalid");
		  fileError.setAttribute("aria-hidden", false);
		  fileError.setAttribute("aria-invalid", true);
		}else{
		  const fileError = document.getElementById("fileError");
		  fileError.classList.remove("visible");
		  fileField.classList.remove("invalid");
		  fileError.setAttribute("aria-hidden", true);
		  fileError.setAttribute("aria-invalid", false);
		}
		if (!nameField.value) {
			success = false;
			const nameError = document.getElementById("nameError");
			nameError.classList.add("visible");
			nameField.classList.add("invalid");
			nameError.setAttribute("aria-hidden", false);
			nameError.setAttribute("aria-invalid", true);
		}else{
			const nameError = document.getElementById("nameError");
			nameError.classList.remove("visible");
			nameField.classList.remove("invalid");
			nameError.setAttribute("aria-hidden", true);
			nameError.setAttribute("aria-invalid", false);
		}
		if (!hauteurField.value) {
			success = false;
			const hauteurError = document.getElementById("hauteurError");
			hauteurError.classList.add("visible");
			hauteurField.classList.add("invalid");
			hauteurError.setAttribute("aria-hidden", false);
			hauteurError.setAttribute("aria-invalid", true);
		}else{
			const hauteurError = document.getElementById("hauteurError");
			hauteurError.classList.remove("visible");
			hauteurField.classList.remove("invalid");
			hauteurError.setAttribute("aria-hidden", true);
			hauteurError.setAttribute("aria-invalid", false);
		}

		if (!largeurField.value) {
			success = false;
			const largeurError = document.getElementById("largeurError");
			largeurError.classList.add("visible");
			largeurField.classList.add("invalid");
			largeurError.setAttribute("aria-hidden", false);
			largeurError.setAttribute("aria-invalid", true);
		}else{
			const largeurError = document.getElementById("largeurError");
			largeurError.classList.remove("visible");
			largeurField.classList.remove("invalid");
			largeurError.setAttribute("aria-hidden", true);
			largeurError.setAttribute("aria-invalid", false);
		}

		if (!emailField.value) {
			success = false;
			const emailError = document.getElementById("emailError");
			emailError.classList.add("visible");
			emailField.classList.add("invalid");
			emailError.setAttribute("aria-hidden", false);
			emailError.setAttribute("aria-invalid", true);
		}else{
			const emailError = document.getElementById("emailError");
			let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
			if(!emailField.value.match(mailformat))
			{
				success = false;
				emailError.innerText="You have entered an invalid email address!"
			}
			else{
				emailError.classList.remove("visible");
				emailField.classList.remove("invalid");
				emailError.setAttribute("aria-hidden", true);
				emailError.setAttribute("aria-invalid", false);
			}
		}
		if (!phoneField.value) {
			success = false;
			const telephoneError = document.getElementById("telephoneError");
			telephoneError.classList.add("visible");
			phoneField.classList.add("invalid");
			telephoneError.setAttribute("aria-hidden", false);
			telephoneError.setAttribute("aria-invalid", true);
		}else{
			const telephoneError = document.getElementById("telephoneError");
			let phoneno = /^[-+]?[0-9]+$/;
			if(!phoneField.value.match(phoneno))
			{
				success = false;
				telephoneError.innerText="Not a valid Phone Number!"
			}
			else{
				telephoneError.classList.remove("visible");
				phoneField.classList.remove("invalid");
				telephoneError.setAttribute("aria-hidden", true);
				telephoneError.setAttribute("aria-invalid", false);
			}
		}
		if (!villeField.value) {
			success = false;
			const villeError = document.getElementById("yourVilleError");
			villeError.classList.add("visible");
			villeField.classList.add("invalid");
			villeError.setAttribute("aria-hidden", false);
			villeError.setAttribute("aria-invalid", true);
		}else{
			const yourVilleError = document.getElementById("yourVilleError");
			yourVilleError.classList.remove("visible");
			villeField.classList.remove("invalid");
			yourVilleError.setAttribute("aria-hidden", true);
			yourVilleError.setAttribute("aria-invalid", false);
		}
		if (!success){
			return valid;
		}
		ResizeImage()
		return valid;
	}

    function ResizeImage() {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            var filesToUploads = document.getElementById("fileToUpload").files;
            var file = filesToUploads[0];
            if (file) {

                var reader = new FileReader();
                // Set the image once loaded into file reader
                reader.onload = function (e) {

                    var img = document.createElement("img");
                    img.src = e.target.result;

                    var canvas = document.createElement("canvas");
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0);
                    max_size = 1024,// TODO : pull max size from a site config
                        width = img.width,
                        height = img.height;
                    if (width > height) {
                        if (width > max_size) {
                            height *= max_size / width;
                            width = max_size;
                        }
                    } else {
                        if (height > max_size) {
                            width *= max_size / height;
                            height = max_size;
                        }
                    }
                    canvas.width = width;
                    canvas.height = height;
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0, width, height);

                    dataurl = canvas.toDataURL(file.type);
                    var resizedImage = dataURLToBlob(dataurl);
                    $.event.trigger({
                        type: "imageResized",
                        blob: resizedImage
                    });
                document.getElementById("output").src = dataurl;
                }
                reader.readAsDataURL(file);

            }

        } else {
            alert("The File APIs are not fully supported in this browser.");
        }
    }

    var dataURLToBlob = function (dataURL) {
        var BASE64_MARKER = ";base64,";
        if (dataURL.indexOf(BASE64_MARKER) == -1) {
            var parts = dataURL.split(",");
            var contentType = parts[0].split(":")[1];
            var raw = parts[1];

            return new Blob([raw], { type: contentType });
        }

        var parts = dataURL.split(BASE64_MARKER);
        var contentType = parts[0].split(":")[1];
        var raw = window.atob(parts[1]);
        var rawLength = raw.length;

        var uInt8Array = new Uint8Array(rawLength);

        for (var i = 0; i < rawLength; ++i) {
            uInt8Array[i] = raw.charCodeAt(i);
        }

        return new Blob([uInt8Array], { type: contentType });
    }
    /* End Utility function to convert a canvas to a BLOB      */

    $(document).on("imageResized", function (event) {
        var formElement = document.querySelector("form");
        var data = new FormData(formElement);
        if (event.blob) {
            data.append("fileToUpload", event.blob);
  
            
            if (document.querySelector(`input[name="GroupMosaicstyle"]:checked`).value === "st1") {
                // Show spinner before making the AJAX request
                $("#spinner").show();
                $("#canvas-container").show();
                $("html").attr("id", "spinner-html");
                
                startSpinner();
            } else {
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }
                
                $("#spinner").hide();
                $("#canvas-container").hide();
                $("html").removeAttr("id");
            }
          
            var t = data;
            $.ajax({
                url: "index4en.php",
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                type: "POST",
                success: function (data) {
                    // Hide spinner after receiving the response
                    
                    $("#spinner").hide();
                    $("#canvas-container").hide();
                    $("html").removeAttr("id");

                    document.getElementById("img_show").innerHTML = data
                },
                error: function () {
                  // Hide spinner in case of an error
                    $("#spinner").hide();
                    $("#canvas-container").hide();
                    $("html").removeAttr("id");
                },
            });
        }
    });
    </script>

    <script>
        let countdownInterval;

        function startSpinner() {
            document.getElementById("img_show").innerHTML = "";
            $("#myCanvas").show();

            // Clear any existing interval
            clearInterval(countdownInterval);

            // You can use JavaScript to update the countdown number
            const countdownElement = document.querySelector(".countdown");
            const initialCountValue = 200;
            let countdownValue = initialCountValue;
    
            const c = document.getElementById("myCanvas");
            const ctx = c.getContext("2d");
            const canvasSize = 2000;
    
            const centerX = canvasSize / 2; // Adjust as needed
            const centerY = canvasSize / 2; // Adjust as needed
            const radius = canvasSize * Math.sqrt(2);
    
            countdownInterval = setInterval(() => {
            countdownElement.textContent = parseInt(countdownValue / 10);
    
            ctx.clearRect(0, 0, c.width, c.height); // Clear the canvas for a new arc
            ctx.beginPath();
    
            const startAngle = -Math.PI / 2; // Start angle (90 degrees)
            const endAngle =
                (Math.PI * 2 * (initialCountValue - countdownValue)) /
                initialCountValue +
                startAngle;
    
            // Move to the center of the canvas
            ctx.moveTo(centerX, centerY);
    
            // Draw a line to the starting point of the arc
            // ctx.lineTo(centerX + radius * Math.cos(startAngle), centerY + radius * Math.sin(startAngle));
            ctx.lineTo(
                centerX + radius * Math.cos(endAngle),
                centerY + radius * Math.sin(endAngle)
            );
    
            // Draw the arc
            ctx.arc(centerX, centerY, radius, endAngle, startAngle);
            // ctx.arc(centerX, centerY, radius, startAngle, endAngle);
    
            // Draw a line back to the center of the canvas
            ctx.lineTo(centerX, centerY);
    
            // Set the fill style to red
            ctx.fillStyle = "#ffffff44";
            ctx.strokeStyle = "#ffffff44";
    
            // Fill the sector with the chosen color
            ctx.fill();
    
            // Stroke the arc
            ctx.stroke();
    
            countdownValue--;
    
            if (countdownValue < 0) {
                clearInterval(countdownInterval);
                $("#myCanvas").hide();
            }
            }, 100);
        }
    </script>
</body>
</html>
';
} else {
    echo '
	<h1 class="text-center" style="color: var(--bs-black-rgb);">Your 100% personalized mosaic</h1>
	<div id="demo" class="carousel slide" data-bs-ride="carousel">
		<div class="carousel-indicators">
			<button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
			<button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
		</div>
		<div class="carousel-inner">
			<div class="carousel-item active">
				<img src="' . $pathMosaic . '" alt="image1" class="d-block w-100" style="box-shadow: 0px 0px;color: var(--bs-yellow);background: var(--bs-gray-100);">
				<div class="carousel-caption d-none d-md-block btn btn-primary" style="margin-left: auto; margin-right: auto; width: 150px; padding: 0">
				<h5>Mockup</h5>
				</div>
			</div>
			<div class="carousel-item">
				<img src="' . $pathMosaic2 . '" alt="image2" class="d-block w-100" style="box-shadow: 0px 0px;color: var(--bs-yellow);background: var(--bs-gray-100);">
				<div class="carousel-caption d-none d-md-block btn btn-primary" style="margin-left: auto; margin-right: auto; width: 150px; padding: 0">
				<h5>Mosaic</h5>
				</div>
			</div>
		</div>
		<button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
			<span class="carousel-control-prev-icon"></span>
		</button>
		<button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
			<span class="carousel-control-next-icon"></span>
		</button>
	</div>
	<form encytype="multipart/form-data" method="post" action="index4en.php">
		<input class="form-control" type="text" id="avis" name="monAvis" style="width: 100%;background: var(--bs-gray-400);color: var(--bs-black-rgb);border-color: var(--bs-secondary);" required="true" placeholder="' . $placeholderAvis . '">
		<button class="btn btn-primary" id="submitAvis" type="submit" name="submitAvis">Send my opinion</button>
	</form>
 ';
}
