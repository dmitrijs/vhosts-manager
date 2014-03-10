<?php
ini_set('date.timezone', 'Europe/Riga');

require 'vendor/autoload.php';

$app = new Slim\Slim();

$filename = 'test.conf';

function get_all_vhosts($filename) {
	$file = file_get_contents($filename);

	$all = array();
	foreach (preg_split('/\r|\n|\r\n/', $file) as $line) {
		if (preg_match('/^## #(.*?) Path: (.*?) ServerName: (.*?)$/', $line, $matches)) {
			$v_hash = $matches[1];
			$v_path = $matches[2];
			$v_server_name = $matches[3];

			$all[] = array('nn' => $v_hash, 'path' => $v_path, 'server_name' => $v_server_name);
		}
	}
	return $all;
}

$app->get('/api/vhosts', function () use ($filename) {
	die(json_encode(get_all_vhosts($filename)));
});

$app->post('/api/vhosts', function () use ($app, $filename) {
	$post = json_decode(file_get_contents('php://input'), true);

	if (isset($post['path']) && isset($post['server_name'])) {
		$v_path = str_replace('\\', '/', $post['path']);
		$v_server_name = $post['server_name'];

		$template = file_get_contents('./config/vhost.template.conf');
		$template = str_replace('{{NN}}', md5($v_server_name), $template);
		$template = str_replace('{{PATH}}', $v_path, $template);
		$template = str_replace('{{SERVER_NAME}}', $v_server_name, $template);

		file_put_contents($filename, $template . "\n", FILE_APPEND);
	}
	die(json_encode(get_all_vhosts($filename)));
});

$app->delete('/api/vhosts/:nn', function ($nn) use ($app, $filename) {
	$lines = file($filename, FILE_IGNORE_NEW_LINES);

	$backup_filename = 'backup/' . $filename . '.' . date('Ymd_Hi');
	$new_filename = $filename . '.updated';
	file_put_contents($new_filename, '');

	$do_print = false;
	foreach ($lines as $line) {
		if (preg_match('/^## #([0-9a-f]+) /', $line, $matches)) {
			$do_print = ! ($matches[1] === $nn);
		}
		if ($do_print) {
			file_put_contents($new_filename, $line . "\n", FILE_APPEND);
		}
		if (preg_match('/^## End/', $line)) {
			$do_print = true;
		}
	}
	rename($filename, $backup_filename);
	rename($new_filename, $filename);

	die(json_encode(get_all_vhosts($filename)));
});

$app->run();