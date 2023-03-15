<?php

namespace app\controllers;

use Exception;
use core\exceptions\ApplicationException;
use core\exceptions\DatabaseException;

use core\models\MessageModel;
use core\models\WebServiceResponseModel;

use app\models\StaffModel;

/**
 * Middleware between model and view for SERTEC personal interactions.
 *
 * @package   app\controllers
 *
 * @author    Diego Valentín
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class Staff
{
    /**
     * @var string $temporaryFilePath Location of the temporary file where the current catalog will be stored.
     */
    private $temporaryFilePath = TMP_PATH . 'staff/';

    /**
     * @var string $logPath Location of the file where the log will be stored.
     */
    private $logPath = LOG_PATH . 'staff/';

    /**
     * @var object $modelHandler Database connection instance handler.
     */
    private $modelHandler;

    /**
     * Default constructor.
     *
     * - Initializes the values of the class properties.
     *
     * @return void
     */
    public function __construct()
    {
        $this->modelHandler = new StaffModel();
    }

    /**
     * Update the Person and Worker catalogs of SERTEC employees.
     *
     * @param  string  $method  Method of execution.
     *
     * @return void
     */
    public function updateStaffCatalog($method = 'cronjob')
    {
        $webServiceResponse = new WebServiceResponseModel();
        $webServiceResponse->data->rowsToInsert = 0;
        $webServiceResponse->data->rowsInserted = 0;

        $start = microtime(true);

        try {
            $currentCatalog = $this->modelHandler->getCurrentCatalog();

            if (count($currentCatalog) === 0) {
                throw new DatabaseException('No personnel data were located');
            }

            $webServiceResponse->data->rowsToInsert = $this->setFile($currentCatalog);
            $webServiceResponse->data->rowsInserted = $this->setData();

            $this->modelHandler->updatePersonTable();
            $this->modelHandler->updateEmployeeTable();
            $this->modelHandler->updateStatusEmployee();
            $this->modelHandler->dropBulkTable();

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel(
                    'success', 'Update Catalog', 'Successfully update'
            );
            http_response_code(200);
        } catch (ApplicationException $appEx) {
            $webServiceResponse->statusCode = 405;
            $webServiceResponse->message = new MessageModel(
                    'danger', 'Unauthorized HTTP Method | (create, open) file', $appEx->getMessage()
            );
            http_response_code(405);
        } catch (Exception $ex) {
            $webServiceResponse->statusCode = 400;
            $webServiceResponse->message = new MessageModel('danger', 'Uncaught Exception', $ex->getMessage());
            http_response_code(400);
        } finally {
            $webServiceResponse->data->processingTime = number_format((microtime(true) - $start), 2) . 's';

            $openMode = file_exists($this->logPath) ? 'a' : 'w';
            Helpers::createFolder($this->logPath);

            $logFile = @fopen($this->logPath . 'log.txt', $openMode);

            fwrite($logFile,
                    date('Y-m-d') . ' | ' . date('H:i:s') . ' | ' . $webServiceResponse->message->type . ' | ' .
                    $webServiceResponse->message->description . ' | ' .
                    'rowsToInsert: ' . $webServiceResponse->data->rowsToInsert . ' | ' .
                    'rowsInserted: ' . $webServiceResponse->data->rowsInserted . ' | ' .
                    $webServiceResponse->data->processingTime . ' | ' .
                    'executedBy: ' . $method . "\n"
            );
            fclose($logFile);

            echo json_encode(
                    $webServiceResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            # Clears explicitly the variables used.
            foreach (get_defined_vars() as $key => $var) {
                unset(${$key});
            }
        }
    }

    /**
     * Creates the temporary file with the personnel data.
     *
     * @throws ApplicationException Will throw the exception if file couldn't be created or opened.
     *
     * @param  array  $data  Personnel data.
     *
     * @return int Number of rows inserted in the file.
     */
    private function setFile($data)
    {
        $fileName = date('Y-m-d') . '.txt';

        Helpers::createFolder($this->temporaryFilePath);

        $fileHandler = @fopen($this->temporaryFilePath . $fileName, 'wb');

        if (!$fileHandler) {
            throw new ApplicationException(sprintf("Couldn't create | open file %s", $fileName));
        }

        $rows = 0;

        foreach ($data as $row) {
            fwrite($fileHandler, implode('|', $row) . "\n");

            $rows++;
        }

        fclose($fileHandler);

        return $rows;
    }

    /**
     * Loads the data from the file into the temporary table.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @return int|string Number of inserted rows, -1 indicates error.
     */
    private function setData()
    {
        $fileName = date('Y-m-d') . '.txt';
        $filePath = str_replace(['\\'], ['/'], $this->temporaryFilePath . $fileName);

        $this->modelHandler->createBulkTable();

        $rows = $this->modelHandler->loadData($filePath);

        unlink($filePath);
        rmdir($this->temporaryFilePath);

        return $rows;
    }
}
