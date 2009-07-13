#!/usr/local/php5/bin/php -q
<?php
require("../jobs/_ini.php");

$result = DBPal::query("SELECT * FROM etablissements");
while ($obj = $result->fetch_object())
{
	$data = array(
		'ville_id'     => $obj->ville_id,
		'nom'          => $obj->nom,
		'cursus'       => $obj->cursus,
		'secondaire'   => $obj->secondaire
	);
	if ($obj->moderated)
		$data['moderated_by'] = $obj->moderated;
	
	// system log
	App::log('Legacy State', 'school', $obj->id, 0, $data);
}

fwrite(STDOUT, "Logged school states...\n");

$result = DBPal::query("SELECT * FROM professeurs");
while ($obj = $result->fetch_object())
{
	$data = array(
		'nom'        => $obj->nom,
		'prenom'     => $obj->prenom,
		'matiere_id' => $obj->matiere_id,
		'sujet'      => $obj->sujet
	);
	if ($obj->moderated)
		$data['moderated_by'] = $obj->moderated;
	if ($obj->deleted)
		$data['deleted_by'] = $obj->deleted;
	
	// system log
	App::log('Legacy State', 'prof', $obj->id, 0, $data);
}

fwrite(STDOUT, "Logged prof states...\n");

$result = DBPal::query("SELECT * FROM notes");
while ($obj = $result->fetch_object())
{
	$data = array(
		'creation_ip'   => $obj->ip,
		'creation_host' => $obj->hostname
	);
	if ($obj->moderated)
		$data['moderated_by'] = $obj->moderated;
	if ($obj->deleted)
		$data['deleted_by'] = $obj->deleted;
	if (strlen($obj->reason) > 0)
		$data['reason_for_delete'] = stripslashes($obj->reason);
	
	// system log
	App::log('Legacy State', 'comment', $obj->id, 0, $data);
}

fwrite(STDOUT, "Logged comment states...\n");

// convert ratings date format
DBPal::query("UPDATE notes SET date_utc = date");

fwrite(STDOUT, "Converted rating dates to UTC...\n");

$result = DBPal::query("SELECT * FROM delegues");
while ($obj = $result->fetch_object())
{
	$data = array(
		'nom'           => $obj->nom,
		'prenom'        => $obj->prenom,
		'email'         => $obj->email
	);
	
	// customized system log
	$data = DBPal::str2null(json_encode($data));
	$query = "INSERT INTO logs (log_msg, object_type, object_id, client_ip, client_host, related_data, actor_id, time) ".
		         "VALUES ('Legacy Creation', 'user', {$obj->id}, '{$obj->sub_ip}', '{$obj->sub_host}', $data, 0, '{$obj->sub_date}')";
	$iid = DBPal::insert($query);
	
	DBPal::query("UPDATE delegues SET create_record = $iid WHERE id = {$obj->id}");
}

fwrite(STDOUT, "Logged admin states...\n");

// convert admins date format
DBPal::query("UPDATE delegues SET last_conn_utc = last_conn");

fwrite(STDOUT, "Converted admin dates to UTC...\n");
