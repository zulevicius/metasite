<?php

namespace App\Repository;

use App\Exception\DatabaseException;
use App\Entity\EntityInterface;

/**
 * Class CsvDatabaseManager.
 */
class CsvDatabaseManager implements DatabaseInterface
{
    /**
     * CSV file delemiter.
     */
    const CSV_DELIMITER = ';';

    /**
     * New line delimiter.
     */
    const NEW_LINE_DELIMITER = "\r\n";

    /**
     * @var string
     */
    private $dbFile;

    /**
     * @var string
     */
    private $class;

    /**
     * CsvDatabaseManager constructor.
     *
     * @param string $filePath
     * @param string $entityClass
     */
    public function __construct(string $filePath, string $entityClass)
    {
        $this->dbFile = __DIR__ . '/' . $filePath;
        $this->class = $entityClass;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->class;
    }

    /**
     * @return array|null
     */
    public function getAllRecords(): ?array
    {
        $allRecords = $this->readFile();

        return $allRecords;
    }

    /**
     * @param string $id
     *
     * @return EntityInterface|null
     */
    public function getRecord(string $id): ?EntityInterface
    {
        $entities = $this->readFile();
        if (!array_key_exists($id, $entities)) {
            return null;
        }

        return $entities[$id];
    }

    /**
     * @param EntityInterface $entity
     *
     * @return bool
     */
    public function saveRecord(EntityInterface $entity): bool
    {
        $entities = $this->readFile();
        if (!$this->checkDuplicates($entities, $entity)) {
            return false;
        }
        $entities[] = $entity;
        $this->saveFile($entities);
        return true;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function deleteRecord(string $id): bool
    {
        $entities = $this->readFile();
        if (empty($entities[$id])) {
            return false;
        }
        unset($entities[$id]);
        $this->saveFile($entities);
        return true;
    }

    /**
     * @param string $id
     * @param $entity
     */
    public function updateRecord(string $id, $entity): void
    {
        $entities = $this->readFile();
        $entities[$id] = $entity;
        $this->saveFile($entities);
    }

    /**
     * @return array
     */
    private function readFile(): array
    {
        $content = '';
        if (file_exists($this->dbFile)) {
            $content = file_get_contents($this->dbFile);
        }
        if (empty($content)) {
            return [];
        }
        $lines = explode(self::NEW_LINE_DELIMITER, $content);
        $entities = [];
        foreach ($lines as $line) {
            $entity = $this->mapToEntity($line, $id);
            $entities[$id] = $entity;
        }

        return $entities;
    }

    /**
     * @param array $entities
     *
     * @throws DatabaseException
     */
    private function saveFile(array $entities): void
    {
        $contentLine = [];
        foreach ($entities as $entity) {
            $contentLine[] = $this->mapEntityToLine($entity);
        }

        $content = implode(self::NEW_LINE_DELIMITER, $contentLine);
        file_put_contents($this->dbFile, $content);
    }

    /**
     * @param string $csvLine
     * @param mixed &$id
     *
     * @throws DatabaseException
     *
     * @return EntityInterface
     */
    public function mapToEntity(string $csvLine, &$id): EntityInterface
    {
        $csvElements = explode(self::CSV_DELIMITER, $csvLine);
        $elementsRequired = $this->countGetters();
        $elementsProvided = count($csvElements);
        if ($elementsRequired !== $elementsProvided) {
            throw new DatabaseException(
                sprintf(
                    'Wrong number of elements provided. Provided %s. Should be %s',
                    $elementsProvided, $elementsRequired
                )
            );
        }

        $entity = new $this->class();
        $elementNumber = 0;
        $id = $csvElements[$elementNumber];

        foreach ($this->getEntitySetters() as $method) {
            $entity->$method($csvElements[$elementNumber]);
            $elementNumber++;
        }

        return $entity;
    }

    /**
     * @param $entity
     *
     * @throws DatabaseException
     *
     * @return string
     */
    public function mapEntityToLine($entity): string
    {
        $providedEntityClass = get_class($entity);
        $requiredEntityClass = $this->class;

        if ($providedEntityClass !== $requiredEntityClass) {
            throw new DatabaseException(
                sprintf(
                    'Wrong entity provided. Provided entity is %s. Should be %s',
                    $providedEntityClass, $requiredEntityClass
                )
            );
        }

        $values = [];

        foreach ($this->getEntityGetters() as $method) {
            $values[] = $entity->$method();
        }

        return implode(self::CSV_DELIMITER, $values);
    }

    /**
     * @return array
     */
    public function getEntityGetters(): array
    {
        $getters = [];
        foreach (get_class_methods($this->class) as $method) {
            if ($this->isGetterMethod($method)) {
                $getters[] = $method;
            }
        }

        return $getters;
    }

    /**
     * @return array
     */
    public function getEntitySetters(): array
    {
        $setters = [];
        foreach (get_class_methods($this->class) as $method) {
            if ($this->isSetterMethod($method)) {
                $setters[] = $method;
            }
        }

        return $setters;
    }

    /**
     * @return int
     */
    public function getEntitiesMaxId(): int
    {
        if (!is_callable(array($this->class, 'setId'))) {
            return -1;
        }
        
        $entities = $this->readFile();
        if (empty($entities)) {
            return 1;
        }

        $maxId = 1;
        foreach ($entities as $entity) {
            if ($entity->getId() > $maxId) {
                $maxId = $entity->getId();
            }
        }
        return $maxId + 1;
    }

    /**
     * @param string $methodName
     *
     * @return bool
     */
    private function isGetterMethod(string $methodName): bool
    {
        return substr($methodName, 0, 3) === 'get';
    }

    /**
     * @param string $methodName
     *
     * @return bool
     */
    private function isSetterMethod(string $methodName): bool
    {
        return substr($methodName, 0, 3) === 'set';
    }

    /**
     * @return int
     */
    private function countGetters(): int
    {
        $count = 0;
        foreach (get_class_methods($this->class) as $method) {
            if ($this->isGetterMethod($method)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param array           $entities
     * @param EntityInterface $entity
     *
     * @return bool
     */
    private function checkDuplicates(array $entities, EntityInterface $entity): bool
    {
        foreach ($entities as $k => $v) {
            if ($k === $entity->primaryKeyVal()) {
                return false;
            }
        }
        return true;
    }
}
