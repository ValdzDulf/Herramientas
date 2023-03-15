<?php

namespace app\models;

use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for interaction with the staff catalog.
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class StaffModel
{
    /**
     * Creates the temporary table.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function createBulkTable()
    {
        $statement = "
            CREATE TABLE IF NOT EXISTS data.temporaryStaffBulk (
                firstSurname        VARCHAR(255) NOT NULL,
                secondSurName       VARCHAR(255) NOT NULL,
                firstName           VARCHAR(255) NOT NULL,
                gender              VARCHAR(6)   NOT NULL,
                dateOfBirth         DATE         NOT NULL,
                curp                CHAR(16)     NOT NULL,
                jobCode             CHAR(5)      NOT NULL,
                rfc                 VARCHAR(13)  NOT NULL,
                tenuredDirectorCode CHAR(5)      NULL,
                directorCode        CHAR(5)      NULL,
                managerCode         CHAR(5)      NULL,
                supervisorCode      CHAR(5)      NULL,
                position            VARCHAR(50)  NULL,
                jobProfile          VARCHAR(50)  NULL,
                jobType             VARCHAR(15)  NULL,
                shift               VARCHAR(15)  NULL,
                branch              VARCHAR(50)  NULL,
                startDate           DATE         NOT NULL,
                colorRibbon         VARCHAR(20)  NULL,
                id                  INT          NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (id)
            );
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Deletes the temporary table.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function dropBulkTable()
    {
        $statement = "
            DROP TABLE
                data.temporaryStaffBulk;
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Fetches the active staff catalog.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An array of associative arrays holding the personnel data.
     */
    public function getCurrentCatalog()
    {
        $statement = "
            SELECT
                LTRIM(RTRIM(ApellidoPaterno)) AS firstSurname,
                LTRIM(RTRIM(ApellidoMaterno)) AS secondSurName,
                LTRIM(RTRIM(Nombres)) AS firstName,
                LTRIM(RTRIM(Sexo)) AS gender,
                CONVERT(VARCHAR(10), FechaNacimiento, 23) AS dateOfBirth,
                CONCAT(
                    SUBSTRING(ApellidoPaterno, 1, 2),
                    SUBSTRING(ApellidoMaterno, 1, 1),
                    SUBSTRING(Nombres, 1, 1),
                    CONVERT(VARCHAR, FechaNacimiento, 12),
                    IIF (Sexo = 'HOMBRE', 'H', 'M')
                ) AS curp,
                LTRIM(RTRIM(Clave)) as jobCode,
                CONCAT(
                    SUBSTRING(ApellidoPaterno, 1, 2),
                    SUBSTRING(ApellidoMaterno, 1, 1),
                    SUBSTRING(Nombres, 1, 1),
                    CONVERT(VARCHAR, FechaNacimiento, 12)
                ) AS rfc,
                LTRIM(RTRIM(ClaveDirectorTitular)) AS tenuredDirectorCode,
                LTRIM(RTRIM(ClaveDirector)) AS directorCode,
                LTRIM(RTRIM(ClaveGerente)) AS managerCode,
                LTRIM(RTRIM(ClaveSupervisor)) AS supervisorCode,
                LTRIM(RTRIM(Puesto)) AS position,
                LTRIM(RTRIM(Perfil)) AS jobProfile,
                LTRIM(RTRIM(TipoEmpleado)) AS jobType,
                LTRIM(RTRIM(Turno)) AS shift,
                LTRIM(RTRIM(Sucursal)) AS branch,
                CONVERT(VARCHAR(10), FechaIngreso, 23) AS startDate,
                LTRIM(RTRIM(REPLACE(Liston, 'LISTON ', ''))) AS colorRibbon
            FROM
                [MAC].[dbo].[CatPersonal_Replica_SPIDER];
        ";

        return DatabaseConnection::dqlStatement($statement, 'server51');
    }

    /**
     * Inserts rows from the personnel catalog file.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  string  $fileContent  Data that will be inserted
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function loadData($fileContent)
    {
        $statement = "
            LOAD DATA
            LOCAL
            INFILE '$fileContent'
            INTO TABLE data.temporaryStaffBulk
            CHARACTER SET latin1
            FIELDS
                TERMINATED BY '|'
                OPTIONALLY ENCLOSED BY '\"'
            LINES
                TERMINATED BY '\n'
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Insert the employee's data if it doesn't exist, if it does exist, updates the data on specified
     * columns.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updateEmployeeTable()
    {
        $statement = "
            INSERT INTO data.Employee (
                jobCode,
                personId,
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
                colorRibbon,
                createdOn
            )
            SELECT
                PersonT.jobCode,
                PersonT.id,
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
                colorRibbon,
                CURRENT_TIMESTAMP
            FROM
                data.temporaryStaffBulk AS TemporaryT
            INNER JOIN
                data.Person AS PersonT
                ON TemporaryT.jobCode = PersonT.jobCode
                AND PersonT.isActive = 1
            ON DUPLICATE KEY UPDATE
                tenuredDirectorCode = VALUES(tenuredDirectorCode),
                directorCode = VALUES(directorCode),
                managerCode = VALUES(managerCode),
                supervisorCode = VALUES(supervisorCode),
                position = VALUES(position),
                jobProfile = VALUES(jobProfile),
                jobType = VALUES(jobType),
                shift = VALUES(shift),
                branch = VALUES(branch),
                colorRibbon = VALUES(colorRibbon)
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Insert the person's data if it doesn't exist, if it does exist, updates the data on specified columns.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updatePersonTable()
    {
        $statement = "
            INSERT INTO data.Person (
                firstSurname,
                secondSurname,
                firstName,
                gender,
                dateOfBirth,
                curp,
                jobCode,
                createdOn
            )
            SELECT
                firstSurname,
                secondSurname,
                firstName,
                gender,
                dateOfBirth,
                curp,
                jobCode,
                CURRENT_TIMESTAMP
            FROM
                data.temporaryStaffBulk
            WHERE
                position NOT IN (
                    'ABOGADO TITULADO',
                    'AJUSTADOR',
                    'ASESOR DOMICILIARIO',
                    'AUXILIAR SINIESTROS',
                    'ENFERMERIA',
                    'ENLACE RRHH',
                    'INTENDENCIA',
                    'MEDICO',
                    'MENSAJERIA',
                    'RECEPCIONISTA'
                )
            ON DUPLICATE KEY UPDATE
                gender = VALUES(gender),
                curp = VALUES(curp);
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Updates the status flag in case the employee no longer works at SERTEC.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updateStatusEmployee()
    {
        $statement = "
            UPDATE
                data.Person AS PersonT,
                data.Employee AS EmployeeT
            SET
                PersonT.isActive = 0,
                EmployeeT.isActive = 0,
                PersonT.updatedOn = CURRENT_TIMESTAMP,
                EmployeeT.updatedOn = CURRENT_TIMESTAMP
            WHERE
                PersonT.id = EmployeeT.personId
                AND PersonT.jobCode NOT IN (
                    SELECT
                        temporaryStaffBulk.jobCode FROM data.temporaryStaffBulk
                );
        ";

        return DatabaseConnection::dmlStatement($statement);
    }
}
