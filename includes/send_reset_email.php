<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send a password reset email.
 *
 * Returns:
 * true  = email sent successfully
 * false = email failed
 */
function send_password_reset_email(
    string $email,
    string $username,
    string $reset_link
): bool {

    // Load PHPMailer.
    $autoload = __DIR__ . '/../vendor/autoload.php';

    if (!file_exists($autoload)) {
        error_log('Password reset email failed: PHPMailer autoload.php not found.');
        return false;
    }

    require_once $autoload;

    $mail = new PHPMailer(true);

    try {

        /*
         * SMTP configuration
         */
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        /*
         * Sender
         */
        $mail->setFrom(
            MAIL_FROM_EMAIL,
            MAIL_FROM_NAME
        );

        /*
         * Recipient
         */
        $mail->addAddress($email, $username);

        /*
         * Email format
         */
        $mail->isHTML(true);

        $mail->Subject = 'Password Reset Request';

        /*
         * Safely escape values before putting them
         * into the HTML email.
         */
        $safe_username = htmlspecialchars(
            $username,
            ENT_QUOTES,
            'UTF-8'
        );

        $safe_link = htmlspecialchars(
            $reset_link,
            ENT_QUOTES,
            'UTF-8'
        );

        /*
         * HTML email
         */
        $mail->Body = '
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>

<body style="
    margin:0;
    padding:30px 15px;
    background:#f7faf7;
    font-family:Arial,Helvetica,sans-serif;
    color:#1c2321;
">

    <div style="
        max-width:520px;
        margin:0 auto;
        background:#ffffff;
        padding:30px;
        border:1px solid #dce5dc;
        border-radius:10px;
    ">

        <h2 style="
            margin:0 0 20px;
            color:#2e7d32;
        ">
            Password Reset
        </h2>

        <p>
            Hello ' . $safe_username . ',
        </p>

        <p>
            We received a request to reset the password
            for your account.
        </p>

        <p>
            Click the button below to create a new password.
        </p>

        <div style="
            text-align:center;
            margin:30px 0;
        ">

            <a
                href="' . $safe_link . '"
                style="
                    display:inline-block;
                    padding:13px 24px;
                    background:#2e7d32;
                    color:#ffffff;
                    text-decoration:none;
                    border-radius:7px;
                    font-weight:bold;
                "
            >
                Reset Password
            </a>

        </div>

        <p>
            This password reset link will expire in
            <strong>15 minutes</strong>.
        </p>

        <p>
            If you did not request a password reset,
            you can safely ignore this email.
        </p>

        <hr style="
            border:0;
            border-top:1px solid #dce5dc;
            margin:25px 0;
        ">

        <p style="
            margin:0;
            font-size:12px;
            color:#526352;
        ">
            For security, this reset link can only be used once.
        </p>

    </div>

</body>
</html>
';

        /*
         * Plain-text version for email clients
         * that don't display HTML.
         */
        $mail->AltBody =
            "Hello {$username},\n\n" .
            "We received a request to reset your password.\n\n" .
            "Use this link to reset your password:\n" .
            "{$reset_link}\n\n" .
            "This link expires in 15 minutes.\n\n" .
            "If you did not request this password reset, " .
            "you can safely ignore this email.";

        /*
         * Send email.
         */
        $mail->send();

        return true;

    } catch (Exception $e) {

        /*
         * Never expose SMTP/PHPMailer errors to the user.
         * Keep the technical error in the server log.
         */
        error_log(
            'Password reset email failed: ' .
            $mail->ErrorInfo
        );

        return false;

    } catch (Throwable $e) {

        /*
         * Catch unexpected errors as well.
         */
        error_log(
            'Password reset email unexpected error: ' .
            $e->getMessage()
        );

        return false;
    }
}
