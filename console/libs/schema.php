<?php
/* SVN FILE: $Id: schema.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Command-line database management utility to automate programmer chores.
 *
 * Schema is CakePHP's database management utility. This helps you maintain versions of
 * of your database.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link			http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.console.libs
 * @since			CakePHP(tm) v 1.2.0.5550
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
uses('file', 'model' . DS . 'schema');
/**
 * Schema is a command-line database management utility for automating programmer chores.
 *
 * @package		cake
 * @subpackage	cake.cake.console.libs
 */
class SchemaShell extends Shell {
/**
 * is this a dry run?
 *
 * @var boolean
 * @access private
 */
	var $__dry = null;
/**
 * Override initialize
 *
 * @access public
 */
	function initialize() {
		$this->out('Cake Schema Shell');
		$this->hr();
	}
/**
 * Override startup
 *
 * @access public
 */
	function startup() {
		$name = null;
		if (!empty($this->params['name'])) {
		 	$name = $this->params['name'];
		}
		$path = null;
		if (!empty($this->params['path'])) {
		 	$path = $this->params['path'];
		}
		$file = null;
		if (!empty($this->params['file'])) {
		 	$file = $this->params['file'];
		}

		$this->Schema =& new CakeSchema(compact('name', 'path', 'file'));
	}
/**
 * Override main
 *
 * @access public
 */
	function main() {
		$this->help();
	}
/**
 * Read and output contents od schema object
 * path to read as second arg
 *
 * @access public
 */
	function view() {
		$File = new File($this->Schema->path . DS .'schema.php');
		if ($File->exists()) {
			$this->out($File->read());
			exit();
		} else {
			$this->err(__('Schema could not be found', true));
			exit();
		}
	}
/**
 * Read database and Write schema object
 * accepts a connection as first arg or path to save as second arg
 *
 * @access public
 */
	function generate() {
		$this->out('Generating Schema...');
		$options = array();
		if (isset($this->params['f'])) {
			$options = array('models' => false);
		}

		$snapshot = false;
		if (isset($this->args[0]) && $this->args[0] === 'snapshot') {
			$snapshot = true;
		}

		if (!$snapshot && file_exists($this->Schema->path . DS . 'schema.php')) {
			$snapshot = true;
			$result = $this->in("Schema file exists.\n [O]verwrite\n [S]napshot\n [Q]uit\nWould you like to do?", array('o', 's', 'q'), 's');
			if ($result === 'q') {
				exit();
			}
			if ($result === 'o') {
				$snapshot = false;
			}
		}

		$content = $this->Schema->read($options);
		$content['file'] = 'schema.php';

		if ($snapshot === true) {
			$Folder =& new Folder($this->Schema->path);
			$result = $Folder->read();
			$count = 1;
			if (!empty($result[1])) {
				foreach ($result[1] as $file) {
					if (preg_match('/schema/', $file)) {
						$count++;
					}
				}
			}
			$content['file'] = 'schema_'.$count.'.php';
		}

		if ($this->Schema->write($content)) {
			$this->out(sprintf(__('Schema file: %s generated', true), $content['file']));
			exit();
		} else {
			$this->err(__('Schema file: %s generated', true));
			exit();
		}
	}
/**
 * Dump Schema object to sql file
 * if first arg == write, file will be written to sql file
 * or it will output sql
 *
 * @access public
 */
	function dump() {
		$write = false;
		$Schema = $this->Schema->load();
		if (!$Schema) {
			$this->err(__('Schema could not be loaded', true));
			exit();
		}
		if (!empty($this->args[0])) {
			if ($this->args[0] == 'true') {
				$write = Inflector::underscore($this->Schema->name);
			} else {
				$write = $this->args[0];
			}
		}
		$db =& ConnectionManager::getDataSource($this->Schema->connection);
		$contents = "#". $Schema->name ." sql generated on: " . date('Y-m-d H:m:s') . " : ". time()."\n\n";
		$contents .= $db->dropSchema($Schema) . "\n\n". $db->createSchema($Schema);
		if ($write) {
			if (strpos($write, '.sql') === false) {
				$write .= '.sql';
			}
			$File = new File($this->Schema->path . DS . $write, true);
			if ($File->write($contents)) {
				$this->out(sprintf(__('SQL dump file created in %s', true), $File->pwd()));
				exit();
			} else {
				$this->err(__('SQL dump could not be created', true));
				exit();
			}
		}
		$this->out($contents);
		return $contents;
	}
/**
 * Run database commands: create, update
 *
 * @access public
 */
	function run() {
		if (!isset($this->args[0])) {
			$this->err('command not found');
			exit();
		}

		$command = $this->args[0];

		$this->Dispatch->shiftArgs();

		$name = null;
		if (isset($this->args[0])) {
			$name = $this->args[0];
		}

		if (isset($this->params['dry'])) {
			$this->__dry = true;
			$this->out(__('Performing a dry run.', true));
		}

		$options = array('name' => $name, 'file' => $this->Schema->file);
 		if (isset($this->params['s'])) {
			$options = array('file' => 'schema_'.$this->params['s'].'.php');
		}

		$Schema = $this->Schema->load($options);

		if (!$Schema) {
			$this->err(sprintf(__('%s could not be loaded', true), $this->Schema->file));
			exit();
		}

		$table = null;
		if (isset($this->args[1])) {
			$table = $this->args[1];
		}

		switch($command) {
			case 'create':
				$this->__create($Schema, $table);
			break;
			case 'update':
				$this->__update($Schema, $table);
			break;
			default:
				$this->err(__('command not found', true));
			exit();
		}
	}
/**
 * Create database from Schema object
 * Should be called via the run method
 *
 * @access private
 */
	function __create($Schema, $table = null) {
		$db =& ConnectionManager::getDataSource($this->Schema->connection);

		$drop = $create = array();

		if (!$table) {
			foreach ($Schema->tables as $table => $fields) {
				$drop[$table] = $db->dropSchema($Schema, $table);
				$create[$table] = $db->createSchema($Schema, $table);
			}
		} elseif (isset($Schema->tables[$table])) {
			$drop[$table] = $db->dropSchema($Schema, $table);
			$create[$table] = $db->createSchema($Schema, $table);
		}
		if (empty($drop) || empty($create)) {
			$this->out(__('Schema is up to date.', true));
			exit();
		}

		$this->out("\n" . __('The following tables will drop.', true));
		$this->out(array_keys($drop));

		if ('y' == $this->in(__('Are you sure you want to drop the tables?', true), array('y', 'n'), 'n')) {
			$this->out('Dropping tables.');
			$this->__run($drop, 'drop');
		}

		$this->out("\n" . __('The following tables will create.', true));
		$this->out(array_keys($create));

		if ('y' == $this->in(__('Are you sure you want to create the tables?', true), array('y', 'n'), 'y')) {
			$this->out('Creating tables.');
			$this->__run($create, 'create');
		}

		$this->out(__('End create.', true));
	}
/**
 * Update database with Schema object
 * Should be called via the run method
 *
 * @access private
 */
	function __update($Schema, $table = null) {
		$db =& ConnectionManager::getDataSource($this->Schema->connection);

		$this->out('Comparing Database to Schema...');
		$Old = $this->Schema->read();
		$compare = $this->Schema->compare($Old, $Schema);

		$contents = array();

		if (!$table) {
			foreach ($compare as $table => $changes) {
				$contents[$table] = $db->alterSchema(array($table => $changes), $table);
			}
		} elseif (isset($compare[$table])) {
			$contents[$table] = $db->alterSchema(array($table => $compare[$table]), $table);
		}

		if (empty($contents)) {
			$this->out(__('Schema is up to date.', true));
			exit();
		}

		$this->out("\n" . __('The following statements will run.', true));
		$this->out(array_map('trim', $contents));
		if ('y' == $this->in(__('Are you sure you want to alter the tables?', true), array('y', 'n'), 'n')) {
			$this->out('');
			$this->out(__('Updating Database...', true));
			$this->__run($contents, 'update');
		}

		$this->out(__('End update.', true));
	}
/**
 * runs sql from __create() or __update()
 *
 * @access private
 */
	function __run($contents, $event) {
		if (empty($contents)) {
			$this->err(__('Sql could not be run', true));
			return;
		}
		Configure::write('debug', 2);
		$db =& ConnectionManager::getDataSource($this->Schema->connection);
		$db->fullDebug = true;

		$errors = array();
		foreach($contents as $table => $sql) {
			if (empty($sql)) {
				$this->out(sprintf(__('%s is up to date.', true), $table));
			} else {
				if ($this->__dry === true) {
					$this->out(sprintf(__('Dry run for %s :', true), $table));
					$this->out($sql);
				} else {
					if (!$this->Schema->before(array($event => $table))) {
						return false;
					}
					if (!$db->_execute($sql)) {
						$error = $db->lastError();
					}

					$this->Schema->after(array($event => $table, 'errors'=> $errors));

					if (isset($error)) {
						$this->out($errors);
					} elseif ($this->__dry !== true) {
						$this->out(sprintf(__('%s updated.', true), $table));
					}
				}
			}
		}
	}
/**
 * Displays help contents
 *
 * @access public
 */
	function help() {
		$this->out("The Schema Shell generates a schema object from \n\t\tthe database and updates the database from the schema.");
		$this->hr();
		$this->out("Usage: cake schema <command> <arg1> <arg2>...");
		$this->hr();
		$this->out('Params:');
		$this->out("\n\t-connection <config>\n\t\tset db config <config>. uses 'default' if none is specified");
		$this->out("\n\t-path <dir>\n\t\tpath <dir> to read and write schema.php.\n\t\tdefault path: ". $this->Schema->path);
		$this->out("\n\t-file <name>\n\t\tfile <name> to read and write.\n\t\tdefault file: ". $this->Schema->file);
		$this->out("\n\t-s <number>\n\t\tsnapshot <number> to use for run.");
		$this->out("\n\t-dry\n\t\tPerform a dry run on 'run' commands.\n\t\tQueries will be output to window instead of executed.");
		$this->out("\n\t-f\n\t\tforce 'generate' to create a new schema.");
		$this->out('Commands:');
		$this->out("\n\tschema help\n\t\tshows this help message.");
		$this->out("\n\tschema view\n\t\tread and output contents of schema file");
		$this->out("\n\tschema generate\n\t\treads from 'connection' writes to 'path'\n\t\tTo force genaration of all tables into the schema, use the -f param.");
		$this->out("\n\tschema dump <filename>\n\t\tdump database sql based on schema file to filename in schema path. \n\t\tif filename is true, default will use the app directory name.");
		$this->out("\n\tschema run create <table>\n\t\tdrop tables and create database based on schema file\n\t\toptional <table> arg for creating only one table\n\t\tpass the -s param with a number to use a snapshot\n\t\tTo see the changes, perform a dry run with the -dry param");
		$this->out("\n\tschema run update <table>\n\t\talter tables based on schema file\n\t\toptional <table> arg for altering only one table.\n\t\tTo use a snapshot, pass the -s param with the snapshot number\n\t\tTo see the changes, perform a dry run with the -dry param");
		$this->out("");
		exit();
	}
}
?>