<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = htmlspecialchars(strip_tags($_POST['subject']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(strip_tags($_POST['message']));
    $captcha = $_POST['cf-turnstile-response'];

    if ($email && $captcha) {
        // Verify CAPTCHA
        $secretKey = "0x4AAAAAAA5BsVQ3278l57FcdWpFSiZV3bw"; // Replace with your Turnstile secret key
        $verifyURL = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
        $data = [
            'secret' => $secretKey,
            'response' => $captcha
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($verifyURL, false, $context);
        $responseKeys = json_decode($response, true);

        if ($responseKeys["success"]) {
            // Send Email
            $to = "vivek.krishnamurthy@colorado.edu"; // Replace with your email address
            $headers = "From: " . $email . "\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $fullMessage = "You have received a new message from your website:\n\n" .
                           "Subject: $subject\n\n" .
                           "Message:\n$message";
            
            if (mail($to, $subject, $fullMessage, $headers)) {
                echo "Your message has been sent successfully!";
            } else {
                echo "Sorry, there was a problem sending your message. Please try again later.";
            }
        } else {
            echo "CAPTCHA verification failed. Please try again.";
        }
    } else {
        echo "Invalid email address or CAPTCHA. Please go back and try again.";
    }
} else {
    echo "Invalid request method.";
}
?>