<?php

// require
require_once 'config.php';
require_once '../teamdesk.php';

// create instance
$teamdesk = new Teamdesk(EMAIL,PASS, SERVER);

//$response = $teamdesk->create($table, $xml);
//$response = $teamdesk->delete($table, $ids);
//$response = $teamdesk->describeApp();
//$response = $teamdesk->getUserInfo();
//$response = $teamdesk->sendMail('from@verkoyen.eu', 'tijs@verkoyen.eu', null, null, 'subject', 'format', 'body');
//$response = $teamdesk->describeTable('Website');
//$response = $teamdesk->describeTables(array('Website', 'People'));
//$response = $teamdesk->query('SELECT * FROM [Website]');
//$response = $teamdesk->retrieve('Website', array('Name', 'URL'), array(1, 2));
//$response = $teamdesk->getDeleted('Website', mktime(00, 00, 00, 06, 20, 2011), time());
//$response = $teamdesk->getUpdated('Website', mktime(00, 00, 00, 06, 20, 2011), time());
//$response = $teamdesk->getAttachmentInfo('Invoice', 'Tender', 2, 10);
//$response = $teamdesk->getAttachment('Invoice', 'Tender', 2);

// output (Spoon::dump())
ob_start();
var_dump($response);
$output = ob_get_clean();

// cleanup the output
$output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

// print
echo '<pre>' . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . '</pre>';

?>