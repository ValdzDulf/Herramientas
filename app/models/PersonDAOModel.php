<?php

namespace app\models;

use app\controllers\Helpers;
use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for accessing the person's data
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2022  Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class PersonDAOModel
{
    /**
     * @var integer Unique person identifier.
     */
    public $id;

    /**
     * @var string Person's first surname.
     */
    public $firstSurname;

    /**
     * @var string Person's second surname.
     */
    public $secondSurname;

    /**
     * @var string Person's name.
     */
    public $firstName;

    /**
     * @var string Form name: first surname + second surname + first name
     */
    public $fullName;

    /**
     * @var string Male or Female
     */
    public $gender;

    /**
     * @var string
     */
    public $dateOfBirth;

    /**
     * @var string Unique identity code in mexico.
     */
    public $curp;

    /**
     * Fetches all persons, no matter their activation status.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An associative array holding the person list.
     */
    public function getAllPersons()
    {
        $statement = "
            SELECT
                id,
                firstSurname,
                secondSurname,
                firstName,
                gender,
                dateOfBirth,
                curp,
                jobCode,
                isActive
            FROM
                data.Person;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }

    /**
     * Fetches the person's data using his unique key.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @param  string  $jobCode  Unique employee code.
     *
     * @return void
     */
    public function getPersonByKey($jobCode)
    {
        $statement = "
            SELECT
                id,
                firstSurname,
                secondSurname,
                firstName,
                gender,
                dateOfBirth,
                curp
            FROM
                data.Person
            WHERE
                jobCode = '$jobCode'
                AND isActive = 1;
        ";

        $data = DatabaseConnection::dqlStatement($statement);

        $this->setPerson($data);
    }

    /**
     * Set the person entity attributes.
     *
     * @param  array  $data  Person's data.
     *
     * @return void
     */
    private function setPerson($data)
    {
        $this->id = $data['id'];
        $this->firstSurname = Helpers::firstLetterStringUpperCase($data['firstSurname']);
        $this->secondSurname = Helpers::firstLetterStringUpperCase($data['secondSurname']);
        $this->firstName = Helpers::firstLetterStringUpperCase($data['firstName']);
        $this->fullName = "$this->firstSurname $this->secondSurname $this->firstName";
        $this->gender = $data['gender'];
        $this->dateOfBirth = $data['dateOfBirth'];
        $this->curp = $data['curp'];
    }
}
