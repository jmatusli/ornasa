<?php
// Inspired by tutorials: http://www.phpfreaks.com/tutorials/130/6.php
// http://www.vbulletin.com/forum/archive/index.php/t-113143.html
// http://hudzilla.org

// Create the mysql backup file
// edit this section
	$dbhost = "localhost"; // usually localhost
	$dbuser = "ornasa";
	$dbpass = "ornasa";
	$dbname = "ornasa";
// don't need to edit below this section

	$backupfile = "E:/xampp/htdocs/backup".$dbname . date("Y-m-d") . '.sql';
	$backupzip = "E:/xampp/htdocs/backup".$dbname . date("Y-m-d") . '.zip';

//echo "mysqldump -h $dbhost -u $dbuser -p$dbpass $dbname > $backupfile";
	echo "Producing backup file...\n";
	system("E:/xampp/mysql/bin/mysqldump -h $dbhost -u $dbuser -p$dbpass $dbname > $backupfile");
	echo "Producing zip file...\n";
	//echo "C:/Program Files/7-Zip/7z.exe a -tzip $backupzip $backupfile";
	system("\"C:/Program Files/7-Zip/7z.exe\" a -tzip $backupzip $backupfile");
	echo "Start sending mail...\n";
	
// include and start phpmailer
    require_once('PHPMailer_5.2.4/class.phpmailer.php');
    
	$mail = new PHPMailer();
	$mail->IsSMTP(); // send via SMTP
	$mail->SMTPAuth = true; // turn on SMTP authentication
	$mail->Username = "backupintersinaptico@gmail.com"; // Enter your SMTP username
	$mail->Password = "espEci@lm_nte_pr@_resp!"; // SMTP password
	$webmaster_email = "david.dionys@gmail.com" ; //Add reply-to email address
	$email="info@intersinaptico.com"; // Add recipients email address
	$name="Backup Webmaster"; // Add Your Recipient’s name
	$mail->From = $webmaster_email;
	$mail->FromName = "Webmaster";
	$mail->AddAddress($email,$name);
	$mail->AddReplyTo($webmaster_email,"Webmaster");
	$mail->WordWrap = "<strong>50</strong>"; // set word wrap
	$mail->AddAttachment($backupzip); // attachment
	$mail->IsHTML(true); // send as HTML
	$mail->Host = "smtp.gmail.com"; // sets the SMTP server
	$mail->Port = 465;                    // set the SMTP port for the GMAIL 
	$mail->SMTPSecure = 'ssl';
	 
	$mail->Subject = "Backup of Ornasa";
	 
	$mail->Body =      "Your daily database backup" ;      //HTML Body
	 
	$mail->AltBody = "Your daily database backup";     //Plain Text Body
	
	if(!$mail->Send()){
		echo "Mailer Error: " . $mail->ErrorInfo;
	} 
	else {
		echo "Message has been sent";
	}
	
// Delete the file from your server
	unlink($backupfile);
	unlink($backupzip);
?>
