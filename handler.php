<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// load environment variables
$dotenv = new Dotenv(__DIR__);
$dotenv->load();
$dotenv->required('SENSU_MAIL_HANDLER_SEND_TO')->notEmpty();
$dotenv->required('SENSU_MAIL_HANDLER_SEND_TO_ALIAS')->notEmpty();
$dotenv->required('SENSU_MAIL_HANDLER_SEND_FROM')->notEmpty();
$dotenv->required('SENSU_MAIL_HANDLER_SEND_FROM_ALIAS')->notEmpty();

$rawSensuOutput = fgets(STDIN);
$sensuOutput = json_decode($rawSensuOutput, true);

$exitCode = $sensuOutput['check']['status'];

$subjects = ['Ok', 'Warning', 'Critical'];

$status = in_array($exitCode, array_keys($subjects))
    ? $subjects[$exitCode]
    : 'Unknown!';

$subject = $sensuOutput['check']['name'] . ' - ' . $status;

$message = sprintf(
    '<html>
        <body>
            <p>The "%s" sensor is reporting a status of <strong>%s!</strong></p>

            <table>
                <thead>
                    <tr>
                        <td style="font-weight: bold; padding: 0 5px 0 0">Client</td>
                        <td style="font-weight: bold; padding: 0 5px">Sensor</td>
                        <td style="font-weight: bold; padding: 0 5px">Exit code</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 0 5px 0 0">%s</td>
                        <td style="padding: 0 5px">%s</td>
                        <td style="padding: 0 5px">%d</td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-bottom: 0"><strong>Sensor Output:<strong></p>
            <p style="margin-top: 5px">%s</p>
        </body>
    </html>',
    $sensuOutput['check']['name'],
    $status,
    $sensuOutput['client']['name'],
    $sensuOutput['check']['name'],
    $exitCode,
    $sensuOutput['check']['output']
);

$mail = new SimpleMail;
$success = $mail->setTo(getenv('SENSU_MAIL_HANDLER_SEND_TO'), getenv('SENSU_MAIL_HANDLER_SEND_TO_ALIAS'))
    ->setFrom(getenv('SENSU_MAIL_HANDLER_SEND_FROM'), getenv('SENSU_MAIL_HANDLER_SEND_FROM_ALIAS'))
    ->setSubject($subject)
    ->setMessage($message)
    ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
    ->send();

if (!$success) {
    echo 'Unable to send mail!' . PHP_EOL;
    throw new Exception('Unable to send mail!');
}
