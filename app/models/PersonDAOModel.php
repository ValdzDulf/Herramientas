<?php

namespace app\models;

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
        $this->firstSurname = ucwords(mb_strtolower($data['firstSurname'], 'UTF-8'));
        $this->secondSurname = ucwords(mb_strtolower($data['secondSurname'], 'UTF-8'));
        $this->firstName = ucwords(mb_strtolower($data['firstName'], 'UTF-8'));
        $this->fullName = "$this->firstSurname $this->secondSurname $this->firstName";
        $this->gender = $data['gender'];
        $this->dateOfBirth = $data['dateOfBirth'];
        $this->curp = $data['curp'];
    }
}
