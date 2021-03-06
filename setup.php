<?php

# require the YAML loader
require("lib/spyc-0.4.5/spyc.php");
require("config.gen.copy.inc.php");

# functions to DRY out the setup script
function copy_gen_config($gen_config) {
	$install_path = getcwd();
	$data = file_get_contents("config.gen.copy.inc.php");
	$data = str_replace("/path/to/install/", $install_path."/", $data);
	$fp = fopen($gen_config, "w");
	fwrite($fp, $data);
	fclose($fp);
	system("chmod 664 ".$gen_config);
	echo("Default configuration in ".$gen_config." copied...");
}

function copy_db($sqlite_db) {
	system("cp db/development.copy.sqlite3 ".$sqlite_db);
	system("chmod 664 ".$sqlite_db);
	echo("\nDefault SQLite database copied...");
}

function copy_section($dir,$filepath,$name) {
	system("cp web/".$dir."/data/data.copy.inc.php ".$filepath);
	system("chmod 664 ".$filepath);
	echo($name.": initial data loaded...");
}

# start the general install
echo("Starting install of Mobile Web OSP v".$mosp_version."...");

# copy the general config file
echo("\nSetting up the general config...");
$gen_config = "config.gen.inc.php";
if (file_exists($gen_config)) {
	echo("\n".$gen_config." already exists. Overwrite it? Y/n ");
	$handle = fopen("php://stdin","r");
	$line = fgets($handle);
	if (strtolower(trim($line)) != "y") {
		echo("Skipping ".$gen_config."...");
	} else {
		copy_gen_config($gen_config);
	}
} else {
	echo("\n");
	copy_gen_config($gen_config);
}		

# COMMENTED OUT BECAUSE SQLite SUPPORT IS BROKEN
# copy the SQLite database assuming a user wants to use it
/* echo("\nAre you going to use SQLite for the database? Y/n ");
$handle = fopen("php://stdin","r");
$line = fgets($handle);
if (strtolower(trim($line)) == 'y') {
	$sqlite_db = "db/development.sqlite3";
	if (file_exists($sqlite_db)) {
		echo($sqlite_db." already exists. Overwrite it? Y/n ");
		$handle = fopen("php://stdin","r");
		$line = fgets($handle);
		if (strtolower(trim($line)) != 'y') {
			echo("Skipping ".$sqlite_db."...");
		} else {
			copy_db($sqlite_db);
		}
	} else {
		copy_db($sqlite_db);
	}
} else {
	echo("You will need to manually update config.gen.inc.php to support MySQL...");
} */

echo("\nYou will need to manually update config.gen.inc.php to support MySQL...");

# set-up individual sections based on availability of setup.yml files
echo("\nSetting up individual sections...");
$messages = array();
$base_dir = getcwd()."/web/";
$files = scandir($base_dir);
foreach ($files as $file) {
	if (is_dir($base_dir.$file)) {
		$config_file = $base_dir.$file."/info.yml";
		if (file_exists($config_file)) {
			$config = Spyc::YAMLLoad($config_file);
			if ($config["data"] == true) {
				$filepath =  $base_dir.$file."/data/data.inc.php";
				if (file_exists($filepath)) {
					echo("\n".$config["name"].": data file already exists. Overwrite it? Y/n ");
					$handle = fopen("php://stdin","r");
					$line = fgets($handle);
					if(strtolower(trim($line)) != 'y') {
						echo($config['name'].": skipping data file...");
					} else {
						copy_section($file,$filepath,$config["name"]);
					}
				} else {
					echo("\n");
					copy_section($file,$filepath,$config["name"]);
				}
			}
			$config_messages = $config["messages"]; $i = 0;
			while ($i < count($config_messages)) {
				$messages[] = $config["name"].": ".$config_messages[$i];
				$i++;
			}
		}
	}
}

echo("\nSome things you should look at after installing Mobile Web OSP: ");
foreach ($messages as $message) {
	echo("\n - ".$message);
}

echo("\nInstallation complete!\n");

?>
