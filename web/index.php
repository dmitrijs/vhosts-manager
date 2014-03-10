<?php

require 'vendor/autoload.php';

$app = new Slim\Slim();

$filename = 'test.conf';

function get_all_vhosts($filename) {
	$file = file_get_contents($filename);

	$all = array();
	foreach (preg_split('/\r|\n|\r\n/', $file) as $line) {
		if (preg_match('/^## Path: (.*?) ServerName: (.*?)$/', $line, $matches)) {
			$v_path = $matches[1];
			$v_server_name = $matches[2];

			$all[] = array('path' => $v_path, 'server_name' => $v_server_name);
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
		$template = str_replace('{{PATH}}', $v_path, $template);
		$template = str_replace('{{SERVER_NAME}}', $v_server_name, $template);

		file_put_contents($filename, $template . "\n", FILE_APPEND);
	}
	die(json_encode(get_all_vhosts($filename)));
});

$app->run();