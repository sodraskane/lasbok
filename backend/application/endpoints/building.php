<?php
$app->get('/building', function () use ($app, $db) {
	$cols = array('id', 'name', 'description', 'sites_id');
	$buildings = $db->select('Buildings', $cols);
	echo json_encode($buildings, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
});

$app->get('/building/:id', function ($id) use ($app, $db) {
	if (intval($id) < 1) {
		$app->halt(400, 'Bad request');
	}
	
	$cols = array('id', 'name', 'description', 'sites_id');
	$where = array('id' => $id);
	$buildings = $db->select('Buildings', $cols, $where);
	if (count($buildings) > 0) {
		echo json_encode($buildings[0], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
	} else {
		$app->halt(404, 'Building not found');
	}
});

$app->post('/building', function () use ($app, $db) {
	// TODO: Kontrollera om användaren har behörighet att skapa byggnad
	
	$name = $app->request->post('name');
	$description = $app->request->post('description');
	$site = $app->request->post('site');
	
	if (!(strlen($name) > 0 && strlen($description) > 0 &&
			intval($site) > 0)) {
		$app->halt(400, 'Bad request');
	}
	
	$values = array('name' => $name,
			'description' => $description,
			'sites_id' => $site);
	
	$db->insert('Buildings', $values);
	
	$errors = $db->error();
	
	switch (intval($errors[0])) {
		case 0:
			$app->response()->status(201);
			break;
		case 23000:
			$app->halt(409, "Building '$id' already exists");
			break;
		default:
			$app->halt(500, $errors[2]);
	}
});

$app->put('/building/:id', function ($id) use ($app, $db) {
	// TODO: Kontrollera om användaren har behörighet att ändra byggnad
	if (intval($id) < 1) {
		$app->halt(400, 'Bad request');
	}
	
	$id = $app->request->post('id');
	$name = $app->request->post('name');
	$description = $app->request->post('description');
	$site = $app->request->post('site');
	
	if (!(intval($id) > 0 && strlen($name) > 0 &&
			strlen($description) > 0 &&  intval($site) > 0)) {
		$app->halt(400, 'Bad request');
	}
	
	$values = array('name' => $name, 'description' => $description,
			'sites_id' => $site);
	$where = array('id' => $id);
	
	$db->update('Buildings', $values, $where);
	
	$errors = $db->error();
	
	switch (intval($errors[0])) {
		case 0:
			$app->response()->status(201);
			break;
		case 23000:
			$app->halt(409, "Building 'id' already exists");
			break;
		default:
			$app->halt(500, $errors[2]);
	}
});

$app->delete('/building/:id', function ($id) use ($app, $db) {
	// TODO: Kontrollera om användaren har behörighet att ta bort byggnad
	if (intval($id) < 1) {
		$app->halt(400, 'Bad request');
	}
	
	$db->delete('Buildings', array('id' => $id));
	
	$app->response()->status(200);
});

$app->get('/building/:id/facilities', function ($id) use ($app, $db) {
	if (intval($id) < 1) {
		$app->halt(400, 'Bad request');
	}
	
	$cols = array('id', 'facilities_id');
	$select = array('Building_has_facilities.Buildings_id' => $id);
	$join = array('[>]Buildings_has_facilities'=> array('id' => 'Facilities_id'));

	$roles = $db->select('Buildings', $join, $cols, $select);
	
	echo json_encode($roles, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
});

$app->post('/building/:id/facilities', function ($id) use ($app, $db) {
	// TODO: Kontrollera om användaren har behörighet att ge byggnader nya avdelningar
	if (intval($id) < 1) {
		$app->halt(400, 'Bad request');
	}
	
	$facility = $app->request->post('facility');
	
	if (intval($facility) < 1) {
		$app->halt(400, 'Bad request');
	}
	
	$facilities = array('Buildings_id' => $id,
			'Facilities_id' => $facility);
	
	$db->insert('Buildings_has_facilities', $facilities);
	
	$errors = $db->error();
	
	switch (intval($errors[0])) {
		case 0:
			$app->response()->status(201);
			break;
		case 23000:
			$app->halt(409, "Buildings 'id' already has this facility");
			break;
		default:
			$app->halt(500, $errors[2]);
	}
});

$app->delete('/building/:id/facilities/:fid', function ($id, $fid) use ($app, $db) {
	// TODO: Kontrollera om användaren har behörighet att ta bort byggandens avdelningar
	
	if (intval($id) < 1 || intval($fid) < 1) {
		$app->halt(400, 'Bad request');
	}
	
	$facilities = array('AND' => array('Buildings_id' => $id,
			'Facilities_id' => $fid));
		
	$db->delete('Buildings_has_facilities', $facilities);
	
	$app->response()->status(200);
});
?>