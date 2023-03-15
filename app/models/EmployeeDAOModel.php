<?php

namespace app\models;

use core\exceptions\DatabaseException;
use core\exceptions\InputDataException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for accessing the employee's data.
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class EmployeeDAOModel
{
    /**
     * @var string $jobCode Unique employee code.
     */
    public $jobCode;

    /**
     * @var string $rfc Unique code of an individual or legal entity.
     */
    public $rfc;

    /**
     * @var string $tenuredDirectorCode Employee's unique tenured director's code.
     */
    public $tenuredDirectorCode;

    /**
     * @var string $directorCode Employee's unique director's code.
     */
    public $directorCode;

    /**
     * @var string $managerCode Employee's unique tenured manager's code.
     */
    public $managerCode;

    /**
     * @var string $supervisorCode Employee's unique tenured supervisor's code.
     */
    public $supervisorCode;

    /**
     * @var string $position Position held by the employee.
     */
    public $position;

    /**
     * @var string $jobProfile Internal employee job profile.
     */
    public $jobProfile;

    /**
     * @var string $jobType Type of position in which the employee works.
     */
    public $jobType;

    /**
     * @var string $shift Shift in which the employee performs his activities.
     */
    public $shift;

    /**
     * @var string $branch Branch where the employee carries out his activities.
     */
    public $branch;

    /**
     * @var string $startDate Date employee started working.
     */
    public $startDate;

    /**
     * @var string $colorRibbon Distinctive color for the employee's length of work.
     */
    public $colorRibbon;

    /**
     * Fetches all employees no matter their activation status.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An associative array holding the employee list.
     */
    public function getAllEmployees()
    {
        $statement = "
            SELECT
                jobCode,
                personId,
                tenuredDirectorCode,
                directorCode,
                managerCode,
                supervisorCode,
                position,
                shift,
                branch,
                isActive,
                CASE
                    WHEN colorRibbon = 'AMARRILLO' THEN 'yellow'
                    WHEN colorRibbon = 'AZUL' THEN 'blue'
                    WHEN colorRibbon = 'BLANCO' THEN 'white'
                    WHEN colorRibbon = 'DORADO' THEN 'gold'
                    WHEN colorRibbon = 'NEGRO' THEN 'black'
                    WHEN colorRibbon = 'PLATA' THEN 'silver'
                    WHEN colorRibbon = 'ROJO' THEN 'red'
                    ELSE colorRibbon
                END AS colorRibbon
            FROM
                data.Employee
        ";

        return DatabaseConnection::dqlStatement($statement);
    }

    /**
     * Fetches the employee's data using his unique code.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     * @throws InputDataException Will throw the exception if the employee doesn't exist.
     *
     * @param  string  $jobCode  Unique employee code.
     *
     * @return void
     */
    public function getEmployeeByKey($jobCode)
    {
        $statement = "
            SELECT
                jobCode,
                rfc,
                tenuredDirectorCode,
                directorCode,
                managerCode,
                supervisorCode,
                position,
                jobProfile,
                jobType,
                shift,
                branch,
                startDate,
                colorRibbon
            FROM
                data.Employee
            WHERE
                jobCode = '$jobCode'
                AND isActive = 1;
        ";

        $data = DatabaseConnection::dqlStatement($statement);

        if (count($data) === 0) {
            throw new InputDataException(sprintf("Employee [%s] doesn't exist", $jobCode));
        }

        $this->setEmployee($data);
    }

    /**
     * Set the employee entity attributes.
     *
     * @param  array  $data  Employee's data.
     *
     * @return void
     */
    private function setEmployee($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
