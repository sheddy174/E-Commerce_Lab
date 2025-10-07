<?php
include_once 'db_cred.php';

/**
 * Database connection class
 * Handles all database operations for the e-commerce platform
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!class_exists('db_connection')) {
    class db_connection
    {
        //properties
        public $db = null;
        public $results = null;

        /**
         * Database connection
         * @return boolean Success status
         */
        function db_connect()
        {
            //connection
            $this->db = mysqli_connect(SERVER, USERNAME, PASSWD, DATABASE);

            //test the connection
            if (mysqli_connect_errno()) {
                error_log("Database connection failed: " . mysqli_connect_error());
                return false;
            } else {
                // Set charset to utf8mb4 for better character support
                mysqli_set_charset($this->db, "utf8mb4");
                return true;
            }
        }

        /**
         * Get database connection object
         * @return mysqli|false Database connection or false on failure
         */
        function db_conn()
        {
            //connection
            $this->db = mysqli_connect(SERVER, USERNAME, PASSWD, DATABASE);

            //test the connection
            if (mysqli_connect_errno()) {
                error_log("Database connection failed: " . mysqli_connect_error());
                return false;
            } else {
                mysqli_set_charset($this->db, "utf8mb4");
                return $this->db;
            }
        }

        /**
         * Query the Database for SELECT statements
         * @param string $sqlQuery SQL query to execute
         * @return boolean Success status
         */
        function db_query($sqlQuery)
        {
            if (!$this->db_connect()) {
                return false;
            } elseif ($this->db == null) {
                return false;
            }

            //run query 
            $this->results = mysqli_query($this->db, $sqlQuery);

            if ($this->results == false) {
                error_log("Query failed: " . mysqli_error($this->db) . " Query: " . $sqlQuery);
                return false;
            } else {
                return true;
            }
        }

        /**
         * Query the Database for INSERT, UPDATE, DELETE statements
         * @param string $sqlQuery SQL query to execute
         * @return boolean Success status
         */
        function db_write_query($sqlQuery)
        {
            if (!$this->db_connect()) {
                return false;
            } elseif ($this->db == null) {
                return false;
            }

            //run query 
            $result = mysqli_query($this->db, $sqlQuery);

            if ($result == false) {
                error_log("Write query failed: " . mysqli_error($this->db) . " Query: " . $sqlQuery);
                return false;
            } else {
                return true;
            }
        }

        /**
         * Get a single record
         * @param string $sql SQL query
         * @return array|false Record array or false on failure
         */
        function db_fetch_one($sql)
        {
            // if executing query returns false
            if (!$this->db_query($sql)) {
                return false;
            }
            //return a record
            return mysqli_fetch_assoc($this->results);
        }

        /**
         * Get all records
         * @param string $sql SQL query
         * @return array|false Records array or false on failure
         */
        function db_fetch_all($sql)
        {
            // if executing query returns false
            if (!$this->db_query($sql)) {
                return false;
            }
            //return all records
            return mysqli_fetch_all($this->results, MYSQLI_ASSOC);
        }

        /**
         * Get count of records
         * @return int|false Record count or false on failure
         */
        function db_count()
        {
            //check if result was set
            if ($this->results == null) {
                return false;
            } elseif ($this->results == false) {
                return false;
            }

            //return count
            return mysqli_num_rows($this->results);
        }

        /**
         * Get last inserted ID
         * @return int Last insert ID
         */
        function last_insert_id()
        {
            return mysqli_insert_id($this->db);
        }

        /**
         * Close database connection
         * @return void
         */
        function db_close()
        {
            if ($this->db) {
                mysqli_close($this->db);
                $this->db = null;
            }
        }

        /**
         * Sanitize input data
         * @param string $data Data to sanitize
         * @return string Sanitized data
         */
        function sanitize($data)
        {
            if (!$this->db) {
                $this->db_connect();
            }
            return mysqli_real_escape_string($this->db, trim($data));
        }

        /**
         * Begin transaction
         * @return boolean Success status
         */
        function begin_transaction()
        {
            if (!$this->db) {
                $this->db_connect();
            }
            return mysqli_autocommit($this->db, false);
        }

        /**
         * Commit transaction
         * @return boolean Success status
         */
        function commit_transaction()
        {
            if (!$this->db) {
                return false;
            }
            $result = mysqli_commit($this->db);
            mysqli_autocommit($this->db, true);
            return $result;
        }

        /**
         * Rollback transaction
         * @return boolean Success status
         */
        function rollback_transaction()
        {
            if (!$this->db) {
                return false;
            }
            $result = mysqli_rollback($this->db);
            mysqli_autocommit($this->db, true);
            return $result;
        }
    }
}
?>
