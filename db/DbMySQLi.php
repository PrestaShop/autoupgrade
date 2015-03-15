<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class DbMySQLiCore extends Db
{
	/**
	 * @see DbCore::connect()
	 */
	public function	connect()
	{
		$socket = false;
                $port = false;
                if (strpos($this->server, ':') !== false)
                {
                        list($server, $port) = explode(':', $this->server);
                        if (is_numeric($port) === false) {
                                $socket = $port;
                                $port = false;
                        }
                } elseif (str_pos($this->server, '/') !== false)
                        $socket = $this->server;
                if ($socket)
                        $this->link = @new mysqli(null, $this->user, $this->password, $this->database, null, $socket);
                elseif ($port)
                        $this->link = @new mysqli($server, $this->user, $this->password, $this->database, $port);
                else
                        $this->link = @new mysqli($this->server, $this->user, $this->password, $this->database);

		// Do not use object way for error because this work bad before PHP 5.2.9
		if (mysqli_connect_error())
		{
			Tools14::displayError(sprintf(Tools14::displayError('Link to database cannot be established: %s'), mysqli_connect_error()));
			exit();
		}

		// UTF-8 support
		if (!$this->link->query('SET NAMES \'utf8\''))
		{
			Tools14::displayError(Tools14::displayError('PrestaShop Fatal error: no utf-8 support. Please check your server configuration.'));
			exit();
		}

		return $this->link;
	}

	/**
	 * @see DbCore::disconnect()
	 */
	public function	disconnect()
	{
		@$this->link->close();
	}

	/**
	 * @see DbCore::_query()
	 */
	protected function _query($sql)
	{
		return $this->link->query($sql);
	}

	/**
	 * @see DbCore::nextRow()
	 */
	public function nextRow($result = false)
	{
		if (!$result)
			$result = $this->result;
		if (!is_object($result))
			return false;
		return $result->fetch_assoc();
	}

	/**
	 * @see DbCore::_numRows()
	 */
	protected function _numRows($result)
	{
		return $result->num_rows;
	}

	/**
	 * @see DbCore::Insert_ID()
	 */
	public function	Insert_ID()
	{
		return $this->link->insert_id;
	}

	/**
	 * @see DbCore::Affected_Rows()
	 */
	public function	Affected_Rows()
	{
		return $this->link->affected_rows;
	}

	/**
	 * @see DbCore::getMsgError()
	 */
	public function getMsgError($query = false)
	{
		return $this->link->error;
	}

	/**
	 * @see DbCore::getNumberError()
	 */
	public function getNumberError()
	{
		return $this->link->errno;
	}

	/**
	 * @see DbCore::getVersion()
	 */
	public function getVersion()
	{
		return $this->getValue('SELECT VERSION()');
	}

	/**
	 * @see DbCore::_escape()
	 */
	public function _escape($str)
	{
		return $this->link->real_escape_string($str);
	}

	/**
	 * @see DbCore::set_db()
	 */
	public function set_db($db_name)
	{
		return $this->link->query('USE '.pSQL($db_name));
	}

	/**
	 * @see Db::hasTableWithSamePrefix()
	 */
	public static function hasTableWithSamePrefix($server, $user, $pwd, $db, $prefix)
	{
		$link = @new mysqli($server, $user, $pwd, $db);
		if (mysqli_connect_error())
			return false;

		$sql = 'SHOW TABLES LIKE \''.$prefix.'%\'';
		$result = $link->query($sql);
		return (bool)$result->fetch_assoc();
	}

	/**
	 * @see Db::checkConnection()
	 */
	public static function tryToConnect($server, $user, $pwd, $db, $newDbLink = true, $engine = null, $timeout = 5)
	{
		$link = mysqli_init();
		if (!$link)
			return -1;

		if (!$link->options(MYSQLI_OPT_CONNECT_TIMEOUT, $timeout))
			return 1;

		if (!$link->real_connect($server, $user, $pwd, $db))
			return (mysqli_connect_errno() == 1049) ? 2 : 1;

		if (strtolower($engine) == 'innodb')
		{
			$sql = 'SHOW VARIABLES WHERE Variable_name = \'have_innodb\'';
			$result = $link->query($sql);
			if (!$result)
				return 4;
			$row = $result->fetch_assoc();
			if (!$row || strtolower($row['Value']) != 'yes')
				return 4;
		}
		$link->close();
		return 0;
	}
	
	public static function checkCreatePrivilege($server, $user, $pwd, $db, $prefix, $engine)
	{
		$link = @new mysqli($server, $user, $pwd, $db);
		if (mysqli_connect_error())
			return false;

		$sql = '
			CREATE TABLE `'.$prefix.'test` (
			`test` tinyint(1) unsigned NOT NULL
			) ENGINE=MyISAM';
		$result = $link->query($sql);

		if (!$result)
			return $link->error;

		$link->query('DROP TABLE `'.$prefix.'test`');
		return true;
	}

	/**
	 * @see Db::checkEncoding()
	 */
	static public function tryUTF8($server, $user, $pwd)
	{
		$link = @new mysqli($server, $user, $pwd, $db);
		$ret = $link->query("SET NAMES 'UTF8'");
		$link->close();
		return $ret;
	}
}
