<?php
$to = "dreamhoster@gmail.com";
$subject = "Test mail";
$message = "Hello! This is a simple test email message.";
$from = "test@dreamhost.com";
$headers = "From: $from";
mail($to,$subject,$message,$headers);
echo "Mail Sent.";
?> 
